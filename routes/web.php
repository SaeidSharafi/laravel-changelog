<?php

use Illuminate\Support\Facades\Route;
use SaeidSharafi\Changelog\Http\Controllers\AcknowledgeChangelogController;
use SaeidSharafi\Changelog\Http\Controllers\ChangelogController;

Route::group([
    'prefix' => config('changelog.routes.prefix', 'changelog'),
    'middleware' => config('changelog.routes.middleware', ['web', 'auth']),
], function () {
    Route::get('/', ChangelogController::class)->name('changelog');

    Route::post('/acknowledge', AcknowledgeChangelogController::class)->name('acknowledge.changelog');
});
