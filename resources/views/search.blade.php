@extends('layouts.app')

@section('title', $term !== '' ? 'Search: ' . $term : 'Search')

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/css/home-modern.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/search.css') }}">
<script>document.documentElement.classList.add('ml-js');</script>
@endsection

@section('content')
<div class="ml-home ml-search-page">
    <div class="container">

        <header class="ml-searchpage-head">
            {{-- Tombol kembali ke beranda, di kiri atas tepat di atas judul. --}}
            <a href="{{ route('home') }}" class="ml-back-home">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                <span>{{ __('Kembali ke Beranda') }}</span>
            </a>

            <h1 class="ml-searchpage-title">{{ __('Search') }}</h1>

            <form class="ml-searchpage-form"
                  id="mlCourseSearchForm"
                  method="GET"
                  action="{{ route('search') }}"
                  data-suggest-url="{{ route('search.suggestions') }}"
                  role="search"
                  aria-label="Search MersifLab">
                <div class="ml-search-field">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <label class="visually-hidden" for="mlCourseSearchInput">{{ __('Search MersifLab') }}</label>
                    <input type="search"
                           class="ml-search-input"
                           id="mlCourseSearchInput"
                           name="q"
                           value="{{ $term }}"
                           placeholder="{{ __('Search courses, pages, topics, or instructors...') }}"
                           autocomplete="off"
                           role="combobox"
                           aria-expanded="false"
                           aria-controls="mlSearchSuggestions"
                           aria-autocomplete="list"
                           @if($term === '') autofocus @endif>
                    <span class="ml-search-spinner" aria-hidden="true"></span>

                    <ul class="ml-suggest"
                        id="mlSearchSuggestions"
                        role="listbox"
                        aria-label="Search suggestions"
                        hidden></ul>
                </div>
                <button type="submit" class="ml-btn ml-btn-primary ml-search-submit">
                    <i class="fas fa-magnifying-glass ml-search-submit-icon" aria-hidden="true"></i>
                    <span>{{ __('Search') }}</span>
                </button>
            </form>
            <p class="visually-hidden" id="mlSearchStatus" role="status" aria-live="polite"></p>

            @if($term !== '' && $results['total'] > 0)
            <p class="ml-searchpage-count">
                {{ $results['total'] }} {{ \Illuminate\Support\Str::plural('result', $results['total']) }}
                for <strong>"{{ $term }}"</strong>
            </p>
            @endif
        </header>

        @if($term === '')
            <div class="ml-searchpage-state">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <h2>What are you looking for?</h2>
                <p>Search across courses, categories, instructors, and every page on MersifLab.</p>
            </div>

        @elseif(mb_strlen($term) < $minLength)
            <div class="ml-searchpage-state">
                <i class="fas fa-keyboard" aria-hidden="true"></i>
                <h2>Keep typing</h2>
                <p>Enter at least {{ $minLength }} characters to search.</p>
            </div>

        @elseif($results['total'] === 0)
            <div class="ml-searchpage-state">
                <i class="fas fa-circle-question" aria-hidden="true"></i>
                <h2>No results for "{{ $term }}"</h2>
                <p>Try a different keyword, or browse the full course catalogue.</p>
                <a href="{{ route('courses') }}#all-courses" class="ml-btn ml-btn-primary">
                    Browse All Courses
                    <i class="fas fa-arrow-right ml-btn-arrow" aria-hidden="true"></i>
                </a>
            </div>

        @else
            @foreach($results['groups'] as $key => $items)
            <section class="ml-result-group ml-reveal">
                <h2 class="ml-result-group-title">
                    {{ \App\Support\SiteSearch::groupLabel($key) }}
                    <span class="ml-result-count">{{ count($items) }}</span>
                </h2>

                <ul class="ml-result-list">
                    @foreach($items as $item)
                    <li>
                        <a class="ml-result" href="{{ $item['url'] }}">
                            <span class="ml-result-thumb ml-result-thumb--{{ $item['type'] }}">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="" loading="lazy">
                                @else
                                    <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>
                                @endif
                            </span>
                            <span class="ml-result-text">
                                <span class="ml-result-title">{{ $item['title'] }}</span>
                                <span class="ml-result-sub">{{ $item['subtitle'] }}</span>
                            </span>
                            <i class="fas fa-arrow-right ml-result-go" aria-hidden="true"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </section>
            @endforeach
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/home-modern.js') }}" defer></script>
@endsection
