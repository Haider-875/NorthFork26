<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\AppointmentController;

Route::post('/bookings', [BookingController::class, 'submit']);
Route::post('/contact', [ContactController::class, 'submit']);

// North Fork Auto - AI Auto Service Chatbot API
Route::post('/chat', [ChatbotController::class, 'sendMessage']);
Route::post('/chat/appointment', [ChatbotController::class, 'bookAppointment']);
Route::post('/chat/clear', [ChatbotController::class, 'clearHistory']);

// Real Appointment Lookup & Verification API
Route::post('/appointments/lookup', [AppointmentController::class, 'lookup']);
Route::post('/appointments/verify', [AppointmentController::class, 'verify']);
Route::get('/appointments/provider-status', [AppointmentController::class, 'providerStatus']);
Route::post('/appointments/reschedule', [AppointmentController::class, 'reschedule']);
Route::post('/appointments/cancel', [AppointmentController::class, 'cancel']);
