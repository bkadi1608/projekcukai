@extends('layouts.app')

@section('title', 'Data Perusahaan - Aplikasi Cukai')
@section('topbar-title', 'Data Perusahaan')
@section('page-title', 'Data Perusahaan')
@section('page-subtitle', 'Monitoring NPPBKC berdasarkan status perusahaan')

@push('vendor-styles')
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/filter-control/bootstrap-table-filter-control.min.css" rel="stylesheet">
@endpush

@section('page-actions')
    <form action="{{ route('perusahaan.sync') }}" method="POST" class="mb-0">
        @csrf
        <button class="btn btn-success btn-sm shadow-sm" type="submit">
            <i class="fas fa-sync-alt fa-sm text-white-50 mr-1"></i>
            Sync Data
        </button>
    </form>
@endsection

@section('content')
    <div class="card shadow content-card mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Perusahaan</h6>
            <span class="badge badge-light border text-gray-700">{{ $data->count() }} data</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table"
                       data-toggle="table"
                       data-search="true"
                       data-pagination="true"
                       data-page-size="10"
                       data-show-columns="true"
                       data-show-toggle="true"
                       data-striped="true"
                       data-filter-control="true"
                       class="table table-bordered table-hover">

                    <thead class="thead-light">
                        <tr>
                            <th data-field="no" data-sortable="true" class="text-center">
                                No
                            </th>

                            <th data-field="nama" data-filter-control="input" data-sortable="true">
                                Nama Pabrik
                            </th>

                            <th data-field="nama_perusahaan" data-filter-control="input" data-sortable="true">
                                Nama Perusahaan
                            </th>

                            <th data-field="jenis_perusahaan" data-filter-control="select" data-sortable="true">
                                Jenis Perusahaan
                            </th>

                            <th data-field="status"
                                data-filter-control="select"
                                data-filter-data="var:statusList"
                                data-sortable="true">
                                Status
                            </th>

                            <th data-field="npwp" data-filter-control="input">
                                NPWP 16
                            </th>

                            <th data-field="nppbkc22" data-filter-control="input">
                                NPPBKC 22
                            </th>

                            <th data-field="alamat" data-filter-control="input">
                                Alamat
                            </th>

                            <th data-field="lokasi_gmaps" data-filter-control="input">
                                Lokasi GMAPS
                            </th>

                            <th data-field="longlat" data-filter-control="input">
                                Longitude Latitude
                            </th>

                            <th data-field="kecamatan" data-filter-control="input" data-sortable="true">
                                Kecamatan
                            </th>

                            <th data-field="nomor_kep" data-filter-control="input">
                                Nomor SKEP
                            </th>

                            <th data-field="tgl_kep" data-filter-control="input" data-sortable="true">
                                Tanggal SKEP
                            </th>

                            <th data-field="profil" data-filter-control="input">
                                Profil
                            </th>

                            <th data-field="jenis_usaha" data-filter-control="input" data-visible="false">
                                Jenis Usaha
                            </th>

                            <th data-field="jenis_bkc" data-filter-control="input" data-visible="false">
                                Jenis BKC
                            </th>

                            <th data-field="nib" data-filter-control="input" data-visible="false">
                                NIB
                            </th>

                            <th data-field="nppbkc10" data-filter-control="input" data-visible="false">
                                NPPBKC 10
                            </th>

                            <th data-field="nilku" data-filter-control="input" data-visible="false">
                                NILKU
                            </th>

                            <th data-field="pkp" data-filter-control="select" data-visible="false">
                                PKP
                            </th>

                            <th data-field="no_pkp" data-filter-control="input" data-visible="false">
                                No PKP
                            </th>

                            <th data-field="status_beku_cabut" data-filter-control="input" data-visible="false">
                                Status Beku/Cabut
                            </th>

                            <th data-field="nomor_kep_cabut" data-filter-control="input" data-visible="false">
                                Nomor KEP Cabut/Beku
                            </th>

                            <th data-field="tgl_kep_cabut" data-filter-control="input" data-visible="false">
                                Tanggal KEP Cabut/Beku
                            </th>

                            <th data-field="nitku_utama" data-filter-control="input" data-visible="false">
                                NITKU Utama
                            </th>

                            <th data-field="lokasi_fix" data-filter-control="input" data-visible="false">
                                Lokasi Fix
                            </th>

                            <th data-field="nama_pemilik" data-filter-control="input" data-visible="false">
                                Nama Pemilik
                            </th>

                            <th data-field="npwp_pemilik" data-filter-control="input" data-visible="false">
                                NPWP Pemilik
                            </th>

                            <th data-field="nik_pemilik" data-filter-control="input" data-visible="false">
                                NIK Pemilik
                            </th>

                            <th data-field="alamat_pemilik" data-filter-control="input" data-visible="false">
                                Alamat Pemilik
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $p)
                            @php
                                $status = strtoupper(trim($p->status));
                            @endphp

                            <tr>
                                <td data-field="no" class="text-center">
                                    {{ $loop->iteration }}
                                </td>

                                <td data-field="nama">
                                    {{ $p->nama_pabrik_sac }}
                                </td>

                                <td data-field="nama_perusahaan">
                                    {{ $p->nama_perusahaan }}
                                </td>

                                <td data-field="jenis_perusahaan">
                                    {{ $p->jenis_perusahaan }}
                                </td>

                                <td data-field="status" data-value="{{ $status }}">
                                    @if($status == 'AKTIF')
                                        <span class="badge badge-success">AKTIF</span>
                                    @elseif($status == 'CABUT')
                                        <span class="badge badge-danger">CABUT</span>
                                    @elseif($status == 'BEKU')
                                        <span class="badge badge-warning text-dark">BEKU</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $status }}</span>
                                    @endif
                                </td>

                                <td data-field="npwp">
                                    {{ $p->npwp_formatted ?? $p->npwp }}
                                </td>

                                <td data-field="nppbkc22">
                                    {{ $p->nppbkc_22_formatted ?? $p->nppbkc_22 }}
                                </td>

                                <td data-field="alamat">
                                    {{ \Illuminate\Support\Str::limit($p->alamat_pabrik_utama, 90) }}
                                </td>

                                <td data-field="lokasi_gmaps">
                                    @if($p->lokasi_gmaps)
                                        <a href="{{ $p->lokasi_gmaps }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            Maps
                                        </a>
                                    @elseif($p->longlat)
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($p->longlat) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            Maps
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td data-field="longlat">
                                    {{ $p->longlat }}
                                </td>

                                <td data-field="kecamatan">
                                    {{ $p->kecamatan }}
                                </td>

                                <td data-field="nomor_kep">
                                    {{ $p->no_kep_nppbkc }}
                                </td>

                                <td data-field="tgl_kep">
                                    {{ $p->tgl_kep_nppbkc }}
                                </td>

                                <td data-field="profil">
                                    <span class="font-weight-bold
                                        @if($status=='AKTIF') text-success
                                        @elseif($status=='CABUT') text-danger
                                        @elseif($status=='BEKU') text-warning
                                        @endif
                                    ">
                                        {{ \Illuminate\Support\Str::limit($p->profil, 80) }}
                                    </span>
                                </td>

                                <td data-field="jenis_usaha">{{ $p->jenis_usaha }}</td>
                                <td data-field="jenis_bkc">{{ $p->jenis_bkc }}</td>
                                <td data-field="nib">{{ $p->nib }}</td>
                                <td data-field="nppbkc10">{{ $p->nppbkc_10 }}</td>
                                <td data-field="nilku">{{ $p->nilku }}</td>
                                <td data-field="pkp">{{ $p->pkp }}</td>
                                <td data-field="no_pkp">{{ $p->no_pkp }}</td>
                                <td data-field="status_beku_cabut">{{ $p->status_beku_cabut }}</td>
                                <td data-field="nomor_kep_cabut">{{ $p->nomor_kep }}</td>
                                <td data-field="tgl_kep_cabut">{{ $p->tgl_kep }}</td>
                                <td data-field="nitku_utama">{{ $p->nitku_utama }}</td>
                                <td data-field="lokasi_fix">{{ $p->lokasi_fix }}</td>
                                <td data-field="nama_pemilik">{{ $p->nama_pemilik }}</td>
                                <td data-field="npwp_pemilik">{{ $p->npwp_pemilik }}</td>
                                <td data-field="nik_pemilik">{{ $p->nik_pemilik }}</td>
                                <td data-field="alamat_pemilik">{{ $p->alamat_pemilik }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
var statusList = {
    "AKTIF": "AKTIF",
    "CABUT": "CABUT",
    "BEKU": "BEKU",
    "BELUM AKTIF": "BELUM AKTIF",
    "DITOLAK": "DITOLAK",
    "PROSES PENGAJUAN NPPBKC": "PROSES PENGAJUAN NPPBKC"
};

document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery) {

        const $table = $('#table');

        $table.bootstrapTable();

        setTimeout(() => {
            let el = document.querySelector('.bootstrap-table-filter-control-status');

            if (el) {
                el.value = 'AKTIF';
                el.dispatchEvent(new Event('change'));
            }
        }, 500);
    }
});
</script>

@endsection

@push('vendor-scripts')
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
@endpush
