<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use App\Models\Pegawai;
use App\Models\Project;
use App\Models\ProjectNdPermohonanSt;
use App\Models\TujuanSt;
use App\Services\SuratPermohonanOcrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    private const JENIS_PERMOHONAN = [
        'CK-5 Manual',
        'CK-5 Market Return',
        'Ekspor',
        'Kepatuhan Pengusaha BKC',
        'Kepatuhan Pengguna Fasilitas Cukai',
        'Lain-lain',
        'PBCK-1',
        'PBCK-3 KPPBC',
        'PBCK-4',
        'PBCK-7',
        'PBCK-8',
        'PBCK-8 (Pelaksanaan)',
        'Pemeriksaan Lokasi',
        'Pencacahan PC',
        'Pendampingan BPK',
        'Penyegelan / Buka Segel',
        'SE-25',
        'Visiting dan asistensi',
        'WASTE',
    ];

    private const MONITORING_CUKAI_NAMES = [
        'rahmad ardian',
        'i made dwiky',
        'munfarid',
        'arif sholi',
        'fredy',
        'achmad junaidi',
        'endah',
        'wardah',
        'harsono',
        'bagas kurnia',
    ];

    public function index()
    {
        $projects = Project::with(['user', 'ndPermohonanSt'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function monitoring()
    {
        $nds = ProjectNdPermohonanSt::with(['project', 'pegawais'])
            ->whereHas('pegawais')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('tanggal_st')
            ->get();

        $assignments = $nds->flatMap(function (ProjectNdPermohonanSt $nd) {
            $project = $nd->project;

            return $nd->pegawais
                ->filter(fn (Pegawai $pegawai) => ! $this->isExcludedMonitoringPegawai($pegawai))
                ->map(function (Pegawai $pegawai) use ($nd, $project) {
                    return [
                        'pegawai_id' => $pegawai->id,
                        'pegawai_nama' => $pegawai->nm_pegawai,
                        'pegawai_nip' => $pegawai->nip,
                        'pegawai_jabatan' => $pegawai->jabatan ?: '-',
                        'pegawai_pangkat' => $pegawai->pangkat_golongan ?: '-',
                        'pegawai_foto' => $pegawai->url_foto ?: null,
                        'project_id' => $project?->id,
                        'project_label' => $project?->jenis_permohonan ?: 'Project',
                        'project_url' => $project?->id ? route('projects.show', $project) : null,
                        'nama_pabrik' => $project?->nama_pabrik ?: '-',
                        'nomor_st' => $nd->nomor_st ?: '-',
                        'tanggal_st' => optional($nd->tanggal_st)?->format('Y-m-d'),
                        'tanggal_st_label' => $nd->tanggal_st ? $this->formatDateIndonesia($nd->tanggal_st) : '-',
                        'hal_st' => $this->buildMonitoringHalSt($nd, $project),
                        'tanggal_mulai' => optional($nd->tanggal_mulai)?->format('Y-m-d'),
                        'tanggal_selesai' => optional($nd->tanggal_selesai)?->format('Y-m-d'),
                        'rentang_label' => $this->formatMonitoringRange($nd),
                        'waktu_label' => $nd->waktu_pelaksanaan_text ?: '-',
                        'st_pdf_url' => $nd->st_pdf_path ? route('projects.nomor-st.pdf', $project) : null,
                        'is_cukai' => $this->isMonitoringCukaiPegawai($pegawai),
                    ];
                });
        })->values();

        $pegawaiGroups = $assignments
            ->groupBy('pegawai_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'pegawai_id' => $first['pegawai_id'],
                    'pegawai_nama' => $first['pegawai_nama'],
                    'pegawai_nip' => $first['pegawai_nip'],
                    'pegawai_jabatan' => $first['pegawai_jabatan'],
                    'pegawai_pangkat' => $first['pegawai_pangkat'],
                    'pegawai_foto' => $first['pegawai_foto'],
                    'assignment_count' => $items->count(),
                    'assignments' => $items
                        ->sortBy([
                            ['tanggal_mulai', 'asc'],
                            ['tanggal_st', 'desc'],
                        ])
                        ->values(),
                ];
            })
            ->sortBy('pegawai_nama')
            ->values();

        $calendarEvents = $assignments
            ->groupBy(fn ($item) => implode('|', [
                $item['pegawai_id'],
                $item['project_id'],
                $item['tanggal_mulai'] ?: '',
                $item['tanggal_selesai'] ?: '',
                $item['nomor_st'],
            ]))
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'pegawai_id' => $first['pegawai_id'],
                    'pegawai_nama' => $first['pegawai_nama'],
                    'pegawai_nip' => $first['pegawai_nip'],
                    'pegawai_foto' => $first['pegawai_foto'],
                    'project_id' => $first['project_id'],
                    'project_label' => $first['project_label'],
                    'project_url' => $first['project_url'],
                    'nama_pabrik' => $first['nama_pabrik'],
                    'nomor_st' => $first['nomor_st'],
                    'tanggal_st_label' => $first['tanggal_st_label'],
                    'hal_st' => $first['hal_st'],
                    'tanggal_mulai' => $first['tanggal_mulai'],
                    'tanggal_selesai' => $first['tanggal_selesai'] ?: $first['tanggal_mulai'],
                    'rentang_label' => $first['rentang_label'],
                    'waktu_label' => $first['waktu_label'],
                    'st_pdf_url' => $first['st_pdf_url'],
                    'is_cukai' => $first['is_cukai'],
                ];
            })
            ->filter(fn ($event) => filled($event['tanggal_mulai']))
            ->values();

        $summary = [
            'pegawai' => $pegawaiGroups->count(),
            'penugasan' => $assignments->count(),
            'st_uploaded' => $nds->whereNotNull('st_pdf_uploaded_at')->count(),
            'bulan_ini' => $calendarEvents->filter(function ($event) {
                $mulai = $event['tanggal_mulai'] ? Carbon::parse($event['tanggal_mulai']) : null;
                $selesai = $event['tanggal_selesai'] ? Carbon::parse($event['tanggal_selesai']) : $mulai;

                if (! $mulai || ! $selesai) {
                    return false;
                }

                return $mulai->startOfDay()->lte(now()->endOfMonth()) && $selesai->endOfDay()->gte(now()->startOfMonth());
            })->count(),
        ];

        return view('projects.monitoring', [
            'summary' => $summary,
            'assignments' => $assignments
                ->sortBy([
                    ['pegawai_nama', 'asc'],
                    ['tanggal_mulai', 'asc'],
                    ['tanggal_st', 'desc'],
                ])
                ->values(),
            'pegawaiGroups' => $pegawaiGroups,
            'calendarEvents' => $calendarEvents,
        ]);
    }

    public function create()
    {
        return view('projects.create', [
            'jenisPermohonanOptions' => self::JENIS_PERMOHONAN,
            'pengirimAktifOptions' => $this->pengirimOptions(true),
            'pengirimSemuaOptions' => $this->pengirimOptions(false),
        ]);
    }

    public function extractSuratPermohonan(Request $request, SuratPermohonanOcrService $ocrService)
    {
        $data = $request->validate([
            'surat_permohonan_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $ocr = $ocrService->extract($data['surat_permohonan_pdf']->getRealPath());

        return response()->json([
            'fields' => $ocr['fields'] ?? [],
            'engine' => $ocr['engine'],
            'has_text' => filled($ocr['text'] ?? null),
            'text_excerpt' => str($ocr['text'] ?? '')->squish()->limit(500)->toString(),
        ]);
    }

    public function showSuratPermohonanPdf(Project $project)
    {
        $this->authorizeOwner($project);

        if (! $project->surat_permohonan_pdf_path || ! Storage::exists($project->surat_permohonan_pdf_path)) {
            abort(404);
        }

        return response()->file(Storage::path($project->surat_permohonan_pdf_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function store(Request $request, SuratPermohonanOcrService $ocrService)
    {
        $data = $request->validate([
            'jenis_permohonan' => 'required|string|max:255',
            'no_surat_permohonan' => 'nullable|string|max:255',
            'tgl_surat_permohonan' => 'nullable|date',
            'hal_surat_permohonan' => 'nullable|string|max:255',
            'pengirim' => 'nullable|string',
            'tanpa_pabrik_tujuan' => 'nullable|boolean',
            'label_project_tanpa_pabrik' => 'nullable|string|max:255|required_if:tanpa_pabrik_tujuan,1',
            'surat_permohonan_pdf' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $data = $this->handleSuratPermohonanPdf($request, $data, $ocrService);
        $data = $this->normalizeSuratPermohonanData($data);
        $data['pengirim'] = $this->resolvePengirimValue($data);
        $data['user_id'] = Auth::id();
        $data['nama_project'] = $this->projectName($data);
        $data['perusahaan'] = $data['pengirim'];
        $data['tanggal'] = $data['tgl_surat_permohonan'] ?? now()->toDateString();
        $data['keterangan'] = $data['hal_surat_permohonan'] ?? null;

        unset($data['surat_permohonan_pdf'], $data['tanpa_pabrik_tujuan'], $data['label_project_tanpa_pabrik']);

        Project::create($data);

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil ditambahkan');
    }

    public function show(Project $project)
    {
        $project->load(['ndPermohonanSt.pegawais', 'ndPermohonanSt.tujuans']);

        $pegawaiOptions = Pegawai::orderByRaw('CAST(NULLIF(nomor, "") AS INTEGER) asc')
            ->orderBy('nm_pegawai')
            ->get();

        $kepalaSeksiOptions = Pegawai::whereNotNull('jabatan_duk')
            ->whereRaw('CAST(NULLIF(nomor, "") AS INTEGER) BETWEEN 2 AND 17')
            ->orderByRaw('CAST(NULLIF(nomor, "") AS INTEGER) asc')
            ->get(['nm_pegawai', 'jabatan_duk', 'jabatan2', 'nomor'])
            ->map(fn (Pegawai $pegawai) => [
                'nomor' => $pegawai->nomor,
                'name' => $pegawai->nm_pegawai,
                'label' => $pegawai->nm_pegawai.' - '.trim($pegawai->jabatan2 ?: $pegawai->jabatan_duk),
                'value' => $pegawai->jabatan_duk,
            ])
            ->values();

        $tujuanOptions = TujuanSt::orderBy('nama_tujuan')->get();

        return view('projects.show', compact(
            'project',
            'pegawaiOptions',
            'kepalaSeksiOptions',
            'tujuanOptions'
        ));
    }

    public function edit(Project $project)
    {
        $this->authorizeOwner($project);

        return view('projects.edit', [
            'project' => $project,
            'jenisPermohonanOptions' => self::JENIS_PERMOHONAN,
            'pengirimAktifOptions' => $this->pengirimOptions(true),
            'pengirimSemuaOptions' => $this->pengirimOptions(false),
        ]);
    }

    public function update(Request $request, Project $project, SuratPermohonanOcrService $ocrService)
    {
        $this->authorizeOwner($project);

        $data = $request->validate([
            'jenis_permohonan' => 'required|string|max:255',
            'no_surat_permohonan' => 'nullable|string|max:255',
            'tgl_surat_permohonan' => 'nullable|date',
            'hal_surat_permohonan' => 'nullable|string|max:255',
            'pengirim' => 'nullable|string',
            'tanpa_pabrik_tujuan' => 'nullable|boolean',
            'label_project_tanpa_pabrik' => 'nullable|string|max:255|required_if:tanpa_pabrik_tujuan,1',
            'surat_permohonan_pdf' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $data = $this->handleSuratPermohonanPdf($request, $data, $ocrService);
        $data = $this->normalizeSuratPermohonanData($data);
        $data['pengirim'] = $this->resolvePengirimValue($data);
        $data['nama_project'] = $this->projectName($data);
        $data['perusahaan'] = $data['pengirim'];
        $data['tanggal'] = $data['tgl_surat_permohonan'] ?? $project->tanggal ?? now()->toDateString();
        $data['keterangan'] = $data['hal_surat_permohonan'] ?? null;

        if ((bool) ($data['tanpa_pabrik_tujuan'] ?? false)) {
            $this->deleteSuratPermohonanPdf($project);
        }

        unset($data['surat_permohonan_pdf'], $data['tanpa_pabrik_tujuan'], $data['label_project_tanpa_pabrik']);

        $project->update($data);

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil diupdate');
    }

    public function destroy(Project $project)
    {
        $this->authorizeOwner($project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil dihapus');
    }

    // 🔥 helper function (biar gak nulis berulang)
    private function authorizeOwner(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function pengirimOptions(bool $aktifOnly)
    {
        $query = Perusahaan::whereNotNull('nppbkc_perusahaan')
            ->where('nppbkc_perusahaan', '!=', '')
            ->orderBy('nama_pabrik_sac');

        if ($aktifOnly) {
            $query->whereRaw("LOWER(TRIM(status)) = 'aktif'");
        }

        return $query
            ->get(['nppbkc_perusahaan', 'nama_pabrik_sac'])
            ->map(fn (Perusahaan $perusahaan) => [
                'label' => $this->cleanPengirim($perusahaan->nppbkc_perusahaan),
                'sort' => strtoupper(trim($perusahaan->nama_pabrik_sac ?? '')),
            ])
            ->sortBy('sort')
            ->pluck('label')
            ->map(fn ($value) => $this->cleanPengirim($value))
            ->filter()
            ->unique()
            ->values();
    }

    private function projectName(array $data): string
    {
        $jenisPermohonan = trim((string) ($data['jenis_permohonan'] ?? ''));
        $pengirim = $this->cleanPengirim($data['pengirim'] ?? '');
        $labelProject = trim((string) ($data['label_project_tanpa_pabrik'] ?? ''));

        if ($pengirim === '') {
            return trim($jenisPermohonan.' - '.($labelProject !== '' ? $labelProject : 'Tanpa Pabrik Tujuan'));
        }

        return trim($jenisPermohonan.' - '.$pengirim);
    }

    private function cleanPengirim(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }

    private function resolvePengirimValue(array $data): string
    {
        if ((bool) ($data['tanpa_pabrik_tujuan'] ?? false)) {
            return '';
        }

        return $this->cleanPengirim($data['pengirim'] ?? '');
    }

    private function normalizeSuratPermohonanData(array $data): array
    {
        if (! (bool) ($data['tanpa_pabrik_tujuan'] ?? false)) {
            return $data;
        }

        if (! empty($data['surat_permohonan_pdf_path']) && Storage::exists($data['surat_permohonan_pdf_path'])) {
            Storage::delete($data['surat_permohonan_pdf_path']);
        }

        $data['no_surat_permohonan'] = null;
        $data['tgl_surat_permohonan'] = null;
        $data['hal_surat_permohonan'] = null;
        $data['surat_permohonan_pdf_path'] = null;
        $data['surat_permohonan_ocr_text'] = null;
        $data['surat_permohonan_ocr_engine'] = null;
        $data['surat_permohonan_ocr_at'] = null;

        return $data;
    }

    private function deleteSuratPermohonanPdf(Project $project): void
    {
        if ($project->surat_permohonan_pdf_path && Storage::exists($project->surat_permohonan_pdf_path)) {
            Storage::delete($project->surat_permohonan_pdf_path);
        }
    }

    private function handleSuratPermohonanPdf(Request $request, array $data, SuratPermohonanOcrService $ocrService): array
    {
        if (! $request->hasFile('surat_permohonan_pdf')) {
            return $data;
        }

        $path = $request->file('surat_permohonan_pdf')->store('surat-permohonan');
        $absolutePath = Storage::path($path);
        $ocr = $ocrService->extract($absolutePath);
        $fields = $ocr['fields'] ?? [];

        $data['surat_permohonan_pdf_path'] = $path;
        $data['surat_permohonan_ocr_text'] = $ocr['text'] ?: null;
        $data['surat_permohonan_ocr_engine'] = $ocr['engine'];
        $data['surat_permohonan_ocr_at'] = now();

        foreach (['no_surat_permohonan', 'tgl_surat_permohonan', 'hal_surat_permohonan'] as $field) {
            if (blank($data[$field] ?? null) && filled($fields[$field] ?? null)) {
                $data[$field] = $fields[$field];
            }
        }

        return $data;
    }

    private function isExcludedMonitoringPegawai(Pegawai $pegawai): bool
    {
        $jabatan = mb_strtolower(trim((string) ($pegawai->jabatan ?? '')), 'UTF-8');
        $jabatanDuk = mb_strtolower(trim((string) ($pegawai->jabatan_duk ?? '')), 'UTF-8');

        return str_contains($jabatan, 'kepala kantor') || str_contains($jabatanDuk, 'kepala kantor');
    }

    private function isMonitoringCukaiPegawai(Pegawai $pegawai): bool
    {
        $name = mb_strtolower(trim((string) $pegawai->nm_pegawai), 'UTF-8');

        foreach (self::MONITORING_CUKAI_NAMES as $candidate) {
            if (str_contains($name, $candidate)) {
                return true;
            }
        }

        return false;
    }

    private function buildMonitoringHalSt(ProjectNdPermohonanSt $nd, ?Project $project): string
    {
        $parts = collect([
            trim((string) ($nd->tanggal_pelaksanaan_text ?? '')),
            trim((string) ($nd->kegiatan ?? '')),
        ])->filter(fn ($value) => $value !== '');

        if ($parts->isNotEmpty()) {
            return $parts->implode(' - ');
        }

        return $nd->hal_nd ?: ($project?->hal_surat_permohonan ?: '-');
    }

    private function formatDateIndonesia($date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [
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

        return $date->format('j').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function formatMonitoringRange(ProjectNdPermohonanSt $nd): string
    {
        if (! $nd->tanggal_mulai && ! $nd->tanggal_selesai) {
            return '-';
        }

        if ($nd->tanggal_mulai && $nd->tanggal_selesai && $nd->tanggal_mulai->isSameDay($nd->tanggal_selesai)) {
            return $this->formatDateIndonesia($nd->tanggal_mulai);
        }

        if ($nd->tanggal_mulai && $nd->tanggal_selesai) {
            return $this->formatDateIndonesia($nd->tanggal_mulai).' s.d. '.$this->formatDateIndonesia($nd->tanggal_selesai);
        }

        return $this->formatDateIndonesia($nd->tanggal_mulai ?: $nd->tanggal_selesai);
    }
}
