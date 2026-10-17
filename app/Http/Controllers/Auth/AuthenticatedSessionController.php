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
    public function create(Request $request): View
    {
        return match ($request->route('loginRole')) {
            'admin' => view('auth.login-admin'),
            'karyawan' => view('auth.login-karyawan'),
            default => view('auth.login'),
        };
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $role = $request->route('loginRole');

        abort_unless(in_array($role, ['admin', 'karyawan'], true), 404);

        $request->authenticate($role);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
