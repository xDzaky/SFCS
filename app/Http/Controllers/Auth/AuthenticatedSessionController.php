<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Alur redirect setelah login:
     * 1. Jika ada ?intended=pengaduan/pinjaman dan role siswa/guru → ke halaman tersebut
     * 2. Jika ada ?intended= tapi bukan siswa/guru → abaikan, ke dashboard per role
     * 3. Fallback ke intended() laravel biasa, atau dashboard
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user     = $request->user();
        $intended = $request->input('intended') ?? $request->query('intended'); // mis: "pengaduan" atau "pinjaman"

        // Jika user siswa/guru:
        if (in_array($user->role, ['siswa', 'guru'])) {
            if ($intended === 'pengaduan') {
                return redirect()->route('pengaduan.create');
            }
            if ($intended === 'pinjaman') {
                return redirect()->route('pinjaman.create');
            }
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Jika user lain (admin, superadmin, teknisi, kepsek) → langsung ke dashboard sesuai role nya
        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
