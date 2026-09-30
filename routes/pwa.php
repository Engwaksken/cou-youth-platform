<?php

declare(strict_types=1);

use App\Http\Controllers\PwaController;
use Illuminate\Support\Facades\Route;

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/pwa/icon/{size}', [PwaController::class, 'icon'])
    ->whereIn('size', ['192', '512'])
    ->name('pwa.icon');
