@extends('layouts.admin')

@section('title', 'Teacher Financial Management - ' . $teacher->name)

@section('styles')
{{--
    Tombol kembali ke Financial Dashboard.
    Warna & bentuk mengikuti tombol "Kembali" yang sudah dipakai di halaman
    Detail Penarikan (admin/finance/withdrawal-detail.blade.php), yaitu tombol
    sekunder putih dengan teks biru #2F80ED, supaya konsisten dengan tema
    Admin Dashboard MersifLab. Style di-scope ke kelas .btn-back-finance saja
    agar tidak menyentuh komponen lain.
--}}
<style>
    .btn-back-finance {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #2F80ED;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-back-finance:hover,
    .btn-back-finance:focus {
        background: #f8f9fa;
        border-color: #2F80ED;
        color: #1c62c4;
        box-shadow: 0 2px 6px rgba(47, 128, 237, 0.15);
        text-decoration: none;
    }

    /* Panah bergeser sedikit ke kiri saat hover - petunjuk arah "kembali". */
    .btn-back-finance i {
        transition: transform 0.2s ease;
    }

    .btn-back-finance:hover i {
        transform: translateX(-3px);
    }

    .page-title-back {
        margin-bottom: 12px;
    }
</style>
@endsection

@section('content')
<!-- Tombol kembali ke halaman utama Financial Management -->
<div class="page-title-back">
    <a href="{{ back_url(route('admin.finance.dashboard')) }}" class="btn-back-finance">
        <i class="fas fa-arrow-left"></i>Kembali ke Financial Management
    </a>
</div>

<div class="page-title" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <div>
        <h1>Financial Management - {{ $teacher->name }}</h1>
    </div>
</div>

<!-- Balance Overview -->
<div class="row mb-4">
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-balance">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Blue Theme) -->
                <div class="stat-icon-container stat-icon-balance-bg me-3">
                    <i class="fas fa-wallet"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Current Balance</div>
                    {{-- Nilai akhir dirender langsung dari server: tidak ada kedipan
                         angka "0" sebelum animasi, dan nominalnya tetap benar
                         seandainya JavaScript gagal dimuat. --}}
                    <div class="stat-value counter" data-count="{{ $balance->balance }}">Rp {{ number_format($balance->balance, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-earnings">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Green Theme) -->
                <div class="stat-icon-container stat-icon-earnings-bg me-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Total Earnings</div>
                    <div class="stat-value counter" data-count="{{ $balance->total_earnings }}">Rp {{ number_format($balance->total_earnings, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-withdrawn">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Orange Theme) -->
                <div class="stat-icon-container stat-icon-withdrawn-bg me-3">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Total Withdrawn</div>
                    <div class="stat-value counter" data-count="{{ $balance->total_withdrawn }}">Rp {{ number_format($balance->total_withdrawn, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-pending">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Purple Theme) -->
                <div class="stat-icon-container stat-icon-pending-bg me-3">
                    <i class="fas fa-clock"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Pending Earnings</div>
                    <div class="stat-value counter" data-count="{{ $balance->pending_earnings }}">Rp {{ number_format($balance->pending_earnings, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="row">
    <!-- Commission Settings -->
    <div class="col-lg-4">
        <div class="card-content">
            <div class="card-content-title">
                <span>
                    <i class="fas fa-percentage me-2"></i>Commission Settings
                </span>
            </div>
            <form method="POST" action="{{ route('admin.finance.teacher.commission', $teacher->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Commission Type</label>
                    <select class="form-select" name="commission_type" style="font-size: 14px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <option value="per_course" {{ $commissionSettings->commission_type == 'per_course' ? 'selected' : '' }}>Per Course</option>
                        <option value="fixed" {{ $commissionSettings->commission_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                        <option value="tiered" {{ $commissionSettings->commission_type == 'tiered' ? 'selected' : '' }}>Tiered</option>
                    </select>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Platform %</label>
                            <input type="number" class="form-control" name="platform_percentage" 
                                   value="{{ $commissionSettings->platform_percentage }}" step="0.01" min="0" max="100" required
                                   style="font-size: 14px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Teacher %</label>
                            <input type="number" class="form-control" name="teacher_percentage" 
                                   value="{{ $commissionSettings->teacher_percentage }}" step="0.01" min="0" max="100" required
                                   style="font-size: 14px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Minimum Amount</label>
                    <input type="number" class="form-control" name="min_amount" 
                           value="{{ $commissionSettings->min_amount }}" step="1000" min="0"
                           style="font-size: 14px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                </div>
                <div class="mb-4">
                    <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Notes</label>
                    <textarea class="form-control" name="notes" rows="2" 
                              style="font-size: 14px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; resize: vertical;">{{ $commissionSettings->notes ?? '' }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; border-radius: 6px; font-weight: 500; background: #2F80ED; border: none;">
                    <i class="fas fa-save me-2"></i>Update Settings
                </button>
            </form>
        </div>
    </div>

    <!-- Courses -->
    <div class="col-lg-8">
        <div class="card-content">
            <div class="card-content-title">
                <span>
                    <i class="fas fa-book me-2"></i>Teacher Courses
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Course Name</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Type</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Students</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Revenue</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Commission</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Teacher Earning</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses as $course)
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td>
                                <div style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 4px;">{{ $course->name }}</div>
                                <small class="text-muted" style="font-size: 12px;">Created: {{ $course->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="badge" style="background: {{ $course->commission_type == 'premium' ? '#fff3cd' : '#f5f5f5' }}; color: {{ $course->commission_type == 'premium' ? '#856404' : '#666' }}; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                    {{ ucfirst($course->commission_type) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                    {{ $course->purchases_count ?? 0 }}
                                </span>
                            </td>
                            <td style="color: #333; font-weight: 500;">Rp {{ number_format($course->revenue ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge" style="background: #e8f5e9; color: #27AE60; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                    20%
                                </span>
                            </td>
                            <td style="color: #27AE60; font-weight: 700;">
                                Rp {{ number_format($course->teacher_earning ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Transaction History -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card-content">
            <div class="card-content-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-history me-2"></i>Transaction History
                </span>
                <button class="btn btn-light btn-sm" onclick="exportTransactions()" style="font-size: 13px; color: #2F80ED; border: 1px solid #e0e0e0; padding: 6px 12px; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Date</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Student</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Course</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Amount</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Status</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Teacher Share</th>
                            <th style="font-weight: 600; color: #333; border-bottom: 2px solid #f0f0f0;">Platform Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td style="font-size: 13px;">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td style="font-size: 14px; font-weight: 500;">{{ $transaction->user->name }}</td>
                            <td style="font-size: 14px;">{{ $transaction->course->name }}</td>
                            <td style="color: #333; font-weight: 500;">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge" style="background: {{ $transaction->status == 'success' ? '#d4edda' : ($transaction->status == 'pending' ? '#fff3cd' : '#f8d7da') }}; color: {{ $transaction->status == 'success' ? '#155724' : ($transaction->status == 'pending' ? '#856404' : '#721c24') }}; padding: 6px 12px; border-radius: 6px; font-weight: 500;">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td style="color: #27AE60; font-weight: 600;">Rp {{ number_format($transaction->teacher_earning ?? 0, 0, ',', '.') }}</td>
                            <td style="color: #f57c00; font-weight: 600;">Rp {{ number_format($transaction->platform_commission ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal History -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card-content">
            <div class="card-content-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-history me-2"></i>Withdrawal History
                </span>
                <button class="btn btn-light btn-sm" onclick="exportTransactions()" style="font-size: 13px; color: #2F80ED; border: 1px solid #e0e0e0; padding: 6px 12px; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm" style="font-size: 13px; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Date</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Withdrawal Code</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Amount</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Bank</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawals as $withdrawal)
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td style="border: none; padding: 16px 8px; vertical-align: middle; color: #333; font-weight: 500; font-size: 13px;">{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                            <td style="border: none; padding: 16px 8px; vertical-align: middle;">
                                <code style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">{{ $withdrawal->withdrawal_code }}</code>
                            </td>
                            <td style="border: none; padding: 16px 8px; vertical-align: middle; color: #333; font-weight: 500; font-size: 14px;">
                                <strong>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</strong>
                            </td>
                            <td style="border: none; padding: 16px 8px; vertical-align: middle;">{{ $withdrawal->bank_name }}</td>
                            <td style="border: none; padding: 16px 8px; vertical-align: middle;">
                                @php
                                    // Sebelumnya rantai ternary hanya mengenali 'pending' dan
                                    // 'approved'; sisanya - termasuk 'processed' - jatuh ke merah
                                    // seolah-olah gagal. Palet pastelnya dipertahankan persis,
                                    // hanya pemetaannya yang dilengkapi.
                                    [$badgeBg, $badgeFg] = match ($withdrawal->status) {
                                        'pending' => ['#fff3cd', '#856404'],   // kuning - menunggu
                                        'approved' => ['#d4edda', '#155724'],  // hijau  - sudah cair
                                        'processed' => ['#cfe2ff', '#084298'], // biru   - sedang diproses
                                        default => ['#f8d7da', '#721c24'],     // merah  - ditolak
                                    };
                                @endphp
                                <span class="badge" style="background: {{ $badgeBg }}; color: {{ $badgeFg }}; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                    {{ $withdrawal->status_label }}
                                </span>
                            </td>
                            <td style="border: none; padding: 16px 8px; vertical-align: middle;">
                                <a href="{{ route('admin.finance.withdrawal.show', $withdrawal->id) }}" class="btn btn-sm" style="color: #1976d2; text-decoration: none; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background='transparent'" title="View Details">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<style>
/* Teacher Finance Management Consistent Styles */
.stat-card-modern {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 25px;
    transition: all 0.3s ease;
}

.stat-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-icon-container {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    /* Jarak ikon-teks dirapatkan dari 16px (me-3) ke 12px. Bersama ukuran
       ikon yang sedikit dikecilkan, ini mengembalikan ~20px ruang untuk
       kolom nominal - cukup untuk angka belasan juta. */
    margin-right: 12px !important;
}

/* Pembungkus teks kanan ikon.
   min-width: 0 penting: anak flex secara bawaan punya min-width auto sehingga
   MENOLAK menyusut di bawah lebar kontennya, dan itulah yang membuat nominal
   panjang mendorong keluar sampai mepet tepi kartu. */
.stat-text {
    min-width: 0;
    padding-right: 8px;
}

.stat-icon-balance-bg {
    background: #e3f2fd;
}

.stat-icon-balance-bg i {
    color: #1976d2;
    font-size: 24px;
}

.stat-icon-earnings-bg {
    background: #e8f5e9;
}

.stat-icon-earnings-bg i {
    color: #27AE60;
    font-size: 24px;
}

.stat-icon-withdrawn-bg {
    background: #fff3e0;
}

.stat-icon-withdrawn-bg i {
    color: #f57c00;
    font-size: 24px;
}

.stat-icon-pending-bg {
    background: #f3e5f5;
}

.stat-icon-pending-bg i {
    color: #7b1fa2;
    font-size: 24px;
}

.stat-label {
    font-size: 13px;
    color: #828282;
    margin-bottom: 8px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    /* Ukuran menyesuaikan lebar layar: 22px di monitor lebar, mengecil
       sampai 17px saat kolom menyempit. 28px sebelumnya membuat
       "Rp 1.070.000" (~176px) melebihi kolom teks yang hanya ~141px. */
    font-size: clamp(17px, 1.45vw, 22px);
    font-weight: 700;
    /* tracking-tight: merapatkan sedikit tanpa mengurangi keterbacaan. */
    letter-spacing: -0.02em;
    color: #333333;
    line-height: 1.25;
    /* Angka memakai lebar digit seragam (tabular figures) supaya teks tidak
       bergoyang saat nilainya berubah tiap frame. Tanpa ini, digit "1" yang
       lebih sempit dari "8" membuat lebar teks berubah-ubah dan terlihat
       seperti flickering. */
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum" 1;
    white-space: nowrap;
}

.card-content {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 25px;
}

.card-content-title {
    font-size: 18px;
    font-weight: 700;
    color: #333333;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table {
    font-size: 13px;
    border-collapse: separate;
    border-spacing: 0;
}

.table td {
    vertical-align: middle;
}

.badge {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    font-size: 0.8rem;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary {
    background: #2F80ED;
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(47, 128, 237, 0.4);
}

.btn-light {
    background: white;
    color: #2F80ED;
    border: 1px solid #e0e0e0;
}

.btn-light:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.form-control {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px;
    font-size: 14px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #2F80ED;
    box-shadow: 0 0 0 0.2rem rgba(47, 128, 237, 0.25);
}

.form-label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-check-input:checked + .form-check-label {
    color: #2F80ED;
    font-weight: 600;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .stat-card-modern {
        margin-bottom: 1rem;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
/* =========================================================================
   Count-Up Animation — Teacher Finance Stats
   =========================================================================
   Implementasi lama memakai setInterval(…, 16) dengan penambahan tetap
   (increment = target / (duration/16)). Tiga masalah pada pendekatan itu:

     1. setInterval tidak tersinkron dengan refresh layar, sehingga frame
        sering meleset/menumpuk -> gerakan terasa patah-patah.
     2. Kemajuan angka dihitung dari JUMLAH TICK, bukan waktu nyata. Kalau
        browser sempat sibuk atau tab tidak aktif, angkanya jadi melenceng
        dan animasi berjalan lebih lama dari semestinya.
     3. Durasi 2 detik + jeda antar kartu 200ms membuat kartu terakhir baru
        selesai di detik ~2,6.

   Versi ini memakai requestAnimationFrame dengan basis waktu nyata
   (performance.now()), easing easeOutExpo, dan durasi 900ms.
   ========================================================================= */

/** Format nominal rupiah, sama persis dengan format dari server. */
function formatRupiah(value) {
    return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

/**
 * easeOutExpo — cepat di awal lalu melambat halus mendekati nilai akhir,
 * sehingga terasa responsif sekaligus rapi saat berhenti.
 */
function easeOutExpo(t) {
    return t >= 1 ? 1 : 1 - Math.pow(2, -10 * t);
}

function animateCountUp(element, delay) {
    const target = parseFloat(element.getAttribute('data-count')) || 0;
    const duration = 900; // 0,9 detik

    // Lebar kotak dikunci sesuai nilai AKHIR (nilai terpanjang) sebelum
    // animasi mulai, supaya tidak ada layout shift saat jumlah digit
    // bertambah dari "Rp 0" menuju "Rp 1.018.640".
    element.style.minWidth = element.getBoundingClientRect().width + 'px';
    element.style.display = 'inline-block';

    let startTime = null;

    function frame(now) {
        if (startTime === null) {
            startTime = now;
        }

        // Progres dihitung dari waktu nyata, bukan jumlah frame, sehingga
        // durasi tetap 900ms walau ada frame yang terlewat.
        const progress = Math.min((now - startTime) / duration, 1);

        element.textContent = formatRupiah(target * easeOutExpo(progress));

        if (progress < 1) {
            requestAnimationFrame(frame);
        } else {
            // Pastikan berhenti tepat di nilai asli dari server.
            element.textContent = formatRupiah(target);
        }
    }

    element.textContent = formatRupiah(0);
    window.setTimeout(function () {
        requestAnimationFrame(frame);
    }, delay);
}

document.addEventListener('DOMContentLoaded', function () {
    const statValues = document.querySelectorAll('.counter[data-count]');

    // Hormati pengguna yang mematikan animasi di sistemnya: nilai akhir
    // langsung ditampilkan tanpa animasi.
    const prefersReducedMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || typeof requestAnimationFrame !== 'function') {
        statValues.forEach(function (element) {
            element.textContent = formatRupiah(parseFloat(element.getAttribute('data-count')) || 0);
        });
        return;
    }

    statValues.forEach(function (element, index) {
        // Jeda antar kartu dipersingkat (80ms) agar keempatnya selesai
        // sekitar 1,1 detik, bukan 2,6 detik seperti sebelumnya.
        animateCountUp(element, index * 80);
    });
});

function exportTransactions() {
    const startDate = prompt('Start date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    const endDate = prompt('End date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    
    if (startDate && endDate) {
        window.open(`/admin/finance/export?type=transactions&start_date=${startDate}&end_date=${endDate}`, '_blank');
    }
}

function exportReport(type) {
    window.open(`/admin/finance/export?teacher_id={{ $teacher->id }}&type=${type}`, '_blank');
}
</script>
@endsection
