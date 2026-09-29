<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('mockups.panel', Auth::user()->nombreRol());
        }

        return view('mockups.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'El correo no tiene un formato válido. Ejemplo: nombre@hospital.cl',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        $ingreso = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'activo' => true,
        ], $request->boolean('remember'));

        if ($ingreso) {
            $request->session()->regenerate();

            return redirect()->route('mockups.panel', Auth::user()->nombreRol());
        }

        return back()->withErrors([
            'login' => 'No pudimos iniciar sesión. El correo o la contraseña no coinciden.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}