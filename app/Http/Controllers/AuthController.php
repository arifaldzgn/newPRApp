<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    //
    public function showLoginForm()
    {
        return view('login.login'); 
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Try login using email or badge_no
        $user = User::where('email', $username)
                    ->orWhere('badge_no', $username)
                    ->first();

        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/dashboard'); // change to your desired page
        }

        return back()->withErrors([
            'username' => 'Invalid login credentials.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
