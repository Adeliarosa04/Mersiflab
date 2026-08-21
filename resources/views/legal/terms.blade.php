@extends('layouts.app')

@section('title', 'Terms and Conditions')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/legal.css') }}">
@endsection

@section('content')
<div class="container">
    <section class="legal-hero">
        <h1>Terms and Conditions</h1>
        <p>Syarat dan ketentuan penggunaan platform MersifLab</p>
        @if($updatedAt)
            <span class="legal-meta">Terakhir diperbarui: {{ $updatedAt->translatedFormat('d F Y') }}</span>
        @endif
    </section>

    <div class="legal-wrapper">
        <div class="row g-4">
            @unless($content)
                {{-- Daftar isi hanya relevan untuk kerangka bawaan. --}}
                <div class="col-lg-4">
                    @include('legal.partials.toc', ['sections' => $sections])
                </div>
            @endunless

            <div class="{{ $content ? 'col-12' : 'col-lg-8' }}">
                <div class="legal-content">
                    @include('legal.partials.body', [
                        'content' => $content,
                        'sections' => $sections,
                        'documentName' => 'Syarat dan Ketentuan',
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
