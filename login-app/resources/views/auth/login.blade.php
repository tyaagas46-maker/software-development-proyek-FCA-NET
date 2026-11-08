<form method="POST" action="{{ route('login') }}">
    @csrf

    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
    @error('email') <p>{{ $message }}</p> @enderror

    <input type="password" name="password" placeholder="Password" required>

    <label><input type="checkbox" name="remember"> Remember me</label>

    <button type="submit">Login</button>
    <a href="{{ route('register') }}">Register</a>
</form>