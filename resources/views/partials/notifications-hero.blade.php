{{--
    Hero Banner halaman Notifikasi — dipakai bersama oleh Siswa, Guru, dan Admin
    supaya tampilannya benar-benar seragam dan hanya perlu diubah di satu tempat.

    Parameter:
      $title    (opsional) judul banner. Default: "Notification Center".
      $subtitle (opsional) sub-teks di bawah judul. Kosongkan untuk menyembunyikan.

    Contoh:
      @include('partials.notifications-hero', [
          'subtitle' => 'Stay updated with your latest activities',
      ])

    Catatan penamaan kelas: sengaja memakai awalan "notif-hero-" dan BUKAN
    .page-title / .page-subtitle, karena:
      - layouts/admin.blade.php punya aturan .page-title / .page-title h1 sendiri;
      - assets/css/ui-refinements.css (dimuat paling akhir pada layouts/app)
        memberi .page-subtitle margin auto yang membuat sub-teks ter-center.
    Dengan nama kelas tersendiri, banner ini tampil identik di semua layout.
--}}
@php
    $notifHeroTitle = trim($title ?? 'Notification Center');
    $notifHeroSubtitle = trim($subtitle ?? '');

    // Varian tampilan:
    //   'hero'  (default) - kartu banner biru + wadah ikon lonceng. Dipakai Admin.
    //   'plain'           - judul teks biasa tanpa kartu & tanpa ikon, gaya
    //                       minimalis. Dipakai halaman Guru dan Siswa.
    $notifHeroVariant = ($variant ?? 'hero') === 'plain' ? 'plain' : 'hero';
@endphp

@once
<style>
/* ── Hero Banner Notifikasi (bersama: Siswa, Guru, Admin) ── */
.notif-hero {
    margin-bottom: 20px;
}

/* Kartu biru bersudut tumpul. Ikon di kiri, blok teks di kanannya. */
.notif-hero-content {
    display: flex;
    align-items: center;
    gap: 20px;
    background: linear-gradient(135deg, #1A76D1 0%, #2196F3 100%);
    padding: 30px 35px;
    border-radius: 20px;
    box-shadow: 0 8px 24px rgba(26, 118, 209, 0.25);
}

/* Wadah kotak untuk ikon lonceng. */
.notif-hero-icon {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #fff;
    flex-shrink: 0;
    backdrop-filter: blur(10px);
}

/* Pembungkus teks: judul dan sub-teks tersusun vertikal, rata kiri. */
.notif-hero-text {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    gap: 6px;
}

.notif-hero-text .notif-hero-title {
    font-size: 2rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
    line-height: 1.2;
    text-align: left;
}

.notif-hero-text .notif-hero-subtitle {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    line-height: 1.5;
    text-align: left;
}

@media (max-width: 992px) {
    .notif-hero-content {
        padding: 24px 28px;
    }

    .notif-hero-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }

    .notif-hero-text .notif-hero-title {
        font-size: 1.75rem;
    }
}

/* Pada layar kecil banner menumpuk dan rata tengah. */
@media (max-width: 768px) {
    .notif-hero-content {
        flex-direction: column;
        text-align: center;
        padding: 28px 24px;
    }

    .notif-hero-text {
        align-items: center;
    }

    .notif-hero-text .notif-hero-title {
        font-size: 1.5rem;
        text-align: center;
    }

    .notif-hero-text .notif-hero-subtitle {
        font-size: 0.9rem;
        text-align: center;
    }
}

/* ── Varian "plain": judul teks biasa di luar card (Guru & Siswa) ── */
.notif-plain-header {
    /* Jarak ke section "All Notifications" di bawahnya. */
    margin-bottom: 24px;
}

.notif-plain-header .notif-plain-title {
    /* Setara text-3xl bold, dengan warna teks utama MersifLab. */
    font-size: 1.875rem;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.25;
    letter-spacing: -0.02em;
    margin: 0 0 6px 0;
    text-align: left;
}

.notif-plain-header .notif-plain-subtitle {
    font-size: 0.95rem;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
    /* ui-refinements.css memberi .page-subtitle margin auto; kelas ini
       sengaja berbeda supaya sub-teks tetap rata kiri di bawah judul. */
    text-align: left;
    max-width: none;
}

@media (max-width: 768px) {
    .notif-plain-header {
        margin-bottom: 18px;
    }

    .notif-plain-header .notif-plain-title {
        font-size: 1.5rem;
    }

    .notif-plain-header .notif-plain-subtitle {
        font-size: 0.9rem;
    }
}
</style>
@endonce

@if($notifHeroVariant === 'plain')
    {{-- Gaya minimalis: judul teks biasa, tanpa kartu biru dan tanpa ikon. --}}
    <div class="notif-plain-header">
        <h1 class="notif-plain-title">{{ $notifHeroTitle }}</h1>
        @if($notifHeroSubtitle !== '')
            <p class="notif-plain-subtitle">{{ $notifHeroSubtitle }}</p>
        @endif
    </div>
@else
    {{-- Gaya hero: kartu biru dengan wadah ikon lonceng di kiri. --}}
    <div class="notif-hero">
        <div class="notif-hero-content">
            <div class="notif-hero-icon">
                <i class="fas fa-bell"></i>
            </div>
            <div class="notif-hero-text">
                <h1 class="notif-hero-title">{{ $notifHeroTitle }}</h1>
                @if($notifHeroSubtitle !== '')
                    <p class="notif-hero-subtitle">{{ $notifHeroSubtitle }}</p>
                @endif
            </div>
        </div>
    </div>
@endif
