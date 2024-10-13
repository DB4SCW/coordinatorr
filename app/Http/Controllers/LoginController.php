<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function login()
    {
        //check if login is already done
        if(auth()->check())
        {
            //redirect to admin panel
            return redirect('/admin')->with('success', 'Successfull login to admin panel');
        }

        //if user reaches this route, login admin user
        $user = User::find(1);
        Auth::login($user);

        //regenerate session
        session()->regenerate();

        //get locally installed version
        $versioninfo_path = storage_path('app/version.txt');
        $installed_version = File::get($versioninfo_path);
        $installed_version = preg_replace('/\s+/', ' ', trim($installed_version));
        
        //get globally available version
        $available_version = $installed_version;
        try {
            $available_version = Http::get('https://hamawardz.de/versionfiles/coordinatorr_version.txt')->body();
            $available_version = preg_replace('/\s+/', ' ', trim($available_version));
        } catch (\Throwable $th) {
            // do nothing, cannot reach info for updated version
        }

        //assure there is an app-mode in the configuration
        db4scw_assure_appmode_in_env();

        //check if upgrade is needed and set updateinfo for display on GUI
        if(version_compare($available_version, $installed_version, '>'))
        {
            //redirect to admin panel
            return redirect('/admin')->with('success', 'Successfull login to admin panel')->with('updateinfo', $available_version);
        }else
        {
            //redirect to admin panel
            return redirect('/admin')->with('success', 'Successfull login to admin panel');
        }
        
    }

    public function logout()
    {
        //Logout user
        if(auth()->check())
        {
            auth()->logout();
            session()->invalidate();
        }
        
        //return to home page
        return redirect()->route('home')->with('success', 'Logout successful. Hope to see you again soon.');
    }
}
