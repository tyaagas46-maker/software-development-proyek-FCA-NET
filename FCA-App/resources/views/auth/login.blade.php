<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - FCA Net (Tes Backend)</title>
</head>
<body>
    <h2>Login FCA Net</h2>

    @if (session('status'))
        <p style="color: green;">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <p style="color: red;">{{ $errors->first() }}</p>
    @endif

    <form action="{{ route('login.process') }}" method="POST">
        @csrf
        <div>
            <label>Username / No. HP</label><br>
            <input type="text" name="username" value="{{ old('username') }}" required autofocus>
        </div>
        <br>
        <div>
            <label>Password</label><br>
            <input type="password" name="password" required>
        </div>
        <br>
        <div>
            <label>
                <input type="checkbox" name="remember"> Ingat saya
            </label>
        </div>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>