{{--
    Kartu satu materi unduhan (Modul PDF / Slide Presentasi).

    Dipakai oleh kedua jenis berkas supaya struktur, padding, dan kelompok
    tombolnya identik — sisi kiri informasi, sisi kanan aksi.

    Variabel:
      $kind      string  'pdf' | 'ppt'  — menentukan cara pratinjau
      $icon      string  kelas ikon Font Awesome
      $iconColor string  warna ikon
      $label     string  judul materi
      $fileName  string  nama berkas
      $fileUrl   string  URL berkas
      $downloadLabel string teks tombol unduh
--}}
<div class="free-class-material">
    <div class="free-class-material-info">
        <span class="free-class-material-icon">
            <i class="{{ $icon }}" style="color: {{ $iconColor }}"></i>
        </span>
        <span class="free-class-material-text">
            <strong>{{ $label }}</strong>
            <span class="free-class-material-file" title="{{ $fileName }}">{{ $fileName }}</span>
        </span>
    </div>

    <div class="free-class-material-actions">
        <button type="button"
                class="free-class-btn free-class-btn-outline"
                data-preview-url="{{ $fileUrl }}"
                data-preview-kind="{{ $kind }}"
                data-preview-title="{{ $label }} — {{ $fileName }}">
            <i class="fas fa-eye"></i> Lihat
        </button>

        <a href="{{ $fileUrl }}" download="{{ $fileName }}"
           class="free-class-btn free-class-btn-primary">
            <i class="fas fa-download"></i> {{ $downloadLabel }}
        </a>
    </div>
</div>
