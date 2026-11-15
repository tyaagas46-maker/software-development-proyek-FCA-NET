<?php

namespace App\Http\Controllers;

use App\Support\PhoneHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username atau nomor HP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $username = PhoneHelper::normalizeLoginIdentifier($validated['username']);
        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $username, 'password' => $validated['password']], $remember)) {
            $request->session()->regenerate(); // Cegah session fixation

            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return back()
            ->withErrors([
                'username' => 'Username / nomor HP atau password salah.',
            ])
            ->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Anda berhasil keluar.');
    }

    private function redirectBasedOnRole(string $role): RedirectResponse
    {
        return $role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('pelanggan.dashboard');
    }
}