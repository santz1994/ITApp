<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin|super-admin'])->group(function () {
    // Master Data landing (exports overview)
    Route::get('/exports', [\App\Http\Controllers\MasterDataController::class, 'index'])->name('masterdata.index');
    // Export all data
    Route::get('/exports/download', [\App\Http\Controllers\MasterDataController::class, 'exportAllData'])->name('masterdata.export');
    // Imports landing and upload
    Route::get('/imports', [\App\Http\Controllers\MasterDataController::class, 'imports'])->name('masterdata.imports');
    Route::post('/imports', [\App\Http\Controllers\MasterDataController::class, 'handleImport'])->name('masterdata.import.post');
    // Download template
    Route::get('/imports/template', [\App\Http\Controllers\MasterDataController::class, 'downloadTemplate'])->name('masterdata.template');
    // Templates listing
    Route::get('/exports/templates', [\App\Http\Controllers\MasterDataController::class, 'templates'])->name('masterdata.templates');
    // Import result listing
    Route::get('/imports/results', [\App\Http\Controllers\MasterDataController::class, 'results'])->name('masterdata.results');
    Route::get('/imports/results/{file}', [\App\Http\Controllers\MasterDataController::class, 'downloadResult'])->name('masterdata.results.download');
});
