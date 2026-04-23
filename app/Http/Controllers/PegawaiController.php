<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function sync()
    {
        $url = 'https://opensheet.elk.sh/1jxhC4FPzClKDT7rOpiOen3U5cZ3fLVXXi9D-9VVD7JU/Data%20Pegawai';

        $json = file_get_contents($url);
        $rows = json_decode($json, true);

        if (! is_array($rows)) {
            return redirect()->route('pegawai.index')
                ->with('error', 'Sync gagal: data spreadsheet tidak dapat dibaca.');
        }

        DB::transaction(function () use ($rows) {
            Pegawai::query()->delete();

            foreach ($rows as $row) {
                $nama = trim($row['NmPegawai'] ?? $row['Nama Pegawai'] ?? '');

                if ($nama === '') {
                    continue;
                }

                Pegawai::create([
                    'ulang_tahun' => $this->parseSpreadsheetDate($row['Ulang Tahun'] ?? null),
                    'nm_pegawai' => $nama,
                    'nickname' => trim($row['Nickname'] ?? ''),
                    'jabatan' => trim($row['Jabatan'] ?? ''),
                    'pangkat_golongan' => trim($row['Pangkat - Golongan'] ?? ''),
                    'url_foto' => trim($row['UrlFoto'] ?? ''),
                    'nip' => trim($row['NIP'] ?? ''),
                    'email_kemenkeu' => trim($row['Email Kemenkeu'] ?? ''),
                    'grade' => trim($row['Grade'] ?? ''),
                    'jenis_kelamin' => trim($row['Jenis Kelamin'] ?? ''),
                    'tanggal_lahir' => $this->parseSpreadsheetDate($row['Tanggal Lahir'] ?? null),
                    'tgl_ulang_tahun' => $this->parseSpreadsheetDate($row['Tgl Ulang Tahun'] ?? null),
                    'umur' => trim($row['Umur'] ?? ''),
                    'bulan_if' => trim($row['Bulan if'] ?? ''),
                    'bulan' => trim($row['Bulan'] ?? ''),
                    'urutan' => trim($row['Urutan'] ?? ''),
                    'jabatan2' => trim($row['Jabatan2'] ?? ''),
                    'nama_pegawai' => trim($row['Nama Pegawai'] ?? ''),
                    'atasan' => trim($row['Atasan'] ?? ''),
                    'seksi' => trim($row['Seksi'] ?? ''),
                    'nomor' => trim($row['Nomor'] ?? ''),
                    'jabatan_duk' => trim($row['Jabatan DUK'] ?? ''),
                ]);
            }
        });

        return redirect()->route('pegawai.index')
            ->with('success', 'Sync pegawai berhasil');
    }

    private function parseSpreadsheetDate(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $days = [
            'senin' => '',
            'selasa' => '',
            'rabu' => '',
            'kamis' => '',
            'jumat' => '',
            'sabtu' => '',
            'minggu' => '',
            'sen' => '',
            'sel' => '',
            'rab' => '',
            'kam' => '',
            'jum' => '',
            'sab' => '',
            'min' => '',
        ];

        $months = [
            'januari' => 'January',
            'jan' => 'January',
            'februari' => 'February',
            'feb' => 'February',
            'maret' => 'March',
            'mar' => 'March',
            'april' => 'April',
            'apr' => 'April',
            'mei' => 'May',
            'juni' => 'June',
            'jun' => 'June',
            'juli' => 'July',
            'jul' => 'July',
            'agustus' => 'August',
            'agu' => 'August',
            'september' => 'September',
            'sep' => 'September',
            'oktober' => 'October',
            'okt' => 'October',
            'november' => 'November',
            'nov' => 'November',
            'desember' => 'December',
            'des' => 'December',
        ];

        $normalized = preg_replace(
            '/\b('.implode('|', array_map('preg_quote', array_keys($days))).')\b/i',
            '',
            $value
        );
        $normalized = preg_replace_callback(
            '/\b('.implode('|', array_map('preg_quote', array_keys($months))).')\b/i',
            fn (array $matches) => $months[strtolower($matches[1])] ?? $matches[1],
            $normalized
        );
        $normalized = trim(preg_replace('/\s+/', ' ', str_replace(',', ' ', $normalized)));

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
        $data = Pegawai::orderByRaw('CAST(NULLIF(nomor, "") AS INTEGER) asc')
            ->orderBy('nm_pegawai')
            ->get();

        return view('pegawai.index', compact('data'));
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
    public function show(Pegawai $pegawai)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pegawai $pegawai)
    {
        //
    }
}
