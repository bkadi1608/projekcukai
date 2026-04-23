@extends('layouts.app')

@section('title', 'Dashboard - Aplikasi Cukai')
@section('topbar-title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan data dan akses cepat aplikasi')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #company-map {
            min-height: 420px;
            width: 100%;
        }

        .map-summary-list {
            max-height: 420px;
            overflow-y: auto;
        }

        .map-summary-dot {
            border-radius: 50%;
            display: inline-block;
            height: 9px;
            margin-right: .45rem;
            width: 9px;
        }

        .leaflet-container {
            background: #eef2f7;
            font-family: inherit;
        }

        .filter-chip {
            border-radius: 999px;
            display: inline-flex;
            gap: .4rem;
            align-items: center;
            padding: .35rem .75rem;
        }

        .summary-link {
            color: inherit;
            display: block;
            text-decoration: none;
        }

        .summary-link:hover {
            color: inherit;
            text-decoration: none;
        }

        .pivot-table th,
        .pivot-table td {
            border-color: #ffffff;
            min-width: 92px;
            padding: .72rem .85rem;
            vertical-align: middle;
        }

        .pivot-table th:first-child,
        .pivot-table td:first-child {
            min-width: 150px;
            text-align: left;
        }

        .pivot-table td {
            background: #f8fafc;
            text-align: right;
        }

        .pivot-table .pivot-empty {
            background: #ffffff;
            color: transparent;
        }

        .pivot-heat-0 { background: #ffffff; color: transparent; }
        .pivot-heat-1 { background: rgba(78, 115, 223, .14); }
        .pivot-heat-2 { background: rgba(78, 115, 223, .24); }
        .pivot-heat-3 { background: rgba(78, 115, 223, .36); }
        .pivot-heat-4 { background: rgba(78, 115, 223, .50); }
        .pivot-heat-5 { background: rgba(78, 115, 223, .64); }
        .pivot-heat-6 { background: rgba(78, 115, 223, .80); color: #ffffff; }
        .pivot-heat-7 { background: rgba(78, 115, 223, .94); color: #ffffff; }

        .pivot-total {
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Status Aktif
                    </div>
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="mr-3">
                            <div class="h4 mb-2 font-weight-bold text-gray-800">
                                {{ $summaryCards['aktif']['total'] }}
                            </div>
                            @foreach($summaryCards['aktif']['breakdown'] as $item)
                                <div class="small text-gray-700">
                                    {{ $item['label'] }} ({{ $item['count'] }} / {{ number_format($item['percentage'], 1, ',', '.') }}%)
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Status Beku
                    </div>
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="mr-3">
                            <div class="h4 mb-1 font-weight-bold text-gray-800">
                                {{ $summaryCards['beku']['total'] }}
                            </div>
                            <div class="small text-gray-700">
                                {{ number_format($summaryCards['beku']['percentage'], 1, ',', '.') }}% dari total data
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-snowflake fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Status PKP
                    </div>
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="mr-3">
                            <div class="small text-gray-800">
                                <strong>PKP:</strong> {{ $summaryCards['pkp']['pkp']['count'] }} ({{ number_format($summaryCards['pkp']['pkp']['percentage'], 1, ',', '.') }}%)
                            </div>
                            <div class="small text-gray-800 mt-1">
                                <strong>Bukan PKP:</strong> {{ $summaryCards['pkp']['non_pkp']['count'] }} ({{ number_format($summaryCards['pkp']['non_pkp']['percentage'], 1, ',', '.') }}%)
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Project Sedang Berjalan
                    </div>
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="mr-3">
                            <div class="h4 mb-1 font-weight-bold text-gray-800">
                                {{ $projectSummary['st_total'] }}
                            </div>
                            <div class="small text-gray-700">
                                {{ $projectSummary['ongoing_projects'] }} project aktif hari ini
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-wrap">
                        <h6 class="m-0 font-weight-bold text-primary mr-3">Sebaran Lokasi Perusahaan</h6>
                        @if($selectedDistrict)
                            <span class="badge badge-primary filter-chip">
                                Filter: {{ \Illuminate\Support\Str::title(strtolower($selectedDistrict)) }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge badge-light border text-gray-700 mr-2">{{ $mapLocations->count() }} titik</span>
                        @if($selectedDistrict)
                            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="company-map"></div>
                    <textarea id="company-map-locations" class="d-none">{{ $mapLocationsBase64 }}</textarea>
                    <input type="hidden" id="dashboard-selected-district" value="{{ $selectedDistrict }}">
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ringkasan Kecamatan</h6>
                </div>
                <div class="card-body map-summary-list">
                    @if($mapLocations->isEmpty())
                        <p class="text-muted mb-0">Belum ada data kecamatan.</p>
                    @else
                        @foreach($locationSummaryGroups as $group)
                            @if($group['items']->isNotEmpty())
                                <div class="{{ $loop->last ? '' : 'mb-4' }}">
                                    <h6 class="font-weight-bold text-gray-800 mb-2">{{ $group['area'] }}</h6>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($group['items'] as $summary)
                                            <li class="py-2 border-bottom">
                                                <a href="{{ route('home', ['kecamatan' => $summary['kecamatan']]) }}" class="summary-link">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-gray-800">
                                                            <span class="map-summary-dot" style="background: {{ $summary['color'] }}"></span>
                                                            {{ \Illuminate\Support\Str::title(strtolower($summary['kecamatan'])) }}
                                                        </span>
                                                        <strong>{{ $summary['total'] }}</strong>
                                                    </div>
                                                    <div class="small text-gray-600 mt-1">
                                                        {{ $summary['total'] }} titik aktif / {{ number_format($summary['percentage'], 1, ',', '.') }}%
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                        @if($selectedDistrict)
                            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary mt-3">Tampilkan semua kecamatan</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Pivot Jenis BKC - Golongan</h6>
                    <span class="badge badge-light border text-gray-700">Status Aktif</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table pivot-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-gray-800">Jenis</th>
                                    @foreach($pivotColumns as $column)
                                        <th class="text-center text-gray-800">{{ $column }}</th>
                                    @endforeach
                                    <th class="text-right text-gray-900">Total Keseluruhan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pivotTableRows as $row)
                                    <tr>
                                        <td class="font-weight-normal text-gray-800">{{ $row['name'] }}</td>
                                        @foreach($row['cells'] as $cell)
                                            @switch($cell['class'])
                                                @case('pivot-heat-1')
                                                    <td class="pivot-heat-1">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @case('pivot-heat-2')
                                                    <td class="pivot-heat-2">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @case('pivot-heat-3')
                                                    <td class="pivot-heat-3">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @case('pivot-heat-4')
                                                    <td class="pivot-heat-4">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @case('pivot-heat-5')
                                                    <td class="pivot-heat-5">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @case('pivot-heat-6')
                                                    <td class="pivot-heat-6">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @case('pivot-heat-7')
                                                    <td class="pivot-heat-7">{{ $cell['value'] ?: '' }}</td>
                                                    @break
                                                @default
                                                    <td class="pivot-heat-0"></td>
                                            @endswitch
                                        @endforeach
                                        <td class="pivot-total text-right text-gray-900">{{ $row['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-gray-900">Total Keseluruhan</th>
                                    @foreach($pivotColumns as $column)
                                        <th class="text-right text-gray-900">{{ $pivotColumnTotals[$column] ?? 0 }}</th>
                                    @endforeach
                                    <th class="text-right text-gray-900">{{ $pivotGrandTotal }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Data Perusahaan</h6>
                </div>
                <div class="card-body">
                    <p class="mb-4 text-gray-700">
                        Kelola dan pantau data NPPBKC, status, NPWP, dan informasi SKEP perusahaan.
                    </p>
                    <a href="{{ route('perusahaan.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-right fa-sm mr-1"></i>
                        Buka Perusahaan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Project</h6>
                </div>
                <div class="card-body">
                    <p class="mb-4 text-gray-700">
                        Catat pekerjaan dan tindak lanjut yang berhubungan dengan perusahaan.
                    </p>
                    <a href="{{ route('projects.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-right fa-sm mr-1"></i>
                        Buka Project
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('assets/js/dashboard-map.js') }}"></script>
@endpush
