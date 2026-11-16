<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Data Pelanggan (Tes)</title></head>
<body>
    <h2>Data Pelanggan FCA Net</h2>
    <a href="{{ route('admin.pelanggan.create') }}">+ Tambah Pelanggan</a> |
    <a href="{{ route('admin.paket.index') }}">Kelola Paket</a>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form method="GET" action="{{ route('admin.pelanggan.index') }}" style="margin: 12px 0;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / no HP / alamat">
        <select name="status">
            <option value="">-- Semua Status --</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <select name="siklus_tagihan">
            <option value="">-- Semua Siklus --</option>
            <option value="5" @selected(request('siklus_tagihan') == '5')>Tanggal 5</option>
            <option value="21" @selected(request('siklus_tagihan') == '21')>Tanggal 21</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Nama</th>
                <th>No. HP</th>
                <th>Paket</th>
                <th>Tarif Aktif</th>
                <th>Siklus</th>
                <th>Status</th>
                <th>Akun Terdaftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pelanggans as $p)
                <tr>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->no_hp }}</td>
                    <td>{{ $p->paket->nama_paket }} ({{ $p->paket->kecepatan_mbps }} Mbps)</td>
                    <td>Rp{{ number_format($p->tarif_aktif, 0, ',', '.') }}</td>
                    <td>Tgl {{ $p->siklus_tagihan }}</td>
                    <td>{{ $p->status }}</td>
                    <td>{{ $p->id_user ? 'Sudah' : 'Belum' }}</td>
                    <td>
                        <a href="{{ route('admin.pelanggan.edit', $p) }}">Edit</a>
                        <form action="{{ route('admin.pelanggan.toggle-status', $p) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit">
                                {{ $p->isAktif() ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Belum ada data pelanggan.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $pelanggans->links() }}
</body>
</html>