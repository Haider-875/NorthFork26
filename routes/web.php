<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\ServiceManagementController;
use App\Http\Controllers\Admin\MessageManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SettingManagementController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/book', [BookingController::class, 'index'])->name('book');
Route::post('/book', [BookingController::class, 'submit'])->name('book.submit');

// Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Bookings
    Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/status', [BookingManagementController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/bookings/{id}/notes', [BookingManagementController::class, 'updateNotes'])->name('bookings.notes');
    Route::delete('/bookings/{id}', [BookingManagementController::class, 'destroy'])->name('bookings.destroy');

    // Services
    Route::get('/services', [ServiceManagementController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceManagementController::class, 'create'])->name('services.create');
    Route::post('/services', [ServiceManagementController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit', [ServiceManagementController::class, 'edit'])->name('services.edit');
    Route::post('/services/{id}', [ServiceManagementController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}', [ServiceManagementController::class, 'destroy'])->name('services.destroy');

    // Contact Messages
    Route::get('/messages', [MessageManagementController::class, 'index'])->name('messages.index');
    Route::get('/messages/{id}', [MessageManagementController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{id}', [MessageManagementController::class, 'destroy'])->name('messages.destroy');

    // Users & Staff
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::post('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Shop Settings
    Route::get('/settings', [SettingManagementController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingManagementController::class, 'update'])->name('settings.update');
});
