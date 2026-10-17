<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\PergerakanBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $barangs = Barang::with(['kategori', 'supplier'])
            ->when($search, function ($query, $search) {
                return $query->where('nama_barang', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('barang.index', compact('barangs', 'search'));
    }

    public function create()
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        return view('barang.create', compact('kategoris', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'harga_masuk' => 'required|integer|min:1|max:99999999',
            'tanggal_masuk' => 'required|date',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        DB::transaction(function () use ($validated) {
            $barang = Barang::create([
                'nama_barang' => $validated['nama_barang'],
                'jumlah' => $validated['jumlah'],
                'tanggal_masuk' => $validated['tanggal_masuk'],
                'kategori_id' => $validated['kategori_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
            ]);

            PergerakanBarang::create([
                'barang_id' => $barang->id,
                'jumlah' => $validated['jumlah'],
                'harga_satuan' => $validated['harga_masuk'],
                'tipe' => 'masuk',
                'tanggal' => $validated['tanggal_masuk'],
                'keterangan' => 'Stok awal',
            ]);
        });

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Barang $barang)
    {
        $riwayat = PergerakanBarang::where('barang_id', $barang->id)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return view('barang.show', compact('barang', 'riwayat'));
    }

    public function edit(Barang $barang)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        return view('barang.edit', compact('barang', 'kategoris', 'suppliers'));
    }

    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah' => 'required|integer',
            'tanggal_masuk' => 'required|date',
            'kategori_id' => 'nullable|exists:kategoris,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Data barang berhasil diupdate.');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();
        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
