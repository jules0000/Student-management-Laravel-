<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (auth('student')->check()) {
            return redirect()->route('student.portal');
        }

        if (auth('admin')->check()) {
            return redirect()->route('dashboard');
        }

        if (auth('instructor')->check()) {
            return redirect()->route('dashboard');
        }

        if (auth('program_chair')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Ensure only one role is authenticated at a time.
        // Without this, a user can end up with multiple guards active in the same session
        // and the UI may show the wrong role's student list.
        Auth::guard('student')->logout();
        Auth::guard('admin')->logout();
        Auth::guard('instructor')->logout();
        Auth::guard('program_chair')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Normalize email to avoid login failures due to casing differences.
        $credentials['email'] = strtolower(trim($credentials['email']));

        if (Auth::guard('student')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('student.portal'));
        }

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        if (Auth::guard('instructor')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        if (Auth::guard('program_chair')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('instructor')->logout();
        Auth::guard('program_chair')->logout();
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

