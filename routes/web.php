<?php

use App\Http\Controllers\ActivationController;
use App\Http\Controllers\ActivatorController;
use App\Http\Controllers\CallsignController;
use App\Http\Controllers\PlannedActivationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminpanelController;
use App\Http\Controllers\HamalertController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

//Main Page
Route::get('/', [ActivationController::class, 'index'])->name('home');

//Activation handling
Route::post('/add_activation', [ActivationController::class, 'add'])->name('add');
Route::get('/end_activation/{activation:id}', [ActivationController::class, 'end'])->name('end');

//View planned activations
Route::get('/planned_activations', [PlannedActivationController::class, 'index'])->name('planned_activations');

//Planned activation handling
Route::post('/add_planned_activation', [PlannedActivationController::class, 'add'])->name('add_planned_activation');
Route::get('/planned_activation/{plannedactivation:id}/delete', [PlannedActivationController::class, 'remove'])->name('delete_planned_activation');

//QRZ IFrame Integration
Route::get('/status/{callsign:call}', [CallsignController::class, 'status'])->name('getstatus');

//Login Route with token
Route::get('/adminkey/' . urlencode(env('ADMIN_PANEL_SECRET', 'simsalabim')), [LoginController::class, 'login'])->name('loginwithtoken');

//dummy login route to redirect to home (necessary because of auth middleware)
Route::get('/login', function() { return redirect('/'); })->name('login');

//Routes for logged in users
Route::middleware('auth')->group(function () { 

    //only logged in users can logout
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');   

    //Adminpanel
    Route::get('/admin', [AdminpanelController::class, 'index'])->name('adminpanel');

    //Activator handling
    Route::post('/admin/add_activator', [ActivatorController::class, 'create']);
    Route::get('/activator/{activator:call}/remove', [ActivatorController::class, 'destroy']);
    Route::get('/activator/{activator:call}/lock', [ActivatorController::class, 'lock']);
    
    //Callsign handling
    Route::post('/admin/add_callsign', [CallsignController::class, 'create']);
    Route::get('/callsign/{callsign:call}/remove', [CallsignController::class, 'destroy']);
    Route::get('/callsign/{callsign:call}/hide', [CallsignController::class, 'hide']);

    //Mode handling
    Route::post('/admin/switch_mode', [AdminpanelController::class, 'switchmode']);

});

//Hamalert integration
Route::post('/hamalertreceiver', [HamalertController::class, 'receive'])->name('hamalertreceiver')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
