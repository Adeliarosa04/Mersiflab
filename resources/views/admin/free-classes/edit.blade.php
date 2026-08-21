@extends('layouts.admin')

@section('title', 'Edit Free Course')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/free-class-admin.css') }}">
@endsection

@section('content')
<div class="page-title">
    <h1>Edit Free Course</h1>
</div>

<div class="card-content">
    <div class="card-content-title">
        <span>{{ $freeClass->title }}</span>
        <span class="d-inline-flex align-items-center gap-2 flex-wrap">
            @include('partials.guide-book')
            <a href="{{ back_url(route('admin.free-classes.index')) }}" class="btn btn-secondary" style="font-size: 13px; padding: 6px 16px;">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </span>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.free-classes.update', $freeClass) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.free-classes.partials.form', ['freeClass' => $freeClass])

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ back_url(route('admin.free-classes.index')) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Update Free Course
            </button>
        </div>
    </form>
</div>
@endsection
