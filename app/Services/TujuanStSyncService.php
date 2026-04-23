<?php

namespace App\Services;

use App\Models\TujuanSt;
use Illuminate\Support\Facades\DB;

class TujuanStSyncService
{
    private const URL = 'https://opensheet.elk.sh/1E6q4O7rjBNBe4TdOG9tirB-MBYqGpdIyQSydgXxY2IM/REF';

    public function sync(): int
    {
        $json = file_get_contents(self::URL);
        $rows = json_decode($json, true);

        if (! is_array($rows)) {
            throw new \RuntimeException('Data spreadsheet Tujuan ST tidak dapat dibaca.');
        }

        $count = 0;

        DB::transaction(function () use ($rows, &$count) {
            TujuanSt::query()->delete();

            collect($rows)
                ->map(fn (array $row) => [
                    'nama_tujuan' => trim($row['Nama Tujuan'] ?? ''),
                    'alamat_tujuan' => trim($row['Alamat Tujuan'] ?? ''),
                ])
                ->filter(fn (array $row) => $row['nama_tujuan'] !== '')
                ->unique(fn (array $row) => $row['nama_tujuan'].'|'.$row['alamat_tujuan'])
                ->values()
                ->each(function (array $row) use (&$count) {
                    TujuanSt::create($row);
                    $count++;
                });
        });

        return $count;
    }
}
