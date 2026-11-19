<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaketController extends Controller
{
    public function index(): View
    {
        $pakets = Paket::withCount('pelanggan')
            ->orderBy('tarif_standar')
            ->get();

        return view('admin.paket.index', compact('pakets'));
    }

    public function create(): View
    {
        return view('admin.paket.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_paket' => ['required', 'string', 'max:100'],
            'kecepatan_mbps' => ['required', 'string', 'max:50'],
            'tarif_standar' => ['required', 'integer', 'min:1000'],
        ], [
            'nama_paket.required' => 'Nama paket wajib diisi.',
            'kecepatan_mbps.required' => 'Kecepatan wajib diisi.',
            'tarif_standar.required' => 'Tarif standar wajib diisi.',
            'tarif_standar.integer' => 'Tarif standar harus berupa angka bulat.',
        ]);

        Paket::create($validated);

        return redirect()->route('admin.paket.index')
            ->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit(Paket $paket): View
    {
        return view('admin.paket.edit', compact('paket'));
    }

    public function update(Request $request, Paket $paket): RedirectResponse
    {
        $validated = $request->validate([
            'nama_paket' => ['required', 'string', 'max:100'],
            'kecepatan_mbps' => ['required', 'string', 'max:50'],
            'tarif_standar' => ['required', 'integer', 'min:1000'],
        ]);

        $paket->update($validated);

        return redirect()->route('admin.paket.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Paket $paket): RedirectResponse
    {
        if ($paket->pelanggan()->exists()) {
            return redirect()->route('admin.paket.index')
                ->with('error', 'Paket tidak dapat dihapus karena masih digunakan oleh pelanggan.');
        }

        $paket->delete();

        return redirect()->route('admin.paket.index')
            ->with('success', 'Paket berhasil dihapus.');
    }
}