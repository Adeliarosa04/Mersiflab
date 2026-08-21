{{--
    Seksi "Free Class" pada halaman Courses.

    Kartu di sini sengaja minimalis: HANYA thumbnail + judul. Video player,
    deskripsi lengkap, dan modul PDF ada di halaman detail
    (route free-classes.show).

    Variabel:
      $freeClasses  Collection<FreeClass>  kelas gratis aktif, sudah terurut

    Seksi tidak dirender bila belum ada data, sehingga tata letak halaman
    Courses tetap seperti semula selama admin belum mengisi.
--}}
@if(isset($freeClasses) && $freeClasses->count() > 0)
<section id="free-class" class="free-class-section mb-5">
    <div class="section-header">
        <h2 class="section-title">
            Free Class
            <span class="free-class-badge">Gratis</span>
        </h2>
        <p class="free-class-subtitle">Mulai belajar sekarang — pilih kelas untuk menonton video dan mengunduh modulnya.</p>
    </div>

    <div class="row g-3">
        @foreach($freeClasses as $freeClass)
            <div class="col-lg-3 col-md-4 col-sm-6">
                <a href="{{ route('free-classes.show', $freeClass) }}" class="free-class-card">
                    <div class="free-class-thumb">
                        @if($freeClass->thumbnail_url)
                            <img src="{{ $freeClass->thumbnail_url }}" alt="{{ $freeClass->title }}" loading="lazy">
                        @else
                            <div class="free-class-thumb-placeholder" aria-hidden="true">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        @endif

                        <span class="free-class-thumb-tag">Gratis</span>
                    </div>

                    <div class="free-class-body">
                        <h3 class="free-class-title">{{ $freeClass->title }}</h3>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</section>
@endif
