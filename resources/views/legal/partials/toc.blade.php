{{-- Daftar isi dokumen legal --}}
<nav class="legal-toc" aria-label="Daftar isi">
    <h2>Daftar Isi</h2>
    <ol>
        @foreach($sections as $index => $section)
            <li><a href="#bagian-{{ $index + 1 }}">{{ $section }}</a></li>
        @endforeach
    </ol>
</nav>
