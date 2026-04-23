@extends('layouts.app')

@section('title', 'Data Pegawai - Aplikasi Cukai')
@section('topbar-title', 'Data Pegawai')
@section('page-title', 'Data Pegawai')
@section('page-subtitle', 'Database pegawai dari Google Sheets')

@push('vendor-styles')
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/filter-control/bootstrap-table-filter-control.min.css" rel="stylesheet">
@endpush

@section('page-actions')
    <form action="{{ route('pegawai.sync') }}" method="POST" class="mb-0">
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
            <h6 class="m-0 font-weight-bold text-primary">Daftar Pegawai</h6>
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
                            <th data-field="nomor" data-sortable="true" class="text-center">Nomor</th>
                            <th data-field="foto" class="text-center">Foto</th>
                            <th data-field="nm_pegawai" data-filter-control="input" data-sortable="true">NmPegawai</th>
                            <th data-field="nickname" data-filter-control="input" data-sortable="true">Nickname</th>
                            <th data-field="jabatan" data-filter-control="input" data-sortable="true">Jabatan</th>
                            <th data-field="pangkat_golongan" data-filter-control="select" data-sortable="true">Pangkat - Golongan</th>
                            <th data-field="nip" data-filter-control="input">NIP</th>
                            <th data-field="email_kemenkeu" data-filter-control="input">Email Kemenkeu</th>
                            <th data-field="grade" data-filter-control="select" data-sortable="true">Grade</th>
                            <th data-field="jenis_kelamin" data-filter-control="select" data-sortable="true">Jenis Kelamin</th>
                            <th data-field="tanggal_lahir" data-filter-control="input" data-sortable="true">Tanggal Lahir</th>
                            <th data-field="ulang_tahun" data-filter-control="input" data-sortable="true">Ulang Tahun</th>
                            <th data-field="tgl_ulang_tahun" data-filter-control="input" data-sortable="true">Tgl Ulang Tahun</th>
                            <th data-field="umur" data-filter-control="input" data-sortable="true">Umur</th>
                            <th data-field="atasan" data-filter-control="input" data-sortable="true">Atasan</th>
                            <th data-field="seksi" data-filter-control="select" data-sortable="true">Seksi</th>
                            <th data-field="jabatan_duk" data-filter-control="input" data-sortable="true">Jabatan DUK</th>
                            <th data-field="bulan_if" data-filter-control="select" data-visible="false">Bulan if</th>
                            <th data-field="bulan" data-filter-control="select" data-visible="false">Bulan</th>
                            <th data-field="urutan" data-filter-control="input" data-visible="false">Urutan</th>
                            <th data-field="jabatan2" data-filter-control="input" data-visible="false">Jabatan2</th>
                            <th data-field="nama_pegawai" data-filter-control="input" data-visible="false">Nama Pegawai</th>
                            <th data-field="url_foto" data-filter-control="input" data-visible="false">UrlFoto</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($data as $pegawai)
                            <tr>
                                <td data-field="nomor" class="text-center">{{ $pegawai->nomor ?: $loop->iteration }}</td>
                                <td data-field="foto" class="text-center">
                                    @if($pegawai->url_foto)
                                        <img src="{{ $pegawai->url_foto }}"
                                             alt="{{ $pegawai->nm_pegawai }}"
                                             class="rounded-circle border"
                                             style="width: 44px; height: 44px; object-fit: cover;"
                                             loading="lazy">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-field="nm_pegawai">{{ $pegawai->nm_pegawai }}</td>
                                <td data-field="nickname">{{ $pegawai->nickname }}</td>
                                <td data-field="jabatan">{{ $pegawai->jabatan }}</td>
                                <td data-field="pangkat_golongan">{{ $pegawai->pangkat_golongan }}</td>
                                <td data-field="nip">{{ $pegawai->nip }}</td>
                                <td data-field="email_kemenkeu">
                                    @if($pegawai->email_kemenkeu)
                                        <a href="mailto:{{ $pegawai->email_kemenkeu }}">{{ $pegawai->email_kemenkeu }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-field="grade">
                                    @if($pegawai->grade)
                                        <span class="badge badge-primary">{{ $pegawai->grade }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td data-field="jenis_kelamin">{{ $pegawai->jenis_kelamin }}</td>
                                <td data-field="tanggal_lahir">{{ optional($pegawai->tanggal_lahir)->format('Y-m-d') }}</td>
                                <td data-field="ulang_tahun">{{ optional($pegawai->ulang_tahun)->format('Y-m-d') }}</td>
                                <td data-field="tgl_ulang_tahun">{{ optional($pegawai->tgl_ulang_tahun)->format('Y-m-d') }}</td>
                                <td data-field="umur">{{ $pegawai->umur }}</td>
                                <td data-field="atasan">{{ $pegawai->atasan }}</td>
                                <td data-field="seksi">{{ $pegawai->seksi }}</td>
                                <td data-field="jabatan_duk">{{ $pegawai->jabatan_duk }}</td>
                                <td data-field="bulan_if">{{ $pegawai->bulan_if }}</td>
                                <td data-field="bulan">{{ $pegawai->bulan }}</td>
                                <td data-field="urutan">{{ $pegawai->urutan }}</td>
                                <td data-field="jabatan2">{{ $pegawai->jabatan2 }}</td>
                                <td data-field="nama_pegawai">{{ $pegawai->nama_pegawai }}</td>
                                <td data-field="url_foto">
                                    @if($pegawai->url_foto)
                                        <a href="{{ $pegawai->url_foto }}" target="_blank" rel="noopener">Buka Foto</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery) {
        $('#table').bootstrapTable();
    }
});
</script>
@endsection

@push('vendor-scripts')
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
@endpush
