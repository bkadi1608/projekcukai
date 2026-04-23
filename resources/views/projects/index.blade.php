@extends('layouts.app')

@section('title', 'Project - Aplikasi Cukai')
@section('topbar-title', 'Project')
@section('page-title', 'Daftar Project')
@section('page-subtitle', 'Project permohonan dan ruang kerja dokumen')

@section('page-actions')
    <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i>
        Tambah Project
    </a>
@endsection

@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Project</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="projects-table" class="table table-bordered table-hover project-table" data-font-scale="100">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 64px;">No</th>
                            <th>Pengirim</th>
                            <th>Jenis Permohonan</th>
                            <th>No Surat</th>
                            <th>Tgl Surat</th>
                            <th class="project-hal-column">Hal</th>
                            <th>Status</th>
                            <th>Dibuat Oleh</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                        <tr class="project-table-filters">
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="0" aria-label="Cari No">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="1" aria-label="Cari Pengirim">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="2" aria-label="Cari Jenis Permohonan">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="3" aria-label="Cari No Surat">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="4" aria-label="Cari Tgl Surat">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="5" aria-label="Cari Hal">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="6" aria-label="Cari Status">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm project-column-filter" data-column="7" aria-label="Cari Dibuat Oleh">
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td title="{{ $project->pengirim ?? $project->perusahaan }}">{{ $project->nama_pabrik }}</td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $project->jenis_permohonan ?? $project->nama_project }}
                                    </span>
                                    @if($project->label_project_tanpa_pabrik)
                                        <div class="small text-muted mt-1">{{ $project->label_project_tanpa_pabrik }}</div>
                                    @endif
                                </td>
                                <td>{{ $project->no_surat_permohonan ?? '-' }}</td>
                                <td>{{ $project->tgl_surat_permohonan?->format('d/m/Y') ?? $project->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="project-hal-column">{{ \Illuminate\Support\Str::limit($project->hal_surat_permohonan ?? $project->keterangan, 120) }}</td>
                                <td>
                                    @if($project->ndPermohonanSt)
                                        <span class="badge badge-success">Draft ND Permohonan ST tersimpan</span>
                                    @else
                                        <span class="badge badge-secondary">Belum buat ND Permohonan ST</span>
                                    @endif
                                </td>
                                <td>{{ $project->user->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-folder-open"></i>
                                    </a>

                                    @if($project->user_id === auth()->id())
                                        <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus project ini?')" type="submit">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada project</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end align-items-center mt-3 project-font-controls">
                <span class="small text-muted mr-2">Ukuran font</span>
                <div class="btn-group btn-group-sm" role="group" aria-label="Ukuran font tabel">
                    <button type="button" class="btn btn-outline-secondary project-font-size" data-size="75">75%</button>
                    <button type="button" class="btn btn-outline-secondary project-font-size active" data-size="100">100%</button>
                    <button type="button" class="btn btn-outline-secondary project-font-size" data-size="125">125%</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .project-table {
            --project-font-size: .875rem;
            --project-badge-font-size: .72rem;
            --project-button-font-size: .78rem;
            --project-small-font-size: .72rem;
            font-size: var(--project-font-size);
        }

        .project-table[data-font-scale="75"] {
            --project-font-size: .75rem;
            --project-badge-font-size: .64rem;
            --project-button-font-size: .7rem;
            --project-small-font-size: .64rem;
        }

        .project-table[data-font-scale="100"] {
            --project-font-size: .875rem;
            --project-badge-font-size: .72rem;
            --project-button-font-size: .78rem;
            --project-small-font-size: .72rem;
        }

        .project-table[data-font-scale="125"] {
            --project-font-size: 1rem;
            --project-badge-font-size: .8rem;
            --project-button-font-size: .88rem;
            --project-small-font-size: .8rem;
        }

        .project-table th,
        .project-table td {
            font-size: var(--project-font-size) !important;
            vertical-align: middle;
        }

        .project-table .badge {
            font-size: var(--project-badge-font-size) !important;
        }

        .project-table .btn,
        .project-table .btn-sm,
        .project-table .fas,
        .project-table .fa {
            font-size: var(--project-button-font-size) !important;
        }

        .project-table .small,
        .project-table small,
        .project-table .text-muted {
            font-size: var(--project-small-font-size) !important;
        }

        .project-table .project-hal-column {
            min-width: 360px;
            width: 32%;
        }

        .project-table-filters th {
            padding: .3rem;
        }

        .project-table-filters .form-control {
            border-radius: .55rem;
            font-size: .72rem;
            min-height: 32px;
            min-width: 72px;
            padding: .3rem .55rem;
        }

        .project-font-controls .btn.active {
            color: #fff;
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .project-font-controls .btn {
            min-height: 30px;
            padding: .3rem .7rem;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var table = document.getElementById('projects-table');
            var filters = Array.prototype.slice.call(document.querySelectorAll('.project-column-filter'));
            var fontButtons = Array.prototype.slice.call(document.querySelectorAll('.project-font-size'));

            function applyFontScale(size) {
                var scaleMap = {
                    '75': {
                        table: '.75rem',
                        badge: '.64rem',
                        button: '.7rem',
                        small: '.64rem'
                    },
                    '100': {
                        table: '.875rem',
                        badge: '.72rem',
                        button: '.78rem',
                        small: '.72rem'
                    },
                    '125': {
                        table: '1rem',
                        badge: '.8rem',
                        button: '.88rem',
                        small: '.8rem'
                    }
                };
                var scale = scaleMap[size] || scaleMap['100'];

                table.dataset.fontScale = size;
                table.style.setProperty('--project-font-size', scale.table);
                table.style.setProperty('--project-badge-font-size', scale.badge);
                table.style.setProperty('--project-button-font-size', scale.button);
                table.style.setProperty('--project-small-font-size', scale.small);
            }

            function filterTable() {
                var terms = filters.map(function (input) {
                    return {
                        column: parseInt(input.dataset.column, 10),
                        value: input.value.trim().toLowerCase()
                    };
                });

                Array.prototype.slice.call(table.tBodies[0].rows).forEach(function (row) {
                    if (row.cells.length <= 1) {
                        return;
                    }

                    var visible = terms.every(function (term) {
                        if (!term.value) {
                            return true;
                        }

                        var cell = row.cells[term.column];
                        return cell && cell.textContent.toLowerCase().indexOf(term.value) !== -1;
                    });

                    row.classList.toggle('d-none', !visible);
                });
            }

            filters.forEach(function (input) {
                input.addEventListener('input', filterTable);
            });

            fontButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    applyFontScale(button.dataset.size);
                    fontButtons.forEach(function (item) {
                        item.classList.toggle('active', item === button);
                    });
                });
            });

            applyFontScale(table.dataset.fontScale || '100');
        });
    </script>
@endpush
