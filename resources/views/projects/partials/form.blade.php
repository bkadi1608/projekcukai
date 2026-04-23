@php
    $suratPermohonanPreviewUrl = isset($project) && $project?->exists && ! empty($project->surat_permohonan_pdf_path)
        ? route('projects.surat-permohonan-pdf', $project)
        : null;
    $selectedPengirim = old('pengirim', $project->pengirim ?? $project->perusahaan ?? '');
    $labelProjectTanpaPabrik = old('label_project_tanpa_pabrik', $project->label_project_tanpa_pabrik ?? '');
    $tanpaPabrikTujuan = old('tanpa_pabrik_tujuan');

    if ($tanpaPabrikTujuan === null) {
        $tanpaPabrikTujuan = isset($project) && $project?->exists && blank($selectedPengirim) ? '1' : '0';
    }
@endphp

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Surat Permohonan</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="jenis_permohonan">Jenis Permohonan</label>
                    <select id="jenis_permohonan" name="jenis_permohonan" class="form-control searchable-select @error('jenis_permohonan') is-invalid @enderror" data-placeholder="Pilih jenis permohonan" required>
                        <option value="">Pilih jenis permohonan</option>
                        @foreach($jenisPermohonanOptions as $option)
                            <option value="{{ $option }}" @selected(old('jenis_permohonan', $project->jenis_permohonan ?? '') === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_permohonan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="d-flex align-items-center justify-content-between">
                        <label for="pengirim">Pengirim</label>
                        <button type="button" class="btn btn-link btn-sm p-0" id="toggle-pengirim-mode" data-mode="aktif">
                            Tampilkan semua perusahaan
                        </button>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input
                            id="tanpa_pabrik_tujuan"
                            type="checkbox"
                            name="tanpa_pabrik_tujuan"
                            value="1"
                            class="custom-control-input"
                            @checked($tanpaPabrikTujuan === '1')
                        >
                        <label class="custom-control-label" for="tanpa_pabrik_tujuan">
                            Tidak ada pabrik tujuan / tidak ada surat permohonan
                        </label>
                    </div>
                    <select id="pengirim" name="pengirim" class="form-control searchable-select @error('pengirim') is-invalid @enderror" data-placeholder="Pilih NPPBKC - Perusahaan">
                        <option value="">Pilih NPPBKC - Perusahaan</option>
                        @foreach($pengirimAktifOptions as $option)
                            <option value="{{ $option }}" @selected($selectedPengirim === $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted" id="pengirim-mode-label">
                        Menampilkan perusahaan aktif.
                    </small>
                    <small class="form-text text-muted d-none" id="pengirim-empty-label">
                        Mode tanpa pabrik tujuan aktif. Pengirim boleh dikosongkan.
                    </small>
                    @error('pengirim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="surat-permohonan-section">
                    <label for="surat_permohonan_pdf">PDF Surat Permohonan</label>
                    <div class="surat-permohonan-upload-row">
                        <input id="surat_permohonan_pdf" type="file" name="surat_permohonan_pdf" accept="application/pdf" class="form-control-file @error('surat_permohonan_pdf') is-invalid @enderror">
                        <button id="extract-surat-permohonan" type="button" class="btn btn-outline-primary btn-sm" disabled>
                            <i class="fas fa-magic fa-sm mr-1"></i>
                            Extract Data
                        </button>
                    </div>
                    @error('surat_permohonan_pdf')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small id="surat-permohonan-extract-status" class="form-text text-muted"></small>
                    @if(! empty($project?->surat_permohonan_pdf_path))
                        <small class="form-text text-muted">
                            PDF sudah diupload. OCR terakhir:
                            {{ $project->surat_permohonan_ocr_engine ?? 'belum tersedia' }}
                            @if($project->surat_permohonan_ocr_at)
                                ({{ $project->surat_permohonan_ocr_at->format('d/m/Y H:i') }})
                            @endif
                        </small>
                    @else
                        <small class="form-text text-muted">
                            Jika PDF punya teks atau OCR tersedia, nomor, tanggal, dan hal akan dicoba diisi otomatis.
                        </small>
                    @endif
                    <small class="form-text text-muted d-none" id="surat-permohonan-disabled-label">
                        Mode tanpa pabrik tujuan aktif. Data surat permohonan tidak digunakan.
                    </small>
                </div>

                <div class="form-group d-none" id="label-project-tanpa-pabrik-group">
                    <label for="label_project_tanpa_pabrik">Pembeda Project</label>
                    <input
                        id="label_project_tanpa_pabrik"
                        type="text"
                        name="label_project_tanpa_pabrik"
                        value="{{ $labelProjectTanpaPabrik }}"
                        class="form-control @error('label_project_tanpa_pabrik') is-invalid @enderror"
                        placeholder="Contoh: Monitoring April 2026 / Tindak Lanjut Audit"
                    >
                    <small class="form-text text-muted">
                        Dipakai untuk membedakan project saat tidak ada pabrik tujuan. Jenis permohonan tetap diisi terpisah.
                    </small>
                    @error('label_project_tanpa_pabrik')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row" id="surat-permohonan-fields">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="no_surat_permohonan">No Surat Permohonan</label>
                            <input id="no_surat_permohonan" type="text" name="no_surat_permohonan" value="{{ old('no_surat_permohonan', $project->no_surat_permohonan ?? '') }}" class="form-control @error('no_surat_permohonan') is-invalid @enderror">
                            @error('no_surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tgl_surat_permohonan">Tgl Surat Permohonan</label>
                            <input id="tgl_surat_permohonan" type="date" name="tgl_surat_permohonan" value="{{ old('tgl_surat_permohonan', optional($project?->tgl_surat_permohonan)->format('Y-m-d') ?? $project->tgl_surat_permohonan ?? '') }}" class="form-control @error('tgl_surat_permohonan') is-invalid @enderror">
                            @error('tgl_surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="hal_surat_permohonan">Hal Surat Permohonan</label>
                    <textarea id="hal_surat_permohonan" name="hal_surat_permohonan" rows="3" class="form-control @error('hal_surat_permohonan') is-invalid @enderror">{{ old('hal_surat_permohonan', $project->hal_surat_permohonan ?? '') }}</textarea>
                    @error('hal_surat_permohonan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end align-items-center">
                    <a href="{{ route('projects.index') }}" class="btn btn-light mr-2">
                        Kembali
                    </a>
                    <button class="btn {{ $submitClass }}" type="submit">
                        <i class="fas fa-save fa-sm mr-1"></i>
                        {{ $submitLabel }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Preview PDF</h6>
            </div>
            <div class="card-body p-2">
                <div id="surat-permohonan-preview-empty" class="pdf-preview-empty {{ $suratPermohonanPreviewUrl ? 'd-none' : '' }}">
                    <i class="fas fa-file-pdf fa-2x text-gray-400 mb-2"></i>
                    <span class="text-muted small">Pilih PDF untuk melihat preview.</span>
                </div>
                <iframe
                    id="surat-permohonan-preview"
                    class="pdf-preview-frame {{ $suratPermohonanPreviewUrl ? '' : 'd-none' }}"
                    src="{{ $suratPermohonanPreviewUrl ?? '' }}"
                    title="Preview PDF Surat Permohonan"
                ></iframe>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <style>
        .pdf-preview-frame {
            border: 0;
            border-radius: .35rem;
            height: 640px;
            width: 100%;
        }

        .pdf-preview-empty {
            align-items: center;
            border: 1px dashed #d1d3e2;
            border-radius: .35rem;
            display: flex;
            flex-direction: column;
            height: 640px;
            justify-content: center;
            text-align: center;
        }

        .surat-permohonan-upload-row {
            align-items: center;
            display: grid;
            gap: .75rem;
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .surat-permohonan-upload-row .form-control-file {
            min-width: 0;
        }

        .surat-permohonan-upload-row .btn {
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <textarea id="project-pengirim-aktif-options" class="d-none">{{ base64_encode($pengirimAktifOptions->toJson()) }}</textarea>
    <textarea id="project-pengirim-semua-options" class="d-none">{{ base64_encode($pengirimSemuaOptions->toJson()) }}</textarea>
    <textarea id="project-selected-pengirim" class="d-none">{{ base64_encode($selectedPengirim) }}</textarea>
    <textarea id="project-extract-url" class="d-none">{{ base64_encode(route('projects.extract-surat-permohonan')) }}</textarea>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/project-form.js') }}"></script>
@endpush
