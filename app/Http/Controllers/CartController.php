<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display shopping cart
     */
    public function index()
    {
        // Clear checkout session when user returns to cart
        // This allows user to checkout again if they didn't complete previous checkout
        Session::forget(['latest_purchase_ids', 'checkout_items', 'checkout_total_amount', 'checkout_course_ids', 'latest_purchase_id']);
        
        $cart = Session::get('cart', []);
        $courses = [];
        $total = 0;
        $user = auth()->user();

        foreach ($cart as $courseId) {
            $course = ClassModel::where('id', $courseId)
                ->where('is_published', true)
                ->with('teacher')
                ->withCount(['chapters', 'modules'])
                ->first();
            
            if (!$course) {
                continue;
            }

            // Skip if user is enrolled AND has successful purchase (lifetime access)
            // But keep in cart if enrolled but no purchase (subscription expired)
            if ($user && $user->isStudent()) {
                $isEnrolled = DB::table('class_student')
                    ->where('class_id', $courseId)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($isEnrolled) {
                    // Check if user has successful purchase (lifetime access)
                    $hasSuccessfulPurchase = Purchase::where('user_id', $user->id)
                        ->where('class_id', $courseId)
                        ->where('status', 'success')
                        ->exists();

                    if ($hasSuccessfulPurchase) {
                        // User has lifetime access, remove from cart
                        $cart = array_values(array_filter($cart, function($id) use ($courseId) {
                            return intval($id) !== intval($courseId);
                        }));
                        continue;
                    }
                    // If enrolled but no purchase = subscription expired
                    // Keep in cart so user can purchase the course
                }

                // JANGAN hapus cart jika ada pending purchase
                // Cart akan tetap ada sampai payment berhasil di processPayment()
                // Ini memungkinkan user kembali ke cart jika belum selesai checkout
                $hasPendingPurchase = Purchase::where('user_id', $user->id)
                    ->where('class_id', $courseId)
                    ->where('status', 'pending')
                    ->exists();

                if ($hasPendingPurchase) {
                    // Tampilkan course tapi dengan indikator bahwa ada pending purchase
                    // Cart tetap ada sampai payment berhasil
                    // continue; // Jangan skip, biarkan course tetap ditampilkan
                }
            }
            
            $courses[] = $course;
            // Use discounted price if available, otherwise course price, fallback to 150000
            $priceToUse = $course->discounted_price ?? $course->price ?? 150000;
            $total += (float) $priceToUse;
        }

        // Update cart session if any items were removed
        Session::put('cart', $cart);

        return view('cart.index', compact('courses', 'total'));
    }

    /**
     * Add course to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:classes,id'
        ]);

        $courseId = $request->input('course_id');
        
        $course = ClassModel::where('id', $courseId)
            ->where('is_published', true)
            ->firstOrFail();

        $cart = Session::get('cart', []);

        // Check if already in cart
        if (in_array($courseId, $cart)) {
            return redirect()->back()->with('info', 'Course sudah ada di keranjang.');
        }

        // Check if user already has a successful purchase (lifetime access)
        if (auth()->check() && auth()->user()->isStudent()) {
            $hasSuccessfulPurchase = Purchase::where('user_id', auth()->id())
                ->where('class_id', $courseId)
                ->where('status', 'success')
                ->exists();

            if ($hasSuccessfulPurchase) {
                return redirect()->back()->with('info', 'Anda sudah memiliki akses lifetime untuk course ini.');
            }

            // Check if there's a pending purchase for this course
            $hasPendingPurchase = Purchase::where('user_id', auth()->id())
                ->where('class_id', $courseId)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingPurchase) {
                return redirect()->back()->with('error', 'Anda sudah memiliki pembelian pending untuk course ini. Silakan tunggu persetujuan admin terlebih dahulu.');
            }

            // Check if user is enrolled but doesn't have successful purchase
            // This means enrollment is from subscription that has expired
            // Allow adding to cart so user can purchase the course
            $isEnrolled = DB::table('class_student')
                ->where('class_id', $courseId)
                ->where('user_id', auth()->id())
                ->exists();

            if ($isEnrolled && !$hasSuccessfulPurchase) {
                // User is enrolled but no purchase = subscription expired
                // Allow adding to cart to purchase the course
                // Don't return error, continue to add to cart
            }
        }

        // Add to cart
        $cart[] = $courseId;
        Session::put('cart', $cart);

        return redirect()->back()->with('success', 'Course berhasil ditambahkan ke keranjang!');
    }

    /**
     * Remove course from cart
     */
    public function remove(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:classes,id'
        ]);

        $courseId = $request->input('course_id');
        
        $cart = Session::get('cart', []);
        $cart = array_values(array_filter($cart, function($id) use ($courseId) {
            return $id != $courseId;
        }));

        Session::put('cart', $cart);

        return redirect()->route('cart')->with('success', 'Course berhasil dihapus dari keranjang.');
    }

    /**
     * Clear all items from cart
     */
    public function clear()
    {
        Session::forget('cart');
        return redirect()->route('cart')->with('success', 'Keranjang berhasil dikosongkan.');
    }

    /**
     * Get cart count (for AJAX/API)
     */
    public function count()
    {
        $cart = Session::get('cart', []);
        return response()->json(['count' => count($cart)]);
    }

    /**
     * Checkout - enroll selected courses from cart
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();
        
        if (!$user || !$user->isStudent()) {
            return redirect()->route('cart')->with('error', 'Hanya student yang bisa checkout.');
        }

        // Get selected course IDs from request
        $selectedCourseIds = $request->input('course_ids', []);

        if (empty($selectedCourseIds)) {
            return redirect()->route('cart')->with('error', 'Tidak ada course yang dipilih.');
        }

        $enrolledCount = 0;
        $alreadyEnrolled = 0;

        foreach ($selectedCourseIds as $courseId) {
            // Validate course ID
            $courseId = intval($courseId);
            
            if (!ClassModel::where('id', $courseId)->where('is_published', true)->exists()) {
                continue; // Skip if course not found or not published
            }

            // Check if already enrolled AND has successful purchase
            // If enrolled but no purchase = subscription expired, allow purchase
            $isEnrolled = DB::table('class_student')
                ->where('class_id', $courseId)
                ->where('user_id', $user->id)
                ->exists();

            $hasSuccessfulPurchase = false;
            if ($isEnrolled) {
                $hasSuccessfulPurchase = Purchase::where('user_id', $user->id)
                    ->where('class_id', $courseId)
                    ->where('status', 'success')
                    ->exists();
            }

            // Get course details
            $course = ClassModel::find($courseId);
            
            if (!$course) {
                continue; // Skip if course not found
            }

            // Only skip if enrolled AND has successful purchase (lifetime access)
            // If enrolled but no purchase = subscription expired, proceed with purchase
            if (!$isEnrolled) {
                // Enroll student (new enrollment)
                DB::table('class_student')->insert([
                    'class_id' => $courseId,
                    'user_id' => $user->id,
                    'enrolled_at' => now(),
                    'progress' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $enrolledCount++;
            } elseif ($isEnrolled && !$hasSuccessfulPurchase) {
                // Already enrolled but no purchase = subscription expired
                // Just create purchase record to give lifetime access
                // Don't create new enrollment, keep existing one
                $enrolledCount++;
            } else {
                // Already enrolled AND has successful purchase
                $alreadyEnrolled++;
                continue; // Skip this course
            }

            // Determine amount using discount if present
            $amount = $course->discounted_price ?? $course->price ?? 150000;

            // Create purchase record with actual course price
            Purchase::create([
                'purchase_code' => Purchase::generatePurchaseCode(),
                'user_id' => $user->id,
                'class_id' => $courseId,
                'amount' => $amount,
                'status' => 'success',
                'payment_method' => 'checkout', // Default payment method
                'payment_provider' => 'system',
                'paid_at' => now(),
            ]);

            $user->logActivity('purchased', "Membeli kelas: {$course->name}");
        }

        // Remove only selected courses from cart
        $cart = Session::get('cart', []);
        $cart = array_values(array_filter($cart, function($id) use ($selectedCourseIds) {
            return !in_array($id, $selectedCourseIds);
        }));
        Session::put('cart', $cart);

        $message = "Berhasil mendaftar ke {$enrolledCount} course!";
        if ($alreadyEnrolled > 0) {
            $message .= " ({$alreadyEnrolled} course sudah terdaftar sebelumnya)";
        }

        return redirect()->route('my-courses')->with('success', $message);
    }

    /**
     * Buy Now: create a pending purchase for a single course and redirect to checkout page
     */
    public function buyNow(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:classes,id'
        ]);

        if (!auth()->check() || !auth()->user()->isStudent()) {
            return redirect()->route('login');
        }

        $courseId = $request->input('course_id');

        $course = ClassModel::where('id', $courseId)->where('is_published', true)->firstOrFail();

        // Check if user already has a successful purchase (lifetime access)
        $hasSuccessfulPurchase = Purchase::where('user_id', auth()->id())
            ->where('class_id', $courseId)
            ->where('status', 'success')
            ->exists();

        if ($hasSuccessfulPurchase) {
            return redirect()->back()->with('info', 'Anda sudah memiliki akses lifetime untuk course ini.');
        }

        $amount = $course->discounted_price ?? $course->price ?? 150000;

        // Set flag to skip auto-invoice creation (invoice akan dibuat saat user klik "Bayar Sekarang")
        Session::put('skip_auto_invoice', true);

        // Pending purchase yang belum dibayar dipakai ulang, bukan ditolak.
        // Sebelumnya siswa dipentalkan dengan pesan "tunggu persetujuan admin",
        // padahal belum ada invoice apa pun untuk disetujui — akibatnya course
        // itu tidak pernah bisa dibeli lagi setelah sesi checkout hilang.
        $purchase = Purchase::where('user_id', auth()->id())
            ->where('class_id', $courseId)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if ($purchase) {
            // Invoice-nya sudah terbit: antarkan ke invoice itu — lengkap dengan
            // QRIS-nya — daripada menerbitkan nomor invoice baru yang membatalkan
            // tagihan yang mungkin sudah dibayar siswa.
            $existingInvoice = \App\Models\Invoice::where('invoiceable_id', $purchase->id)
                ->where('invoiceable_type', Purchase::class)
                ->whereIn('status', ['pending', 'paid'])
                ->latest('id')
                ->first();

            if ($existingInvoice) {
                Session::forget('skip_auto_invoice');

                return redirect()->route('invoice', $existingInvoice->id)
                    ->with('info', 'Anda sudah memiliki invoice untuk course ini. Silakan selesaikan pembayarannya.');
            }
        }

        if (!$purchase) {
            // Create a pending purchase record (no enrollment yet)
            $purchase = Purchase::create([
                'purchase_code' => Purchase::generatePurchaseCode(),
                'user_id' => auth()->id(),
                'class_id' => $courseId,
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => null,
                'payment_provider' => null,
            ]);
        }

        // JANGAN hapus cart di sini - cart akan dihapus setelah payment berhasil di processPayment()
        // Cart harus tetap ada jika user kembali ke halaman sebelumnya

        // Store latest purchase for checkout view (format konsisten dengan prepareCheckout)
        session([
            'latest_purchase_id' => $purchase->id,
            'latest_purchase_ids' => [$purchase->id], // Format array untuk konsistensi dengan processPayment
            'checkout_items' => [[
                'name' => $course->name ?? 'Course Tidak Diketahui',
                'title' => $course->name ?? 'Course Tidak Diketahui',
                'price' => $amount,
                'amount' => $amount,
                'course_id' => $courseId,
                'purchase_code' => $purchase->purchase_code,
            ]],
            'checkout_total_amount' => $amount,
            'checkout_course_ids' => [$courseId],
        ]);

        // JANGAN hapus skip_auto_invoice flag di sini - akan dihapus di processPayment()
        // Flag ini perlu tetap aktif sampai user klik "Bayar Sekarang"

        return redirect()->route('checkout');
    }

    /**
     * Show checkout/invoice page for the latest pending purchase
     */
    /**
     * Bangun ulang data checkout dari database.
     *
     * Seluruh alur checkout course bergantung pada session (latest_purchase_ids,
     * checkout_items, checkout_total_amount). Begitu session habis — tutup
     * browser, ganti perangkat, atau kembali esok hari — data itu hilang,
     * sementara baris Purchase berstatus pending tetap ada di database.
     * Akibatnya siswa terkunci: checkout menolak karena session kosong, dan
     * pembelian baru dulu ditolak karena sudah ada pending.
     *
     * Fungsi ini menyusun ulang session dari pending purchase milik siswa yang
     * belum punya invoice, sehingga alurnya bisa dilanjutkan.
     *
     * @return array<int> daftar id purchase yang dipulihkan
     */
    private function restoreCheckoutFromPendingPurchases(): array
    {
        $pendingPurchases = Purchase::with('course')
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->orderBy('id')
            ->get()
            ->filter(function ($purchase) {
                // Purchase yang invoice-nya sudah terbit menunggu verifikasi admin,
                // jadi tidak boleh ikut di-checkout ulang.
                return $purchase->course
                    && ! \App\Models\Invoice::where('invoiceable_id', $purchase->id)
                        ->where('invoiceable_type', Purchase::class)
                        ->whereIn('status', ['pending', 'paid'])
                        ->exists();
            })
            ->values();

        if ($pendingPurchases->isEmpty()) {
            return [];
        }

        $purchaseIds = [];
        $items = [];
        $courseIds = [];
        $total = 0;

        foreach ($pendingPurchases as $purchase) {
            $purchaseIds[] = $purchase->id;
            $courseIds[] = $purchase->class_id;
            $total += (float) $purchase->amount;

            $items[] = [
                'name' => $purchase->course->name ?? 'Course Tidak Diketahui',
                'title' => $purchase->course->name ?? 'Course Tidak Diketahui',
                'price' => (float) $purchase->amount,
                'amount' => (float) $purchase->amount,
                'course_id' => $purchase->class_id,
                'purchase_code' => $purchase->purchase_code,
            ];
        }

        session([
            'latest_purchase_ids' => $purchaseIds,
            'latest_purchase_id' => $purchaseIds[0],
            'checkout_items' => $items,
            'checkout_total_amount' => $total,
            'checkout_course_ids' => $courseIds,
        ]);

        return $purchaseIds;
    }

    public function showCheckout()
    {
        // Support both single purchase (from buyNow) and multiple purchases (from cart prepareCheckout)
        $purchaseIds = session('latest_purchase_ids');

        // Session checkout hilang tapi pembelian pending-nya masih ada di database.
        if (empty($purchaseIds) && ! session('latest_purchase_id')) {
            $purchaseIds = $this->restoreCheckoutFromPendingPurchases();
        }

        if ($purchaseIds && is_array($purchaseIds) && count($purchaseIds) > 0) {
            $purchases = Purchase::with([
                'course' => function($query) {
                    $query->with(['teacher', 'category'])
                          ->withCount(['chapters', 'modules', 'reviews']);
                }
            ])->whereIn('id', $purchaseIds)->get();
            if ($purchases->isEmpty()) {
                return redirect()->route('cart')->with('error', 'Pembelian tidak ditemukan.');
            }
            return view('cart.checkout', ['purchases' => $purchases]);
        }

        $purchaseId = session('latest_purchase_id') ?? request('purchase_id');

        if (!$purchaseId) {
            return redirect()->route('cart')->with('error', 'Tidak ada pembelian yang ditemukan untuk checkout.');
        }

        $purchase = Purchase::with([
            'course' => function($query) {
                $query->with(['teacher', 'category'])
                      ->withCount(['chapters', 'modules', 'reviews']);
            }
        ])->find($purchaseId);

        if (!$purchase) {
            return redirect()->route('cart')->with('error', 'Pembelian tidak ditemukan.');
        }

        return view('cart.checkout', compact('purchase'));
    }

    /**
     * Prepare checkout for multiple cart items: create pending purchases and redirect to checkout
     */
    public function prepareCheckout(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isStudent()) {
            return redirect()->route('login');
        }

        $selectedCourseIds = $request->input('course_ids', Session::get('cart', []));

        if (empty($selectedCourseIds)) {
            return redirect()->route('cart')->with('error', 'Tidak ada course yang dipilih.');
        }

        // Set flag to skip auto-invoice creation for individual purchases
        // We'll create a single combined invoice instead saat user klik "Bayar Sekarang"
        Session::put('skip_auto_invoice', true);

        $purchaseIds = [];
        $purchases = [];
        $totalAmount = 0;
        $items = [];

        foreach ($selectedCourseIds as $courseId) {
            $courseId = intval($courseId);
            $course = ClassModel::where('id', $courseId)->where('is_published', true)->first();
            if (!$course) continue;

            // Skip if already enrolled AND has successful purchase (lifetime access)
            // But allow checkout if enrolled but no purchase (subscription expired)
            $isEnrolled = DB::table('class_student')
                ->where('class_id', $courseId)
                ->where('user_id', auth()->id())
                ->exists();

            if ($isEnrolled) {
                // Check if user has successful purchase (lifetime access)
                $hasSuccessfulPurchase = Purchase::where('user_id', auth()->id())
                    ->where('class_id', $courseId)
                    ->where('status', 'success')
                    ->exists();

                if ($hasSuccessfulPurchase) {
                    // User has lifetime access, skip
                    continue;
                }
                // If enrolled but no purchase = subscription expired
                // Allow checkout so user can purchase the course
            }

            $amount = $course->discounted_price ?? $course->price ?? 150000;

            // Check if there's a pending purchase for this course
            $existingPendingPurchase = Purchase::where('user_id', auth()->id())
                ->where('class_id', $courseId)
                ->where('status', 'pending')
                ->first();

            if ($existingPendingPurchase) {
                // Use existing pending purchase instead of creating new one
                // We'll include it in the combined invoice regardless of existing individual invoices
                $purchase = $existingPendingPurchase;
            } else {
                // Create new pending purchase
                $purchase = Purchase::create([
                    'purchase_code' => Purchase::generatePurchaseCode(),
                    'user_id' => auth()->id(),
                    'class_id' => $courseId,
                    'amount' => $amount,
                    'status' => 'pending',
                    'payment_method' => null,
                    'payment_provider' => null,
                ]);
            }

            $purchaseIds[] = $purchase->id;
            $purchases[] = $purchase;
            $totalAmount += $amount;
            
            // Prepare items for invoice metadata
            $items[] = [
                'name' => $course->name ?? 'Course Tidak Diketahui',
                'title' => $course->name ?? 'Course Tidak Diketahui',
                'price' => $amount,
                'amount' => $amount,
                'course_id' => $courseId,
                'purchase_code' => $purchase->purchase_code,
            ];
        }

        // JANGAN hapus skip_auto_invoice flag di sini - akan dihapus di processPayment()
        // Flag ini perlu tetap aktif sampai user klik "Bayar Sekarang" untuk mencegah auto-create invoice

        if (empty($purchaseIds)) {
            // Hapus flag jika tidak ada purchase yang dibuat
            Session::forget('skip_auto_invoice');
            return redirect()->route('cart')->with('info', 'Tidak ada pembelian baru yang perlu diproses (mungkin semua sudah terdaftar).');
        }

        // JANGAN buat invoice di sini - invoice akan dibuat saat user klik "Bayar Sekarang"
        // JANGAN kosongkan cart di sini - cart akan dikosongkan setelah pembayaran berhasil di processPayment()

        // Simpan course IDs yang dipilih untuk nanti (jika user kembali, cart masih ada)
        session(['checkout_course_ids' => $selectedCourseIds]);
        
        // Store purchase ids, items, dan total amount untuk checkout view
        session([
            'latest_purchase_ids' => $purchaseIds,
            'checkout_items' => $items,
            'checkout_total_amount' => $totalAmount,
        ]);

        return redirect()->route('checkout');
    }

    /**
     * Process payment: create invoice, send email, and clear cart
     * Called when user clicks "Bayar Sekarang" button after selecting payment method
     */
    public function processPayment(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isStudent()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_method' => 'required|string',
            'payment_provider' => 'nullable|string',
        ]);

        $user = auth()->user();
        $paymentMethod = $request->input('payment_method');
        $paymentProvider = $request->input('payment_provider', 'manual');

        // Get purchase IDs from session
        $purchaseIds = session('latest_purchase_ids');

        // Session checkout bisa habis di antara membuka halaman dan menekan
        // "Bayar Sekarang". Pulihkan dari pending purchase milik siswa sendiri
        // agar pembayaran tidak gagal hanya karena session kedaluwarsa.
        if (empty($purchaseIds) || !is_array($purchaseIds)) {
            $purchaseIds = $this->restoreCheckoutFromPendingPurchases();
        }

        $items = session('checkout_items', []);
        $totalAmount = session('checkout_total_amount', 0);
        $selectedCourseIds = session('checkout_course_ids', []);

        if (empty($purchaseIds) || !is_array($purchaseIds)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada pembelian yang ditemukan. Silakan ulangi proses checkout.'], 400);
        }

        // Use DB::transaction for atomic operation
        return DB::transaction(function () use ($user, $paymentMethod, $paymentProvider, $purchaseIds, $items, $totalAmount, $selectedCourseIds) {
            try {
                // Get purchases with lock to prevent race conditions
                $purchases = Purchase::where('user_id', $user->id)
                    ->whereIn('id', $purchaseIds)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->get();

                if ($purchases->isEmpty()) {
                    throw new \Exception('Pembelian tidak ditemukan atau sudah diproses.');
                }

                // Verify all purchase IDs are valid
                $validPurchaseIds = $purchases->pluck('id')->toArray();
                if (count($validPurchaseIds) !== count($purchaseIds)) {
                    throw new \Exception('Beberapa pembelian tidak valid atau sudah diproses.');
                }

                // Set flag to skip auto-invoice creation (kita akan buat manual)
                Session::put('skip_auto_invoice', true);

                // Update payment method di semua purchases
                foreach ($purchases as $purchase) {
                    $purchase->update([
                        'payment_method' => $paymentMethod,
                        'payment_provider' => $paymentProvider,
                    ]);
                    
                    // Delete any existing invoices for this purchase to avoid duplicate invoices
                    $existingInvoices = \App\Models\Invoice::where('invoiceable_id', $purchase->id)
                        ->where('invoiceable_type', Purchase::class)
                        ->where('status', 'pending')
                        ->get();
                    
                    foreach ($existingInvoices as $existingInvoice) {
                        // Invoice items will be automatically deleted due to cascade delete
                        $existingInvoice->delete();
                    }
                }

                // Prepare invoice items data with proper purchase_id mapping
                $invoiceItemsData = [];
                foreach ($items as $index => $item) {
                    $purchaseId = $purchaseIds[$index] ?? null;
                    if ($purchaseId && in_array($purchaseId, $validPurchaseIds)) {
                        $invoiceItemsData[] = [
                            'purchase_id' => $purchaseId,
                            'course_id' => $item['course_id'] ?? null,
                            'name' => $item['name'] ?? $item['title'] ?? 'Course Item',
                            'description' => $item['description'] ?? null,
                            'amount' => $item['amount'] ?? $item['price'] ?? 0,
                            'total_amount' => $item['total_amount'] ?? $item['amount'] ?? $item['price'] ?? 0,
                            'currency' => 'IDR',
                            'metadata' => [
                                'purchase_code' => $item['purchase_code'] ?? null,
                                'course_id' => $item['course_id'] ?? null,
                            ],
                        ];
                    }
                }

                // ALWAYS create single invoice for all purchases (regardless of count)
                $firstPurchase = $purchases->first();
                $invoice = \App\Models\Invoice::create([
                    'user_id' => $user->id,
                    'type' => 'course',
                    'invoiceable_id' => $firstPurchase->id,
                    'invoiceable_type' => Purchase::class,
                    'amount' => $totalAmount,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'currency' => 'IDR',
                    'status' => 'pending',
                    'payment_method' => $paymentMethod,
                    'payment_provider' => $paymentProvider,
                    'metadata' => [
                        'items' => $items,
                        'purchase_ids' => $purchaseIds,
                        'purchase_codes' => array_column($items, 'purchase_code'),
                        'is_multiple_courses' => count($purchaseIds) > 1,
                        'course_count' => count($items),
                    ],
                ]);

                // Create invoice items for all courses
                $invoice->createInvoiceItems($invoiceItemsData);

                // Remove skip flag
                Session::forget('skip_auto_invoice');

                // Remove courses from cart (hanya setelah invoice dibuat dan semua item tersimpan)
                if (!empty($selectedCourseIds)) {
                    $cart = Session::get('cart', []);
                    $cart = array_values(array_filter($cart, function($id) use ($selectedCourseIds) {
                        return !in_array(intval($id), array_map('intval', $selectedCourseIds));
                    }));
                    Session::put('cart', $cart);
                }

                // Clear checkout session data
                Session::forget(['latest_purchase_ids', 'checkout_items', 'checkout_total_amount', 'checkout_course_ids']);

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice pembayaran telah dikirim ke email Anda.',
                    'invoice_number' => $invoice->invoice_number,
                    'items_count' => count($invoiceItemsData),

                    // Rincian invoice + QRIS agar modal pembayaran bisa langsung
                    // ditampilkan tanpa permintaan susulan.
                    'payment' => $this->paymentPayload($invoice, $purchases, $items),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Payment processing failed', [
                    'user_id' => $user->id,
                    'purchase_ids' => $purchaseIds,
                    'error' => $e->getMessage(),
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage()
                ], 500);
            }
        });
    }

    /**
     * Rincian yang dibutuhkan modal pembayaran QRIS pada checkout course.
     *
     * Bentuknya sengaja disamakan dengan SubscriptionController::paymentPayload
     * supaya satu partial + satu berkas JS melayani kedua alur checkout.
     *
     * QRIS-nya adalah QR merchant statis yang sama dengan yang dipakai email
     * invoice dan PDF — bukan QR dinamis dari payment gateway.
     */
    private function paymentPayload(\App\Models\Invoice $invoice, $purchases, array $items): array
    {
        $qrisPath = config('app.payment.qris_image_path');
        $total = (float) $invoice->total_amount;
        $codes = $purchases->pluck('purchase_code')->filter()->implode(', ');

        $lines = [];
        foreach ($items as $item) {
            $amount = (float) ($item['amount'] ?? $item['price'] ?? 0);
            $lines[] = [
                'name' => $item['name'] ?? $item['title'] ?? 'Course',
                'amount' => 'Rp' . number_format($amount, 0, ',', '.'),
            ];
        }

        $confirmText = 'Halo MersifLab, saya ingin konfirmasi pembayaran untuk invoice '
            . $invoice->invoice_number . ' sebesar Rp' . number_format($total, 0, ',', '.');

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_url' => route('invoice', $invoice->id),
            'reference' => $codes !== '' ? $codes : $invoice->invoice_number,
            'items' => $lines,
            'created_at' => $invoice->created_at->format('d M Y, H:i') . ' WIB',
            'due_date' => $invoice->due_date ? $invoice->due_date->format('d M Y, H:i') . ' WIB' : null,
            'subtotal' => 'Rp' . number_format($total, 0, ',', '.'),
            'discount' => 'Rp0',
            'has_discount' => false,
            'total' => 'Rp' . number_format($total, 0, ',', '.'),
            'qris_url' => $qrisPath && file_exists(public_path($qrisPath)) ? asset($qrisPath) : null,
            'whatsapp_url' => 'https://wa.me/' . config('app.payment.whatsapp_number')
                . '?text=' . rawurlencode($confirmText),
        ];
    }

    /**
     * Cancel pending purchase
     */
    public function cancelPendingPurchase(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isStudent()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'course_id' => 'required|exists:classes,id'
        ]);

        $courseId = $request->input('course_id');
        $userId = auth()->id();

        // Find pending purchase without invoice for this course and user
        $pendingPurchase = Purchase::where('user_id', $userId)
            ->where('class_id', $courseId)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(24)) // Only recent purchases
            ->first();

        // Check if there's an invoice for this purchase
        if ($pendingPurchase) {
            $hasInvoice = \App\Models\Invoice::where('invoiceable_id', $pendingPurchase->id)
                ->where('invoiceable_type', Purchase::class)
                ->exists();
            
            if ($hasInvoice) {
                return response()->json(['success' => false, 'message' => 'No cancellable pending purchase found.']);
            }
        }

        if (!$pendingPurchase) {
            return response()->json(['success' => false, 'message' => 'No cancellable pending purchase found.']);
        }

        // Delete the pending purchase
        $pendingPurchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pending purchase cancelled successfully.'
        ]);
    }
}
