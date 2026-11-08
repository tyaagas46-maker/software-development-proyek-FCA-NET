<form method="POST" action="{{ route('register') }}">
    @csrf

    <input type="text" name="name" value="{{ old('name') }}" placeholder="Name" required>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="password_confirmation" placeholder="Confirm password" required>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <button type="submit">Register</button>
</form>