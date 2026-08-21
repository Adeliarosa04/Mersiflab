{{--
    Satu kelompok field untuk sebuah Level materi.

    Variabel:
      $index   int|string        indeks array pada name="levels[...]"
      $level   FreeClassLevel|null  level tersimpan (null untuk level baru)
      $old     array             nilai old() untuk indeks ini (setelah validasi gagal)

    Dipakai dua kali: untuk merender level yang sudah ada, dan sebagai isi
    <template> yang dikloning tombol "Add Level".
--}}
@php
    $level = $level ?? null;
    $old = $old ?? [];
    $nameValue = $old['name'] ?? $level?->name ?? '';
    $videoUrlValue = $old['video_url'] ?? $level?->video_url ?? '';
@endphp

<div class="free-level-item" data-level-item>
    <div class="free-level-head">
        <span class="free-level-badge" data-level-number>Level</span>
        <button type="button" class="free-level-remove" data-level-remove title="Hapus level ini">
            <i class="fas fa-trash"></i> Hapus Level
        </button>
    </div>

    @if($level)
        <input type="hidden" name="levels[{{ $index }}][id]" value="{{ $level->id }}">
    @endif

    <div class="mb-3">
        <label class="form-label">Nama Level <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error("levels.{$index}.name") is-invalid @enderror"
               name="levels[{{ $index }}][name]" value="{{ $nameValue }}"
               placeholder="Contoh: Level 1 — Pengenalan" required>
        @error("levels.{$index}.name")
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Tautan Video</label>
            <input type="url" class="form-control @error("levels.{$index}.video_url") is-invalid @enderror"
                   name="levels[{{ $index }}][video_url]" value="{{ $videoUrlValue }}"
                   placeholder="https://www.youtube.com/watch?v=...">
            @error("levels.{$index}.video_url")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">YouTube, Vimeo, atau URL langsung ke berkas video.</small>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Atau Unggah Video</label>
            <input type="file" class="form-control @error("levels.{$index}.video_file") is-invalid @enderror"
                   name="levels[{{ $index }}][video_file]" accept="video/mp4,video/webm,video/ogg">
            @error("levels.{$index}.video_file")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">MP4/WebM/OGG, maks 40 MB.</small>

            @if($level && $level->video_path)
                <div class="free-level-current">
                    <i class="fas fa-file-video"></i>
                    <a href="{{ $level->uploaded_video_url }}" target="_blank" rel="noopener">Video tersimpan</a>
                    <span class="text-muted">— unggah berkas baru untuk mengganti.</span>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Modul PDF</label>
            <input type="file" class="form-control @error("levels.{$index}.pdf_file") is-invalid @enderror"
                   name="levels[{{ $index }}][pdf_file]" accept="application/pdf">
            @error("levels.{$index}.pdf_file")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Opsional. PDF, maks 20 MB.</small>

            @if($level && $level->hasPdf())
                <div class="free-level-current">
                    <i class="fas fa-file-pdf" style="color:#d32f2f"></i>
                    <a href="{{ $level->pdf_url }}" target="_blank" rel="noopener">{{ $level->pdf_display_name }}</a>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" value="1"
                               id="remove_pdf_{{ $index }}" name="levels[{{ $index }}][remove_pdf]">
                        <label class="form-check-label" for="remove_pdf_{{ $index }}">Hapus modul ini</label>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label">Slide PPT</label>
            <input type="file" class="form-control @error("levels.{$index}.ppt_file") is-invalid @enderror"
                   name="levels[{{ $index }}][ppt_file]" accept=".ppt,.pptx,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation">
            @error("levels.{$index}.ppt_file")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Opsional. PPT/PPTX, maks 20 MB.</small>

            @if($level && $level->hasPpt())
                <div class="free-level-current">
                    <i class="fas fa-file-powerpoint" style="color:#d24726"></i>
                    <a href="{{ $level->ppt_url }}" target="_blank" rel="noopener">{{ $level->ppt_display_name }}</a>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" value="1"
                               id="remove_ppt_{{ $index }}" name="levels[{{ $index }}][remove_ppt]">
                        <label class="form-check-label" for="remove_ppt_{{ $index }}">Hapus slide ini</label>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
