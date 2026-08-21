<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationPreference;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'biography' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        } else {
            // Keep existing avatar if no new file uploaded
            unset($validated['avatar']);
        }
        
        $user->update($validated);
        
        return redirect()->route('profile')->with('success', 'Profile updated successfully');
    }

    public function myCourses()
    {
        $user = auth()->user();
        
        // If user is a teacher, redirect to teacher courses
        if ($user->isTeacher()) {
            return redirect()->route('teacher.courses');
        }
        
        // Get purchased course IDs (courses yang sudah dibeli - selalu muncul di My Courses)
        $purchasedCourseIds = Purchase::where('user_id', $user->id)
            ->where('status', 'success')
            ->orderByDesc('paid_at')
            ->pluck('class_id')
            ->unique()
            ->values();

        // Get course IDs yang sudah memiliki minimal 1 completed module (untuk subscription courses)
        // Course hanya muncul di My Courses setelah user mark as complete minimal 1 module
        $coursesWithCompletedModules = \Illuminate\Support\Facades\DB::table('module_completions')
            ->where('user_id', $user->id)
            ->pluck('class_id')
            ->unique()
            ->values();

        // Combine: purchased courses (selalu muncul) + courses dengan completed modules (sudah mark as complete)
        $allCourseIds = $purchasedCourseIds->merge($coursesWithCompletedModules)->unique()->values();

        // Get courses with progress tracking
        $courses = \App\Models\ClassModel::whereIn('id', $allCourseIds)
            ->with('teacher')
            ->withCount(['chapters', 'modules'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Add progress data for each course
        foreach ($courses as $course) {
            $enrollment = \Illuminate\Support\Facades\DB::table('class_student')
                ->where('class_id', $course->id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($enrollment) {
                // Count completed modules
                $completedModules = \Illuminate\Support\Facades\DB::table('module_completions')
                    ->where('class_id', $course->id)
                    ->where('user_id', $user->id)
                    ->count();
                
                // Progress hanya dihitung jika sudah ada completed modules
                if ($completedModules > 0) {
                    $course->progress = $enrollment->progress ?? 0;
                } else {
                    $course->progress = 0;
                }
                
                $course->completed_modules = $completedModules;
                $course->enrolled_at = $enrollment->enrolled_at ?? null;
                
                // Check if user has purchased this course (lifetime access) or only via subscription
                $hasPurchase = $purchasedCourseIds->contains($course->id);
                $course->has_lifetime_access = $hasPurchase;
                
                // Check if subscription is still active (if accessed via subscription)
                if (!$hasPurchase && $user->hasActiveSubscription()) {
                    $course->has_subscription_access = $user->canAccessViaPlanTier($course->price_tier ?? 'standard');
                } else {
                    $course->has_subscription_access = false;
                }
            }
        }
            
        return view('profile.my-courses', compact('courses'));
    }

    public function purchaseHistory()
    {
        $user = auth()->user();
        
        // Sync purchases dengan enrollments yang sudah ada (hanya yang bukan dari subscription)
        $this->syncPurchasesForUser($user);
        
        // Get all invoices for this user (course type only)
        $invoices = \App\Models\Invoice::where('user_id', $user->id)
            ->where('type', 'course')
            ->with(['invoiceItems.course', 'invoiceItems.purchase'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all purchases that don't have invoices (direct purchases without checkout)
        $purchasesWithoutInvoices = Purchase::where('user_id', $user->id)
            ->where('status', 'success')
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('invoiceable_id')
                    ->from('invoices')
                    ->where('invoiceable_type', \App\Models\Purchase::class)
                    ->where('user_id', $user->id);
            })
            ->with('course.teacher')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filter out purchases that are from subscription
        $purchasesWithoutInvoices = $purchasesWithoutInvoices->filter(function($purchase) use ($user) {
            // Skip purchase yang jelas dari subscription sync
            if ($purchase->payment_method === 'enrollment' && $purchase->payment_provider === 'system') {
                // Check if enrollment was created when user had subscription
                $enrollment = \Illuminate\Support\Facades\DB::table('class_student')
                    ->where('user_id', $user->id)
                    ->where('class_id', $purchase->class_id)
                    ->first();
                
                if ($enrollment) {
                    $enrollmentDate = $enrollment->enrolled_at ?? $enrollment->created_at;
                    
                    // Check if purchase was created after enrollment (likely from sync)
                    if ($purchase->created_at >= $enrollmentDate) {
                        // Check if user had subscription when enrollment was created
                        $activeSubscriptionAtEnrollment = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                            ->where('status', 'success')
                            ->where(function($query) use ($enrollmentDate) {
                                $query->where(function($q) use ($enrollmentDate) {
                                    // Subscription paid/started before or at enrollment
                                    $q->where('paid_at', '<=', $enrollmentDate)
                                      ->orWhereNull('paid_at');
                                })
                                ->where(function($q) use ($enrollmentDate) {
                                    // Subscription expires after enrollment (or never expires)
                                    $q->whereNull('expires_at')
                                      ->orWhere('expires_at', '>', $enrollmentDate);
                                });
                            })
                            ->exists();
                        
                        if ($activeSubscriptionAtEnrollment) {
                            return false; // Skip purchase from subscription
                        }
                    }
                }
            }
            
            // Include all other purchases (real purchases)
            return true;
        })->values();
        
        // Combine invoices and direct purchases
        $allTransactions = collect();
        
        // Add invoice-based transactions (may contain multiple courses)
        foreach ($invoices as $invoice) {
            // Get courses from invoice items
            $courses = [];
            $totalAmount = 0;
            
            if ($invoice->invoiceItems->isNotEmpty()) {
                foreach ($invoice->invoiceItems as $item) {
                    if ($item->course) {
                        $courses[] = $item->course;
                        $totalAmount += $item->total_amount;
                    }
                }
            } else {
                // Fallback to metadata if no invoice items
                if (isset($invoice->metadata['purchase_ids']) && is_array($invoice->metadata['purchase_ids'])) {
                    $purchases = Purchase::whereIn('id', $invoice->metadata['purchase_ids'])
                        ->with('course')
                        ->get();
                    
                    foreach ($purchases as $purchase) {
                        if ($purchase->course) {
                            $courses[] = $purchase->course;
                            $totalAmount += $purchase->amount;
                        }
                    }
                }
            }
            
            $allTransactions->push([
                'id' => $invoice->id,
                'type' => 'course',
                'purchase_code' => $invoice->invoice_number,
                'status' => $invoice->status === 'paid' ? 'success' : $invoice->status,
                'status_badge' => $invoice->status_badge,
                'amount' => $totalAmount > 0 ? $totalAmount : $invoice->total_amount,
                'payment_method' => $invoice->payment_method,
                'payment_provider' => $invoice->payment_provider,
                'paid_at' => $invoice->paid_at,
                'created_at' => $invoice->created_at,
                'due_date' => $invoice->due_date,
                'courses' => $courses, // Multiple courses
                'course' => $courses[0] ?? null, // First course for compatibility
                'invoice' => $invoice,
            ]);
        }
        
        // Add direct purchases (single course purchases without invoices)
        foreach ($purchasesWithoutInvoices as $purchase) {
            $allTransactions->push([
                'id' => $purchase->id,
                'type' => 'course',
                'purchase_code' => $purchase->purchase_code,
                'status' => $purchase->status,
                'status_badge' => $purchase->status_badge,
                'amount' => $purchase->amount,
                'payment_method' => $purchase->payment_method,
                'payment_provider' => $purchase->payment_provider,
                'paid_at' => $purchase->paid_at,
                'created_at' => $purchase->created_at,
                'due_date' => null,
                'courses' => [$purchase->course], // Single course
                'course' => $purchase->course,
                'purchase' => $purchase,
            ]);
        }
        
        // Get subscription purchases
        $subscriptionPurchases = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Add subscription purchases
        foreach ($subscriptionPurchases as $subscriptionPurchase) {
            // Get invoice for due date
            $invoice = \App\Models\Invoice::where('invoiceable_id', $subscriptionPurchase->id)
                ->where('invoiceable_type', \App\Models\SubscriptionPurchase::class)
                ->first();
            
            $allTransactions->push([
                'id' => $subscriptionPurchase->id,
                'type' => 'subscription',
                'purchase_code' => $subscriptionPurchase->purchase_code,
                'status' => $subscriptionPurchase->status,
                'status_badge' => $subscriptionPurchase->status_badge,
                'amount' => $subscriptionPurchase->final_amount ?? $subscriptionPurchase->amount,
                'payment_method' => $subscriptionPurchase->payment_method,
                'payment_provider' => $subscriptionPurchase->payment_provider,
                'paid_at' => $subscriptionPurchase->paid_at,
                'created_at' => $subscriptionPurchase->created_at,
                'due_date' => $invoice ? $invoice->due_date : null,
                'plan' => $subscriptionPurchase->plan,
                'expires_at' => $subscriptionPurchase->expires_at,
                'subscription_purchase' => $subscriptionPurchase,
            ]);
        }
        
        // Sort by created_at descending
        $allTransactions = $allTransactions->sortByDesc('created_at')->values();
        
        return view('profile.purchase-history', compact('allTransactions'));
    }

    /**
     * Sync purchase records dengan enrollments yang sudah ada
     * Menambahkan purchase record untuk enrollment yang belum punya purchase
     * HANYA untuk enrollment yang BUKAN dari subscription
     */
    private function syncPurchasesForUser($user)
    {
        // Get all enrollments for this user
        $enrollments = \Illuminate\Support\Facades\DB::table('class_student')
            ->where('user_id', $user->id)
            ->get();

        foreach ($enrollments as $enrollment) {
            // Check if purchase already exists for this enrollment
            $existingPurchase = Purchase::where('user_id', $user->id)
                ->where('class_id', $enrollment->class_id)
                ->where('status', 'success')
                ->first();

            // If no purchase exists, check if enrollment is from subscription
            if (!$existingPurchase) {
                $enrollmentDate = $enrollment->enrolled_at ?? $enrollment->created_at;
                
                // Check if user had active subscription when enrollment was created
                $hadActiveSubscription = false;
                if ($enrollmentDate) {
                    // Get subscription purchase that was active at enrollment time
                    $activeSubscriptionAtEnrollment = \App\Models\SubscriptionPurchase::where('user_id', $user->id)
                        ->where('status', 'success')
                        ->where(function($query) use ($enrollmentDate) {
                            $query->where(function($q) use ($enrollmentDate) {
                                // Subscription paid/started before or at enrollment
                                $q->where('paid_at', '<=', $enrollmentDate)
                                  ->orWhereNull('paid_at');
                            })
                            ->where(function($q) use ($enrollmentDate) {
                                // Subscription expires after enrollment (or never expires)
                                $q->whereNull('expires_at')
                                  ->orWhere('expires_at', '>', $enrollmentDate);
                            });
                        })
                        ->exists();
                    
                    // Also check if user had subscription in users table at enrollment time
                    // We can't check historical data, so we check if enrollment was created
                    // when user currently has subscription AND there's no purchase record
                    // This means it's likely from subscription
                    if (!$activeSubscriptionAtEnrollment) {
                        // Check if enrollment date is recent and user currently has subscription
                        // and there's no purchase, it's likely from subscription
                        $daysSinceEnrollment = now()->diffInDays($enrollmentDate);
                        if ($daysSinceEnrollment <= 365 && $user->hasActiveSubscription()) {
                            // Check if there's any purchase created AFTER enrollment
                            // If no purchase exists and user has subscription, likely from subscription
                            $purchaseAfterEnrollment = Purchase::where('user_id', $user->id)
                                ->where('class_id', $enrollment->class_id)
                                ->where('created_at', '>', $enrollmentDate)
                                ->exists();
                            
                            if (!$purchaseAfterEnrollment) {
                                $hadActiveSubscription = true;
                            }
                        }
                    } else {
                        $hadActiveSubscription = true;
                    }
                }
                
                // Only create purchase if enrollment is NOT from subscription
                if (!$hadActiveSubscription) {
                    $class = \App\Models\ClassModel::find($enrollment->class_id);
                    
                    if ($class) {
                        Purchase::create([
                            'purchase_code' => Purchase::generatePurchaseCode(),
                            'user_id' => $user->id,
                            'class_id' => $enrollment->class_id,
                            'amount' => $class->price ?? 0,
                            'status' => 'success',
                            'payment_method' => 'enrollment',
                            'payment_provider' => 'system',
                            'paid_at' => $enrollmentDate ?? now(),
                            'created_at' => $enrollment->created_at ?? now(),
                            'updated_at' => $enrollment->updated_at ?? now(),
                        ]);
                    }
                }
            }
        }
    }

    public function invoice($id)
    {
        $user = auth()->user();

        // Purchase History menautkan /invoice/{subscription_purchase_id}?type=subscription.
        // Tanpa membaca ?type, id itu dicari lebih dulu di tabel invoices, sehingga
        // siswa bisa mendapat invoice course milik id yang sama — atau 403.
        if (request()->query('type') === 'subscription') {
            $subscription = \App\Models\SubscriptionPurchase::find($id);

            if ($subscription) {
                if ($user->isStudent() && $subscription->user_id !== $user->id) {
                    abort(403, 'Anda tidak memiliki akses ke invoice ini.');
                }

                $invoice = \App\Models\Invoice::where('invoiceable_id', $subscription->id)
                    ->where('invoiceable_type', \App\Models\SubscriptionPurchase::class)
                    ->first();

                return view('profile.invoice-subscription', compact('subscription', 'invoice'));
            }
        }

        // First try to find as invoice (new approach for multiple course invoices)
        $invoice = \App\Models\Invoice::where('id', $id)
            ->with(['invoiceItems.course', 'invoiceItems.purchase'])
            ->first();

        // If invoice found, validate access and return invoice view
        if ($invoice) {
            // Check access permission
            if ($user->isStudent()) {
                if ($invoice->user_id !== $user->id) {
                    abort(403, 'Anda tidak memiliki akses ke invoice ini.');
                }
            } elseif ($user->isTeacher()) {
                // For course invoices, check if teacher owns any of the courses
                if ($invoice->type === 'course') {
                    $hasAccess = false;
                    if ($invoice->invoiceItems->isNotEmpty()) {
                        foreach ($invoice->invoiceItems as $item) {
                            if ($item->course && $item->course->teacher_id === $user->id) {
                                $hasAccess = true;
                                break;
                            }
                        }
                    }
                    if (!$hasAccess) {
                        abort(403, 'Anda tidak memiliki akses ke invoice ini.');
                    }
                } else {
                    abort(403, 'Anda tidak memiliki akses ke invoice ini.');
                }
            } else {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
            
            // Auto-expire if overdue
            if ($invoice->status === 'pending' && $invoice->due_date && $invoice->due_date->isPast()) {
                try {
                    $invoice->expire();
                } catch (\Exception $e) {
                    // ignore
                }
            }
            
            // Check if invoice is expired and block access
            if ($invoice->status === 'expired') {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice telah kadaluarsa dan tidak dapat diakses lagi.',
                        'type' => 'error'
                    ], 403);
                } else {
                    return redirect()->back()
                        ->with('error', 'Invoice telah kadaluarsa dan tidak dapat diakses lagi.');
                }
            }
            
            // Check if invoice is cancelled and block access
            if ($invoice->status === 'cancelled') {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice telah dibatalkan oleh admin dan tidak dapat diakses lagi.',
                        'type' => 'error'
                    ], 403);
                } else {
                    return redirect()->back()
                        ->with('error', 'Invoice telah dibatalkan oleh admin dan tidak dapat diakses lagi.');
                }
            }
            
            // Invoice subscription tidak punya invoice_items sama sekali, sedangkan
            // profile.invoice ditulis khusus untuk pembelian course dan langsung
            // membaca $invoice->invoiceItems->first()->course. Untuk subscription
            // pemanggilan itu jatuh ke null dan halaman gagal dirender, sehingga
            // siswa tidak pernah melihat invoice maupun QRIS-nya.
            if ($invoice->type === 'subscription') {
                $subscription = $invoice->invoiceable_type === \App\Models\SubscriptionPurchase::class
                    ? \App\Models\SubscriptionPurchase::find($invoice->invoiceable_id)
                    : null;

                if ($subscription) {
                    return view('profile.invoice-subscription', compact('subscription', 'invoice'));
                }
            }

            return view('profile.invoice', compact('invoice'));
        }

        // Fallback: Try to find as course purchase (backward compatibility)
        $purchase = Purchase::where('id', $id)
            ->with('course.teacher', 'user')
            ->first();
        
        // Try to find as subscription purchase if not found as course purchase
        $subscriptionPurchase = null;
        if (!$purchase) {
            $subscriptionPurchase = \App\Models\SubscriptionPurchase::where('id', $id)
                ->with('user')
                ->first();
        }
        
        // Check if we have either purchase or subscription purchase
        if (!$purchase && !$subscriptionPurchase) {
            abort(404, 'Invoice tidak ditemukan.');
        }
        
        // Check access permission for legacy purchases
        if ($user->isStudent()) {
            // Student hanya bisa melihat invoice mereka sendiri
            if ($purchase && $purchase->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
            if ($subscriptionPurchase && $subscriptionPurchase->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
        } elseif ($user->isTeacher()) {
            // Teacher hanya bisa melihat invoice dari purchases yang terkait dengan courses mereka
            if ($purchase && $purchase->course->teacher_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
            if ($subscriptionPurchase) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
        } else {
            // Admin atau role lain
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }
        
        // If we have a purchase, check for a related invoice and auto-expire if overdue
        if ($purchase) {
            $invoice = \App\Models\Invoice::with(['invoiceItems.course', 'invoiceItems.purchase'])
                ->where('invoiceable_type', \App\Models\Purchase::class)
                ->where('invoiceable_id', $purchase->id)
                ->orderByDesc('created_at')
                ->first();

            if ($invoice && $invoice->status === 'pending' && $invoice->due_date && $invoice->due_date->isPast()) {
                // Expire invoice and mark purchase as expired
                try {
                    $invoice->expire();
                } catch (\Exception $e) {
                    // ignore
                }

                try {
                    if ($purchase->status !== 'success') {
                        $purchase->update(['status' => 'expired']);
                    }
                } catch (\Exception $e) {
                    // ignore
                }
            }

            // Check if invoice is expired and block access
            if ($invoice && $invoice->status === 'expired') {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice telah kadaluarsa dan tidak dapat diakses lagi.',
                        'type' => 'error'
                    ], 403);
                } else {
                    // For non-ajax requests, redirect back with flash message
                    return redirect()->back()
                        ->with('error', 'Invoice telah kadaluarsa dan tidak dapat diakses lagi.');
                }
            }

            // Check if invoice is cancelled and block access
            if ($invoice && $invoice->status === 'cancelled') {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice telah dibatalkan oleh admin dan tidak dapat diakses lagi.',
                        'type' => 'error'
                    ], 403);
                } else {
                    // For non-ajax requests, redirect back with flash message
                    return redirect()->back()
                        ->with('error', 'Invoice telah dibatalkan oleh admin dan tidak dapat diakses lagi.');
                }
            }

            return view('profile.invoice', compact('purchase', 'invoice'));
        } else {
            // Ensure subscription has expires_at set for display (1 month duration)
            if ($subscriptionPurchase && !$subscriptionPurchase->expires_at) {
                try {
                    $expires = $subscriptionPurchase->created_at ? $subscriptionPurchase->created_at->copy()->addMonth() : now()->addMonth();
                    $subscriptionPurchase->update(['expires_at' => $expires]);
                    // refresh model
                    $subscriptionPurchase->refresh();
                } catch (\Exception $e) {
                    // ignore failures
                }
            }

            return view('profile.invoice-subscription', ['subscription' => $subscriptionPurchase]);
        }
    }

    public function downloadInvoice($id)
    {
        $user = auth()->user();

        // Halaman invoice subscription mengirim ?type=subscription. Tanpa
        // membacanya, id yang kebetulan sama dengan id sebuah Purchase akan
        // mengunduh invoice course — dokumen yang salah.
        $wantsSubscription = request()->query('type') === 'subscription';

        // Try to find as course purchase first
        $purchase = $wantsSubscription ? null : Purchase::where('id', $id)
            ->with('course.teacher', 'user')
            ->first();

        // Try to find as subscription purchase if not found as course purchase
        $subscriptionPurchase = null;
        if (!$purchase) {
            $subscriptionPurchase = \App\Models\SubscriptionPurchase::where('id', $id)
                ->with('user')
                ->first();
        }
        
        // Check if we have either purchase or subscription purchase
        if (!$purchase && !$subscriptionPurchase) {
            abort(404, 'Invoice tidak ditemukan.');
        }
        
        // Check access permission
        if ($user->isStudent()) {
            // Student hanya bisa download invoice mereka sendiri
            if ($purchase && $purchase->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
            if ($subscriptionPurchase && $subscriptionPurchase->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
        } elseif ($user->isTeacher()) {
            // Teacher hanya bisa download invoice dari purchases yang terkait dengan courses mereka
            if ($purchase && $purchase->course->teacher_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
            if ($subscriptionPurchase) {
                abort(403, 'Anda tidak memiliki akses ke invoice ini.');
            }
        } else {
            // Admin atau role lain
            abort(403, 'Anda tidak memiliki akses ke invoice ini.');
        }
        
        // Generate PDF
        if ($purchase) {
            // Get invoice data for PDF
            $invoice = \App\Models\Invoice::with(['invoiceItems.course', 'invoiceItems.purchase'])
                ->where('invoiceable_type', \App\Models\Purchase::class)
                ->where('invoiceable_id', $purchase->id)
                ->orderByDesc('created_at')
                ->first();
            
            // Check if invoice is expired and block download
            if ($invoice && $invoice->status === 'expired') {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice telah kadaluarsa dan tidak dapat diunduh lagi.',
                        'type' => 'error'
                    ], 403);
                } else {
                    // For non-ajax requests, redirect back with flash message
                    return redirect()->back()
                        ->with('error', 'Invoice telah kadaluarsa dan tidak dapat diunduh lagi.');
                }
            }

            // Check if invoice is cancelled and block download
            if ($invoice && $invoice->status === 'cancelled') {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invoice telah dibatalkan oleh admin dan tidak dapat diunduh lagi.',
                        'type' => 'error'
                    ], 403);
                } else {
                    // For non-ajax requests, redirect back with flash message
                    return redirect()->back()
                        ->with('error', 'Invoice telah dibatalkan oleh admin dan tidak dapat diunduh lagi.');
                }
            }
                
            $pdf = Pdf::loadView('profile.invoice-pdf', compact('purchase', 'invoice'));
            $filename = 'Invoice-' . $purchase->purchase_code . '.pdf';
        } else {
            $pdf = Pdf::loadView('profile.invoice-subscription-pdf', ['subscription' => $subscriptionPurchase]);
            $filename = 'Invoice-Subscription-' . $subscriptionPurchase->purchase_code . '.pdf';
        }
        
        // Download PDF
        return $pdf->download($filename);
    }

    public function getInvoiceByNumber($invoiceNumber)
    {
        try {
            $user = auth()->user();
            
            // Find invoice by number
            $invoice = \App\Models\Invoice::where('invoice_number', $invoiceNumber)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$invoice) {
                return response()->json(['success' => false, 'message' => 'Invoice tidak ditemukan.'], 404);
            }
            
            return response()->json([
                'success' => true,
                'invoice' => [
                    'id' => $invoice->invoiceable_id, // Return purchase ID for redirect
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $invoice->type,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.'], 500);
        }
    }

    public function notificationPreferences()
    {
        $user = auth()->user();
        $preferences = $user->getNotificationPreference();
        
        return view('profile.notification-preferences', compact('preferences'));
    }

    public function updateNotificationPreferences(Request $request)
    {
        $user = auth()->user();
        
        // Define all possible notification preference fields
        $allFields = [
            'new_course',
            'new_chapter',
            'new_module',
            'module_approved',
            'student_enrolled',
            'course_rated',
            'course_completed',
            'announcements',
            'promotions',
            'course_recommendations',
            'learning_stats',
        ];

        // Build preferences data - set to true if checkbox is checked, false if unchecked
        $preferencesData = [];
        foreach ($allFields as $field) {
            // Checkbox yang checked akan mengirim value '1' (karena kita set value="1")
            // Checkbox yang unchecked tidak akan dikirim sama sekali, jadi kita set false
            $preferencesData[$field] = $request->has($field) && ($request->input($field) === '1' || $request->input($field) === 1 || $request->input($field) === true || $request->input($field) === 'on');
        }

        // Get or create notification preference
        $preference = $user->getNotificationPreference();
        $preference->update($preferencesData);

        return redirect()->route('notification-preferences')->with('success', 'Notification preferences updated successfully');
    }

    /**
     * Upload avatar via AJAX
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        
        try {
            // Delete old avatar if exists
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $avatarPath]);
            
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload',
                'avatar_url' => \Illuminate\Support\Facades\Storage::url($avatarPath)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto profil: ' . $e->getMessage()
            ], 500);
        }
    }
}
