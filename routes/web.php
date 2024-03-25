<?php

use App\Http\Controllers\ActivationController;
use App\Http\Controllers\ActivatorController;
use App\Http\Controllers\CallsignController;
use App\Http\Controllers\PlannedActivationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminpanelController;
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

Route::get('/', [ActivationController::class, 'index'])->name('home');
Route::post('/add_activation', [ActivationController::class, 'add'])->name('add');
Route::get('/end_activation/{activation:id}', [ActivationController::class, 'end'])->name('end');
Route::get('/planned_activations', [PlannedActivationController::class, 'index'])->name('planned_activations');
Route::post('/add_planned_activation', [PlannedActivationController::class, 'add'])->name('add_planned_activation');
Route::get('/planned_activation/{plannedactivation:id}/delete', [PlannedActivationController::class, 'remote'])->name('delete_planned_activation');

Route::get('/status/{callsign:call}', [CallsignController::class, 'status'])->name('getstatus');




//Only guests can login
Route::middleware('guest')->group(function () {
    Route::get('/admin/' . urlencode(env('ADMIN_PANEL_SECRET', 'simsalabim')), [LoginController::class, 'login'])->name('login');
});

//Routes for logged in users
Route::middleware('auth')->group(function () {

    //only logged in users can logout
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');    

    //Adminpanel
    Route::get('/admin', [AdminpanelController::class, 'index'])->name('adminpanel');

    //Activator handling
    Route::post('/admin/add_activator', [ActivatorController::class, 'create']);
    Route::get('/activator/{activator:call}/remove', [ActivatorController::class, 'destroy']);
    
    //Callsign handling
    Route::post('/admin/add_callsign', [CallsignController::class, 'create']);
    Route::get('/callsign/{callsign:call}/remove', [CallsignController::class, 'destroy']);

});
