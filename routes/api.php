<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('updated-information/fetch', [App\Http\Controllers\UpdatedInformationController::class, 'fetch'])
    ->middleware('throttle:1,5');

Route::post('/telegram/{token}/webhook', WebhookController::class)
    ->withoutMiddleware('throttle');
