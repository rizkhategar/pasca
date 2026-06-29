<?php

use App\Http\Controllers\Api\DosenApiV2Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('dosen')
    ->name('api.dosen.')
    ->controller(DosenApiV2Controller::class)
    ->group(function () {
        Route::get('/', 'index')
            ->name('index');

        Route::get('/{category}', 'byCategory')
            ->where('category', 'postgraduate|undergraduate|Postgraduate|Undergraduate')
            ->name('category.index');

        Route::get('/{category}/{sinta_id}', 'show')
            ->where('category', 'postgraduate|undergraduate|Postgraduate|Undergraduate')
            ->name('category.show');

        Route::get('/{category}/{sinta_id}/{module}', 'module')
            ->where('category', 'postgraduate|undergraduate|Postgraduate|Undergraduate')
            ->where('module', 'garuda|scopus|scholar|book|books|research|researches|service|services|lecturer-details|lecturer-detail|details|detail')
            ->name('category.module');

        Route::get('/{category}/{sinta_id}/{module}/{mode}', 'moduleMode')
            ->where('category', 'postgraduate|undergraduate|Postgraduate|Undergraduate')
            ->where('module', 'garuda|scopus|scholar|book|books|research|researches|service|services|lecturer-details|lecturer-detail|details|detail')
            ->where('mode', 'index|yearly')
            ->name('category.module.mode');
    });
