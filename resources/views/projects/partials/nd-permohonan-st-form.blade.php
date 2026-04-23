@php
    $nd = $project->ndPermohonanSt;
    $selectedPegawaiIds = collect(old('pegawai_ids', $nd?->pegawais->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedTujuanIds = collect(old('tujuan_ids', $nd?->tujuans->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $projectDate = optional($project->tgl_surat_permohonan)->format('Y-m-d') ?? optional($project->tanggal)->format('Y-m-d');
    $defaultHalNd = 'Permohonan Surat Tugas '.$project->hal_surat_permohonan;
    $selectedDari = old('dari', $nd?->dari ?? '');
    $selectedKepalaSeksi = collect($kepalaSeksiOptions)->firstWhere('value', $selectedDari);
    $selectedPenandatangan = old('penandatangan', $nd?->penandatangan ?? ($selectedKepalaSeksi['name'] ?? ''));
    $sifatOptions = ['Biasa', 'Segera', 'Sangat Segera'];
    $selectedPegawaiNames = collect($pegawaiOptions ?? [])
        ->whereIn('id', $selectedPegawaiIds)
        ->pluck('nm_pegawai')
        ->values()
        ->all();
    $selectedTujuanNames = collect($tujuanOptions ?? [])
        ->whereIn('id', $selectedTujuanIds)
        ->pluck('nama_tujuan')
        ->values()
        ->all();
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
    $ndHeaderStatus = $nd?->nomor_nd && $ndTanggalLabel
        ? $nd->nomor_nd.' tanggal '.$ndTanggalLabel
        : ($nd ? 'Draft tersimpan' : 'Belum dibuat');
    $previewPegawais = ($nd?->pegawais?->values() ?? collect())
        ->reject(function ($pegawai) {
            $jabatan = mb_strtolower(trim((string) ($pegawai->jabatan ?? '')), 'UTF-8');

            return str_contains($jabatan, 'kepala kantor');
        })
        ->values();
@endphp

<form method="POST" action="{{ route('projects.nd-permohonan-st.store', $project) }}" enctype="multipart/form-data">
    @csrf

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">ND Permohonan ST Detail</h6>
            <div class="d-flex align-items-center ml-3" style="gap: .75rem;">
                @if($nd)
                    <span class="badge badge-success nd-header-badge">{{ $ndHeaderStatus }}</span>
                @else
                    <span class="badge badge-secondary">Belum dibuat</span>
                @endif
                <button type="button"
                        class="btn btn-link text-primary p-0 nd-collapse-toggle"
                        data-target="#nd-detail-body"
                        aria-expanded="true"
                        aria-label="Sembunyikan detail ND">
                    <i class="fas fa-chevron-up"></i>
                </button>
            </div>
        </div>
        <div id="nd-detail-body" class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="yth">Yth</label>
                        <input id="yth" type="text" name="yth" value="{{ old('yth', $nd?->yth ?? 'Kepala Kantor') }}" class="form-control @error('yth') is-invalid @enderror" required>
                        @error('yth')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="dari">Dari</label>
                        <select id="dari" name="dari" class="form-control nd-single-select @error('dari') is-invalid @enderror" data-placeholder="Pilih Kepala Seksi">
                            <option value="">Pilih Kepala Seksi</option>
                            @foreach($kepalaSeksiOptions as $option)
                                <option value="{{ $option['value'] }}"
                                        data-name="{{ $option['name'] }}"
                                        @selected($selectedDari === $option['value'])>
                                    {{ $option['nomor'] }}. {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('dari')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sifat">Sifat</label>
                        <select id="sifat" name="sifat" class="form-control @error('sifat') is-invalid @enderror" required>
                            @foreach($sifatOptions as $sifatOption)
                                <option value="{{ $sifatOption }}" @selected(old('sifat', $nd?->sifat ?? 'Segera') === $sifatOption)>
                                    {{ $sifatOption }}
                                </option>
                            @endforeach
                        </select>
                        @error('sifat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="hal_nd">Hal ND</label>
                <textarea id="hal_nd" name="hal_nd" rows="2" class="form-control @error('hal_nd') is-invalid @enderror">{{ old('hal_nd', $nd?->hal_nd ?? $defaultHalNd) }}</textarea>
                @error('hal_nd')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="border-top pt-3 mt-3">
                <h6 class="font-weight-bold text-gray-800 mb-3">Dasar Surat Permohonan</h6>
                <div class="row">
                    <div class="col-md-6">
                        <dl class="mb-0">
                            <dt>Pengirim</dt>
                            <dd>{{ $project->pengirim ?? '-' }}</dd>
                            <dt>No Surat</dt>
                            <dd>{{ $project->no_surat_permohonan ?? '-' }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="mb-0">
                            <dt>Tgl Surat</dt>
                            <dd>{{ $project->tgl_surat_permohonan?->format('d/m/Y') ?? '-' }}</dd>
                            <dt>Hal Surat</dt>
                            <dd>{{ $project->hal_surat_permohonan ?? '-' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="border-top pt-3 mt-3">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group mb-lg-0">
                            <label for="pegawai-search">Pegawai yang ditugaskan</label>
                            <div class="nd-picker">
                                <div class="nd-picker-header">
                                    <div>
                                        <div class="nd-picker-heading">Daftar Pegawai Tugas</div>
                                        <small class="text-muted">Cari lalu centang pegawai yang akan masuk ke surat tugas.</small>
                                    </div>
                                    <span class="badge badge-light nd-picker-badge" id="pegawai-count">{{ count($selectedPegawaiIds) }} dipilih</span>
                                </div>
                                <div class="nd-picker-toolbar">
                                    <div class="nd-picker-search-wrap">
                                        <span class="nd-picker-search-icon"><i class="fas fa-search"></i></span>
                                        <input id="pegawai-search" type="text" class="form-control form-control-sm nd-picker-search" data-target="#pegawai-picker-list" placeholder="Cari nama, NIP, atau jabatan pegawai">
                                    </div>
                                </div>
                                <small class="nd-picker-hint text-muted" data-hint-for="#pegawai-picker-list">Ketik di kolom pencarian untuk menampilkan daftar pegawai.</small>
                                <div id="pegawai-selected-summary" class="nd-picker-selected @if(empty($selectedPegawaiNames)) d-none @endif">
                                    <div class="nd-picker-selected-label">Dipilih</div>
                                    <div class="nd-picker-selected-items">
                                        @foreach($selectedPegawaiNames as $selectedPegawaiName)
                                            <span class="nd-picker-chip">{{ $selectedPegawaiName }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div id="pegawai-picker-list" class="nd-picker-list">
                                    @foreach($pegawaiOptions as $pegawai)
                                        <label class="nd-picker-item" data-keywords="{{ strtolower($pegawai->nm_pegawai.' '.$pegawai->nip.' '.$pegawai->jabatan.' '.$pegawai->pangkat_golongan) }}">
                                            <input type="checkbox"
                                                   name="pegawai_ids[]"
                                                   value="{{ $pegawai->id }}"
                                                   class="nd-picker-checkbox"
                                                   data-counter="#pegawai-count"
                                                   data-summary="#pegawai-selected-summary"
                                                   data-label="{{ $pegawai->nm_pegawai }}"
                                                   @checked(in_array($pegawai->id, $selectedPegawaiIds, true))>
                                            <span class="nd-picker-item-body">
                                                <span class="nd-picker-title">{{ $pegawai->nm_pegawai }}</span>
                                                <span class="nd-picker-meta">{{ $pegawai->nip ?: '-' }} | {{ $pegawai->pangkat_golongan ?: '-' }}</span>
                                                <span class="nd-picker-submeta">{{ $pegawai->jabatan ?: '-' }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @error('pegawai_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('pegawai_ids.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group mb-0">
                            <label for="tujuan-search">Nama Tujuan</label>
                            <div class="nd-picker">
                                <div class="nd-picker-header">
                                    <div>
                                        <div class="nd-picker-heading">Daftar Tujuan ST</div>
                                        <small class="text-muted">Pilih satu atau lebih tujuan, alamat akan mengikuti database.</small>
                                    </div>
                                    <span class="badge badge-light nd-picker-badge" id="tujuan-count">{{ count($selectedTujuanIds) }} dipilih</span>
                                </div>
                                <div class="nd-picker-toolbar">
                                    <div class="nd-picker-search-wrap">
                                        <span class="nd-picker-search-icon"><i class="fas fa-search"></i></span>
                                        <input id="tujuan-search" type="text" class="form-control form-control-sm nd-picker-search" data-target="#tujuan-picker-list" placeholder="Cari nama tujuan atau alamat">
                                    </div>
                                </div>
                                <small class="nd-picker-hint text-muted" data-hint-for="#tujuan-picker-list">Ketik di kolom pencarian untuk menampilkan daftar tujuan.</small>
                                <div id="tujuan-selected-summary" class="nd-picker-selected @if(empty($selectedTujuanNames)) d-none @endif">
                                    <div class="nd-picker-selected-label">Dipilih</div>
                                    <div class="nd-picker-selected-items">
                                        @foreach($selectedTujuanNames as $selectedTujuanName)
                                            <span class="nd-picker-chip">{{ $selectedTujuanName }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div id="tujuan-picker-list" class="nd-picker-list">
                                    @foreach($tujuanOptions as $tujuan)
                                        <label class="nd-picker-item" data-keywords="{{ strtolower($tujuan->nama_tujuan.' '.$tujuan->alamat_tujuan) }}">
                                            <input type="checkbox"
                                                   name="tujuan_ids[]"
                                                   value="{{ $tujuan->id }}"
                                                   class="nd-picker-checkbox"
                                                   data-counter="#tujuan-count"
                                                   data-summary="#tujuan-selected-summary"
                                                   data-label="{{ $tujuan->nama_tujuan }}"
                                                   @checked(in_array($tujuan->id, $selectedTujuanIds, true))>
                                            <span class="nd-picker-item-body">
                                                <span class="nd-picker-title">{{ $tujuan->nama_tujuan }}</span>
                                                <span class="nd-picker-submeta">{{ $tujuan->alamat_tujuan ?: '-' }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @error('tujuan_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('tujuan_ids.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Alamat tujuan otomatis mengikuti database Tujuan ST.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-top pt-3 mt-3">
                <h6 class="font-weight-bold text-gray-800 mb-3">Pelaksanaan</h6>
                <div class="form-group">
                    <label for="kegiatan">Kegiatan</label>
                    <textarea id="kegiatan" name="kegiatan" rows="2" class="form-control @error('kegiatan') is-invalid @enderror" placeholder="Contoh: Melakukan Pencacahan dan Pengawasan Pemusnahan Barang Kena Cukai Yang Rusak">{{ old('kegiatan', $nd?->kegiatan ?? '') }}</textarea>
                    @error('kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_mulai">Tanggal Mulai</label>
                            <input id="tanggal_mulai" type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', optional($nd?->tanggal_mulai)->format('Y-m-d') ?? $projectDate) }}" class="form-control @error('tanggal_mulai') is-invalid @enderror">
                            @error('tanggal_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tanggal_selesai">Tanggal Selesai</label>
                            <input id="tanggal_selesai" type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($nd?->tanggal_selesai)->format('Y-m-d') ?? $projectDate) }}" class="form-control @error('tanggal_selesai') is-invalid @enderror">
                            @error('tanggal_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="waktu_mulai">Waktu Mulai</label>
                            <input id="waktu_mulai" type="time" name="waktu_mulai" value="{{ old('waktu_mulai', $nd?->waktu_mulai ?? '08:00') }}" class="form-control @error('waktu_mulai') is-invalid @enderror">
                            @error('waktu_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="waktu_selesai">Waktu Selesai</label>
                            <input id="waktu_selesai" type="time" name="waktu_selesai" value="{{ old('waktu_selesai', $nd?->waktu_selesai ?? '') }}" class="form-control @error('waktu_selesai') is-invalid @enderror">
                            @error('waktu_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Kosongkan jika sampai selesai.</small>
                        </div>
                    </div>

                </div>

                @if($nd && $nd->tujuans->isNotEmpty())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Tujuan</label>
                                <textarea rows="{{ $nd->tujuans->count() > 1 ? 4 : 2 }}" class="form-control bg-light" readonly>{{ $nd->tempat }}</textarea>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="border-top pt-3 mt-3">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <div class="form-group mb-md-0">
                            <label>Penandatangan</label>
                            <input id="penandatangan" type="hidden" name="penandatangan" value="{{ $selectedPenandatangan }}">
                            <div id="penandatangan-display" class="form-control bg-light" style="height: auto; min-height: calc(1.5em + .75rem + 2px);">
                                {{ $selectedPenandatangan ?: '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-0">
                            <label for="tembusan">Tembusan</label>
                            <textarea id="tembusan" name="tembusan" rows="2" class="form-control @error('tembusan') is-invalid @enderror">{{ old('tembusan', $nd?->tembusan ?? 'Kepala Subbagian Umum') }}</textarea>
                            @error('tembusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: .75rem;">
                <div>
                    @if($nd)
                        <a href="{{ route('projects.nd-permohonan-st.word', $project) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-word fa-sm mr-1"></i>
                            Buat Word
                        </a>
                    @endif
                </div>
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-save fa-sm mr-1"></i>
                    {{ $nd && $nd->word_generated_at ? 'Simpan Finalisasi ND' : 'Simpan Draft ND' }}
                </button>
            </div>
        </div>
    </div>

@if($nd && $nd->word_generated_at)
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Finalisasi ND</h6>
            <button type="button"
                    class="btn btn-link text-primary p-0 nd-collapse-toggle"
                    data-target="#nd-finalisasi-body"
                    aria-expanded="true"
                    aria-label="Sembunyikan finalisasi ND">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        <div id="nd-finalisasi-body" class="card-body">
            <div class="form-group">
                <label for="nd_pdf">Upload PDF ND Permohonan ST</label>
                <div class="surat-permohonan-upload-row">
                    <div class="custom-file flex-grow-1">
                        <input id="nd_pdf" type="file" name="nd_pdf" accept="application/pdf" class="custom-file-input @error('nd_pdf') is-invalid @enderror">
                        <label class="custom-file-label" for="nd_pdf">Telusuri PDF ND...</label>
                    </div>
                    <button id="extract-nd-pdf" type="button" class="btn btn-outline-primary btn-sm" disabled>
                        <i class="fas fa-magic fa-sm mr-1"></i>
                        Extract Data
                    </button>
                </div>
                @error('nd_pdf')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small id="nd-pdf-extract-status" class="form-text text-muted"></small>
                @if(!empty($nd->nd_pdf_path))
                    <small class="form-text text-muted">
                        PDF ND sudah diupload.
                        @if($nd->nd_pdf_ocr_engine)
                            Extract terakhir: {{ $nd->nd_pdf_ocr_engine }}
                            @if($nd->nd_pdf_ocr_at)
                                ({{ $nd->nd_pdf_ocr_at->format('d/m/Y H:i') }})
                            @endif
                        @endif
                    </small>
                @endif
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-md-0">
                        <label for="nomor_nd">Nomor ND</label>
                        <input id="nomor_nd" type="text" name="nomor_nd" value="{{ old('nomor_nd', $nd?->nomor_nd ?? '') }}" class="form-control @error('nomor_nd') is-invalid @enderror" placeholder="Otomatis dari PDF atau isi manual">
                        @error('nomor_nd')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-0">
                        <label for="tanggal_nd">Tanggal ND</label>
                        <input id="tanggal_nd" type="date" name="tanggal_nd" value="{{ old('tanggal_nd', optional($nd?->tanggal_nd)->format('Y-m-d')) }}" class="form-control @error('tanggal_nd') is-invalid @enderror">
                        @error('tanggal_nd')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
</form>

@if($nd)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Preview Daftar Pegawai ST</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 64px;">No</th>
                            <th>Nama / NIP</th>
                            <th>Pangkat/Golongan</th>
                            <th>Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($previewPegawais as $pegawai)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="font-weight-bold">{{ $pegawai->nm_pegawai }}</div>
                                    <div>{{ $pegawai->nip }}</div>
                                </td>
                                <td>{{ $pegawai->pangkat_golongan }}</td>
                                <td>{{ $pegawai->jabatan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Belum ada pegawai dipilih</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet">
    <style>
        .nd-picker {
            background: #fff;
            border: 1px solid #d1d3e2;
            border-radius: .35rem;
            padding: .75rem;
        }

        .nd-picker-header {
            align-items: flex-start;
            display: flex;
            gap: .75rem;
            justify-content: space-between;
            margin-bottom: .75rem;
        }

        .nd-picker-heading {
            color: #1f2937;
            font-size: .95rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: .15rem;
        }

        .nd-picker-badge {
            flex-shrink: 0;
            font-size: .75rem;
            padding: .4rem .55rem;
        }

        .nd-picker-toolbar {
            margin-bottom: .75rem;
        }

        .nd-picker-hint {
            display: block;
            font-size: .78rem;
            margin-bottom: .75rem;
        }

        .nd-picker-selected {
            background: #f8fbff;
            border: 1px solid #dce7fb;
            border-radius: .45rem;
            margin-bottom: .75rem;
            padding: .65rem .75rem;
        }

        .nd-picker-selected-label {
            color: #4e73df;
            font-size: .75rem;
            font-weight: 700;
            margin-bottom: .45rem;
            text-transform: uppercase;
        }

        .nd-picker-selected-items {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .nd-picker-chip {
            background: #fff;
            border: 1px solid #cdddfb;
            border-radius: 999px;
            color: #1f2937;
            display: inline-flex;
            font-size: .78rem;
            line-height: 1.2;
            padding: .32rem .6rem;
        }

        .nd-picker-search-wrap {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #dbe2ea;
            margin-bottom: .75rem;
            border-radius: .45rem;
            display: flex;
            min-height: 42px;
            overflow: hidden;
            transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
        }

        .nd-picker-search-wrap:focus-within {
            background: #fff;
            border-color: #4e73df;
            box-shadow: 0 0 0 .12rem rgba(78, 115, 223, .15);
        }

        .nd-picker-search-icon {
            color: #6b7280;
            display: inline-flex;
            flex: 0 0 40px;
            justify-content: center;
        }

        .nd-picker-search {
            background: transparent;
            border: 0;
            box-shadow: none !important;
            font-size: .875rem;
            height: 40px;
            padding-left: 0;
        }

        .nd-picker-search:focus {
            background: transparent;
        }

        .nd-picker-list {
            border: 1px solid #e3e6f0;
            border-radius: .35rem;
            max-height: 280px;
            overflow-y: auto;
        }

        .nd-picker-list.nd-picker-list-hidden {
            display: none;
        }

        .nd-picker-item {
            align-items: flex-start;
            cursor: pointer;
            display: grid;
            gap: .75rem;
            grid-template-columns: auto minmax(0, 1fr);
            margin: 0;
            padding: .85rem .9rem;
            transition: background-color .15s ease, border-color .15s ease;
        }

        .nd-picker-item + .nd-picker-item {
            border-top: 1px solid #eef2f7;
        }

        .nd-picker-item:hover {
            background: #f8fbff;
        }

        .nd-picker-item input[type="checkbox"] {
            margin-top: .25rem;
        }

        .nd-picker-item:has(input[type="checkbox"]:checked) {
            background: #eef4ff;
        }

        .nd-picker-item-body {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .nd-picker-title {
            color: #1f2937;
            font-weight: 600;
        }

        .nd-picker-meta,
        .nd-picker-submeta {
            color: #6b7280;
            font-size: .8125rem;
            line-height: 1.4;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            min-height: calc(1.5em + .75rem + 2px);
        }

        .nd-header-badge {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .nd-collapse-toggle {
            line-height: 1;
            text-decoration: none !important;
        }

        .nd-collapse-toggle i {
            transition: transform .15s ease;
        }

        .nd-collapse-toggle.is-collapsed i {
            transform: rotate(180deg);
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function syncPenandatangan() {
                var select = document.getElementById('dari');
                var input = document.getElementById('penandatangan');
                var display = document.getElementById('penandatangan-display');

                if (!select || !input || !display) {
                    return;
                }

                var option = select.options[select.selectedIndex];
                var name = option ? option.getAttribute('data-name') || '' : '';

                input.value = name;
                display.textContent = name || '-';
            }

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('.nd-single-select').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: function () {
                        return jQuery(this).data('placeholder');
                    },
                    allowClear: true,
                });

                jQuery('#dari').on('change', syncPenandatangan);
            }

            function updatePickerCount(selector) {
                var counter = document.querySelector(selector);

                if (!counter) {
                    return;
                }

                var checked = document.querySelectorAll('input.nd-picker-checkbox[data-counter="' + selector + '"]:checked').length;
                counter.textContent = checked + ' dipilih';
            }

            function escapeHtml(value) {
                return (value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function updatePickerSummary(selector) {
                var panel = document.querySelector(selector);

                if (!panel) {
                    return;
                }

                var labels = Array.from(document.querySelectorAll('input.nd-picker-checkbox[data-summary="' + selector + '"]:checked'))
                    .map(function (checkbox) {
                        return checkbox.dataset.label || '';
                    })
                    .filter(Boolean);

                panel.classList.toggle('d-none', labels.length === 0);

                var items = panel.querySelector('.nd-picker-selected-items');

                if (!items) {
                    return;
                }

                items.innerHTML = labels.map(function (label) {
                    return '<span class="nd-picker-chip">' + escapeHtml(label) + '</span>';
                }).join('');
            }

            document.querySelectorAll('.nd-picker-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    updatePickerCount(checkbox.dataset.counter);
                    updatePickerSummary(checkbox.dataset.summary);
                });
            });

            document.querySelectorAll('.nd-picker-search').forEach(function (input) {
                input.addEventListener('input', function () {
                    var list = document.querySelector(input.dataset.target);
                    var hint = document.querySelector('[data-hint-for="' + input.dataset.target + '"]');
                    var keyword = input.value.trim().toLowerCase();

                    if (!list) {
                        return;
                    }

                    list.classList.toggle('nd-picker-list-hidden', keyword === '');

                    if (hint) {
                        hint.classList.toggle('d-none', keyword !== '');
                    }

                    if (keyword === '') {
                        list.querySelectorAll('.nd-picker-item').forEach(function (item) {
                            item.classList.remove('d-none');
                        });

                        return;
                    }

                    list.querySelectorAll('.nd-picker-item').forEach(function (item) {
                        var haystack = item.dataset.keywords || '';
                        item.classList.toggle('d-none', keyword !== '' && haystack.indexOf(keyword) === -1);
                    });
                });
            });

            ['#pegawai-count', '#tujuan-count'].forEach(updatePickerCount);
            ['#pegawai-selected-summary', '#tujuan-selected-summary'].forEach(updatePickerSummary);
            document.querySelectorAll('.nd-picker-list').forEach(function (list) {
                list.classList.add('nd-picker-list-hidden');
            });

            document.querySelectorAll('.nd-collapse-toggle').forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var target = document.querySelector(toggle.dataset.target);

                    if (!target) {
                        return;
                    }

                    var isHidden = target.classList.toggle('d-none');
                    toggle.classList.toggle('is-collapsed', isHidden);
                    toggle.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
                });
            });

            function setNdExtractStatus(message, className) {
                var status = document.getElementById('nd-pdf-extract-status');

                if (!status) {
                    return;
                }

                status.className = 'form-text ' + (className || 'text-muted');
                status.textContent = message || '';
            }

            var ndPdfInput = document.getElementById('nd_pdf');
            var ndExtractButton = document.getElementById('extract-nd-pdf');
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var extractUrl = @json(route('projects.nd-permohonan-st.extract-pdf', $project));

            if (ndPdfInput && ndExtractButton && csrf) {
                ndPdfInput.addEventListener('change', function () {
                    var label = document.querySelector('label[for="nd_pdf"].custom-file-label');

                    if (label) {
                        label.textContent = ndPdfInput.files.length ? ndPdfInput.files[0].name : 'Telusuri PDF ND...';
                    }

                    ndExtractButton.disabled = !ndPdfInput.files.length;
                    setNdExtractStatus('', 'text-muted');
                });

                ndExtractButton.addEventListener('click', function () {
                    if (!ndPdfInput.files.length) {
                        setNdExtractStatus('Pilih PDF ND terlebih dahulu.', 'text-danger');
                        return;
                    }

                    var controller = new AbortController();
                    var timeout = window.setTimeout(function () {
                        controller.abort();
                    }, 20000);
                    var formData = new FormData();
                    formData.append('nd_pdf', ndPdfInput.files[0]);

                    ndExtractButton.disabled = true;
                    ndExtractButton.dataset.originalText = ndExtractButton.innerHTML;
                    ndExtractButton.innerHTML = '<i class="fas fa-spinner fa-spin fa-sm mr-1"></i>Extracting';
                    setNdExtractStatus('Sedang membaca PDF ND, maksimal 20 detik...', 'text-muted');

                    fetch(extractUrl, {
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
                            var nomor = document.getElementById('nomor_nd');
                            var tanggal = document.getElementById('tanggal_nd');

                            if (nomor && fields.nomor_nd) {
                                nomor.value = fields.nomor_nd;
                            }

                            if (tanggal && fields.tanggal_nd) {
                                tanggal.value = fields.tanggal_nd;
                            }

                            if (fields.nomor_nd || fields.tanggal_nd) {
                                setNdExtractStatus('Extract ND selesai' + (json.engine ? ' via ' + json.engine : '') + '. Cek lagi sebelum disimpan.', 'text-success');
                                return;
                            }

                            if (!json.engine) {
                                setNdExtractStatus('Belum ada extractor PDF yang tersedia. Isi manual dulu ya.', 'text-danger');
                                return;
                            }

                            if (!json.has_text) {
                                setNdExtractStatus('PDF ND berhasil diproses via ' + json.engine + ', tapi tidak ada teks terbaca. Isi manual dulu ya.', 'text-warning');
                                return;
                            }

                            setNdExtractStatus('PDF ND terbaca via ' + json.engine + ', tapi nomor/tanggal belum ditemukan. Isi manual dulu ya.', 'text-warning');
                        })
                        .catch(function (error) {
                            if (error && error.name === 'AbortError') {
                                setNdExtractStatus('Extract ND dihentikan karena lebih dari 20 detik. Isi manual dulu ya.', 'text-warning');
                                return;
                            }

                            setNdExtractStatus('Extract ND gagal. Isi manual dulu ya.', 'text-danger');
                        })
                        .finally(function () {
                            window.clearTimeout(timeout);
                            ndExtractButton.disabled = !ndPdfInput.files.length;
                            ndExtractButton.innerHTML = ndExtractButton.dataset.originalText || 'Extract Data';
                        });
                });
            }

            syncPenandatangan();
        });
    </script>
@endpush
