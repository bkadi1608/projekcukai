<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Project;
use App\Models\TujuanSt;
use App\Services\NomorStPdfExtractService;
use App\Services\NdPermohonanStWordService;
use App\Services\NdPermohonanStPdfExtractService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectNdPermohonanStController extends Controller
{
    public function downloadWord(Project $project, NdPermohonanStWordService $wordService)
    {
        $this->authorizeOwner($project);

        $nd = $project->ndPermohonanSt()
            ->with(['project', 'pegawais', 'tujuans'])
            ->first();

        if (! $nd) {
            return redirect()
                ->route('projects.show', $project)
                ->with('error', 'Draft ND Permohonan ST belum dibuat.');
        }

        $path = $wordService->make($nd);

        $nd->forceFill([
            'word_generated_at' => now(),
        ])->save();

        return response()
            ->download($path, $this->wordFileName($project), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])
            ->deleteFileAfterSend(true);
    }

    public function store(Request $request, Project $project, NdPermohonanStPdfExtractService $extractService)
    {
        $this->authorizeOwner($project);

        $data = $request->validate([
            'nomor_nd' => 'nullable|string|max:255',
            'tanggal_nd' => 'nullable|date',
            'yth' => 'required|string|max:255',
            'dari' => 'nullable|string|max:255',
            'sifat' => 'required|string|max:255',
            'hal_nd' => 'nullable|string',
            'kegiatan' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'waktu_mulai' => 'nullable|date_format:H:i',
            'waktu_selesai' => 'nullable|date_format:H:i',
            'tempat' => 'nullable|string',
            'alamat' => 'nullable|string',
            'penandatangan' => 'nullable|string|max:255',
            'tembusan' => 'nullable|string',
            'nd_pdf' => 'nullable|file|mimes:pdf|max:20480',
            'pegawai_ids' => 'nullable|array',
            'pegawai_ids.*' => 'integer|exists:pegawais,id',
            'tujuan_ids' => 'nullable|array',
            'tujuan_ids.*' => 'integer|exists:tujuan_sts,id',
        ]);

        $pegawaiIds = collect($data['pegawai_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();
        $tujuanIds = collect($data['tujuan_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();

        unset($data['pegawai_ids'], $data['tujuan_ids']);

        $tujuans = TujuanSt::whereIn('id', $tujuanIds)->get()->keyBy('id');
        $orderedTujuans = $tujuanIds
            ->map(fn ($id) => $tujuans->get((int) $id))
            ->filter()
            ->values();

        if ($orderedTujuans->isNotEmpty()) {
            $data['tempat'] = $this->formatTujuanPelaksanaan($orderedTujuans);
            $data['alamat'] = null;
        } else {
            $data['tempat'] = null;
            $data['alamat'] = null;
        }

        $data['penandatangan'] = $this->namaPenandatangan($data['dari'] ?? null);
        $data['tanggal_pelaksanaan_text'] = $this->formatTanggalPelaksanaan(
            $data['tanggal_mulai'] ?? null,
            $data['tanggal_selesai'] ?? null
        );
        $data['tanggal_pelaksanaan'] = $data['tanggal_mulai'] ?? null;
        $data['waktu_pelaksanaan_text'] = $this->formatWaktuPelaksanaan(
            $data['waktu_mulai'] ?? null,
            $data['waktu_selesai'] ?? null
        );

        unset($data['nd_pdf']);

        DB::transaction(function () use ($project, $data, $pegawaiIds, $tujuanIds, $request, $extractService) {
            $nd = $project->ndPermohonanSt()->updateOrCreate(
                ['project_id' => $project->id],
                $data
            );

            if ($request->hasFile('nd_pdf')) {
                $path = $request->file('nd_pdf')->store('nd-permohonan-st');
                $ocr = $extractService->extract(Storage::path($path));
                $fields = $ocr['fields'] ?? [];

                if (blank($nd->nomor_nd) && filled($fields['nomor_nd'] ?? null)) {
                    $nd->nomor_nd = $fields['nomor_nd'];
                }

                if (blank($nd->tanggal_nd) && filled($fields['tanggal_nd'] ?? null)) {
                    $nd->tanggal_nd = $fields['tanggal_nd'];
                }

                $nd->forceFill([
                    'nd_pdf_path' => $path,
                    'nd_pdf_ocr_text' => $ocr['text'] ?: null,
                    'nd_pdf_ocr_engine' => $ocr['engine'],
                    'nd_pdf_ocr_at' => now(),
                    'nd_pdf_uploaded_at' => now(),
                ])->save();
            }

            $syncData = $pegawaiIds
                ->mapWithKeys(fn ($id, $index) => [
                    $id => ['urutan' => $index + 1],
                ])
                ->all();

            $nd->pegawais()->sync($syncData);

            $tujuanSyncData = $tujuanIds
                ->mapWithKeys(fn ($id, $index) => [
                    $id => ['urutan' => $index + 1],
                ])
                ->all();

            $nd->tujuans()->sync($tujuanSyncData);
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Draft ND Permohonan ST berhasil disimpan');
    }

    public function extractNdPdf(Request $request, Project $project, NdPermohonanStPdfExtractService $extractService)
    {
        $this->authorizeOwner($project);

        $data = $request->validate([
            'nd_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $result = $extractService->extract($data['nd_pdf']->getRealPath());

        return response()->json([
            'fields' => $result['fields'] ?? [],
            'engine' => $result['engine'],
            'has_text' => filled($result['text'] ?? null),
            'text_excerpt' => str($result['text'] ?? '')->squish()->limit(500)->toString(),
        ]);
    }

    public function storeSt(Request $request, Project $project, NomorStPdfExtractService $extractService)
    {
        $this->authorizeOwner($project);

        $nd = $project->ndPermohonanSt()->with('pegawais')->first();

        if (! $nd) {
            return redirect()
                ->route('projects.show', $project)
                ->with('error', 'Buat draft ND Permohonan ST terlebih dahulu.');
        }

        $data = $request->validate([
            'nomor_st' => 'nullable|string|max:255',
            'tanggal_st' => 'nullable|date',
            'st_pdf' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $updatedPegawai = false;

        if ($request->hasFile('st_pdf')) {
            $path = $request->file('st_pdf')->store('nomor-st');
            $ocr = $extractService->extract(Storage::path($path));
            $fields = $ocr['fields'] ?? [];

            if (blank($data['nomor_st'] ?? null) && filled($fields['nomor_st'] ?? null)) {
                $data['nomor_st'] = $fields['nomor_st'];
            }

            if (blank($data['tanggal_st'] ?? null) && filled($fields['tanggal_st'] ?? null)) {
                $data['tanggal_st'] = $fields['tanggal_st'];
            }

            $nd->forceFill([
                'st_pdf_path' => $path,
                'st_pdf_ocr_text' => $ocr['text'] ?: null,
                'st_pdf_ocr_engine' => $ocr['engine'],
                'st_pdf_ocr_at' => now(),
                'st_pdf_uploaded_at' => now(),
            ]);

            $pegawaiIds = collect($ocr['pegawai_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($pegawaiIds->isNotEmpty()) {
                $currentIds = $nd->pegawais->pluck('id')->map(fn ($id) => (int) $id)->values();

                if ($currentIds->all() !== $pegawaiIds->all()) {
                    $syncData = $pegawaiIds
                        ->mapWithKeys(fn ($id, $index) => [
                            $id => ['urutan' => $index + 1],
                        ])
                        ->all();

                    $nd->pegawais()->sync($syncData);
                    $updatedPegawai = true;
                }
            }
        }

        $nd->fill([
            'nomor_st' => $data['nomor_st'] ?? $nd->nomor_st,
            'tanggal_st' => $data['tanggal_st'] ?? $nd->tanggal_st,
        ])->save();

        return redirect()
            ->route('projects.show', $project)
            ->with('success', $updatedPegawai
                ? 'Nomor ST disimpan dan daftar pegawai diperbarui mengikuti ST.'
                : 'Nomor ST berhasil disimpan');
    }

    public function extractStPdf(Request $request, Project $project, NomorStPdfExtractService $extractService)
    {
        $this->authorizeOwner($project);

        $data = $request->validate([
            'st_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        $result = $extractService->extract($data['st_pdf']->getRealPath());

        return response()->json([
            'fields' => $result['fields'] ?? [],
            'pegawai_ids' => $result['pegawai_ids'] ?? [],
            'engine' => $result['engine'],
            'has_text' => filled($result['text'] ?? null),
            'text_excerpt' => str($result['text'] ?? '')->squish()->limit(500)->toString(),
        ]);
    }

    public function showNdPdf(Project $project)
    {
        $this->authorizeOwner($project);

        $nd = $project->ndPermohonanSt;

        if (! $nd?->nd_pdf_path || ! Storage::exists($nd->nd_pdf_path)) {
            abort(404);
        }

        return response()->file(Storage::path($nd->nd_pdf_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function showStPdf(Project $project)
    {
        $this->authorizeOwner($project);

        $nd = $project->ndPermohonanSt;

        if (! $nd?->st_pdf_path || ! Storage::exists($nd->st_pdf_path)) {
            abort(404);
        }

        return response()->file(Storage::path($nd->st_pdf_path), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function authorizeOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function namaPenandatangan(?string $dari): ?string
    {
        if (! $dari) {
            return null;
        }

        return Pegawai::where('jabatan_duk', $dari)
            ->whereRaw('CAST(NULLIF(nomor, "") AS INTEGER) BETWEEN 2 AND 17')
            ->orderByRaw('CAST(NULLIF(nomor, "") AS INTEGER) asc')
            ->value('nm_pegawai') ?: $dari;
    }

    private function wordFileName(Project $project): string
    {
        $name = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $project->jenis_permohonan ?: 'ND-Permohonan-ST');
        $name = trim($name, '-') ?: 'ND-Permohonan-ST';

        return $name.'-'.$project->id.'.docx';
    }

    private function formatTujuanPelaksanaan($tujuans): string
    {
        if ($tujuans->count() === 1) {
            $tujuan = $tujuans->first();

            return trim($tujuan->nama_tujuan."\n".$tujuan->alamat_tujuan);
        }

        return $tujuans
            ->values()
            ->map(fn ($tujuan, $index) => trim(($index + 1).'. '.$tujuan->nama_tujuan."\n".$tujuan->alamat_tujuan))
            ->implode("\n");
    }

    private function formatTanggalPelaksanaan(?string $tanggalMulai, ?string $tanggalSelesai): ?string
    {
        if (! $tanggalMulai || ! $tanggalSelesai) {
            return null;
        }

        $mulai = Carbon::parse($tanggalMulai);
        $selesai = Carbon::parse($tanggalSelesai);

        if ($selesai->lt($mulai)) {
            return 'Tgl Selesai lebih kecil dari Tgl Mulai';
        }

        if ($mulai->isSameDay($selesai)) {
            return $this->formatDate($mulai);
        }

        if ($mulai->isSameMonth($selesai) && $mulai->isSameYear($selesai)) {
            return $this->dayName($mulai).' s.d. '.$this->dayName($selesai)
                .' / '.$mulai->format('d').' s.d. '.$this->formatDate($selesai);
        }

        if ($mulai->isSameYear($selesai)) {
            return $this->dayName($mulai).' s.d. '.$this->dayName($selesai)
                .' / '.$this->formatDateWithoutYear($mulai).' s.d. '.$this->formatDate($selesai);
        }

        return 'Berangkat';
    }

    private function formatWaktuPelaksanaan(?string $waktuMulai, ?string $waktuSelesai): ?string
    {
        if (! $waktuMulai) {
            return null;
        }

        $mulai = $this->formatTime($waktuMulai);

        if (! $waktuSelesai) {
            return $mulai.' WIB s.d. Selesai';
        }

        return $mulai.' WIB s.d. '.$this->formatTime($waktuSelesai).' WIB';
    }

    private function formatTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('H:i');
    }

    private function formatDate(Carbon $date): string
    {
        return $date->format('d').' '.$this->monthName($date).' '.$date->format('Y');
    }

    private function formatDateWithoutYear(Carbon $date): string
    {
        return $date->format('d').' '.$this->monthName($date);
    }

    private function dayName(Carbon $date): string
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][$date->isoWeekday()];
    }

    private function monthName(Carbon $date): string
    {
        return [
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
        ][(int) $date->format('n')];
    }
}
