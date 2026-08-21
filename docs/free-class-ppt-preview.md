# Pratinjau Slide (PPT) di Halaman Free Class

Dokumen ini menjelaskan cara kerja modal pratinjau materi di
`/free-classes/{id}`, khususnya bagian slide presentasi. Ditulis karena ukuran
kotak pratinjau diatur bersama-sama oleh JavaScript dan CSS — mengubah salah
satu tanpa yang lain akan memunculkan lagi scrollbar atau pita kosong.

## Berkas yang terlibat

| Berkas | Peran |
|---|---|
| [`resources/views/free-classes/show.blade.php`](../resources/views/free-classes/show.blade.php) | Halaman detail; `@include` modal dan memuat JS-nya |
| [`resources/views/free-classes/partials/material.blade.php`](../resources/views/free-classes/partials/material.blade.php) | Kartu materi; tombol "Lihat" membawa `data-preview-url`, `data-preview-kind`, `data-preview-title` |
| [`resources/views/free-classes/partials/preview-modal.blade.php`](../resources/views/free-classes/partials/preview-modal.blade.php) | Markup modal: header, toolbar, panggung, footer |
| [`public/assets/js/free-class-preview.js`](../public/assets/js/free-class-preview.js) | Memuat PPTXjs, merender, menghitung ukuran kotak & skala |
| [`public/assets/css/free-class.css`](../public/assets/css/free-class.css) | Gaya modal; blok `.is-ppt` dan blok "PERBAIKAN POSISI SLIDE" di akhir berkas |
| `public/assets/vendor/pptx/` | PPTXjs beserta dependensinya (JSZip, filereader, dingbat) |

Modal ini dipakai bersama oleh **PDF** (`.is-pdf`) dan **PPT** (`.is-ppt`).
PDF diserahkan ke `<iframe>` bawaan browser; hanya PPT yang melewati alur di
bawah ini.

## Cara slide dirender

PPTXjs mengurai `.pptx` **di sisi klien** (tidak ada layanan luar, jadi tetap
jalan di localhost) lalu menyuntikkan satu `<div class="slide">` per slide ke
dalam `#pptxViewer`. Library menulis ukuran asli slide sebagai gaya inline:

```html
<div class="slide" style="width:960px; height:540px; ...">
```

Angka itu hasil konversi ukuran slide dari EMU di dalam berkas `.pptx`.
**Rasio slide dibaca dari sana, tidak dipatok 16:9**, sehingga deck 4:3 atau
ukuran kustom ikut tampil benar.

`style.width` dipakai lebih dulu daripada `offsetWidth` karena `renderSlide()`
menyembunyikan slide yang tidak aktif dengan `display: none` — dan elemen
tersembunyi selalu melaporkan `offsetWidth` bernilai 0.

## Bagaimana ukuran kotak ditentukan

`layoutStage()` menghitung tinggi lebih dulu dari ruang yang benar-benar
tersisa, baru menurunkan lebarnya dari rasio:

```
availH = innerHeight * 0.85 − (tinggi header + toolbar + footer) − 24
availW = innerWidth  * 0.90 − 32 − 24

h = max(180, availH)
w = h * rasio
bila w > availW  →  w = availW ; h = w / rasio
```

Hasilnya ditulis sebagai custom property `--preview-w` / `--preview-h` pada
`#materialPreviewModal`, lalu dipakai CSS untuk `.modal-dialog` **dan**
`.free-class-preview-frame` sekaligus. Karena keduanya membaca nilai yang sama,
lebar dialog mustahil berbeda dari lebar kotak — itulah yang dulu menyebabkan
pita abu-abu di kiri-kanan.

Setelah kotak terpasang, `fitToStage()` menghitung skala dari **kedua** sumbu:

```js
Math.min((stage.clientWidth - 24) / slide.width,
         (stage.clientHeight - 24) / slide.height)
```

## Kontrak antara JS dan CSS

Empat pasang nilai ini **harus tetap sama**. Kalau berbeda, skala fit meleset
dan scrollbar muncul kembali.

| Makna | JS (`free-class-preview.js`) | CSS (`free-class.css`) |
|---|---|---|
| Batas tinggi modal | `MAX_VIEWPORT_H = 0.85` | `.is-ppt .modal-content { max-height: 85vh }` |
| Batas lebar modal | `MAX_VIEWPORT_W = 0.90` | `.is-ppt .modal-dialog { max-width: 90vw }` |
| Padding panggung | `STAGE_PADDING = 24` | `.is-ppt .free-class-pptx-stage { padding: 12px }` (12 × 2) |
| Ukuran kotak | `--preview-w`, `--preview-h` | dibaca `.modal-dialog` & `.free-class-preview-frame` |

Batas 85vh mencakup header, toolbar, dan footer — `chromeHeight()` mengukur
ketiganya secara langsung, jadi menambah atau menghapus elemen di sana tidak
perlu penyesuaian angka.

## Zoom dan navigasi

- `state.fitted` menandai apakah skala saat ini masih hasil fit otomatis.
  Menekan **+ / −** membuatnya `false`.
- Selama `fitted`, panggung memakai `overflow: hidden` — seluruh slide dijamin
  muat sehingga scrollbar tidak boleh ada. Begitu pengguna memperbesar,
  `applyZoom()` memasang kelas `.is-zoomed` yang mengubahnya jadi
  `overflow: auto`.
- **Ganti slide** dan **resize jendela** menghitung ulang bila masih `fitted`;
  kalau pengguna sedang memakai zoom manual, hanya kotaknya yang disesuaikan
  agar pilihannya tidak dibatalkan diam-diam.
- Tombol **Sesuaikan** mengembalikan ke skala fit.
- Angka persen menampilkan skala nyata terhadap ukuran asli slide, jadi pada
  monitor besar wajar bila fit menghasilkan lebih dari 100%.

## Office Web Viewer

`$officeViewerUsable` di `preview-modal.blade.php` menandai apakah berkas dapat
diunduh oleh server Microsoft. Bernilai `0` di localhost, IP privat, domain
`.test`/`.local`, dan environment `local`.

Flag ini **hanya** dipakai tombol cadangan `#pptxOfficeFallback` yang muncul di
panel galat ketika render lokal gagal. Tombol footer "Buka di tab baru" beserta
konstanta `view.aspx`-nya sudah dihapus.

## Catatan pemeliharaan

- URL berkas berasal langsung dari `Storage::url(...)` melalui atribut
  `data-preview-url`. **Tidak ada route khusus pratinjau** — tombol Unduh dan
  tombol Lihat menunjuk berkas yang sama.
- PPTXjs mengambil berkas secara async dan tidak bisa dibatalkan.
  `state.renderToken` dinaikkan setiap `resetView()` agar hasil render lama yang
  datang terlambat dibuang, bukan menimpa slide yang baru dibuka.
- Modal PDF tidak terpengaruh perubahan apa pun di atas; semua aturan baru
  dibatasi selector `.is-ppt`.
