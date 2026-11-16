<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Tambah Pelanggan (Tes)</title></head>
<body>
    <h2>Tambah Pelanggan</h2>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('admin.pelanggan.store') }}" method="POST">
        @csrf
        <div><label>Nama:</label><br><input type="text" name="nama" value="{{ old('nama') }}" required></div>
        <div><label>No. HP:</label><br><input type="text" name="no_hp" value="{{ old('no_hp') }}" required></div>
        <div><label>Alamat:</label><br><textarea name="alamat" required>{{ old('alamat') }}</textarea></div>
        <div>
            <label>Paket:</label><br>
            <select name="id_paket" required>
                @foreach ($pakets as $paket)
                    <option value="{{ $paket->id }}" @selected(old('id_paket') == $paket->id)>
                        {{ $paket->nama_paket }} - Rp{{ number_format($paket->tarif_standar, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div><label>Tarif Khusus (kosongkan jika pakai tarif standar):</label><br><input type="number" name="tarif_khusus" value="{{ old('tarif_khusus') }}"></div>
        <div>
            <label>Siklus Tagihan:</label><br>
            <select name="siklus_tagihan" required>
                <option value="5" @selected(old('siklus_tagihan') == 5)>Tanggal 5</option>
                <option value="21" @selected(old('siklus_tagihan') == 21)>Tanggal 21</option>
            </select>
        </div>
        <div>
            <label>Status:</label><br>
            <select name="status" required>
                <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(old('status') === 'nonaktif')>Nonaktif</option>
            </select>
        </div>
        <br>
        <button type="submit">Simpan</button>
        <a href="{{ route('admin.pelanggan.index') }}">Batal</a>
    </form>
</body>
</html>