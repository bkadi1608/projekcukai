<?php

use Illuminate\Support\Facades\Route;
use App\Models\Perusahaan;
use App\Models\Project;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\ProjectNdPermohonanStController;
use App\Http\Controllers\TujuanStController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🔐 SEMUA BUTUH LOGIN
Route::middleware(['auth'])->group(function () {

    // 🏠 HOME (dashboard)
    Route::get('/', function () {
        $selectedDistrict = strtoupper(trim((string) request()->query('kecamatan', '')));

        $parseCoordinates = static function (?string $longlat): ?array {
            if (! preg_match('/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/', (string) $longlat, $matches)) {
                return null;
            }

            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        };

        $normalizeJenis = static function (string $jenis): string {
            $jenis = trim($jenis);

            return match (true) {
                str_contains($jenis, 'Rokok Elektrik') => 'REL',
                str_contains($jenis, 'Hasil Pengolahan Tembakau Lainnya') => 'HPTL',
                default => $jenis,
            };
        };

        $normalizeGolongan = static function (string $golongan): string {
            $golongan = trim($golongan);

            return match (true) {
                strcasecmp($golongan, 'I') === 0 => 'I',
                strcasecmp($golongan, 'II') === 0 => 'II',
                strcasecmp($golongan, 'III') === 0 => 'III',
                strcasecmp($golongan, 'Importir') === 0 => 'Importir',
                strcasecmp($golongan, 'Tanpa Golongan') === 0 => 'Tanpa Golongan',
                default => $golongan,
            };
        };

        $normalizeDistrict = static function (?string $district): string {
            $district = strtoupper(trim((string) $district));

            return $district !== '' ? $district : 'TANPA KECAMATAN';
        };

        $normalizeCompanyName = static function (?string $name): string {
            return preg_replace('/\s+/u', ' ', mb_strtoupper(trim((string) $name), 'UTF-8'));
        };

        $cityDistricts = [
            'BUGUL KIDUL',
            'GADINGREJO',
            'PANGGUNGREJO',
            'PURWOREJO',
        ];

        $isPkp = static function (?string $value): bool {
            return in_array(strtoupper(trim((string) $value)), ['TRUE', 'YA', 'Y', 'PKP', '1'], true);
        };

        $percent = static function (int $value, int $total): float {
            if ($total <= 0) {
                return 0.0;
            }

            return round(($value / $total) * 100, 1);
        };

        $districtPalette = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#858796',
            '#5a5c69',
            '#fd7e14',
            '#20c997',
            '#6f42c1',
            '#e83e8c',
            '#17a2b8',
            '#28a745',
            '#dc3545',
            '#6610f2',
            '#2c9faf',
        ];

        $companies = Perusahaan::query()
            ->orderBy('nama_pabrik_sac')
            ->get([
                'nama_pabrik_sac',
                'jenis_usaha',
                'pkp',
                'status',
                'jenis_bkc_golongan',
                'alamat_pabrik_utama',
                'lokasi_gmaps',
                'longlat',
                'kecamatan',
            ]);

        $allDistricts = $companies
            ->pluck('kecamatan')
            ->map($normalizeDistrict)
            ->filter(fn (string $district) => $district !== 'TANPA KECAMATAN')
            ->unique()
            ->sort()
            ->values()
            ->mapWithKeys(fn ($district, $index) => [
                $district => $districtPalette[$index % count($districtPalette)],
            ]);

        if ($selectedDistrict !== '' && ! $allDistricts->has($selectedDistrict)) {
            $selectedDistrict = '';
        }

        $filteredCompanies = $selectedDistrict !== ''
            ? $companies->filter(fn (Perusahaan $perusahaan) => $normalizeDistrict($perusahaan->kecamatan) === $selectedDistrict)->values()
            : $companies->values();

        $activeCompanies = $filteredCompanies
            ->filter(fn (Perusahaan $perusahaan) => strtolower(trim((string) $perusahaan->status)) === 'aktif')
            ->values();

        $companyDistrictMap = $companies
            ->filter(fn (Perusahaan $perusahaan) => filled($perusahaan->nama_pabrik_sac))
            ->mapWithKeys(fn (Perusahaan $perusahaan) => [
                $normalizeCompanyName($perusahaan->nama_pabrik_sac) => $normalizeDistrict($perusahaan->kecamatan),
            ]);

        $mapLocations = $activeCompanies
            ->filter(fn (Perusahaan $perusahaan) => filled($perusahaan->longlat))
            ->map(function (Perusahaan $perusahaan) use ($parseCoordinates, $normalizeDistrict, $allDistricts) {
                $coordinates = $parseCoordinates($perusahaan->longlat);

                if (! $coordinates) {
                    return null;
                }

                $district = $normalizeDistrict($perusahaan->kecamatan);

                return [
                    'name' => $perusahaan->nama_pabrik_sac,
                    'status' => trim((string) $perusahaan->status),
                    'address' => $perusahaan->alamat_pabrik_utama,
                    'kecamatan' => $district,
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng'],
                    'color' => $allDistricts[$district] ?? '#4e73df',
                    'maps' => $perusahaan->lokasi_gmaps
                        ?: 'https://www.google.com/maps/search/?api=1&query='.urlencode($perusahaan->longlat),
                ];
            })
            ->filter()
            ->values();

        $mapLocationsJson = $mapLocations->toJson(
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        );
        $mapLocationsBase64 = base64_encode($mapLocationsJson);

        $totalCompanies = $filteredCompanies->count();
        $activeTotal = $activeCompanies->count();
        $bekuTotal = $filteredCompanies->filter(fn (Perusahaan $perusahaan) => strtolower(trim((string) $perusahaan->status)) === 'beku')->count();

        $activeBreakdown = collect([
            ['label' => 'Pabrik Rokok', 'match' => 'Pabrik Rokok'],
            ['label' => 'Kawasan Berikat', 'match' => 'Kawasan Berikat'],
            ['label' => 'Importir', 'match' => 'Importir'],
        ])->map(function (array $item) use ($activeCompanies, $percent, $activeTotal) {
            $count = $activeCompanies->filter(fn (Perusahaan $perusahaan) => stripos((string) $perusahaan->jenis_usaha, $item['match']) !== false)->count();

            return [
                'label' => $item['label'],
                'count' => $count,
                'percentage' => $percent($count, $activeTotal),
            ];
        });

        $pkpTotal = $activeCompanies->filter(fn (Perusahaan $perusahaan) => $isPkp($perusahaan->pkp))->count();
        $nonPkpTotal = max(0, $activeTotal - $pkpTotal);

        $summaryCards = [
            'aktif' => [
                'total' => $activeTotal,
                'breakdown' => $activeBreakdown,
            ],
            'beku' => [
                'total' => $bekuTotal,
                'percentage' => $percent($bekuTotal, $totalCompanies),
            ],
            'pkp' => [
                'pkp' => [
                    'count' => $pkpTotal,
                    'percentage' => $percent($pkpTotal, $activeTotal),
                ],
                'non_pkp' => [
                    'count' => $nonPkpTotal,
                    'percentage' => $percent($nonPkpTotal, $activeTotal),
                ],
            ],
        ];

        $mapSummaryTotal = $mapLocations->count();
        $locationSummary = $mapLocations
            ->groupBy(fn (array $location) => in_array($location['kecamatan'], $cityDistricts, true) ? 'Kota' : 'Kabupaten')
            ->map(function ($locations) use ($percent, $mapSummaryTotal) {
                return $locations
                    ->groupBy('kecamatan')
                    ->map(function ($items, $district) use ($percent, $mapSummaryTotal) {
                        $total = $items->count();

                        return [
                            'kecamatan' => $district,
                            'total' => $total,
                            'percentage' => $percent($total, $mapSummaryTotal),
                            'color' => $items->first()['color'] ?? '#4e73df',
                        ];
                    })
                    ->sortByDesc('total')
                    ->values();
            });

        $locationSummaryGroups = collect(['Kota', 'Kabupaten'])
            ->map(fn (string $area) => [
                'area' => $area,
                'items' => $locationSummary->get($area, collect()),
            ]);

        $districtSummary = $activeCompanies
            ->groupBy(fn (Perusahaan $perusahaan) => $normalizeDistrict($perusahaan->kecamatan))
            ->reject(fn ($items, $district) => $district === 'TANPA KECAMATAN')
            ->map(fn ($items, $district) => [
                'kecamatan' => $district,
                'total' => $items->count(),
                'percentage' => $percent($items->count(), $totalCompanies),
                'color' => $allDistricts[$district] ?? '#4e73df',
            ])
            ->sortByDesc('total')
            ->values();

        $pivotRows = ['SKT', 'SKM', 'SPM', 'TIS', 'SPT', 'REL', 'SKTF', 'KLM', 'HPTL', 'CRT'];
        $pivotColumns = ['I', 'II', 'III', 'Importir', 'Tanpa Golongan'];
        $pivotData = [];
        $pivotRowTotals = array_fill_keys($pivotRows, 0);
        $pivotColumnTotals = array_fill_keys($pivotColumns, 0);

        foreach ($pivotRows as $row) {
            $pivotData[$row] = array_fill_keys($pivotColumns, 0);
        }

        $activeCompanies
            ->filter(fn (Perusahaan $perusahaan) => filled($perusahaan->jenis_bkc_golongan))
            ->each(function (Perusahaan $perusahaan) use (&$pivotData, &$pivotRowTotals, &$pivotColumnTotals, $pivotRows, $pivotColumns, $normalizeJenis, $normalizeGolongan) {
                $pairs = [];

                foreach (explode(',', $perusahaan->jenis_bkc_golongan) as $item) {
                    if (! str_contains($item, ' - ')) {
                        continue;
                    }

                    [$jenis, $golongan] = array_map('trim', explode(' - ', $item, 2));
                    $jenis = $normalizeJenis($jenis);
                    $golongan = $normalizeGolongan($golongan);

                    if (! in_array($jenis, $pivotRows, true) || ! in_array($golongan, $pivotColumns, true)) {
                        continue;
                    }

                    $pairs[$jenis.'|'.$golongan] = [$jenis, $golongan];
                }

                foreach ($pairs as [$jenis, $golongan]) {
                    $pivotData[$jenis][$golongan]++;
                    $pivotRowTotals[$jenis]++;
                    $pivotColumnTotals[$golongan]++;
                }
            });

        $pivotData = collect($pivotData)
            ->filter(fn ($columns, $row) => ($pivotRowTotals[$row] ?? 0) > 0);

        $pivotRows = $pivotData->keys()->values();
        $pivotMax = max(1, (int) ($pivotData
            ->flatMap(fn ($columns) => array_values($columns))
            ->max() ?? 1));
        $pivotGrandTotal = $activeTotal;
        $pivotTableRows = $pivotRows->map(function ($row) use ($pivotColumns, $pivotData, $pivotMax, $pivotRowTotals) {
            $cells = collect($pivotColumns)->map(function ($column) use ($row, $pivotData, $pivotMax) {
                $value = $pivotData[$row][$column] ?? 0;
                $heat = $value ? min(7, max(1, (int) ceil(($value / $pivotMax) * 7))) : 0;

                return [
                    'value' => $value,
                    'class' => 'pivot-heat-'.$heat,
                ];
            });

            return [
                'name' => $row,
                'cells' => $cells,
                'total' => $pivotRowTotals[$row] ?? 0,
            ];
        });

        $today = now()->startOfDay();
        $ongoingProjects = Project::with('ndPermohonanSt')
            ->get()
            ->filter(function (Project $project) use ($today, $selectedDistrict, $companyDistrictMap, $normalizeCompanyName) {
                $nd = $project->ndPermohonanSt;

                if (! $nd || ! $nd->tanggal_mulai) {
                    return false;
                }

                $start = $nd->tanggal_mulai->copy()->startOfDay();
                $end = ($nd->tanggal_selesai ?: $nd->tanggal_mulai)->copy()->endOfDay();

                if (! $today->between($start, $end)) {
                    return false;
                }

                if ($selectedDistrict === '') {
                    return true;
                }

                $projectDistrict = $companyDistrictMap[$normalizeCompanyName($project->nama_pabrik)] ?? null;

                return $projectDistrict === $selectedDistrict;
            })
            ->values();

        $projectSummary = [
            'ongoing_projects' => $ongoingProjects->count(),
            'st_total' => $ongoingProjects->filter(fn (Project $project) => filled(optional($project->ndPermohonanSt)->nomor_st))->count(),
        ];

        return view('dashboard', compact(
            'mapLocations',
            'mapLocationsBase64',
            'summaryCards',
            'locationSummaryGroups',
            'districtSummary',
            'pivotColumns',
            'pivotTableRows',
            'pivotColumnTotals',
            'pivotGrandTotal',
            'projectSummary',
            'selectedDistrict'
        ));
    })->name('home');

    // optional redirect
    Route::get('/dashboard', function () {
        return redirect('/');
    })->name('dashboard');

    // 👤 PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 📁 PROJECT
    Route::get('/projects/monitoring', [ProjectController::class, 'monitoring'])
        ->name('projects.monitoring');
    Route::resource('projects', ProjectController::class)
        ->only(['index', 'show', 'create', 'edit']);
    Route::resource('projects', ProjectController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('throttle:project-write');
    Route::post('/projects/extract-surat-permohonan', [ProjectController::class, 'extractSuratPermohonan'])
        ->middleware('throttle:ocr-extract')
        ->name('projects.extract-surat-permohonan');
    Route::get('/projects/{project}/surat-permohonan-pdf', [ProjectController::class, 'showSuratPermohonanPdf'])
        ->name('projects.surat-permohonan-pdf');
    Route::post('/projects/{project}/nd-permohonan-st', [ProjectNdPermohonanStController::class, 'store'])
        ->middleware('throttle:project-write')
        ->name('projects.nd-permohonan-st.store');
    Route::post('/projects/{project}/nd-permohonan-st/extract-pdf', [ProjectNdPermohonanStController::class, 'extractNdPdf'])
        ->middleware('throttle:ocr-extract')
        ->name('projects.nd-permohonan-st.extract-pdf');
    Route::get('/projects/{project}/nd-permohonan-st/word', [ProjectNdPermohonanStController::class, 'downloadWord'])
        ->name('projects.nd-permohonan-st.word');
    Route::get('/projects/{project}/nd-permohonan-st/pdf', [ProjectNdPermohonanStController::class, 'showNdPdf'])
        ->name('projects.nd-permohonan-st.pdf');
    Route::post('/projects/{project}/nomor-st', [ProjectNdPermohonanStController::class, 'storeSt'])
        ->middleware('throttle:project-write')
        ->name('projects.nomor-st.store');
    Route::post('/projects/{project}/nomor-st/extract-pdf', [ProjectNdPermohonanStController::class, 'extractStPdf'])
        ->middleware('throttle:ocr-extract')
        ->name('projects.nomor-st.extract-pdf');
    Route::get('/projects/{project}/nomor-st/pdf', [ProjectNdPermohonanStController::class, 'showStPdf'])
        ->name('projects.nomor-st.pdf');

    // 🏢 PERUSAHAAN
    Route::resource('perusahaan', PerusahaanController::class);

    // 🔄 SYNC GOOGLE SHEETS
    Route::post('/perusahaan/sync', [PerusahaanController::class, 'sync'])
        ->middleware('throttle:sync-actions')
        ->name('perusahaan.sync');

    // 👥 PEGAWAI
    Route::resource('pegawai', PegawaiController::class);

    Route::post('/pegawai/sync', [PegawaiController::class, 'sync'])
        ->middleware('throttle:sync-actions')
        ->name('pegawai.sync');

    // 📍 TUJUAN ST
    Route::get('/tujuan-st', [TujuanStController::class, 'index'])
        ->name('tujuan-st.index');
    Route::post('/tujuan-st/sync', [TujuanStController::class, 'sync'])
        ->middleware('throttle:sync-actions')
        ->name('tujuan-st.sync');

    if (config('security.allow_debug_routes')) {
        Route::get('/cek-db', function () {
            return config('database.default');
        });
    }
    
});

// 🔑 AUTH (login, register, dll)
require __DIR__.'/auth.php';
