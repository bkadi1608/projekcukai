<?php

namespace App\Http\Controllers;

use App\Models\TujuanSt;
use App\Services\TujuanStSyncService;

class TujuanStController extends Controller
{
    public function index()
    {
        $data = TujuanSt::orderBy('nama_tujuan')->get();

        return view('tujuan-st.index', compact('data'));
    }

    public function sync(TujuanStSyncService $syncService)
    {
        try {
            $count = $syncService->sync();
        } catch (\Throwable $e) {
            return redirect()->route('tujuan-st.index')
                ->with('error', 'Sync Tujuan ST gagal: '.$e->getMessage());
        }

        return redirect()->route('tujuan-st.index')
            ->with('success', "Sync Tujuan ST berhasil ({$count} data)");
    }
}
