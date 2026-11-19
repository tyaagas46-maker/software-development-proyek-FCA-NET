<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Support\PhoneHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PelangganController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pelanggan::with(['paket', 'user']);

        // Pencarian nama / no HP / alamat
        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        // Filter status (aktif / nonaktif)
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Filter siklus tagihan (5 / 21)
        if ($siklus = $request->query('siklus_tagihan')) {
            $query->where('siklus_tagihan', (int) $siklus);
        }

        // Filter paket
        if ($idPaket = $request->query('id_paket')) {
            $query->where('id_paket', (int) $idPaket);
        }

        $pelanggans = $query->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $pakets = Paket::orderBy('tarif_standar')->get();

        return view('admin.pelanggan.index', compact('pelanggans', 'pakets'));
    }

    public function create(): View
    {
        $pakets = Paket::orderBy('tarif_standar')->get();

        return view('admin.pelanggan.create', compact('pakets'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Normalisasi no_hp SEBELUM validasi unique
        $request->merge([
            'no_hp' => PhoneHelper::normalizeLoginIdentifier((string) $request->input('no_hp', '')),
        ]);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'no_hp' => [
                'required',
                'string',
                'regex:/^08[0-9]{7,13}$/',
                Rule::unique('pelanggan', 'no_hp'),
                Rule::unique('users', 'username'),
            ],
            'alamat' => ['required', 'string', 'max:500'],
            'id_paket' => ['required', 'exists:paket,id'],
            'tarif_khusus' => ['nullable', 'integer', 'min:1000'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'siklus_tagihan' => ['required', 'integer', Rule::in([5, 21])],
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor HP Indonesia (contoh: 081234567890).',
            'no_hp.unique' => 'Nomor HP tersebut sudah digunakan.',
            'siklus_tagihan.in' => 'Siklus tagihan hanya boleh tanggal 5 atau 21.',
        ]);

        Pelanggan::create($validated);

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit(Pelanggan $pelanggan): View
    {
        $pakets = Paket::orderBy('tarif_standar')->get();

        return view('admin.pelanggan.edit', compact('pelanggan', 'pakets'));
    }

    public function update(Request $request, Pelanggan $pelanggan): RedirectResponse
    {
        $request->merge([
            'no_hp' => PhoneHelper::normalizeLoginIdentifier((string) $request->input('no_hp', '')),
        ]);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'no_hp' => [
                'required',
                'string',
                'regex:/^08[0-9]{7,13}$/',
                Rule::unique('pelanggan', 'no_hp')->ignore($pelanggan->id),
                Rule::unique('users', 'username')->ignore($pelanggan->id_user),
            ],
            'alamat' => ['required', 'string', 'max:500'],
            'id_paket' => ['required', 'exists:paket,id'],
            'tarif_khusus' => ['nullable', 'integer', 'min:1000'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'siklus_tagihan' => ['required', 'integer', Rule::in([5, 21])],
        ], [
            'no_hp.regex' => 'Format nomor HP tidak valid. Gunakan nomor HP Indonesia (contoh: 081234567890).',
            'no_hp.unique' => 'Nomor HP tersebut sudah digunakan.',
            'siklus_tagihan.in' => 'Siklus tagihan hanya boleh tanggal 5 atau 21.',
        ]);

        DB::transaction(function () use ($pelanggan, $validated) {
            $pelanggan->update($validated);

            // Jika pelanggan sudah punya akun login, sinkronkan nama & nomor HP-nya
            if ($pelanggan->id_user && $pelanggan->user) {
                $pelanggan->user->update([
                    'nama' => $validated['nama'],
                    'username' => $validated['no_hp'],
                ]);
            }
        });

        return redirect()->route('admin.pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function toggleStatus(Pelanggan $pelanggan): RedirectResponse
    {
        $statusBaru = $pelanggan->status === 'aktif' ? 'nonaktif' : 'aktif';
        $pelanggan->update(['status' => $statusBaru]);

        $pesan = $statusBaru === 'aktif'
            ? "Pelanggan {$pelanggan->nama} berhasil diaktifkan."
            : "Pelanggan {$pelanggan->nama} berhasil dinonaktifkan.";

        return redirect()->route('admin.pelanggan.index')
            ->with('success', $pesan);
    }
}