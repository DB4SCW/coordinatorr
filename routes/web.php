<?php

use App\Http\Controllers\ActivationController;
use App\Http\Controllers\PlannedActivationController;
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