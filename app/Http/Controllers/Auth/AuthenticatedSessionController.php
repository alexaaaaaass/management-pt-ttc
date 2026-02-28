<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Providers\RouteServiceProvider;

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
     */
  public function store(LoginRequest $request): RedirectResponse
{
    // 1️⃣ validasi role (email & password sudah divalidasi oleh LoginRequest)
    $request->validate([
        'role' => ['required'],
    ]);

    // 2️⃣ proses login
    $request->authenticate();
    $request->session()->regenerate();

    // 3️⃣ CEK ROLE → DI SINI TEMPATNYA
    if (auth()->user()->role !== $request->role) {
        auth()->logout();

        return back()->withErrors([
            'email' => 'Role tidak sesuai dengan akun.',
        ]);
    }

    // 4️⃣ redirect kalau aman
    return redirect()->intended(\App\Providers\RouteServiceProvider::HOME);
}

}
