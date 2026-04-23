@extends('layouts.app')

@section('title', 'Detail Project - Aplikasi Cukai')
@section('topbar-title', 'Project')
@section('page-title', $project->jenis_permohonan ?? 'Detail Project')
@section('page-subtitle', 'Ruang kerja project permohonan')

@section('page-actions')
    <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i>
        Kembali
    </a>
@endsection

@section('content')
    @php
        $nd = $project->ndPermohonanSt;
        $suratPermohonanPdfUrl = $project->surat_permohonan_pdf_path
            ? route('projects.surat-permohonan-pdf', $project)
            : null;
        $ndPdfUrl = $nd?->nd_pdf_path ? route('projects.nd-permohonan-st.pdf', $project) : null;
        $stPdfUrl = $nd?->st_pdf_path ? route('projects.nomor-st.pdf', $project) : null;
        $bulanIndonesia = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $ndTanggalLabel = $nd?->tanggal_nd
            ? $nd->tanggal_nd->format('j').' '.$bulanIndonesia[(int) $nd->tanggal_nd->format('n')].' '.$nd->tanggal_nd->format('Y')
            : null;
        $ndSubtitleStatus = match (true) {
            ! $nd => 'Belum buat ND Permohonan ST',
            (bool) $nd?->nd_pdf_uploaded_at => 'ND final sudah diupload',
            (bool) $nd?->word_generated_at => 'Word sudah dibuat, menunggu finalisasi ND',
            (bool) $nd?->updated_at => 'Draft ND Permohonan ST tersimpan',
            default => 'Belum buat ND Permohonan ST',
        };
        $stSubtitleStatus = match (true) {
            ! $nd => 'Lengkapi ND Permohonan ST terlebih dahulu',
            (bool) $nd?->st_pdf_uploaded_at => 'ST sudah diupload',
            filled($nd?->nomor_st) || filled($nd?->tanggal_st) => 'Nomor ST tersimpan',
            default => 'ST belum di Upload',
        };
        $ndProgressSteps = [
            [
                'label' => 'Buat draft',
                'done' => (bool) $nd,
                'tooltip' => $nd ? 'Draft ND sudah dibuat' : 'Draft ND belum dibuat',
            ],
            [
                'label' => 'Draft disimpan',
                'done' => (bool) $nd?->updated_at,
                'tooltip' => $nd?->updated_at
                    ? 'Draft terakhir disimpan '.$nd->updated_at->format('d/m/Y H:i')
                    : 'Simpan draft ND terlebih dahulu',
            ],
            [
                'label' => 'Buat Word',
                'done' => (bool) $nd?->word_generated_at,
                'tooltip' => $nd?->word_generated_at
                    ? 'Word dibuat '.$nd->word_generated_at->format('d/m/Y H:i')
                    : 'Word belum dibuat',
            ],
            [
                'label' => 'Upload ND PDF',
                'done' => (bool) $nd?->nd_pdf_uploaded_at,
                'tooltip' => $nd?->nd_pdf_uploaded_at
                    ? 'PDF diupload '.$nd->nd_pdf_uploaded_at->format('d/m/Y H:i')
                    : 'Upload PDF ND setelah ditandatangani',
            ],
        ];
        $tanpaPabrikTujuan = blank($project->pengirim) && blank($project->perusahaan);
    @endphp

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Jenis Permohonan</dt>
                        <dd class="col-sm-7">{{ $project->jenis_permohonan ?? '-' }}</dd>

                        <dt class="col-sm-5">No Surat</dt>
                        <dd class="col-sm-7">{{ $tanpaPabrikTujuan ? 'Tidak ada surat permohonan' : ($project->no_surat_permohonan ?? '-') }}</dd>

                        <dt class="col-sm-5">Tgl Surat</dt>
                        <dd class="col-sm-7">{{ $tanpaPabrikTujuan ? 'Tidak ada surat permohonan' : ($project->tgl_surat_permohonan?->format('d/m/Y') ?? '-') }}</dd>

                        <dt class="col-sm-5">Hal</dt>
                        <dd class="col-sm-7">{{ $tanpaPabrikTujuan ? 'Tidak ada surat permohonan' : ($project->hal_surat_permohonan ?? '-') }}</dd>

                        <dt class="col-sm-5">Pengirim</dt>
                        <dd class="col-sm-7">{{ filled($project->pengirim) ? $project->pengirim : 'Tidak ada pabrik tujuan' }}</dd>

                        @if($project->label_project_tanpa_pabrik)
                            <dt class="col-sm-5">Pembeda Project</dt>
                            <dd class="col-sm-7">{{ $project->label_project_tanpa_pabrik }}</dd>
                        @endif

                        <dt class="col-sm-5">PDF Surat Permohonan</dt>
                        <dd class="col-sm-7">
                            @if($suratPermohonanPdfUrl)
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-toggle="modal"
                                        data-target="#suratPermohonanPdfModal"
                                        title="Lihat PDF Surat Permohonan">
                                    <i class="fas fa-eye"></i>
                                </button>
                            @else
                                -
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dokumen</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach([
                            ['icon' => 'fa-file-alt', 'title' => 'ND Permohonan ST', 'subtitle' => $ndSubtitleStatus, 'uploaded' => (bool) $nd?->nd_pdf_uploaded_at, 'preview_url' => $ndPdfUrl, 'modal_target' => '#ndPdfModal'],
                            ['icon' => 'fa-hashtag', 'title' => 'Nomor ST', 'subtitle' => $stSubtitleStatus, 'uploaded' => (bool) $nd?->st_pdf_uploaded_at, 'preview_url' => $stPdfUrl, 'modal_target' => '#stPdfModal'],
                            ['icon' => 'fa-lock', 'title' => 'Berita Acara Segel'],
                            ['icon' => 'fa-search', 'title' => 'Berita Acara Pemeriksaan'],
                            ['icon' => 'fa-file-word', 'title' => 'Mail Merge'],
                            ['icon' => 'fa-archive', 'title' => 'Lampiran Project'],
                        ] as $module)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100 {{ !empty($module['uploaded']) ? 'document-card-success' : 'bg-light' }} {{ in_array($module['title'], ['ND Permohonan ST', 'Nomor ST'], true) ? 'document-card document-card-clickable' : 'document-card' }}"
                                     @if(in_array($module['title'], ['ND Permohonan ST', 'Nomor ST'], true)) role="button" tabindex="0" data-target-detail="{{ $module['title'] === 'ND Permohonan ST' ? '#nd-permohonan-st-detail' : '#nomor-st-detail' }}" @endif>
                                    <div class="d-flex align-items-center {{ in_array($module['title'], ['ND Permohonan ST', 'Nomor ST'], true) ? 'nd-status-row' : '' }}">
                                        <i class="fas {{ $module['icon'] }} text-primary fa-lg mr-3 flex-shrink-0"></i>
                                        <div class="flex-grow-1">
                                            <div class="font-weight-bold text-gray-800">{{ $module['title'] }}</div>
                                            @if(!empty($module['subtitle']))
                                                <small class="text-gray-600 d-block">{{ $module['subtitle'] }}</small>
                                            @else
                                                <small class="text-muted d-block">Tempat disiapkan</small>
                                            @endif
                                        </div>

                                        @if($module['title'] === 'ND Permohonan ST')
                                            <div class="nd-progress ml-3">
                                                @foreach($ndProgressSteps as $step)
                                                    <span class="nd-progress-dot {{ $step['done'] ? 'is-done' : '' }}"
                                                          data-toggle="tooltip"
                                                          data-placement="top"
                                                          title="{{ $step['label'] }} - {{ $step['tooltip'] }}">
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($module['preview_url']))
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary ml-3"
                                                    data-toggle="modal"
                                                    data-target="{{ $module['modal_target'] }}"
                                                    title="Lihat PDF"
                                                    aria-label="Lihat PDF">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="nd-permohonan-st-detail" class="d-none">
        @include('projects.partials.nd-permohonan-st-form', [
            'project' => $project,
            'pegawaiOptions' => $pegawaiOptions,
            'kepalaSeksiOptions' => $kepalaSeksiOptions,
            'tujuanOptions' => $tujuanOptions,
        ])
    </div>

    <div id="nomor-st-detail" class="d-none">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Nomor ST</h6>
                @if($nd?->nomor_st || $nd?->tanggal_st)
                    <span class="badge badge-success">
                        {{ $nd?->nomor_st ?: 'Nomor ST tersimpan' }}
                    </span>
                @else
                    <span class="badge badge-secondary">Belum dibuat</span>
                @endif
            </div>
            <div class="card-body">
                @if(! $nd)
                    <div class="alert alert-light border mb-0">
                        Lengkapi dan simpan ND Permohonan ST terlebih dahulu sebelum upload ST.
                    </div>
                @else
                    <form method="POST" action="{{ route('projects.nomor-st.store', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="st_pdf">Upload PDF ST</label>
                            <div class="surat-permohonan-upload-row">
                                <div class="custom-file flex-grow-1">
                                    <input id="st_pdf" type="file" name="st_pdf" accept="application/pdf" class="custom-file-input @error('st_pdf') is-invalid @enderror">
                                    <label class="custom-file-label" for="st_pdf">Telusuri PDF ST...</label>
                                </div>
                                <button id="extract-st-pdf" type="button" class="btn btn-outline-primary btn-sm" disabled>
                                    <i class="fas fa-magic fa-sm mr-1"></i>
                                    Extract Data
                                </button>
                            </div>
                            @error('st_pdf')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small id="st-pdf-extract-status" class="form-text text-muted"></small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-md-0">
                                    <label for="nomor_st">Nomor ST</label>
                                    <input id="nomor_st" type="text" name="nomor_st" value="{{ old('nomor_st', $nd?->nomor_st ?? '') }}" class="form-control @error('nomor_st') is-invalid @enderror" placeholder="Otomatis dari PDF atau isi manual">
                                    @error('nomor_st')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="tanggal_st">Tanggal ST</label>
                                    <input id="tanggal_st" type="date" name="tanggal_st" value="{{ old('tanggal_st', optional($nd?->tanggal_st)->format('Y-m-d')) }}" class="form-control @error('tanggal_st') is-invalid @enderror">
                                    @error('tanggal_st')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button class="btn btn-success" type="submit">
                                <i class="fas fa-save fa-sm mr-1"></i>
                                Simpan Nomor ST
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @if($suratPermohonanPdfUrl)
        <div class="modal fade" id="suratPermohonanPdfModal" tabindex="-1" role="dialog" aria-labelledby="suratPermohonanPdfModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="suratPermohonanPdfModalLabel">PDF Surat Permohonan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe class="surat-permohonan-modal-frame"
                                src="{{ $suratPermohonanPdfUrl }}"
                                title="PDF Surat Permohonan"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($ndPdfUrl)
        <div class="modal fade" id="ndPdfModal" tabindex="-1" role="dialog" aria-labelledby="ndPdfModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ndPdfModalLabel">PDF ND Permohonan ST</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe class="surat-permohonan-modal-frame" src="{{ $ndPdfUrl }}" title="PDF ND Permohonan ST"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($stPdfUrl)
        <div class="modal fade" id="stPdfModal" tabindex="-1" role="dialog" aria-labelledby="stPdfModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="stPdfModalLabel">PDF ST</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe class="surat-permohonan-modal-frame" src="{{ $stPdfUrl }}" title="PDF ST"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .nd-progress {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-shrink: 0;
        }

        .nd-progress-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            background: #d1d3e2;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px #c7c9d9;
        }

        .nd-progress-dot.is-done {
            background: #1cc88a;
            box-shadow: 0 0 0 1px #16a270;
        }

        .nd-status-row {
            gap: 0;
        }

        .document-card-clickable {
            cursor: pointer;
            transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
        }

        .document-card-clickable:hover,
        .document-card-clickable.is-active {
            background-color: #ffffff !important;
            border-color: #4e73df !important;
            transform: translateY(-1px);
        }

        .document-card-success {
            background: #e9f9f1;
            border-color: #8fd9b6 !important;
        }

        .surat-permohonan-modal-frame {
            border: 0;
            height: 78vh;
            width: 100%;
        }

        .surat-permohonan-upload-row {
            align-items: center;
            display: flex;
            gap: .75rem;
        }

        .surat-permohonan-upload-row .custom-file {
            min-width: 0;
        }

        .surat-permohonan-upload-row .btn {
            flex-shrink: 0;
            white-space: nowrap;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery && jQuery.fn.tooltip) {
                jQuery('[data-toggle="tooltip"]').tooltip();
            }

            var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-target-detail]'));

            function showDetail(selector) {
                var detail = document.querySelector(selector);

                if (!detail) {
                    return;
                }

                detail.classList.remove('d-none');
                triggers
                    .filter(function (trigger) {
                        return trigger.dataset.targetDetail === selector;
                    })
                    .forEach(function (trigger) {
                        trigger.classList.add('is-active');
                    });
                detail.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            triggers.forEach(function (trigger) {
                trigger.addEventListener('click', function (event) {
                    if (event.target.closest('a, button, form')) {
                        return;
                    }

                    showDetail(trigger.dataset.targetDetail);
                });
                trigger.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        showDetail(trigger.dataset.targetDetail);
                    }
                });
            });

            if (window.location.hash === '#nd-permohonan-st-detail') {
                showDetail('#nd-permohonan-st-detail');
            }

            if (window.location.hash === '#nomor-st-detail') {
                showDetail('#nomor-st-detail');
            }

            function setStExtractStatus(message, className) {
                var status = document.getElementById('st-pdf-extract-status');

                if (!status) {
                    return;
                }

                status.className = 'form-text ' + (className || 'text-muted');
                status.textContent = message || '';
            }

            var stPdfInput = document.getElementById('st_pdf');
            var stExtractButton = document.getElementById('extract-st-pdf');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var stExtractUrl = @json(route('projects.nomor-st.extract-pdf', $project));

            if (stPdfInput && stExtractButton && csrf) {
                stPdfInput.addEventListener('change', function () {
                    var label = document.querySelector('label[for="st_pdf"].custom-file-label');

                    if (label) {
                        label.textContent = stPdfInput.files.length ? stPdfInput.files[0].name : 'Telusuri PDF ST...';
                    }

                    stExtractButton.disabled = !stPdfInput.files.length;
                    setStExtractStatus('', 'text-muted');
                });

                stExtractButton.addEventListener('click', function () {
                    if (!stPdfInput.files.length) {
                        setStExtractStatus('Pilih PDF ST terlebih dahulu.', 'text-danger');
                        return;
                    }

                    var controller = new AbortController();
                    var timeout = window.setTimeout(function () {
                        controller.abort();
                    }, 20000);
                    var formData = new FormData();
                    formData.append('st_pdf', stPdfInput.files[0]);

                    stExtractButton.disabled = true;
                    stExtractButton.dataset.originalText = stExtractButton.innerHTML;
                    stExtractButton.innerHTML = '<i class="fas fa-spinner fa-spin fa-sm mr-1"></i>Extracting';
                    setStExtractStatus('Sedang membaca PDF ST, maksimal 20 detik...', 'text-muted');

                    fetch(stExtractUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf.getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData,
                        signal: controller.signal
                    })
                        .then(function (response) {
                            return response.json().then(function (json) {
                                if (!response.ok) {
                                    throw json;
                                }

                                return json;
                            });
                        })
                        .then(function (json) {
                            var fields = json.fields || {};
                            var nomor = document.getElementById('nomor_st');
                            var tanggal = document.getElementById('tanggal_st');

                            if (nomor && fields.nomor_st) {
                                nomor.value = fields.nomor_st;
                            }

                            if (tanggal && fields.tanggal_st) {
                                tanggal.value = fields.tanggal_st;
                            }

                            if (fields.nomor_st || fields.tanggal_st) {
                                setStExtractStatus('Extract ST selesai' + (json.engine ? ' via ' + json.engine : '') + '. Pegawai akan disesuaikan saat disimpan jika berbeda dengan ND.', 'text-success');
                                return;
                            }

                            if (!json.engine) {
                                setStExtractStatus('Belum ada extractor PDF yang tersedia. Isi manual dulu ya.', 'text-danger');
                                return;
                            }

                            if (!json.has_text) {
                                setStExtractStatus('PDF ST berhasil diproses via ' + json.engine + ', tapi tidak ada teks terbaca. Isi manual dulu ya.', 'text-warning');
                                return;
                            }

                            setStExtractStatus('PDF ST terbaca via ' + json.engine + ', tapi nomor/tanggal belum ditemukan. Isi manual dulu ya.', 'text-warning');
                        })
                        .catch(function (error) {
                            if (error && error.name === 'AbortError') {
                                setStExtractStatus('Extract ST dihentikan karena lebih dari 20 detik. Isi manual dulu ya.', 'text-warning');
                                return;
                            }

                            setStExtractStatus('Extract ST gagal. Isi manual dulu ya.', 'text-danger');
                        })
                        .finally(function () {
                            window.clearTimeout(timeout);
                            stExtractButton.disabled = !stPdfInput.files.length;
                            stExtractButton.innerHTML = stExtractButton.dataset.originalText || 'Extract Data';
                        });
                });
            }
        });
    </script>
@endpush
