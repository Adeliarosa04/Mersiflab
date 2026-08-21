@extends('layouts.admin')

@section('title', 'Testimonials Moderation')

@section('styles')
{{--
    Style tab filter status. Memakai palet Admin Dashboard MersifLab yang
    sudah ada (#2F80ED primary, #828282 teks sekunder) dan di-scope ke kelas
    .testimonial-* saja agar tidak menyentuh komponen admin lain.
--}}
<style>
    .testimonial-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        border-bottom: 1px solid #f1f3f5;
        padding-bottom: 14px;
    }

    .testimonial-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #828282;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .testimonial-tab:hover {
        background: #f8f9fa;
        color: #2F80ED;
        border-color: #2F80ED;
    }

    .testimonial-tab.active {
        background: #2F80ED;
        border-color: #2F80ED;
        color: #ffffff;
    }

    .testimonial-tab-count {
        background: rgba(0, 0, 0, 0.06);
        border-radius: 10px;
        padding: 1px 8px;
        font-size: 11px;
        font-weight: 700;
    }

    .testimonial-tab.active .testimonial-tab-count {
        background: rgba(255, 255, 255, 0.25);
    }

    .testimonial-stars {
        color: #f5a623;
        font-size: 12px;
        white-space: nowrap;
    }

    .testimonial-excerpt {
        color: #333;
        font-size: 13px;
        line-height: 1.6;
        max-width: 380px;
    }

    .testimonial-action-btn {
        border: none;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        transition: opacity 0.2s ease;
    }

    .testimonial-action-btn:hover {
        opacity: 0.82;
    }

    .testimonial-action-approve {
        background: #e8f5e8;
        color: #2e7d32;
    }

    .testimonial-action-reject {
        background: #fff3e0;
        color: #f57c00;
    }

    .testimonial-action-delete {
        background: #ffebee;
        color: #c62828;
    }

    .testimonial-action-pending {
        background: #eef2f6;
        color: #495057;
    }
</style>
@endsection

@section('content')
<div class="page-title" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
    <div>
        <h1>Testimonials Moderation</h1>
        <p>Tinjau testimoni yang dikirim siswa, lalu setujui atau tolak.</p>
    </div>
    <div style="max-width: 350px; width: 100%; margin-top: 0;">
        <input type="text" id="testimonialSearch" placeholder="Search testimonials..." style="width: 100%; padding: 10px 15px; border: none; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 20px; font-size: 13px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s ease; outline: none;" onfocus="this.style.background='white'; this.style.boxShadow='0 2px 8px rgba(0, 0, 0, 0.1)';" onblur="this.style.background='rgba(255, 255, 255, 0.8)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.05)';">
    </div>
</div>

