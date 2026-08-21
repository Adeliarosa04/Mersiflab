@extends('layouts.admin')

@section('title', 'Financial Dashboard')

@section('content')
<div class="page-title" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <div>
        <h1>Financial Dashboard</h1>
    </div>
    <div style="max-width: 350px; width: 100%; margin-top: 0;">
        <input type="text" id="financeSearch" placeholder="Search transactions..." style="width: 100%; padding: 10px 15px; border: none; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 20px; font-size: 13px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; outline: none;" onfocus="this.style.background='white'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.1)';" onblur="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.05)';">
    </div>
</div>

<!-- Financial Overview Cards -->
<div class="row mb-4">
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-revenue">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Blue Theme) -->
                <div class="stat-icon-container stat-icon-revenue-bg me-3">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-payout">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Green Theme) -->
                <div class="stat-icon-container stat-icon-payout-bg me-3">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Teacher Payouts</div>
                    <div class="stat-value">Rp {{ number_format($totalTeacherPayouts, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 mb-3">
        <div class="stat-card-modern stat-card-commission">
            <div class="d-flex align-items-center">
                <!-- Left: Large Icon Container (Orange Theme) -->
                <div class="stat-icon-container stat-icon-commission-bg me-3">
                    <i class="fas fa-percentage"></i>
                </div>
                <!-- Right: Text Info -->
                <div class="flex-grow-1 stat-text">
                    <div class="stat-label">Platform Commission</div>
                    <div class="stat-value">Rp {{ number_format($platformCommission, 0, ',', '.') }}</div>
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
                    <div class="stat-label">Pending Withdrawals</div>
                    <div class="stat-value">{{ $pendingWithdrawals }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="row">
    <!-- Teacher Statistics -->
    <div class="col-lg-8">
        <div class="card-content">
            <div class="card-content-title" style="display: flex; justify-content: space-between; align-items: center;">
                <span>
                    <i class="fas fa-chalkboard-teacher me-2"></i>Teacher Statistics
                </span>
                <button class="btn btn-light btn-sm" onclick="exportReport('summary')" style="font-size: 13px; color: #2F80ED; border: 1px solid #e0e0e0; padding: 6px 12px; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm" style="font-size: 13px; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Teacher</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Total Sales</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Total Earnings</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Current Balance</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Total Withdrawn</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Pending Earnings</th>
                            <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teacherStats as $teacher)
                        <tr style="border-bottom: 1px solid #f8f9fa;">
                            <td style="padding: 16px 8px; vertical-align: middle;">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px; font-weight: 600;">
                                        {{ strtoupper(substr($teacher['name'], 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="color: #333; font-size: 14px;">{{ $teacher['name'] }}</div>
                                        <small class="text-muted" style="font-size: 12px;">{{ $teacher['email'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 16px 8px; vertical-align: middle; color: #333; font-weight: 500; font-size: 13px;">Rp {{ number_format($teacher['total_sales'], 0, ',', '.') }}</td>
                            <td style="padding: 16px 8px; vertical-align: middle; color: #333; font-weight: 500; font-size: 13px;">Rp {{ number_format($teacher['total_earnings'], 0, ',', '.') }}</td>
                            <td style="padding: 16px 8px; vertical-align: middle;">
                                <span class="badge" style="background: {{ $teacher['current_balance'] > 0 ? '#e8f5e9' : '#f5f5f5' }}; color: {{ $teacher['current_balance'] > 0 ? '#27AE60' : '#666' }}; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                    Rp {{ number_format($teacher['current_balance'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td style="padding: 16px 8px; vertical-align: middle; color: #333; font-weight: 500; font-size: 13px;">Rp {{ number_format($teacher['total_withdrawn'], 0, ',', '.') }}</td>
                            <td style="padding: 16px 8px; vertical-align: middle;">
                                @if($teacher['pending_earnings'] > 0)
                                    <span class="badge" style="background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                        Rp {{ number_format($teacher['pending_earnings'], 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size: 13px;">-</span>
                                @endif
                            </td>
                            <td style="padding: 16px 8px; vertical-align: middle;">
                                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                    <!-- View Button (Text Link) -->
                                    <a href="{{ route('admin.finance.teacher', $teacher['id']) }}" 
                                       style="color: #1976d2; text-decoration: none; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;"
                                       onmouseover="this.style.background='#e3f2fd'" 
                                       onmouseout="this.style.background='transparent'"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($teacher['pending_earnings'] > 0)
                                        <button class="btn btn-sm" style="background: #e8f5e9; color: #27AE60; border: none; padding: 4px 8px; font-size: 11px; border-radius: 4px; cursor: pointer; transition: opacity 0.2s;"
                                                onmouseover="this.style.opacity='0.8'" 
                                                onmouseout="this.style.opacity='1'"
                                                onclick="approveEarnings({{ $teacher['id'] }}, {{ $teacher['pending_earnings'] }})"
                                                title="Approve Earnings">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-lg-4">
        <!-- Recent Transactions -->
        <div class="card-content">
            <div class="card-content-title">
                <span>
                    <i class="fas fa-shopping-cart me-2"></i>Recent Transactions
                </span>
            </div>
            @if($recentTransactions->count() > 0)
                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                    @foreach($recentTransactions as $transaction)
                    <div class="list-group-item" style="border: none; border-bottom: 1px solid #f0f0f0; padding: 12px 0;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div style="font-size: 13px; color: #333; font-weight: 600; margin-bottom: 4px;">{{ $transaction->user->name }}</div>
                                <small class="text-muted" style="font-size: 12px;">{{ $transaction->course->name }}</small>
                            </div>
                            <div class="text-end">
                                <div style="font-size: 14px; font-weight: 700; color: #27AE60;">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
                                <small class="text-muted" style="font-size: 11px;">{{ $transaction->created_at->format('d M') }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center" style="padding: 40px; color: #828282;">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; color: #e0e0e0; margin-bottom: 10px;"></i>
                    <p style="font-size: 14px; margin: 0;">No recent transactions</p>
                </div>
            @endif
        </div>

        <!-- Recent Withdrawals -->
        <div class="card-content">
            <div class="card-content-title">
                <span>
                    <i class="fas fa-money-bill-wave me-2"></i>Recent Withdrawals
                </span>
            </div>
            @if($recentWithdrawals->count() > 0)
                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                    @foreach($recentWithdrawals as $withdrawal)
                    <div class="list-group-item" style="border: none; border-bottom: 1px solid #f0f0f0; padding: 12px 0;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div style="font-size: 13px; color: #333; font-weight: 600; margin-bottom: 4px;">{{ $withdrawal->teacher->name }}</div>
                                <small class="text-muted" style="font-size: 12px;">{{ $withdrawal->bank_name }}</small>
                            </div>
                            <div class="text-end">
                                <div style="font-size: 14px; font-weight: 700; color: #333;">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</div>
                                <span class="badge" style="background: {{ $withdrawal->status == 'pending' ? '#fff3cd' : ($withdrawal->status == 'approved' ? '#d4edda' : '#f8d7da') }}; color: {{ $withdrawal->status == 'pending' ? '#856404' : ($withdrawal->status == 'approved' ? '#155724' : '#721c24') }}; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center" style="padding: 40px; color: #828282;">
                    <i class="fas fa-money-bill-wave" style="font-size: 48px; color: #e0e0e0; margin-bottom: 10px;"></i>
                    <p style="font-size: 14px; margin: 0;">No recent withdrawals</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Earnings Modal -->
<div class="modal fade" id="approveEarningsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px 24px;">
                <h5 class="modal-title" style="font-size: 18px; font-weight: 600; color: #333; margin: 0;">
                    <i class="fas fa-check-circle me-2" style="color: #27AE60;"></i>Approve Pending Earnings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size: 14px;"></button>
            </div>
            <form id="approveEarningsForm" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" id="approveTeacherId" name="teacher_id">
                    <div class="mb-4">
                        <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Amount to Approve</label>
                        <input type="number" class="form-control" id="approveAmount" name="amount" readonly style="font-size: 16px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; background: #f8f9fa;">
                        <small class="text-muted" style="font-size: 12px;">Maximum: Rp <span id="maxAmount">0</span></small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" style="font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Add notes for this approval..." style="font-size: 14px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; resize: vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 16px 24px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="padding: 8px 16px; border-radius: 6px; font-weight: 500;">Cancel</button>
                    <button type="submit" class="btn btn-success" style="padding: 8px 16px; border-radius: 6px; font-weight: 500; background: #27AE60; border: none;">
                        <i class="fas fa-check me-2"></i>Approve Earnings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
function approveEarnings(teacherId, maxAmount) {
    document.getElementById('approveTeacherId').value = teacherId;
    document.getElementById('approveAmount').value = maxAmount;
    document.getElementById('maxAmount').textContent = new Intl.NumberFormat('id-ID').format(maxAmount);
    
    var modal = new bootstrap.Modal(document.getElementById('approveEarningsModal'));
    modal.show();
}

function exportReport(type) {
    // Implementation for export functionality
    window.open('{{ route("admin.finance.export") }}?type=' + type, '_blank');
}

// Auto-refresh dashboard every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

<style>
/* Finance Dashboard Consistent Styles */
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
    /* Disamakan dengan halaman Detail Keuangan Guru: ikon sedikit dikecilkan
       dan jaraknya dirapatkan, mengembalikan ~20px ruang untuk kolom nominal. */
    width: 52px;
    height: 52px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 12px !important;
}

/* Pembungkus teks di kanan ikon (keempat kartu memakai ini).
   min-width: 0 wajib: anak flex secara bawaan ber-min-width auto sehingga
   MENOLAK menyusut di bawah lebar kontennya. Itulah yang membuat
   "Rp 1.070.000" terpotong jadi dua baris. */
.stat-text {
    min-width: 0;
    padding-right: 8px;
}

.stat-icon-revenue-bg {
    background: #e3f2fd;
}

.stat-icon-revenue-bg i {
    color: #1976d2;
    font-size: 24px;
}

.stat-icon-payout-bg {
    background: #e8f5e9;
}

.stat-icon-payout-bg i {
    color: #27AE60;
    font-size: 24px;
}

.stat-icon-commission-bg {
    background: #fff3e0;
}

.stat-icon-commission-bg i {
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
    /* 28px sebelumnya terlalu besar untuk kolom kartu, sehingga "Rp" dan
       angkanya pecah jadi dua baris. Ukuran kini menyesuaikan lebar layar:
       22px di monitor lebar, mengecil sampai 17px saat kolom menyempit. */
    font-size: clamp(17px, 1.45vw, 22px);
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #333333;
    line-height: 1.25;
    /* Kunci utama: nominal TIDAK boleh dipotong ke baris berikutnya. */
    white-space: nowrap;
    /* Lebar digit seragam supaya keempat kartu terlihat presisi. */
    font-variant-numeric: tabular-nums;
    font-feature-settings: "tnum" 1;
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

.list-group-item {
    transition: background-color 0.2s ease;
}

.list-group-item:hover {
    background-color: #f8f9fa;
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
function approveEarnings(teacherId, maxAmount) {
    document.getElementById('approveTeacherId').value = teacherId;
    document.getElementById('approveAmount').value = maxAmount;
    document.getElementById('maxAmount').textContent = new Intl.NumberFormat('id-ID').format(maxAmount);
    document.getElementById('approveEarningsForm').action = `/admin/finance/teacher/${teacherId}/approve-earnings`;
    new bootstrap.Modal(document.getElementById('approveEarningsModal')).show();
}

function exportReport(type) {
    const startDate = prompt('Start date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    const endDate = prompt('End date (YYYY-MM-DD):', new Date().toISOString().split('T')[0]);
    
    if (startDate && endDate) {
        window.open(`/admin/finance/export?type=${type}&start_date=${startDate}&end_date=${endDate}`, '_blank');
    }
}
</script>
