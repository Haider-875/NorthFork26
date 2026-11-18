<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [ServiceController::class, 'index'])->name('services');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');
Route::get('/book', [BookingController::class, 'index'])->name('book');
Route::post('/book', [BookingController::class, 'submit'])->name('book.submit');

// AI Auto Service Chatbot Routes
Route::post('/api/chat', [ChatbotController::class, 'sendMessage'])->name('chat.message');
Route::post('/api/chat/appointment', [ChatbotController::class, 'bookAppointment'])->name('chat.appointment');
Route::post('/api/chat/clear', [ChatbotController::class, 'clearHistory'])->name('chat.clear');

// Real Appointment Lookup & Verification Routes
Route::post('/api/appointments/lookup', [AppointmentController::class, 'lookup'])->name('appointments.lookup');
Route::post('/api/appointments/verify', [AppointmentController::class, 'verify'])->name('appointments.verify');
Route::get('/api/appointments/provider-status', [AppointmentController::class, 'providerStatus'])->name('appointments.provider_status');
Route::post('/api/appointments/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
Route::post('/api/appointments/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

// Admin Auth Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin Protected Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});
