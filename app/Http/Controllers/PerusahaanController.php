<?php

namespace App\Http\Controllers;

use App\Models\Perusahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerusahaanController extends Controller
{
    public function sync()
    {
        $url = "https://opensheet.elk.sh/1I8b3zXJSEltPqY2CGRnkG8IKyh6l0g_R9WPMRotMx8U/Database NPPBKC Pasuruan";

        $json = file_get_contents($url);
        $rows = json_decode($json, true);

        DB::transaction(function () use ($rows) {
            Perusahaan::query()->delete();

            foreach ($rows as $row) {
                if (empty(trim($row['Nama_Pabrik_SAC'] ?? ''))) {
                    continue;
                }

                $rawStatus = strtolower(trim($row['STATUS'] ?? ''));

                $statusMap = [
                    'aktif' => 'Aktif',
                    'cabut' => 'Cabut',
                    'beku' => 'Beku',
                    'ditolak' => 'Ditolak',
                    'belum aktif' => 'Belum Aktif',
                    'proses pengajuan nppbkc' => 'Proses Pengajuan NPPBKC',
                ];

                $status = $statusMap[$rawStatus] ?? ucfirst($rawStatus);

                Perusahaan::create([
                    'no' => trim($row['No'] ?? ''),
                    'nama_pabrik_sac' => trim($row['Nama_Pabrik_SAC'] ?? ''),
                    'nama_perusahaan' => trim($row['NAMA PERUSAHAAN'] ?? ''),
                    'jenis_perusahaan' => trim($row['JENIS PERUSAHAAN'] ?? ''),
                    'jenis_usaha' => trim($row['Jenis Usaha'] ?? ''),
                    'jenis_bkc' => trim($row['Jenis BKC'] ?? ''),

                    // Pakai kolom formatted dari spreadsheet.
                    'npwp' => trim($row['NPWP (16 Digit) (Formated)'] ?? ''),
                    'nib' => trim($row['NIB (13 Digit)'] ?? ''),
                    'nppbkc_10' => trim($row['NPPBKC 10 DIGIT (Formated)'] ?? ''),
                    'nppbkc_22' => trim($row['NPPBKC 22 Digit (Formated)'] ?? ''),
                    'nilku' => trim($row['NILKU'] ?? ''),

                    'status' => $status,
                    'status_beku_cabut' => trim($row['STATUS BEKU/CABUT'] ?? ''),

                    'no_kep_nppbkc' => $row['NO KEP NPPBKC'] ?? null,
                    'tgl_kep_nppbkc' => $this->parseSpreadsheetDate($row['TGL KEP NPPBKC'] ?? null),
                    'pkp' => trim($row['PKP'] ?? ''),
                    'no_pkp' => trim($row['No PKP'] ?? ''),
                    'nomor_kep' => $row['NOMOR KEP'] ?? null,
                    'tgl_kep' => $this->parseSpreadsheetDate($row['TGL KEP'] ?? null),
                    'jenis_bkc_golongan' => trim($row['JENIS BKC - GOLONGAN'] ?? ''),
                    'nitku_utama' => trim($row['NITKU UTAMA'] ?? ''),
                    'alamat_pabrik_utama' => trim($row['ALAMAT PABRIK UTAMA FIX'] ?? '')
                        ?: trim($row['ALAMAT PABRIK UTAMA (PROPER)'] ?? '')
                        ?: trim($row['ALAMAT PABRIK UTAMA (ASAL)'] ?? ''),
                    'nitku_cabang_1' => trim($row['NITKU CABANG 1'] ?? ''),
                    'alamat_cabang_1' => trim($row['ALAMAT PABRIK CABANG 1'] ?? ''),
                    'nitku_cabang_2' => trim($row['NITKU CABANG 2'] ?? ''),
                    'alamat_cabang_2' => trim($row['ALAMAT PABRIK CABANG 2'] ?? ''),
                    'nitku_cabang_3' => trim($row['NITKU CABANG 3'] ?? ''),
                    'alamat_cabang_3' => trim($row['ALAMAT PABRIK CABANG 3'] ?? ''),
                    'lokasi_fix' => trim($row['LOKASI FIX'] ?? ''),
                    'nama_pemilik' => trim($row['NAMA PEMILIK'] ?? ''),
                    'npwp_pemilik' => trim($row['NPWP PEMILIK (Formated)'] ?? ''),
                    'nik_pemilik' => trim($row['NIK PEMILIK'] ?? ''),
                    'alamat_pemilik' => trim($row['ALAMAT PEMILIK'] ?? ''),
                    'lokasi_gmaps' => trim($row['LOKASI GMAPS'] ?? ''),
                    'longlat' => trim($row['LONGITUDE LATITUDE'] ?? ''),

                    'kecamatan' => trim($row['KECAMATAN'] ?? ''),
                    'profil' => trim($row['PROFIL'] ?? ''),
                    'nppbkc_perusahaan' => trim($row['NPPBKC-PERUSAHAAN'] ?? ''),
                ]);
            }
        });

        return redirect()->route('perusahaan.index')
            ->with('success', 'Sync berhasil');
    }

    private function parseSpreadsheetDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $months = [
            'januari' => 'January',
            'februari' => 'February',
            'maret' => 'March',
            'april' => 'April',
            'mei' => 'May',
            'juni' => 'June',
            'juli' => 'July',
            'agustus' => 'August',
            'september' => 'September',
            'oktober' => 'October',
            'november' => 'November',
            'desember' => 'December',
        ];

        $normalized = str_ireplace(array_keys($months), array_values($months), $value);

        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $normalized)) {
                return Carbon::createFromFormat('d/m/Y', $normalized)->format('Y-m-d');
            }

            return Carbon::parse($normalized)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Perusahaan::whereNotNull('status')
            ->orderBy('tgl_kep_nppbkc', 'desc')
            ->get();

        return view('perusahaan.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Perusahaan $perusahaan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Perusahaan $perusahaan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Perusahaan $perusahaan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Perusahaan $perusahaan)
    {
        //
    }
}
