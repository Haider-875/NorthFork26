<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ChatbotController;

Route::post('/bookings', [BookingController::class, 'submit']);
Route::post('/contact', [ContactController::class, 'submit']);

// North Fork Auto - AI Auto Service Chatbot API
Route::post('/chat', [ChatbotController::class, 'sendMessage']);
Route::post('/chat/appointment', [ChatbotController::class, 'bookAppointment']);
Route::post('/chat/clear', [ChatbotController::class, 'clearHistory']);
