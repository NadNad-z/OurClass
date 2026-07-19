<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    /** Tampilkan form cek email */
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    /** Verifikasi email dan tampilkan form reset */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Email ini tidak terdaftar di sistem kami.'])->withInput();
        }

        // Simpan email ke session agar tidak bisa dimanipulasi via URL
        session(['reset_email' => $request->email]);

        return redirect()->route('password.reset.form');
    }

    /** Tampilkan form reset password */
    public function showResetForm()
    {
        if (! session('reset_email')) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi tidak valid. Silakan mulai dari awal.');
        }

        return view('auth.reset-password');
    }

    /** Proses reset password */
    public function resetPassword(Request $request)
    {
        if (! session('reset_email')) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi tidak valid. Silakan mulai dari awal.');
        }

        $request->validate([
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'password.required'   => 'Password baru wajib diisi.',
            'password.min'        => 'Password minimal harus 8 karakter.',
            'password.confirmed'  => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::where('email', session('reset_email'))->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->with('error', 'Akun tidak ditemukan. Silakan mulai dari awal.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus session setelah berhasil
        session()->forget('reset_email');

        return redirect()->route('login')
            ->with('success', 'Password berhasil direset! Silakan login dengan password baru Anda.');
    }
}
