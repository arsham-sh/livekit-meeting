<?php

use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MeetingController::class, 'create'])->name('meetings.create');
Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
Route::get('/meetings/{room}', [MeetingController::class, 'show'])->where('room', '[A-Za-z0-9_-]+')->name('meetings.show');
Route::post('/meetings/{room}/token', [MeetingController::class, 'token'])->where('room', '[A-Za-z0-9_-]+')->name('meetings.token');
