@extends('layouts.app')

@section('title', 'Monitoring Project - Aplikasi Cukai')
@section('topbar-title', 'Monitoring Project')
@section('page-title', 'Monitoring Project')
@section('page-subtitle', 'Project tracker tugas pegawai berdasarkan ST dan jadwal pelaksanaan')

@section('page-actions')
    <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm shadow-sm">
        <i class="fas fa-folder-open fa-sm mr-1"></i>
        Daftar Project
    </a>
@endsection

@push('styles')
    <style>
        .tracker-summary-card {
            border-left: 4px solid #4e73df;
        }

        .tracker-tabs {
            display: flex;
            gap: .75rem;
            margin-bottom: 1.25rem;
        }

        .tracker-tab {
            align-items: center;
            border: 1px solid #dbe4f0;
            border-radius: .6rem;
            color: #4a5568;
            cursor: pointer;
            display: inline-flex;
            font-weight: 600;
            gap: .55rem;
            padding: .7rem 1rem;
            transition: all .15s ease;
        }

        .tracker-tab.is-active {
            background: #eff6ff;
            border-color: #93c5fd;
            color: #1d4ed8;
        }

        .tracker-panel {
            display: none;
        }

        .tracker-panel.is-active {
            display: block;
        }

        .tracker-toolbar {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: .9rem;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .tracker-search {
            align-items: center;
            background: #fff;
            border: 1px solid #d7e0ec;
            border-radius: .65rem;
            display: flex;
            min-width: 280px;
            overflow: hidden;
        }

        .tracker-search span {
            color: #64748b;
            flex: 0 0 44px;
            text-align: center;
        }

        .tracker-search input {
            border: 0;
            box-shadow: none !important;
            height: 46px;
            padding-left: 0;
        }

        .tracker-switch-card {
            align-items: center;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfeff 100%);
            border: 1px solid #bbf7d0;
            border-radius: .9rem;
            display: inline-flex;
            gap: .85rem;
            min-height: 56px;
            padding: .75rem .95rem;
        }

        .tracker-switch-icon {
            align-items: center;
            background: #dcfce7;
            border-radius: .8rem;
            color: #15803d;
            display: inline-flex;
            flex: 0 0 38px;
            font-size: .95rem;
            height: 38px;
            justify-content: center;
            width: 38px;
        }

        .tracker-switch-copy {
            line-height: 1.3;
            min-width: 0;
        }

        .tracker-switch-copy strong {
            color: #14532d;
            display: block;
            font-size: .9rem;
            font-weight: 700;
            margin-bottom: .1rem;
        }

        .tracker-switch-copy span {
            color: #4b5563;
            display: block;
            font-size: .78rem;
        }

        .tracker-switch {
            align-items: center;
            display: inline-flex;
            margin: 0;
            min-height: 38px;
        }

        .tracker-switch .custom-control-label {
            color: #14532d;
            cursor: pointer;
            font-size: .88rem;
            font-weight: 600;
            padding-left: .25rem;
            padding-top: .08rem;
        }

        .tracker-switch .custom-control-label::before,
        .tracker-switch .custom-control-label::after {
            top: .22rem;
        }

        .tracker-switch .custom-control-input:checked ~ .custom-control-label::before {
            background-color: #16a34a;
            border-color: #16a34a;
        }

        .tracker-table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: .9rem;
            overflow: hidden;
        }

        .tracker-table {
            font-size: .9rem;
            margin-bottom: 0;
        }

        .tracker-table thead th {
            background: #f8fafc;
            border-bottom-width: 1px;
            color: #475569;
            font-size: .77rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            vertical-align: middle;
            white-space: nowrap;
        }

        .tracker-table td {
            vertical-align: middle;
        }

        .tracker-pegawai-cell {
            align-items: center;
            display: flex;
            gap: .8rem;
            min-width: 240px;
        }

        .tracker-avatar {
            align-items: center;
            background: #dbeafe;
            border-radius: 50%;
            color: #1d4ed8;
            display: inline-flex;
            flex: 0 0 42px;
            font-size: .9rem;
            font-weight: 700;
            height: 42px;
            justify-content: center;
            overflow: hidden;
            width: 42px;
        }

        .tracker-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .tracker-pegawai-meta {
            min-width: 0;
        }

        .tracker-pegawai-meta strong {
            color: #0f172a;
            display: block;
            font-size: .92rem;
            line-height: 1.35;
        }

        .tracker-pegawai-meta small {
            color: #64748b;
            display: block;
            line-height: 1.45;
        }

        .tracker-eye-btn {
            align-items: center;
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            border-radius: .7rem;
            color: #047857;
            display: inline-flex;
            height: 36px;
            justify-content: center;
            transition: all .15s ease;
            width: 36px;
        }

        .tracker-eye-btn:hover {
            background: #d1fae5;
            color: #065f46;
            text-decoration: none;
        }

        .tracker-st-link {
            color: #1d4ed8;
            font-weight: 700;
            text-decoration: none;
        }

        .tracker-st-link:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .tracker-empty {
            color: #64748b;
            padding: 1rem 1.1rem;
        }

        .calendar-toolbar {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .calendar-title {
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
        }

        .calendar-grid {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: .9rem;
            overflow: hidden;
        }

        .calendar-weekdays,
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }

        .calendar-weekdays div {
            background: #f8fafc;
            color: #64748b;
            font-size: .78rem;
            font-weight: 700;
            padding: .8rem .7rem;
            text-align: center;
            text-transform: uppercase;
        }

        .calendar-day {
            border-right: 1px solid #edf2f7;
            border-top: 1px solid #edf2f7;
            min-height: 126px;
            padding: .65rem;
            position: relative;
        }

        .calendar-day:nth-child(7n) {
            border-right: 0;
        }

        .calendar-day.is-outside {
            background: #f8fafc;
        }

        .calendar-day.is-today {
            background: #effdf5;
        }

        .calendar-day-number {
            color: #0f172a;
            font-size: .85rem;
            font-weight: 700;
            margin-bottom: .55rem;
        }

        .calendar-day.is-outside .calendar-day-number {
            color: #94a3b8;
        }

        .calendar-avatar-stack {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .calendar-avatar {
            align-items: center;
            background: #dcfce7;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(34, 197, 94, .16);
            color: #166534;
            cursor: default;
            display: inline-flex;
            font-size: .72rem;
            font-weight: 700;
            height: 30px;
            justify-content: center;
            overflow: hidden;
            width: 30px;
        }

        .calendar-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .calendar-avatar-more {
            align-items: center;
            background: #bbf7d0;
            border-radius: 999px;
            color: #166534;
            display: inline-flex;
            font-size: .72rem;
            font-weight: 700;
            height: 30px;
            justify-content: center;
            min-width: 30px;
            padding: 0 .45rem;
        }

        .calendar-legend {
            color: #64748b;
            font-size: .8rem;
            margin-top: .8rem;
        }

        @media (max-width: 991.98px) {
            .tracker-toolbar,
            .calendar-toolbar {
                align-items: stretch;
                flex-direction: column;
            }
        }

        @media (max-width: 767.98px) {
            .tracker-table {
                font-size: .84rem;
            }

            .calendar-weekdays div,
            .calendar-day {
                padding: .45rem;
            }

            .calendar-day {
                min-height: 108px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow tracker-summary-card h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">Pegawai Bertugas</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $summary['pegawai'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow tracker-summary-card h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">Total Penugasan</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $summary['penugasan'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow tracker-summary-card h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">ST Uploaded</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $summary['st_uploaded'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card shadow tracker-summary-card h-100">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-2">Agenda Bulan Ini</div>
                    <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $summary['bulan_ini'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="tracker-tabs">
                <button type="button" class="tracker-tab is-active" data-tracker-tab="pegawai">
                    <i class="fas fa-users"></i>
                    Berdasarkan Pegawai
                </button>
                <button type="button" class="tracker-tab" data-tracker-tab="calendar">
                    <i class="fas fa-calendar-alt"></i>
                    Calendar
                </button>
            </div>

            <div class="tracker-panel is-active" data-tracker-panel="pegawai">
                <div class="tracker-toolbar">
                    <div class="tracker-search flex-grow-1" style="max-width: 440px;">
                        <span><i class="fas fa-search"></i></span>
                        <input id="pegawai-monitoring-search" type="text" class="form-control" placeholder="Cari pegawai, NIP, nomor ST, atau hal ST">
                    </div>
                    <div class="tracker-switch-card">
                        <span class="tracker-switch-icon"><i class="fas fa-filter"></i></span>
                        <div class="tracker-switch-copy">
                            <strong>Filter Pegawai</strong>
                            <span>Tampilkan pegawai seksi cukai saja</span>
                        </div>
                        <div class="custom-control custom-switch tracker-switch">
                            <input type="checkbox" class="custom-control-input" id="pegawai-cukai-toggle" checked>
                            <label class="custom-control-label" for="pegawai-cukai-toggle">Aktif</label>
                        </div>
                    </div>
                </div>

                <div class="tracker-table-wrap">
                    <div class="table-responsive">
                        <table class="table table-hover tracker-table">
                            <thead>
                                <tr>
                                    <th style="width: 28px;">No</th>
                                    <th>Pegawai</th>
                                    <th>No ST</th>
                                    <th>Tgl ST</th>
                                    <th style="min-width: 280px;">Hal ST</th>
                                    <th>Pelaksanaan</th>
                                    <th>Waktu</th>
                                    <th class="text-center" style="width: 72px;">PDF</th>
                                </tr>
                            </thead>
                            <tbody id="pegawai-monitoring-body">
                                @forelse($assignments as $assignment)
                                    <tr
                                        data-monitoring-row="pegawai"
                                        data-is-cukai="{{ $assignment['is_cukai'] ? '1' : '0' }}"
                                        data-search="{{ strtolower(implode(' ', [
                                            $assignment['pegawai_nama'],
                                            $assignment['pegawai_nip'],
                                            $assignment['pegawai_jabatan'],
                                            $assignment['nomor_st'],
                                            $assignment['hal_st'],
                                            $assignment['nama_pabrik'],
                                        ])) }}"
                                    >
                                        <td class="text-muted"></td>
                                        <td>
                                            <div class="tracker-pegawai-cell">
                                                <div class="tracker-avatar">
                                                    @if($assignment['pegawai_foto'])
                                                        <img src="{{ $assignment['pegawai_foto'] }}" alt="{{ $assignment['pegawai_nama'] }}">
                                                    @else
                                                        {{ \Illuminate\Support\Str::of($assignment['pegawai_nama'])->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                                                    @endif
                                                </div>
                                                <div class="tracker-pegawai-meta">
                                                    <strong>{{ $assignment['pegawai_nama'] }}</strong>
                                                    <small>{{ $assignment['pegawai_nip'] ?: '-' }}</small>
                                                    <small>{{ $assignment['pegawai_jabatan'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="font-weight-bold text-gray-800">
                                            @if($assignment['project_url'])
                                                <a href="{{ $assignment['project_url'] }}" class="tracker-st-link">{{ $assignment['nomor_st'] }}</a>
                                            @else
                                                {{ $assignment['nomor_st'] }}
                                            @endif
                                        </td>
                                        <td>{{ $assignment['tanggal_st_label'] }}</td>
                                        <td>{{ $assignment['hal_st'] }}</td>
                                        <td>{{ $assignment['rentang_label'] }}</td>
                                        <td>{{ $assignment['waktu_label'] }}</td>
                                        <td class="text-center">
                                            @if($assignment['st_pdf_url'])
                                                <button
                                                    type="button"
                                                    class="tracker-eye-btn"
                                                    data-toggle="modal"
                                                    data-target="#monitoringStPdfModal"
                                                    data-pdf-url="{{ $assignment['st_pdf_url'] }}"
                                                    data-pdf-title="{{ $assignment['nomor_st'] }} - {{ $assignment['pegawai_nama'] }}"
                                                    title="Lihat PDF ST"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="tracker-empty">Belum ada data penugasan untuk dimonitor.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tracker-panel" data-tracker-panel="calendar">
                <div class="calendar-toolbar">
                    <div class="d-flex flex-column" style="gap: .65rem;">
                        <div>
                            <h6 id="tracker-calendar-title" class="calendar-title mb-1"></h6>
                            <small class="text-muted">Avatar hijau menandai pegawai yang bertugas. Hover avatar untuk melihat detail ST per hari.</small>
                        </div>
                        <div class="tracker-switch-card">
                            <span class="tracker-switch-icon"><i class="fas fa-user-check"></i></span>
                            <div class="tracker-switch-copy">
                                <strong>Filter Calendar</strong>
                                <span>Tampilkan pegawai seksi cukai saja</span>
                            </div>
                            <div class="custom-control custom-switch tracker-switch">
                                <input type="checkbox" class="custom-control-input" id="calendar-cukai-toggle" checked>
                                <label class="custom-control-label" for="calendar-cukai-toggle">Aktif</label>
                            </div>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary" id="calendar-prev">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="calendar-today">Bulan Ini</button>
                        <button type="button" class="btn btn-outline-secondary" id="calendar-next">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-weekdays">
                        <div>Sen</div>
                        <div>Sel</div>
                        <div>Rab</div>
                        <div>Kam</div>
                        <div>Jum</div>
                        <div>Sab</div>
                        <div>Min</div>
                    </div>
                    <div id="tracker-calendar-days" class="calendar-days"></div>
                </div>
                <div class="calendar-legend">
                    Saat filter dimatikan, semua pegawai tetap tampil. Bila dalam satu hari ada lebih dari 5 pegawai, sisanya diringkas menjadi <strong>+N</strong>.
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="monitoringStPdfModal" tabindex="-1" role="dialog" aria-labelledby="monitoringStPdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monitoringStPdfModalLabel">Preview PDF ST</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="height: 78vh;">
                    <iframe id="monitoringStPdfFrame" src="about:blank" title="Preview PDF ST" style="width: 100%; height: 100%; border: 0;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var tabs = Array.from(document.querySelectorAll('[data-tracker-tab]'));
            var panels = Array.from(document.querySelectorAll('[data-tracker-panel]'));
            var pegawaiSearchInput = document.getElementById('pegawai-monitoring-search');
            var pegawaiToggle = document.getElementById('pegawai-cukai-toggle');
            var calendarToggle = document.getElementById('calendar-cukai-toggle');
            var tableRows = Array.from(document.querySelectorAll('[data-monitoring-row="pegawai"]'));
            var calendarTitle = document.getElementById('tracker-calendar-title');
            var calendarDays = document.getElementById('tracker-calendar-days');
            var prevButton = document.getElementById('calendar-prev');
            var nextButton = document.getElementById('calendar-next');
            var todayButton = document.getElementById('calendar-today');
            var pdfModal = document.getElementById('monitoringStPdfModal');
            var pdfFrame = document.getElementById('monitoringStPdfFrame');
            var pdfTitle = document.getElementById('monitoringStPdfModalLabel');
            var events = @json($calendarEvents);
            var currentDate = new Date();

            function initials(name) {
                return (name || '')
                    .trim()
                    .split(/\s+/)
                    .slice(0, 2)
                    .map(function (part) { return part.charAt(0).toUpperCase(); })
                    .join('');
            }

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function setActiveTab(name) {
                tabs.forEach(function (tab) {
                    tab.classList.toggle('is-active', tab.dataset.trackerTab === name);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.dataset.trackerPanel === name);
                });
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    setActiveTab(tab.dataset.trackerTab);
                });
            });

            function applyPegawaiTableFilter() {
                var keyword = (pegawaiSearchInput ? pegawaiSearchInput.value : '').trim().toLowerCase();
                var onlyCukai = !pegawaiToggle || pegawaiToggle.checked;
                var visibleIndex = 0;

                tableRows.forEach(function (row) {
                    var haystack = row.dataset.search || '';
                    var matchesKeyword = keyword === '' || haystack.indexOf(keyword) !== -1;
                    var matchesCukai = !onlyCukai || row.dataset.isCukai === '1';
                    var visible = matchesKeyword && matchesCukai;

                    row.classList.toggle('d-none', !visible);

                    if (visible) {
                        visibleIndex += 1;
                        var numberCell = row.querySelector('td');
                        if (numberCell) {
                            numberCell.textContent = visibleIndex;
                        }
                    }
                });
            }

            if (pegawaiSearchInput) {
                pegawaiSearchInput.addEventListener('input', applyPegawaiTableFilter);
            }

            if (pegawaiToggle) {
                pegawaiToggle.addEventListener('change', applyPegawaiTableFilter);
            }

            function formatMonthTitle(date) {
                return new Intl.DateTimeFormat('id-ID', {
                    month: 'long',
                    year: 'numeric'
                }).format(date);
            }

            function isoDate(date) {
                return date.toISOString().slice(0, 10);
            }

            function getFilteredEvents() {
                var onlyCukai = !calendarToggle || calendarToggle.checked;

                return events.filter(function (event) {
                    return !onlyCukai || !!event.is_cukai;
                });
            }

            function buildAvatar(person) {
                var tooltipLines = [person.pegawai_nama].concat(person.assignments.map(function (assignment) {
                    return assignment.nomor_st + ' - ' + assignment.hal_st;
                }));
                var title = escapeHtml(tooltipLines.join('\n'));

                if (person.pegawai_foto) {
                    return '<span class="calendar-avatar" title="' + title + '"><img src="' + escapeHtml(person.pegawai_foto) + '" alt="' + escapeHtml(person.pegawai_nama) + '"></span>';
                }

                return '<span class="calendar-avatar" title="' + title + '">' + escapeHtml(initials(person.pegawai_nama)) + '</span>';
            }

            function eventsByDay(year, month) {
                var map = {};
                var monthStart = new Date(year, month, 1);
                var monthEnd = new Date(year, month + 1, 0);

                getFilteredEvents().forEach(function (event) {
                    if (!event.tanggal_mulai) {
                        return;
                    }

                    var start = new Date(event.tanggal_mulai + 'T00:00:00');
                    var end = new Date((event.tanggal_selesai || event.tanggal_mulai) + 'T00:00:00');

                    if (end < monthStart || start > monthEnd) {
                        return;
                    }

                    var cursor = new Date(start);
                    while (cursor <= end) {
                        var key = isoDate(cursor);
                        if (!map[key]) {
                            map[key] = [];
                        }
                        map[key].push(event);
                        cursor.setDate(cursor.getDate() + 1);
                    }
                });

                return map;
            }

            function renderCalendar() {
                if (!calendarDays || !calendarTitle) {
                    return;
                }

                var year = currentDate.getFullYear();
                var month = currentDate.getMonth();
                var firstDay = new Date(year, month, 1);
                var startWeekday = (firstDay.getDay() + 6) % 7;
                var gridStart = new Date(year, month, 1 - startWeekday);
                var todayKey = isoDate(new Date());
                var dayMap = eventsByDay(year, month);

                calendarTitle.textContent = formatMonthTitle(currentDate);
                calendarDays.innerHTML = '';

                for (var i = 0; i < 42; i++) {
                    var day = new Date(gridStart);
                    day.setDate(gridStart.getDate() + i);

                    var key = isoDate(day);
                    var dayEvents = dayMap[key] || [];
                    var peopleMap = {};

                    dayEvents.forEach(function (event) {
                        if (!peopleMap[event.pegawai_id]) {
                            peopleMap[event.pegawai_id] = {
                                pegawai_id: event.pegawai_id,
                                pegawai_nama: event.pegawai_nama,
                                pegawai_foto: event.pegawai_foto,
                                assignments: []
                            };
                        }

                        peopleMap[event.pegawai_id].assignments.push({
                            nomor_st: event.nomor_st,
                            hal_st: event.hal_st
                        });
                    });

                    var uniquePegawai = Object.keys(peopleMap).map(function (pegawaiId) {
                        return peopleMap[pegawaiId];
                    }).sort(function (a, b) {
                        return a.pegawai_nama.localeCompare(b.pegawai_nama, 'id');
                    });

                    var avatars = uniquePegawai.slice(0, 5).map(buildAvatar).join('');
                    if (uniquePegawai.length > 5) {
                        avatars += '<span class="calendar-avatar-more">+' + (uniquePegawai.length - 5) + '</span>';
                    }

                    var classes = ['calendar-day'];
                    if (day.getMonth() !== month) {
                        classes.push('is-outside');
                    }
                    if (key === todayKey) {
                        classes.push('is-today');
                    }

                    calendarDays.insertAdjacentHTML('beforeend',
                        '<div class="' + classes.join(' ') + '">' +
                            '<div class="calendar-day-number">' + day.getDate() + '</div>' +
                            '<div class="calendar-avatar-stack">' + avatars + '</div>' +
                        '</div>'
                    );
                }
            }

            if (calendarToggle) {
                calendarToggle.addEventListener('change', renderCalendar);
            }

            if (prevButton) {
                prevButton.addEventListener('click', function () {
                    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1);
                    renderCalendar();
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', function () {
                    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1);
                    renderCalendar();
                });
            }

            if (todayButton) {
                todayButton.addEventListener('click', function () {
                    currentDate = new Date();
                    renderCalendar();
                });
            }

            if (pdfModal && pdfFrame) {
                $(pdfModal).on('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    if (!button) {
                        return;
                    }

                    var url = button.getAttribute('data-pdf-url') || 'about:blank';
                    var title = button.getAttribute('data-pdf-title') || 'Preview PDF ST';

                    pdfFrame.src = url;
                    if (pdfTitle) {
                        pdfTitle.textContent = title;
                    }
                });

                $(pdfModal).on('hidden.bs.modal', function () {
                    pdfFrame.src = 'about:blank';
                });
            }

            applyPegawaiTableFilter();
            renderCalendar();
        });
    </script>
@endpush
