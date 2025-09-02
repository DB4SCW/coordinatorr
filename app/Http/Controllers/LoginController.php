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

        //assure there is an app-mode set in the database
        db4scw_assure_appmode();

        //get locally installed version
        $versioninfo_path = storage_path('app/version.txt');
        $installed_version = File::get($versioninfo_path);
        $installed_version = preg_replace('/\s+/', ' ', trim($installed_version));
        
        //get globally available version
        $available_version = $installed_version;

        //get newest release from Github
        $githubinfos =  db4scw_checklatestGithubRelease("DB4SCW", "coordinatorr", $installed_version);

        //check if upgrade is needed and set updateinfo for display on GUI
        if($githubinfos["isNewer"])
        {
            //redirect to admin panel
            return redirect('/admin')->with('success', 'Successfull login to admin panel')->with('updateinfo', $githubinfos);
        }else{
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
