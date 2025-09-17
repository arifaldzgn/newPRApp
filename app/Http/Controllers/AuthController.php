<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\File;
use App\Models\Notification;
use App\Models\PartStock;
use App\Models\prTicket;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


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

    public function dashboard()
    {
        // $data = [];

        // $modelPath = app_path('Models');

        // // Get all PHP files in Models folder
        // foreach (File::files($modelPath) as $file) {
        //     $class = pathinfo($file, PATHINFO_FILENAME); // e.g. "User"
        //     $fqcn = "App\\Models\\{$class}";             // full class name

        //     if (class_exists($fqcn)) {
        //         $data[strtolower($class)] = $fqcn::query()->first(); 
        //     }
        // }

        // return response()->json($data);
        return view('dashboard.dashboard');
    }

    public function data()
    {
        $unread_notifications = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        $total_stock = PartStock::sum('quantity');

        $pr_status_counts = prTicket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->mapWithKeys(function ($count, $status) {
                return [$status => $count];
            })
            ->all();

        $recent_pr_tickets = prTicket::orderBy('updated_at', 'desc')
            ->take(5)
            ->get(['ticketCode', 'status', 'updated_at']);

        return response()->json([
            'unread_notifications' => $unread_notifications,
            'total_stock' => $total_stock,
            'pr_status_counts' => [
                $pr_status_counts['Pending'] ?? 0,
                $pr_status_counts['Approved'] ?? 0,
                $pr_status_counts['Rejected'] ?? 0,
                $pr_status_counts['Revised'] ?? 0
            ],
            'recent_pr_tickets' => $recent_pr_tickets
        ]);
    }
}
