{{--
    Field bersama untuk form Create & Edit Free Course.

    Variabel:
      $freeClass  FreeClass|null  entri yang sedang diedit (null saat create)
--}}
@php $freeClass = $freeClass ?? null; @endphp

<div class="mb-3">
    <label for="title" class="form-label">Judul Kursus Gratis <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror"
           id="title" name="title" value="{{ old('title', $freeClass->title ?? '') }}"
           placeholder="Contoh: Pengenalan Augmented Reality" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="4"
              placeholder="Keterangan singkat tentang kelas gratis ini..." required>{{ old('description', $freeClass->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Maksimal 2000 karakter. Tampil di kartu Free Course pada halaman Courses.</small>
</div>

<hr class="my-4">

<h6 class="mb-3" style="font-weight: 600; color: #333;">
    <i class="fas fa-image me-1" style="color: #1A76D1;"></i>Thumbnail Kartu
</h6>

<div class="mb-3">
    <label for="thumbnail_file" class="form-label">Unggah Thumbnail</label>
    <input type="file" class="form-control @error('thumbnail_file') is-invalid @enderror"
           id="thumbnail_file" name="thumbnail_file" accept="image/jpeg,image/png,image/webp">
    @error('thumbnail_file')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">
        Opsional. JPG/PNG/WebP maksimal 4 MB, rasio 16:10 paling pas.
        Bila dikosongkan, cover video YouTube dipakai otomatis; kalau tidak ada juga, tampil placeholder.
    </small>

    @if($freeClass && $freeClass->thumbnail_path)
        <div class="mt-2 p-2" style="background: #f5f5f5; border-radius: 6px; font-size: 13px;">
            <img src="{{ $freeClass->thumbnail_url }}" alt="Thumbnail saat ini"
                 style="height: 64px; border-radius: 4px; object-fit: cover; vertical-align: middle;">
            <span class="text-muted ms-2">Unggah berkas baru untuk menggantinya.</span>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="remove_thumbnail" name="remove_thumbnail" value="1">
                <label class="form-check-label" for="remove_thumbnail">Hapus thumbnail ini</label>
            </div>
        </div>
    @endif
</div>

<hr class="my-4">

{{-- ==================================================================
     LEVEL MATERI (dinamis)
     ================================================================== --}}
@php
    // Sumber data level, berurutan prioritas:
    //   1. old() -> setelah validasi gagal, isian admin tidak hilang
    //   2. level tersimpan -> saat membuka form edit
    //   3. satu blok kosong -> saat membuat Free Course baru
    $oldLevels = old('levels');
    $savedLevels = $freeClass?->levels ?? collect();
@endphp

<div class="free-level-section">
    <div class="free-level-section-head">
        <div>
            <h6 class="mb-1" style="font-weight: 600; color: #333;">
                <i class="fas fa-layer-group me-1" style="color: #1A76D1;"></i>Level Materi
                <span class="text-danger">*</span>
            </h6>
            <p class="text-muted mb-0" style="font-size: 13px;">
                Satu Free Course bisa punya beberapa level. Setiap level memiliki video,
                modul PDF, dan slide PPT sendiri. Minimal satu level.
            </p>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="addLevelBtn">
            <i class="fas fa-plus me-1"></i>Add Level
        </button>
    </div>

    @error('levels')
        <div class="alert alert-danger py-2 px-3" style="font-size: 13px;">{{ $message }}</div>
    @enderror

    <div id="levelsWrapper">
        @if(is_array($oldLevels) && count($oldLevels) > 0)
            @foreach($oldLevels as $index => $oldLevel)
                @include('admin.free-classes.partials.level-fields', [
                    'index' => $index,
                    'level' => ! empty($oldLevel['id']) ? $savedLevels->firstWhere('id', (int) $oldLevel['id']) : null,
                    'old' => $oldLevel,
                ])
            @endforeach
        @elseif($savedLevels->count() > 0)
            @foreach($savedLevels as $index => $level)
                @include('admin.free-classes.partials.level-fields', [
                    'index' => $index,
                    'level' => $level,
                    'old' => [],
                ])
            @endforeach
        @else
            @include('admin.free-classes.partials.level-fields', [
                'index' => 0,
                'level' => null,
                'old' => [],
            ])
        @endif
    </div>
</div>

{{-- Cetakan untuk level baru; __INDEX__ diganti JS saat dikloning. --}}
<template id="levelTemplate">
    @include('admin.free-classes.partials.level-fields', [
        'index' => '__INDEX__',
        'level' => null,
        'old' => [],
    ])
</template>

<hr class="my-4">

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="sort_order" class="form-label">Urutan Tampil</label>
        <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
               id="sort_order" name="sort_order" min="0" max="9999"
               value="{{ old('sort_order', $freeClass->sort_order ?? 0) }}">
        @error('sort_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Angka kecil tampil lebih dulu.</small>
    </div>

    <div class="col-md-8 mb-3">
        <label class="form-label d-block">Status</label>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                   value="1" {{ old('is_active', $freeClass->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <small class="form-text text-muted">Hanya Free Course aktif yang tampil di halaman Courses.</small>
    </div>
</div>

{{-- ==================================================================
     Perilaku repeater level.
     Diletakkan di partial agar create & edit otomatis ikut mendapatkannya.
     ================================================================== --}}
<script>
(function () {
    const wrapper = document.getElementById('levelsWrapper');
    const template = document.getElementById('levelTemplate');
    const addBtn = document.getElementById('addLevelBtn');

    if (!wrapper || !template || !addBtn) return;

    // Indeks baru selalu lebih besar dari indeks mana pun yang sudah dipakai,
    // sehingga name="levels[i][...]" tidak pernah bentrok meski ada level
    // yang dihapus di tengah (PHP menerima indeks yang tidak berurutan).
    let nextIndex = wrapper.querySelectorAll('[data-level-item]').length;

    function renumber() {
        const items = wrapper.querySelectorAll('[data-level-item]');
        items.forEach((item, i) => {
            const badge = item.querySelector('[data-level-number]');
            if (badge) badge.textContent = 'Level ' + (i + 1);

            // Level terakhir yang tersisa tidak boleh dihapus — minimal satu.
            const removeBtn = item.querySelector('[data-level-remove]');
            if (removeBtn) removeBtn.style.display = items.length > 1 ? '' : 'none';
        });
    }

    addBtn.addEventListener('click', function () {
        const html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex++));
        const holder = document.createElement('div');
        holder.innerHTML = html.trim();

        const item = holder.querySelector('[data-level-item]');
        if (!item) return;

        wrapper.appendChild(item);
        renumber();

        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        const nameInput = item.querySelector('input[type="text"]');
        if (nameInput) nameInput.focus();
    });

    wrapper.addEventListener('click', function (event) {
        const removeBtn = event.target.closest('[data-level-remove]');
        if (!removeBtn) return;

        const items = wrapper.querySelectorAll('[data-level-item]');
        if (items.length <= 1) return;

        if (!confirm('Hapus level ini dari form?')) return;

        removeBtn.closest('[data-level-item]').remove();
        renumber();
    });

    renumber();
})();
</script>
