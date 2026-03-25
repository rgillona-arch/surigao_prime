<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:user,admin'],
        ]);

        $demoUser = ['username' => 'user', 'password' => 'user123'];
        $demoAdmin = ['username' => 'admin', 'password' => 'admin123'];

        $ok = false;
        if ($data['role'] === 'user') {
            $ok = $data['username'] === $demoUser['username'] && $data['password'] === $demoUser['password'];
        } else {
            $ok = $data['username'] === $demoAdmin['username'] && $data['password'] === $demoAdmin['password'];
        }

        if (!$ok) {
            throw ValidationException::withMessages([
                'username' => 'Invalid credentials (demo: user/user123 or admin/admin123).',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('role', $data['role']);
        $request->session()->put('username', $data['username']);

        return $data['role'] === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
