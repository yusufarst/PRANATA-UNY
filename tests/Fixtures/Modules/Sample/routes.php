<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('sample-module/ping', fn () => response()->noContent());
