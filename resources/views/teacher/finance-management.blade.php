@extends('layouts.app')

@section('title', 'Finance Management')

@section('content')
{{-- Struktur pembungkus disamakan dengan halaman Manage Content
     (resources/views/teacher/manage-content.blade.php): grid 2 kolom
     Bootstrap, sidebar di col-lg-3 (kiri) dan konten di col-lg-9 (kanan).

     Sebelumnya halaman ini memakai <div class="main-content"> dan
     <div class="content-area"> - kelas milik layouts/admin.blade.php, bukan
     layouts/app. Di layout ini kedua kelas itu tidak punya arti grid apa pun,
     sehingga sidebar dirender sebagai blok selebar penuh dan mendorong konten
     keuangan ke bawah. --}}
<section class="finance-management-page py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                @include('teacher.partials.sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="profile-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Finance Management</h1>
                <p>Manage your earnings and withdrawals</p>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
                    
                    @php
                        // Catatan: $totalRevenue, $totalTransactions, $successTransactions,
                        // $pendingTransactions, dan $uniqueStudents dulu dihitung di sini tetapi
                        // tidak pernah ditampilkan di mana pun. Angkanya juga menyesatkan:
                        // $recentPurchases dibatasi 10 baris terakhir, sehingga "total" itu
                        // sebenarnya hanya total 10 transaksi terbaru. Semua kartu ringkasan
                        // di bawah memakai $balance, yang hanya bertambah dari purchase
                        // berstatus 'success'. Perhitungan mati itu dihapus agar tidak ada
                        // lagi dua sumber angka pendapatan yang bisa berbeda.
                        $totalCourses = $courses ? $courses->count() : 0;

                        // Angka keuangan berasal dari App\Support\TeacherEarnings, bukan dari
                        // kolom teacher_balances. Baris di tabel itu terlanjur menyimpan nilai
                        // BRUTO (100% harga kursus) karena bug lama, sehingga memakainya akan
                        // menampilkan pendapatan sebelum potongan komisi aplikasi.
                        $currentBalance = $earnings['available'];   // bersih - sudah ditarik
                        $totalEarnings = $earnings['net'];          // sesudah komisi platform
                        $totalWithdrawn = $earnings['withdrawn'];
                        $pendingEarnings = $balance ? $balance->pending_earnings : 0;
                    @endphp
                    
                    <!-- Finance Summary Cards -->
                    <div class="row mb-4 fade-in-up">
                        <div class="col-md-4 mb-3">
                            <div class="card finance-overview-card border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-2">Saldo Saat Ini</h6>
                                            <h4 class="text-primary mb-0">Rp {{ number_format($currentBalance, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="ms-3">
                                            <div class="icon-circle bg-primary">
                                                <i class="fas fa-wallet fa-2x text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card finance-overview-card border-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-2">Total Pendapatan</h6>
                                            <h4 class="text-success mb-0">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="ms-3">
                                            <div class="icon-circle bg-success">
                                                <i class="fas fa-chart-line fa-2x text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="card finance-overview-card border-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="text-muted mb-2">Sudah Ditarik</h6>
                                            <h4 class="text-warning mb-0">Rp {{ number_format($totalWithdrawn, 0, ',', '.') }}</h4>
                                        </div>
                                        <div class="ms-3">
                                            <div class="icon-circle bg-warning">
                                                <i class="fas fa-money-bill-wave fa-2x text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Withdrawal Request Section -->
                    <div class="card mb-4 fade-in-up">
                        <div class="card-header gradient-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-hand-holding-usd me-2"></i>Ajukan Penarikan
                            </h5>
                            @if($currentBalance >= 50000)
                                <button class="btn btn-gradient-primary btn-sm pulse-animation" data-bs-toggle="modal" data-bs-target="#withdrawalModal">
                                    <i class="fas fa-plus me-1"></i>Ajukan Penarikan
                                </button>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    <i class="fas fa-lock me-1"></i>Saldo Tidak Mencukupi
                                </button>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($currentBalance < 50000)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Saldo Anda tidak mencukupi untuk melakukan penarikan. Minimum penarikan adalah Rp 50.000.
                                    <br>Saldo tersedia: <strong>Rp {{ number_format($currentBalance, 0, ',', '.') }}</strong>
                                </div>
                            @else
                                <p class="mb-0">Saldo yang tersedia untuk ditarik: <strong>Rp {{ number_format($currentBalance, 0, ',', '.') }}</strong></p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Withdrawal History -->
                    <div class="card mb-4 fade-in-up">
                        <div class="card-header gradient-header">
                            <h5 class="mb-0">
                                <i class="fas fa-history me-2"></i>Riwayat Penarikan
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($withdrawals->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Jumlah</th>
                                                <th>Bank</th>
                                                <th>Status</th>
                                                <th>Tanggal Pengajuan</th>
                                                <th>Tanggal Proses</th>
                                                <th>Bukti Transfer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($withdrawals as $withdrawal)
                                                <tr>
                                                    <td><code>{{ $withdrawal->withdrawal_code }}</code></td>
                                                    <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                                                    <td>{{ $withdrawal->bank_name }} - {{ $withdrawal->bank_account_name }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $withdrawal->status_badge }}">
                                                            {{ $withdrawal->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $withdrawal->requested_at->format('d M Y H:i') }}</td>
                                                    <td>{{ $withdrawal->processed_at ? $withdrawal->processed_at->format('d M Y H:i') : '-' }}</td>
                                                    <td>
                                                        @if(($withdrawal->status === 'processed' || $withdrawal->status === 'approved') && $withdrawal->transfer_proof)
                                                            <a href="{{ Storage::url($withdrawal->transfer_proof) }}" target="_blank" class="btn btn-sm btn-outline-success" title="Lihat Bukti Transfer">
                                                                <i class="fas fa-image me-1"></i>Lihat
                                                            </a>
                                                        @elseif($withdrawal->status === 'pending')
                                                            <span class="badge bg-warning">Menunggu</span>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada riwayat penarikan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Recent Purchases -->
                    <div class="card fade-in-up">
                        <div class="card-header gradient-header">
                            <h5 class="mb-0">
                                <i class="fas fa-shopping-cart me-2"></i>Penjualan Terbaru
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($recentPurchases->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Pelanggan</th>
                                                <th>Kursus</th>
                                                <th>Jumlah</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentPurchases as $purchase)
                                                <tr>
                                                    <td>{{ $purchase->user->name }}</td>
                                                    <td>{{ $purchase->course->name }}</td>
                                                    <td>Rp {{ number_format($purchase->amount, 0, ',', '.') }}</td>
                                                    <td>{{ $purchase->created_at->format('d M Y H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Belum ada penjualan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Withdrawal Modal -->
<div class="modal fade" id="withdrawalModal" tabindex="-1" aria-labelledby="withdrawalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawalModalLabel">
                    <i class="fas fa-hand-holding-usd me-2"></i>Ajukan Penarikan Dana
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teacher.withdrawal.request') }}" method="POST" id="withdrawalForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Saldo tersedia: <strong>Rp {{ number_format($currentBalance, 0, ',', '.') }}</strong>
                        <br>Minimum penarikan: <strong>Rp 50.000</strong>
                    </div>
                    
                    @if($currentBalance < 50000)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Saldo Anda tidak mencukupi untuk melakukan penarikan. Minimum penarikan adalah Rp 50.000.
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Jumlah Penarikan (Rp)<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               min="50000" step="1000" required
                               @if($currentBalance < 50000) disabled @endif>
                        <small class="text-muted">Minimal Rp 50.000, maksimal Rp {{ number_format($currentBalance, 0, ',', '.') }}</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_name" class="form-label">Nama Bank<span class="text-danger">*</span></label>
                        <select class="form-control" id="bank_name" name="bank_name" required>
                            <option value="">Pilih Bank</option>
                            <option value="BCA">BCA</option>
                            <option value="BNI">BNI</option>
                            <option value="BRI">BRI</option>
                            <option value="Mandiri">Mandiri</option>
                            <option value="CIMB Niaga">CIMB Niaga</option>
                            <option value="Danamon">Danamon</option>
                            <option value="Permata">Permata</option>
                            <option value="BSI">BSI</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_account_name" class="form-label">Nama Pemilik Rekening<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" 
                               placeholder="John Doe" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_account_number" class="form-label">Nomor Rekening<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" 
                               placeholder="1234567890" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submitWithdrawal">
                        <i class="fas fa-paper-plane me-2"></i>Ajukan Penarikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    
.finance-management-page {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2f5 100%);
    min-height: 100vh;
}

/* Enhanced Finance Cards */
.finance-overview-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
    background: white;
}

.finance-overview-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.finance-overview-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.finance-overview-card .card-body {
    padding: 2rem;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.finance-overview-card:hover .icon-circle {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

/* Enhanced Cards */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.gradient-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
    padding: 1.25rem;
}

.gradient-header h5 {
    margin: 0;
    font-weight: 600;
}

/* Enhanced Table */
.table {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    background: white;
}

.table thead th {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
    padding: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9ff;
    transform: scale(1.01);
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-color: #f0f0f0;
}

/* Enhanced Buttons */
.btn {
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    padding: 0.5rem 1.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
}

.btn-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border: none;
    padding: 0.6rem 1.8rem;
    font-weight: 600;
    border-radius: 25px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-gradient-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    transition: left 0.5s ease;
}

.btn-gradient-primary:hover::before {
    left: 100%;
}

.btn-gradient-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
    background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
}

.btn-gradient-primary:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.25);
    }
    50% {
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.4);
    }
    100% {
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.25);
    }
}

