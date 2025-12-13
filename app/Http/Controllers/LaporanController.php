<?php

namespace App\Http\Controllers;

use App\Models\PergerakanBarang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Import facade PDF

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $pergerakan = PergerakanBarang::with('barang')
            ->when($tanggalAwal, function ($query) use ($tanggalAwal) {
                return $query->where('tanggal', '>=', $tanggalAwal);
            })
            ->when($tanggalAkhir, function ($query) use ($tanggalAkhir) {
                return $query->where('tanggal', '<=', $tanggalAkhir);
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalMasuk = $pergerakan->where('tipe', 'masuk')->sum('jumlah');
        $totalKeluar = $pergerakan->where('tipe', 'keluar')->sum('jumlah');
        $perubahanBersih = $totalMasuk - $totalKeluar;

        return view('laporan.index', compact(
            'pergerakan',
            'tanggalAwal',
            'tanggalAkhir',
            'totalMasuk',
            'totalKeluar',
            'perubahanBersih'
        ));
    }

    public function exportPDF(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $pergerakan = PergerakanBarang::with('barang')
            ->when($tanggalAwal, function ($query) use ($tanggalAwal) {
                return $query->where('tanggal', '>=', $tanggalAwal);
            })
            ->when($tanggalAkhir, function ($query) use ($tanggalAkhir) {
                return $query->where('tanggal', '<=', $tanggalAkhir);
            })
            ->orderBy('tanggal', 'desc')
            ->get();

        $pdf = PDF::loadView('laporan.pdf', compact('pergerakan', 'tanggalAwal', 'tanggalAkhir'));

        return $pdf->download('laporan-pergerakan-barang-' . date('Y-m-d') . '.pdf');
    }
}
