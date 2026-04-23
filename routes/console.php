<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\TujuanStSyncService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tujuan-st:sync', function (TujuanStSyncService $syncService) {
    $count = $syncService->sync();

    $this->info("Sync Tujuan ST berhasil ({$count} data)");
})->purpose('Sync database Tujuan ST dari Google Sheets');

Schedule::command('tujuan-st:sync')->dailyAt('00:00')->timezone('Asia/Jakarta');
