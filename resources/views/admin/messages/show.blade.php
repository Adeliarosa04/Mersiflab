@extends('layouts.admin')

@section('title', 'View Message')

@section('content')
<div class="page-title">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Message Details</h1>
        <a href="{{ back_url(route('admin.messages.index')) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card-content">
            <div style="border-bottom: 1px solid #e0e0e0; padding-bottom: 15px; margin-bottom: 20px;">
                <h3>{{ $message->name }}</h3>
                <p class="text-muted mb-0">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                </p>
            </div>

            <div style="line-height: 1.6; color: #333; word-break: break-word;">
                {!! nl2br(e($message->message)) !!}
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <small class="text-muted">
                    <i class="fas fa-calendar"></i> 
                    Received on {{ $message->created_at->format('d F Y \a\t H:i') }}
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-content">
            <h5 class="mb-3">Actions</h5>
            
            @if(!$message->is_read)
                <form action="{{ route('admin.messages.mark-read', $message) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-check"></i> Mark as Read
                    </button>
                </form>
            @else
                <div class="alert alert-info mb-2">
                    <i class="fas fa-info-circle"></i> Already marked as read
                </div>
            @endif

            <!-- Reply via Gmail (opens compose in new tab) -->
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ urlencode($message->email) }}" target="_blank" class="btn btn-outline-success w-100 mb-2">
                <i class="fas fa-reply"></i> Reply via Gmail
            </a>

            <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100">
                    <i class="fas fa-trash"></i> Delete Message
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .card-content {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .btn {
        border-radius: 6px;
        font-size: 14px;
        padding: 8px 16px;
    }

    .alert {
        border-radius: 6px;
        padding: 12px;
        margin: 0;
    }
</style>
@endsection