.btn-light {
    background: rgba(255, 255, 255, 0.9);
    color: #007bff;
    backdrop-filter: blur(10px);
}

.btn-light:hover {
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
}

/* Enhanced Badges */
.badge {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Enhanced Modal */
.modal-content {
    border: none;
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 20px 20px 0 0;
}

/* Enhanced Alerts */
.alert {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    color: #856404;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
}

/* Empty State Styling */
.text-center.py-4 {
    padding: 3rem 1rem !important;
}

.text-center.py-4 i {
    opacity: 0.5;
    margin-bottom: 1rem;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .finance-title {
        font-size: 2rem;
    }
    
    .finance-overview-card {
        margin-bottom: 1.5rem;
    }
    
    .table {
        font-size: 0.875rem;
    }
    
    .btn {
        padding: 0.4rem 1rem;
        font-size: 0.9rem;
    }
    
    .icon-circle {
        width: 50px;
        height: 50px;
    }
    
    .icon-circle .fa-2x {
        font-size: 1.5rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clean up any lingering SweetAlert state
    if (document.body.classList.contains('swal2-shown')) {
        document.body.classList.remove('swal2-shown', 'swal2-height-auto');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // Get withdrawal modal element
    const withdrawalModalEl = document.getElementById('withdrawalModal');
    if (withdrawalModalEl) {
        // Handle modal visibility
        withdrawalModalEl.addEventListener('show.bs.modal', function() {
            const maxAmount = parseFloat('{{ $currentBalance }}') || 0;
            const submitButton = document.getElementById('submitWithdrawal');
            const amountField = document.getElementById('amount');
            
            if (maxAmount < 50000) {
                // Disable amount field and submit button if saldo is less than 50,000
                if (amountField) amountField.disabled = true;
                if (submitButton) submitButton.disabled = true;
            } else {
                // Enable if saldo is valid
                if (amountField) amountField.disabled = false;
                if (submitButton) submitButton.disabled = false;
            }
        });
        
        // Only cleanup body state when modal is completely hidden
        withdrawalModalEl.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    }
    // Form validation and submission
    const withdrawalForm = document.getElementById('withdrawalForm');
    const submitButton = document.getElementById('submitWithdrawal');
    const amountInput = document.getElementById('amount');
    
    if (withdrawalForm) {
        withdrawalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            
            // Get form data
            const formData = new FormData(this);
            const amount = parseFloat(formData.get('amount')) || 0;
            const maxAmount = parseFloat('{{ $currentBalance }}') || 0;
            
            // Client-side validation
            let isValid = true;
            
            // Check if saldo is less than minimum 50,000
            if (maxAmount < 50000) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Saldo Tidak Cukup',
                    text: 'Saldo Anda tidak mencukupi untuk melakukan penarikan. Minimum penarikan adalah Rp 50.000.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }
            
            // Validate amount is entered
            if (isNaN(amount) || amount === '') {
                amountInput.classList.add('is-invalid');
                amountInput.nextElementSibling.nextElementSibling.textContent = 'Jumlah penarikan harus diisi';
                isValid = false;
            } 
            // Validate amount is at least minimum 50,000
            else if (amount < 50000) {
                amountInput.classList.add('is-invalid');
                amountInput.nextElementSibling.nextElementSibling.textContent = 'Jumlah penarikan minimum adalah Rp 50.000';
                isValid = false;
            } 
            // Validate amount doesn't exceed balance
            else if (amount > maxAmount) {
                amountInput.classList.add('is-invalid');
                amountInput.nextElementSibling.nextElementSibling.textContent = `Jumlah penarikan tidak boleh melebihi Rp ${maxAmount.toLocaleString('id-ID')}`;
                isValid = false;
            }
            
            // Validate bank name
            const bankName = formData.get('bank_name');
            if (!bankName || bankName.trim() === '') {
                const bankSelect = document.getElementById('bank_name');
                bankSelect.classList.add('is-invalid');
                bankSelect.nextElementSibling.textContent = 'Nama bank harus dipilih';
                isValid = false;
            }
            
            // Validate account name
            const accountName = formData.get('bank_account_name');
            if (!accountName || accountName.trim() === '') {
                const accountNameInput = document.getElementById('bank_account_name');
                accountNameInput.classList.add('is-invalid');
                accountNameInput.nextElementSibling.textContent = 'Nama pemilik rekening harus diisi';
                isValid = false;
            }
            
            // Validate account number
            const accountNumber = formData.get('bank_account_number');
            if (!accountNumber || accountNumber.trim() === '') {
                const accountNumberInput = document.getElementById('bank_account_number');
                accountNumberInput.classList.add('is-invalid');
                accountNumberInput.nextElementSibling.textContent = 'Nomor rekening harus diisi';
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            // Disable submit button and show loading
            const originalButtonText = '<i class="fas fa-paper-plane me-2"></i>Ajukan Penarikan';
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengajukan...';
            
            // Submit form via fetch API
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                // Parse JSON regardless of response status
                return response.json().then(data => ({
                    status: response.status,
                    ok: response.ok,
                    data: data
                }));
            })
            .then(result => {
                if (result.ok && result.data.success) {
                    // Show success message using SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.data.message || 'Permintaan penarikan Anda telah diajukan',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#667eea',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Close modal and redirect
                        const modal = bootstrap.Modal.getInstance(withdrawalModalEl);
                        if (modal) {
                            modal.hide();
                        }
                        
                        setTimeout(() => {
                            window.location.href = '{{ route("teacher.finance.management") }}';
                        }, 300);
                    });
                } else {
                    // Parse error message from response
                    const errorMessage = result.data.message || 'Terjadi kesalahan saat mengajukan penarikan';
                    throw new Error(errorMessage);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                // Show error message using SweetAlert
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat mengajukan penarikan. Silakan coba lagi.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            });
        });
    }
    
    // Simple validation for amount input
    const amountInputElement = document.getElementById('amount');
    if (amountInputElement) {
        // Only prevent negative values
        amountInputElement.addEventListener('keydown', function(e) {
            if (e.key === '-' || e.key === 'e' || e.key === 'E') {
                e.preventDefault();
            }
        });
        
        // Validate on blur
        amountInputElement.addEventListener('blur', function(e) {
            const value = parseFloat(e.target.value) || 0;
            const maxAmount = parseFloat('{{ $currentBalance }}') || 0;
            
            // Ensure value is non-negative
            if (value < 0) {
                e.target.value = 0;
            }
            
            // Warn if exceeds maximum
            if (value > maxAmount && maxAmount > 0) {
                e.target.classList.add('is-invalid');
                const feedback = e.target.nextElementSibling?.nextElementSibling;
                if (feedback) {
                    feedback.textContent = `Maximum Rp ${maxAmount.toLocaleString('id-ID')}`;
                    feedback.style.display = 'block';
                }
            } else {
                e.target.classList.remove('is-invalid');
            }
        });
        
        // Clear validation on input
        amountInputElement.addEventListener('input', function(e) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.nextElementSibling?.nextElementSibling;
            if (feedback) {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
        });
    }
    
    // Auto-fill bank name if "Lainnya" is selected
    const bankSelect = document.getElementById('bank_name');
    if (bankSelect) {
        bankSelect.addEventListener('change', function(e) {
            if (e.target.value === 'Lainnya') {
                const customBankName = prompt('Masukkan nama bank:');
                if (customBankName && customBankName.trim()) {
                    // Create a new option and select it
                    const newOption = new Option(customBankName, customBankName, true, true);
                    e.target.add(newOption);
                } else {
                    e.target.value = '';
                }
            }
            // Clear validation on change
            e.target.classList.remove('is-invalid');
            const feedback = e.target.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
        });
    }
    
    // Clear validation on input for account name
    const accountNameInput = document.getElementById('bank_account_name');
    if (accountNameInput) {
        accountNameInput.addEventListener('input', function(e) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
        });
    }
    
    // Clear validation on input for account number
    const accountNumberInput = document.getElementById('bank_account_number');
    if (accountNumberInput) {
        accountNumberInput.addEventListener('input', function(e) {
            e.target.classList.remove('is-invalid');
            const feedback = e.target.nextElementSibling;
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.textContent = '';
                feedback.style.display = 'none';
            }
        });
    }
});

</script>
</div>
</div>
@endsection
