<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login()
    {
        //if user reaches this route, login admin user
        $user = User::find(1);
        Auth::login($user);
        
        //regenerate session
        session()->regenerate();
        
        //redirect to admin panel
        return redirect('/admin')->with('success', 'Successfull login to admin panel');
    }

    public function logout()
    {
        //Logout user
        if(auth()->check())
        {
            auth()->logout();
        }

        //return to home page
        return redirect()->route('home')->with('success', 'Logout successful. Hope to see you again soon.');
    }
}
