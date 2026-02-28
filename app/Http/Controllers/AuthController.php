<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
   public function showLogin()
{
    return view('auth.login');
}



    public function login(Request $request)
    {
        return 'PROSES LOGIN';
    }

    public function logout()
    {
        return 'LOGOUT';
    }
}
