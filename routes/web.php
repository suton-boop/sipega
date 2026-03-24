<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/agenda', [\App\Http\Controllers\AgendaController::class, 'store'])->middleware(['auth'])->name('agenda.store');
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::post('/agenda', [\App\Http\Controllers\AgendaController::class, 'store'])->middleware(['auth'])->name('agenda.store');
Route::post('/assign', [\App\Http\Controllers\AssignmentLetterController::class, 'store'])->middleware(['auth'])->name('assign.store');
Route::get('/assign/pdf/{id}', [\App\Http\Controllers\AssignmentLetterController::class, 'generatePdf'])->middleware(['auth'])->name('assign.pdf');
Route::post('/import-absen', [\App\Http\Controllers\AttendanceController::class, 'importExcel'])->middleware(['auth'])->name('attendance.import');

Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->middleware(['auth'])->name('users.index');
Route::put('/users/{id}', [\App\Http\Controllers\UserController::class, 'update'])->middleware(['auth'])->name('users.update');

Route::get('/rbac', [\App\Http\Controllers\RolePermissionController::class, 'index'])->middleware(['auth'])->name('rbac.index');
Route::post('/rbac/sync', [\App\Http\Controllers\RolePermissionController::class, 'sync'])->middleware(['auth'])->name('rbac.sync');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
