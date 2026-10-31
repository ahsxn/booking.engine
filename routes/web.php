<?php

use App\Http\Controllers\Events\ListEventsController;
use App\Http\Controllers\Events\ShowEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', ListEventsController::class)->middleware('throttle:60,1')->name('home');
Route::get('/events/{event:slug}', ShowEventController::class)->middleware('throttle:60,1')->name('events.show');
