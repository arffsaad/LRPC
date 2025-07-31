<?php

use ArffSaad\LRPC\Http\Controllers\LRPCController;
use Illuminate\Support\Facades\Route;

Route::post('/lrpc', [LRPCController::class, 'handle']);
