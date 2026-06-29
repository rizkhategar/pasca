<?php

use App\Http\Controllers\Api\DosenApiV2Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('dosen')
    ->name('api.dosen.')
    ->controller(DosenApiV2Controller::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{sinta_id}', 'show')
            ->where('sinta_id', '[0-9]+')
            ->name('show');

        Route::get('/{sinta_id}/{module}', 'module')
            ->where('sinta_id', '[0-9]+')
            ->where('module', 'garuda|scopus|scholar|book|books|research|researches|service|services|lecturer-details|lecturer-detail|details|detail')
            ->name('module');

        Route::get('/{sinta_id}/{module}/{mode}', 'moduleMode')
            ->where('sinta_id', '[0-9]+')
            ->where('module', 'garuda|scopus|scholar|book|books|research|researches|service|services|lecturer-details|lecturer-detail|details|detail')
            ->where('mode', 'index|yearly')
            ->name('module.mode');
    });
