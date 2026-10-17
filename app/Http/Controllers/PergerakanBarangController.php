<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PergerakanBarang;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PergerakanBarangController extends Controller
{
    public function createMasuk()
    {
        $barangs = Barang::orderBy('nama_barang')->get();
        return view('barang.create_masuk', compact('barangs'));
    }

    public function storeMasuk(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|integer|exists:barangs,id|distinct',
            'items.*.jumlah_masuk' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ], [
            'items.required' => 'Tambahkan setidaknya satu barang.',
            'items.min' => 'Tambahkan setidaknya satu barang.',
            'items.*.barang_id.required' => 'Pilih barang untuk baris ini.',
            'items.*.barang_id.exists' => 'Barang yang dipilih tidak ditemukan.',
            'items.*.barang_id.distinct' => 'Barang ini sudah dipilih pada baris lain.',
            'items.*.jumlah_masuk.required' => 'Masukkan jumlah barang untuk baris ini.',
            'items.*.jumlah_masuk.integer' => 'Jumlah barang harus berupa bilangan bulat.',
            'items.*.jumlah_masuk.min' => 'Jumlah barang minimal 1.',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $barang = Barang::findOrFail($item['barang_id']);
                $barang->jumlah += $item['jumlah_masuk'];
                $barang->save();

                PergerakanBarang::create([
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah_masuk'],
                    'tipe' => 'masuk',
                    'tanggal' => $validated['tanggal'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);
            }
        });

        return redirect()->route('dashboard')->with('success', 'Berhasil mencatat barang masuk.');
    }

    public function createKeluar()
    {
        $barangs = Barang::orderBy('nama_barang')->get();
        return view('barang.create_keluar', compact('barangs'));
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'jumlah_keluar' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $barang = Barang::find($request->barang_id);
                    if ($barang && $value > $barang->jumlah) {
                        $fail("Jumlah keluar melebihi stok yang ada ({$barang->jumlah}).");
                    }
                },
            ],
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        $barang->jumlah -= $request->jumlah_keluar;
        $barang->save();

        PergerakanBarang::create([
            'barang_id' => $request->barang_id,
            'jumlah' => $request->jumlah_keluar,
            'tipe' => 'keluar',
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('dashboard')->with('success', 'Berhasil mencatat barang keluar.');
    }
}
