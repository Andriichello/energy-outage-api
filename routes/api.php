<?php

use Illuminate\Support\Facades\Route;

Route::get('updated-information/fetch', [App\Http\Controllers\UpdatedInformationController::class, 'fetch'])
    ->middleware('throttle:1,5');
