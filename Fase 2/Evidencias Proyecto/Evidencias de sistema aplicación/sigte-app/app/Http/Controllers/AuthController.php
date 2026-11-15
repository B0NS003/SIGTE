<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user instanceof User) {
            if (! $user->isActivo()) {
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();

                return view('auth.login')->withErrors([
                    'login' => 'Esta cuenta está desactivada. Pide a la jefatura que la reactive.',
                ]);
            }

            return redirect()->route('mockups.panel', $user->nombreRol());
        }

        return view('auth.login');
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

        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'No pudimos iniciar sesión. El correo o la contraseña no coinciden.',
            ])->onlyInput('email');
        }

        if (! $user->isActivo()) {
            return back()->withErrors([
                'login' => 'Esta cuenta está desactivada. Pide a la jefatura que la reactive.',
            ])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('mockups.panel', $user->nombreRol());
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
