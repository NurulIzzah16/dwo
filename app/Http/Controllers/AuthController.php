<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = DB::table('schuser')
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['Login gagal']);
        }
        Session::put('schuser', $user->id);
        Session::put('schuser_name', $user->name);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Session::forget('schuser');
        return redirect('/login');
    }
}
