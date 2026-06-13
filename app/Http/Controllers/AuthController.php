<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'national_id' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role) {
                if ($user->role->name === 'admin') {
                    return redirect('/admin');
                } elseif ($user->role->name === 'employee') {
                    return redirect('/employee');
                }
            }

            return redirect('/home')->with('success', __('Login Successful'));
        }

        return back()->withErrors([
            'national_id' => __('Invalid credentials'),
        ])->onlyInput('national_id');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'national_id' => 'required|string|unique:users,national_id|digits:11',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
        ]);

        $citizenRole = \App\Models\Role::where('name', 'citizen')->first();

        $user = User::create([
            'national_id' => $validated['national_id'],
            'role_id' => $citizenRole->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
        ]);

        Auth::login($user);

        return redirect('/home')->with('success', __('Account created successfully'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('Logged out successfully'));
    }
}
