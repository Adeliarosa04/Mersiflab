{{--
    Isi dokumen legal.

    - Jika admin sudah mengisi teks resmi pada tabel `settings`
      (key `terms_content` / `privacy_content`), teks tersebut yang ditampilkan.
    - Jika belum, halaman tetap tampil dengan kerangka dokumen agar link
      Terms & Conditions / Privacy Policy tidak menghasilkan 404.
--}}
@if($content)
    <div class="legal-section">
        {!! $content !!}
    </div>
@else
    <div class="legal-notice">
        <i class="fas fa-info-circle"></i>
        <div>
            Naskah resmi {{ $documentName }} MersifLab sedang difinalisasi oleh
            PT Reka Mersif Abadi. Untuk pertanyaan terkait dokumen ini, silakan
            hubungi kami melalui kontak di bawah.
        </div>
    </div>

    @foreach($sections as $index => $section)
        <section class="legal-section" id="bagian-{{ $index + 1 }}">
            <h2>{{ $index + 1 }}. {{ $section }}</h2>
            <p class="legal-placeholder">Isi bagian ini akan segera diperbarui.</p>
        </section>
    @endforeach
@endif

<div class="legal-contact">
    <strong>PT Reka Mersif Abadi</strong><br>
    Nuryawan, Kepanjen, Delanggu, Klaten Regency, Central Java 57471<br>
    Email: <a href="mailto:support@ptreka.com">support@ptreka.com</a><br>
    Telepon: <a href="tel:+6282226841782">+62 822-2684-1782</a>
</div>
