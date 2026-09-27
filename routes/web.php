<?php

use App\Http\Controllers\BattalionController;
use App\Http\Controllers\BrigadeController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicemanAwardController;
use App\Http\Controllers\ServicemanController;
use App\Http\Controllers\ServicemanPaymentIssueController;
use App\Http\Controllers\ServicemanTreatmentFacilityController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Redirect::route(auth()->check() ? 'servicemen.index' : 'login'));

Route::get('/dashboard', fn () => Redirect::route('servicemen.index'))->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/data', [DataController::class, 'index'])->name('data.index');
    Route::get('/data/statistics', [DataController::class, 'statistics'])->name('data.statistics');
    Route::get('/data/report', [DataController::class, 'report'])->name('data.report');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/settlements/search', [SettlementController::class, 'search'])->name('settlements.search');
    Route::resource('settlements', SettlementController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('servicemen', ServicemanController::class);
    Route::post('servicemen/{serviceman}/payment-issues', [ServicemanPaymentIssueController::class, 'store'])->name('servicemen.payment-issues.store');
    Route::patch('servicemen/{serviceman}/payment-issues/{paymentIssue}', [ServicemanPaymentIssueController::class, 'update'])->name('servicemen.payment-issues.update');
    Route::delete('servicemen/{serviceman}/payment-issues/{paymentIssue}', [ServicemanPaymentIssueController::class, 'destroy'])->name('servicemen.payment-issues.destroy');
    Route::post('servicemen/{serviceman}/awards', [ServicemanAwardController::class, 'store'])->name('servicemen.awards.store');
    Route::patch('servicemen/{serviceman}/awards/{award}', [ServicemanAwardController::class, 'update'])->name('servicemen.awards.update');
    Route::delete('servicemen/{serviceman}/awards/{award}', [ServicemanAwardController::class, 'destroy'])->name('servicemen.awards.destroy');
    Route::patch('servicemen/{serviceman}/facility', [ServicemanController::class, 'updateFacility'])->name('servicemen.facility.update');
    Route::patch('servicemen/{serviceman}/treatment-facilities/{treatmentFacility}', [ServicemanTreatmentFacilityController::class, 'update'])->name('servicemen.treatment-facilities.update');
    Route::delete('servicemen/{serviceman}/treatment-facilities/{treatmentFacility}', [ServicemanTreatmentFacilityController::class, 'destroy'])->name('servicemen.treatment-facilities.destroy');

    Route::resource('battalions', BattalionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('brigades', BrigadeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__.'/auth.php';
