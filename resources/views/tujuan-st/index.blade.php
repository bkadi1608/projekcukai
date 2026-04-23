@extends('layouts.app')

@section('title', 'Tujuan ST - Aplikasi Cukai')
@section('topbar-title', 'Tujuan ST')
@section('page-title', 'Database Tujuan ST')
@section('page-subtitle', 'Referensi nama dan alamat tujuan Surat Tugas')

@push('vendor-styles')
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">
    <link href="https://unpkg.com/bootstrap-table@1.22.1/dist/extensions/filter-control/bootstrap-table-filter-control.min.css" rel="stylesheet">
@endpush

@section('page-actions')
    <form action="{{ route('tujuan-st.sync') }}" method="POST" class="mb-0">
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
            <h6 class="m-0 font-weight-bold text-primary">Daftar Tujuan ST</h6>
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
                            <th data-field="no" data-sortable="true" class="text-center">No</th>
                            <th data-field="nama_tujuan" data-filter-control="input" data-sortable="true">Nama Tujuan</th>
                            <th data-field="alamat_tujuan" data-filter-control="input">Alamat Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $tujuan)
                            <tr>
                                <td data-field="no" class="text-center">{{ $loop->iteration }}</td>
                                <td data-field="nama_tujuan">{{ $tujuan->nama_tujuan }}</td>
                                <td data-field="alamat_tujuan">{{ $tujuan->alamat_tujuan }}</td>
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
