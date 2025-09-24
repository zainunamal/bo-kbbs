<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SnapLoginController;
use App\Http\Controllers\SnapQrisController;

Route::prefix('/snap/v1.0/')->middleware(['logger', 'cors'])->group(function () {
    Route::post('/generate-token', [SnapLoginController::class, 'getToken'])->name('snap-qris.get-token');
    Route::post('/qr/qr-mpm-generate', [SnapQrisController::class, 'generateQr'])->middleware(['jwt-validation', 'signature-validation'])->name('snap-qris.generate-qr');
    Route::post('/qr/qr-mpm-decode', [SnapQrisController::class, 'decodeQr'])->middleware(['jwt-validation', 'signature-validation'])->name('snap-qris.decode-qr');
    Route::post('/qr/qr-mpm-notif', [SnapQrisController::class, 'notifyQr'])->middleware(['jwt-validation', 'signature-validation'])->name('snap-qris.notify-qr');
    Route::post('/qr/qr-mpm-query', [SnapQrisController::class, 'queryQr'])->middleware(['jwt-validation', 'signature-validation'])->name('snap-qris.query-qr');
    Route::post('/qr/qr-mpm-payment', [SnapQrisController::class, 'payment'])->middleware(['jwt-validation', 'signature-validation'])->name('snap-qris.payment');
});