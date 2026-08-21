@extends('layouts.admin')

@section('title', 'Testimonial Detail - Admin')

@section('styles')
<style>
    .btn-back-testimonial {
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

    .btn-back-testimonial:hover {
        background: #f8f9fa;
        border-color: #2F80ED;
        color: #1c62c4;
        box-shadow: 0 2px 6px rgba(47, 128, 237, 0.15);
    }

    .btn-back-testimonial i {
        transition: transform 0.2s ease;
    }

    .btn-back-testimonial:hover i {
        transform: translateX(-3px);
    }

    .detail-row {
        display: flex;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid #f8f9fa;
        font-size: 13px;
    }

    .detail-label {
        width: 160px;
        flex-shrink: 0;
        color: #828282;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        color: #333;
        line-height: 1.7;
    }

    .detail-stars {
        color: #f5a623;
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 12px;">
    <a href="{{ back_url(route('admin.testimonials.index', ['status' => $testimonial->status])) }}" class="btn-back-testimonial">
        <i class="fas fa-arrow-left"></i>Kembali ke Moderasi Testimoni
    </a>
</div>

<div class="page-title" style="margin-bottom: 20px;">
    <h1>Testimonial Detail</h1>
</div>

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

<div class="row">
    <div class="col-lg-8">
        <div class="card-content">
            <div class="card-content-title">
                <span><i class="fas fa-quote-left me-2"></i>Isi Testimoni</span>
            </div>

            <div class="detail-row">
                <div class="detail-label">Siswa</div>
                <div class="detail-value">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="{{ $testimonial->avatarUrl() }}" alt="avatar" style="width:40px; height:40px; object-fit:cover; border-radius:8px;">
                        <div>
                            <strong>{{ $testimonial->name }}</strong>
                            @if($testimonial->user)
                                <div style="color: #828282; font-size: 12px;">{{ $testimonial->user->email }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Rating</div>
                <div class="detail-value">
                    @if($testimonial->rating)
                        <span class="detail-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa{{ $i <= $testimonial->rating ? 's' : 'r' }} fa-star"></i>
                            @endfor
                        </span>
                        <span style="color: #828282; margin-left: 6px;">{{ $testimonial->rating }}/5</span>
                    @else
                        <span style="color: #828282;">Tidak ada rating</span>
                    @endif
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Kursus</div>
                <div class="detail-value">{{ $testimonial->course->name ?? 'Testimoni umum tentang MersifLab' }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Posisi</div>
                <div class="detail-value">{{ $testimonial->position ?: '—' }}</div>
            </div>

            <div class="detail-row" style="border-bottom: none;">
                <div class="detail-label">Testimoni</div>
                <div class="detail-value">{{ $testimonial->content }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-content">
            <div class="card-content-title">
                <span><i class="fas fa-gavel me-2"></i>Moderasi</span>
            </div>

            <div class="detail-row">
                <div class="detail-label" style="width: 110px;">Status</div>
                <div class="detail-value">
                    <span class="badge" style="padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;
                        @if($testimonial->isApproved()) background: #e8f5e8; color: #2e7d32;
                        @elseif($testimonial->isRejected()) background: #ffebee; color: #c62828;
                        @else background: #fff3e0; color: #f57c00; @endif">
                        {{ $testimonial->status_label }}
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label" style="width: 110px;">Dikirim</div>
                <div class="detail-value">{{ $testimonial->created_at->format('d M Y, H:i') }} WIB</div>
            </div>

            @if($testimonial->reviewed_at)
                <div class="detail-row">
                    <div class="detail-label" style="width: 110px;">Ditinjau</div>
                    <div class="detail-value">
                        {{ $testimonial->reviewed_at->format('d M Y, H:i') }} WIB
                        @if($testimonial->reviewer)
                            <div style="color: #828282; font-size: 12px;">oleh {{ $testimonial->reviewer->name }}</div>
                        @endif
                    </div>
                </div>
            @endif

            @if($testimonial->rejection_reason)
                <div class="detail-row">
                    <div class="detail-label" style="width: 110px;">Alasan</div>
                    <div class="detail-value">{{ $testimonial->rejection_reason }}</div>
                </div>
            @endif

            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 18px;">
                @if(!$testimonial->isApproved())
                    <form action="{{ route('admin.testimonials.approve', $testimonial->id) }}" method="POST">
                        @csrf
                        <button type="submit" style="width: 100%; background: #2e7d32; color: #ffffff; border: none; padding: 11px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-check me-1"></i>Approve &amp; Publikasikan
                        </button>
                    </form>
                @endif

                @if(!$testimonial->isRejected())
                    <form action="{{ route('admin.testimonials.reject', $testimonial->id) }}" method="POST">
                        @csrf
                        <textarea name="rejection_reason" rows="3" maxlength="500"
                                  placeholder="Alasan penolakan (opsional)"
                                  style="width: 100%; font-size: 13px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 8px; resize: vertical; margin-bottom: 10px;"></textarea>
                        <button type="submit" style="width: 100%; background: #f57c00; color: #ffffff; border: none; padding: 11px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-times me-1"></i>Reject / Tolak
                        </button>
                    </form>
                @endif

                @if($testimonial->isApproved())
                    <form action="{{ route('admin.testimonials.unpublish', $testimonial->id) }}" method="POST">
                        @csrf
                        <button type="submit" style="width: 100%; background: #ffffff; color: #495057; border: 1px solid #e0e0e0; padding: 11px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-undo me-1"></i>Kembalikan ke Pending
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}"
                   style="width: 100%; display: block; text-align: center; background: #ffffff; color: #2F80ED; border: 1px solid #e0e0e0; padding: 11px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none;">
                    <i class="fas fa-edit me-1"></i>Edit Isi Testimoni
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
