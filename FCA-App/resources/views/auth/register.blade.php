<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun Pelanggan - FCA Net (Tes)</title>
</head>
<body>
    <h2>Registrasi Pelanggan FCA Net</h2>
    <p>Gunakan nomor HP yang sudah terdaftar di pengelola FCA Net.</p>

    @if ($errors->any())
        <ul style="color: red;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('register.process') }}" method="POST">
        @csrf
        <div>
            <label>Nama Lengkap</label><br>
            <input type="text" name="nama" value="{{ old('nama') }}" required>
        </div>
        <br>
        <div>
            <label>Nomor HP (WhatsApp)</label><br>
            <input type="text" name="no_hp" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" required>
        </div>
        <br>
        <div>
            <label>Password</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <div>
            <label>Konfirmasi Password</label><br>
            <input type="password" name="password_confirmation" required>
        </div>
        <br>
        <button type="submit">Daftar Akun</button>
    </form>

    <p>Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a></p>
</body>
</html>