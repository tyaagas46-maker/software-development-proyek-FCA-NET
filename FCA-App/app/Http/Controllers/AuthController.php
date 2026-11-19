<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\User;
use App\Support\PhoneHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view("auth.login");
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        $validated = $request->validate([
            "username" => ["required", "string"],
            "password" => ["required", "string"],
        ], [
            "username.required" => "Username atau nomor HP wajib diisi.",
            "password.required" => "Password wajib diisi.",
        ]);

        $username = PhoneHelper::normalizeLoginIdentifier($validated["username"]);
        $remember = $request->boolean("remember");

        if (Auth::attempt(["username" => $username, "password" => $validated["password"]], $remember)) {
            $request->session()->regenerate(); // Cegah session fixation

            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return back()
            ->withErrors([
                "username" => "Username / nomor HP atau password salah.",
            ])
            ->onlyInput("username");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route("login")
            ->with("status", "Anda berhasil keluar.");
    }

    private function redirectBasedOnRole(string $role): RedirectResponse
    {
        return $role === "admin"
            ? redirect()->route("admin.dashboard")
            : redirect()->route("pelanggan.dashboard");
    }

    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view("auth.register");
    }

    public function register(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        // Normalisasi nomor HP sebelum validasi & pencocokan ke database
        $request->merge([
            "no_hp" => PhoneHelper::normalizeLoginIdentifier((string) $request->input("no_hp", "")),
        ]);

        $validated = $request->validate([
            "nama" => ["required", "string", "max:150"],
            "no_hp" => ["required", "string", "regex:/^08[0-9]{7,13}$/"],
            "password" => ["required", "string", "min:6", "confirmed"],
        ], [
            "nama.required" => "Nama lengkap wajib diisi.",
            "no_hp.required" => "Nomor HP wajib diisi.",
            "no_hp.regex" => "Format nomor HP tidak valid (contoh: 081234567890).",
            "password.required" => "Password wajib diisi.",
            "password.min" => "Password minimal 6 karakter.",
            "password.confirmed" => "Konfirmasi password tidak cocok.",
        ]);

        $user = DB::transaction(function () use ($validated) {
            // lockForUpdate() mencegah pendaftaran ganda jika tombol diklik 2x bersamaan
            $pelanggan = Pelanggan::where("no_hp", $validated["no_hp"])
                ->lockForUpdate()
                ->first();

            if (! $pelanggan) {
                throw ValidationException::withMessages([
                    "no_hp" => "Nomor HP tidak ditemukan di data pelanggan FCA Net. Hubungi pengelola jika Anda sudah berlangganan.",
                ]);
            }

            if ($pelanggan->status !== "aktif") {
                throw ValidationException::withMessages([
                    "no_hp" => "Status berlangganan Anda saat ini nonaktif. Silakan hubungi pengelola FCA Net.",
                ]);
            }

            if ($pelanggan->id_user !== null) {
                throw ValidationException::withMessages([
                    "no_hp" => "Nomor HP ini sudah memiliki akun. Silakan langsung login.",
                ]);
            }

            if (User::where("username", $validated["no_hp"])->exists()) {
                throw ValidationException::withMessages([
                    "no_hp" => "Nomor HP ini sudah terdaftar sebagai akun. Silakan langsung login.",
                ]);
            }

            $user = User::create([
                "nama" => $validated["nama"],
                "username" => $validated["no_hp"],
                "password" => Hash::make($validated["password"]),
                "role" => "pelanggan",
            ]);

            // Tautkan akun user baru ke data pelanggan
            $pelanggan->update([
                "id_user" => $user->id,
            ]);

            return $user;
        });

        // Otomatis login setelah registrasi berhasil
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route("pelanggan.dashboard")
            ->with("success", "Pendaftaran berhasil! Akun Anda telah tertaut dengan data pelanggan.");
    }
}