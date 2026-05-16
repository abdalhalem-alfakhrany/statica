<?php

use Illuminate\Support\Facades\Route;
use Statica\Controllers\SettingsEditorController;

Route::prefix('dashboard')->name('dashboard.')->middleware('api')->group(function () {
    Route::prefix('value')->name('update.value.')->group(function () {
        Route::patch('/single', [SettingsEditorController::class, 'updateSingleValue'])->name('single');
        Route::patch('/single-translatable', [SettingsEditorController::class, 'updateSingleTranslatableValue'])->name('single.translatable');
        Route::patch('/list', [SettingsEditorController::class, 'updateListValue'])->name('list');
    });
    Route::patch('/label', [SettingsEditorController::class, 'updateLabel'])->name('update.label');
});
