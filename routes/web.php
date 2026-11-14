<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\LevyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RateCardController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\WithholdingProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('reconciliations.index'));

Route::get('/dashboard', fn () => redirect()->route('reconciliations.index'))
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class)->except('show');
    Route::resource('levies', LevyController::class)->except('show');

    Route::resource('rate-cards', RateCardController::class)->except('show');
    Route::post('rate-cards/{rateCard}/items', [RateCardController::class, 'addItem'])->name('rate-cards.items.store');
    Route::delete('rate-cards/{rateCard}/items/{item}', [RateCardController::class, 'destroyItem'])->name('rate-cards.items.destroy');

    Route::resource('withholding-profiles', WithholdingProfileController::class)->except('show');

    Route::post('estimates/calculate', [EstimateController::class, 'calculate'])->name('estimates.calculate');
    Route::post('estimates', [EstimateController::class, 'store'])->name('estimates.store');
    Route::get('clients/{client}/estimates', [EstimateController::class, 'history'])->name('estimates.history');

    Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('reconciliations.index');
    Route::post('reconciliation/populate', [ReconciliationController::class, 'populate'])->name('reconciliations.populate');
    Route::post('reconciliation/{reconciliation}/items', [ReconciliationController::class, 'addItem'])->name('reconciliations.items.store');
    Route::patch('reconciliation/items/{item}', [ReconciliationController::class, 'updateItem'])->name('reconciliations.items.update');
    Route::delete('reconciliation/items/{item}', [ReconciliationController::class, 'destroyItem'])->name('reconciliations.items.destroy');
    Route::post('reconciliation/items/{item}/confirm', [ReconciliationController::class, 'confirm'])->name('reconciliations.items.confirm');
    Route::post('reconciliation/{reconciliation}/items/confirm-bulk', [ReconciliationController::class, 'confirmBulk'])->name('reconciliations.items.confirm_bulk');
    Route::post('reconciliation/{reconciliation}/close', [ReconciliationController::class, 'close'])->name('reconciliations.close');
    Route::post('reconciliation/{reconciliation}/reopen', [ReconciliationController::class, 'reopen'])->name('reconciliations.reopen');
    Route::get('reconciliation/{reconciliation}/events', [ReconciliationController::class, 'events'])->name('reconciliations.events');
});

require __DIR__.'/auth.php';