<div class="card-content">
    @if(session('success'))
        <div class="alert alert-success" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <!-- ============ TAB FILTER STATUS ============ -->
    <div class="testimonial-tabs">
        <a href="{{ route('admin.testimonials.index', ['status' => 'pending']) }}"
           class="testimonial-tab {{ $activeStatus === 'pending' ? 'active' : '' }}">
            <i class="fas fa-clock"></i> Pending
            <span class="testimonial-tab-count">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('admin.testimonials.index', ['status' => 'approved']) }}"
           class="testimonial-tab {{ $activeStatus === 'approved' ? 'active' : '' }}">
            <i class="fas fa-check-circle"></i> Approved
            <span class="testimonial-tab-count">{{ $counts['approved'] }}</span>
        </a>
        <a href="{{ route('admin.testimonials.index', ['status' => 'rejected']) }}"
           class="testimonial-tab {{ $activeStatus === 'rejected' ? 'active' : '' }}">
            <i class="fas fa-times-circle"></i> Rejected
            <span class="testimonial-tab-count">{{ $counts['rejected'] }}</span>
        </a>
        <a href="{{ route('admin.testimonials.index', ['status' => 'all']) }}"
           class="testimonial-tab {{ $activeStatus === 'all' ? 'active' : '' }}">
            <i class="fas fa-list"></i> All
            <span class="testimonial-tab-count">{{ $counts['all'] }}</span>
        </a>
    </div>

    <div class="card-content-title">
        {{ ucfirst($activeStatus) }} Testimonials ({{ $testimonials->total() }})
    </div>

    <div class="table-responsive">
        <table class="table table-sm" style="font-size: 13px; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Student</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Rating</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Testimonial</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Course</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                    <th style="border: none; padding: 12px 8px; color: #828282; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($testimonials as $t)
                    <tr style="border-bottom: 1px solid #f8f9fa;">
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $t->avatarUrl() }}" alt="avatar" style="width:40px; height:40px; object-fit:cover; border-radius:8px;">
                                <div>
                                    <div style="color: #333; font-weight: 600; font-size: 13px;">{{ $t->name }}</div>
                                    <div style="color: #828282; font-size: 11.5px;">{{ $t->created_at->format('d M Y') }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            @if($t->rating)
                                <span class="testimonial-stars">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa{{ $i <= $t->rating ? 's' : 'r' }} fa-star"></i>
                                    @endfor
                                </span>
                            @else
                                <span style="color: #828282; font-size: 12px;">—</span>
                            @endif
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div class="testimonial-excerpt">{{ \Illuminate\Support\Str::limit($t->content, 140) }}</div>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle; color: #828282; font-size: 12.5px;">
                            {{ $t->course->name ?? 'Umum' }}
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <span class="badge" style="padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;
                                @if($t->isApproved()) background: #e8f5e8; color: #2e7d32;
                                @elseif($t->isRejected()) background: #ffebee; color: #c62828;
                                @else background: #fff3e0; color: #f57c00; @endif">
                                {{ $t->status_label }}
                            </span>
                        </td>
                        <td style="padding: 16px 8px; vertical-align: middle;">
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                <a href="{{ route('admin.testimonials.show', $t->id) }}"
                                   style="color: #1976d2; text-decoration: none; font-size: 12px; font-weight: 500; padding: 4px 8px; border-radius: 4px; transition: background 0.2s;"
                                   onmouseover="this.style.background='#e3f2fd'"
                                   onmouseout="this.style.background='transparent'"
                                   title="Lihat detail">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>

                                @if(!$t->isApproved())
                                    <!-- Approve / Publikasikan -->
                                    <form action="{{ route('admin.testimonials.approve', $t->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="testimonial-action-btn testimonial-action-approve" title="Setujui & publikasikan">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                @endif

                                @if(!$t->isRejected())
                                    <!-- Reject / Tolak -->
                                    <button type="button"
                                            class="testimonial-action-btn testimonial-action-reject js-reject-testimonial"
                                            data-action="{{ route('admin.testimonials.reject', $t->id) }}"
                                            data-name="{{ $t->name }}"
                                            title="Tolak testimoni">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                @endif

                                @if($t->isApproved())
                                    <!-- Kembalikan ke antrean peninjauan -->
                                    <form action="{{ route('admin.testimonials.unpublish', $t->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="testimonial-action-btn testimonial-action-pending" title="Kembalikan ke pending">
                                            <i class="fas fa-undo"></i> Unpublish
                                        </button>
                                    </form>
                                @endif

                                <!-- Delete -->
                                <form action="{{ route('admin.testimonials.destroy', $t->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="testimonial-action-btn testimonial-action-delete"
                                            title="Hapus testimoni"
                                            onclick="return confirm('Are you sure you want to delete this testimonial? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #828282;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                <i class="fas fa-quote-left" style="font-size: 48px; color: #e0e0e0;"></i>
                                <span style="font-size: 14px;">
                                    @if($activeStatus === 'pending')
                                        Tidak ada testimoni yang menunggu peninjauan.
                                    @else
                                        Belum ada testimoni dengan status ini.
                                    @endif
                                </span>
                                <span style="font-size: 12.5px; color: #b0b0b0;">
                                    Testimoni ditulis oleh siswa melalui halaman profil mereka.
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($testimonials->hasPages())
    <div class="pagination-container">
        {{ $testimonials->links() }}
    </div>
    @endif
</div>

<!-- ============ MODAL TOLAK TESTIMONI ============ -->
<div id="rejectTestimonialModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 33, 51, 0.55); z-index: 1060; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; padding: 24px; max-width: 460px; width: 100%; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
        <h3 style="font-size: 17px; font-weight: 700; color: #333; margin-bottom: 8px;">Tolak Testimoni</h3>
        <p style="font-size: 13px; color: #828282; line-height: 1.6; margin-bottom: 16px;">
            Testimoni dari <strong id="rejectTestimonialName"></strong> tidak akan tampil di halaman publik.
            Alasan penolakan akan dikirim sebagai notifikasi ke siswa.
        </p>
        <form id="rejectTestimonialForm" method="POST">
            @csrf
            <label for="rejection_reason" style="font-size: 13px; font-weight: 600; color: #333; display: block; margin-bottom: 6px;">
                Alasan penolakan (opsional)
            </label>
            <textarea name="rejection_reason" id="rejection_reason" rows="3" maxlength="500"
                      placeholder="Contoh: isi testimoni kurang relevan atau mengandung kata yang tidak pantas."
                      style="width: 100%; font-size: 13px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 8px; resize: vertical;"></textarea>
            <div style="display: flex; gap: 10px; margin-top: 18px;">
                <button type="button" id="cancelRejectBtn"
                        style="flex: 1; background: #ffffff; color: #828282; border: 1px solid #e0e0e0; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Batal
                </button>
                <button type="submit"
                        style="flex: 1; background: #f57c00; color: #ffffff; border: none; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Tolak Testimoni
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- Modal penolakan ----
    const modal = document.getElementById('rejectTestimonialModal');
    const form = document.getElementById('rejectTestimonialForm');
    const nameLabel = document.getElementById('rejectTestimonialName');

    function closeRejectModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    document.querySelectorAll('.js-reject-testimonial').forEach(function(button) {
        button.addEventListener('click', function() {
            form.action = this.dataset.action;
            nameLabel.textContent = this.dataset.name || 'siswa ini';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    });

    document.getElementById('cancelRejectBtn').addEventListener('click', closeRejectModal);

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeRejectModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeRejectModal();
    });

    // ---- Pencarian di sisi klien ----
    const searchInput = document.getElementById('testimonialSearch');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();

            document.querySelectorAll('tbody tr').forEach(function(row) {
                if (row.querySelector('td[colspan]')) return;

                const text = row.textContent.toLowerCase();
                row.style.display = (term === '' || text.includes(term)) ? '' : 'none';
            });
        });
    }

    // ---- Hover baris tabel ----
    document.querySelectorAll('tbody tr').forEach(function(row) {
        row.addEventListener('mouseenter', function() { this.style.backgroundColor = '#f8f9fa'; });
        row.addEventListener('mouseleave', function() { this.style.backgroundColor = ''; });
    });
});
</script>
@endsection
