<?php

use App\Http\Controllers\Auth\TwoFactorBackupCodesController;
use App\Http\Controllers\Auth\TwoFactorRekeyController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;

Route::prefix('auth/two-factor')->middleware(config('fortify.middleware', ['web']))->group(function () {
    $twoFactorLimiter = config('fortify.limiters.two-factor');
    $authMiddleware = 'auth:'.config('fortify.guard', 'web');

    Route::get('challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
        ->middleware(['guest:'.config('fortify.guard', 'web')])
        ->name('two-factor.login');

    Route::post('challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:'.config('fortify.guard', 'web'),
            $twoFactorLimiter ? 'throttle:'.$twoFactorLimiter : null,
        ]))
        ->name('two-factor.login.store');

    Route::middleware($authMiddleware)->group(function () use ($authMiddleware) {
        Route::get('setup', [TwoFactorSetupController::class, 'show'])
            ->name('two-factor.setup');

        Route::get('backup-codes', [TwoFactorBackupCodesController::class, 'show'])
            ->name('two-factor.backup-codes');

        Route::post('enable', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($authMiddleware)
            ->name('two-factor.enable');

        Route::post('rekey', [TwoFactorRekeyController::class, 'store'])
            ->middleware($authMiddleware)
            ->name('two-factor.rekey');

        Route::post('confirm', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->middleware($authMiddleware)
            ->name('two-factor.confirm');

        Route::delete('disable', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($authMiddleware)
            ->name('two-factor.disable');

        Route::get('qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware($authMiddleware)
            ->name('two-factor.qr-code');

        Route::get('recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware($authMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post('recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware($authMiddleware)
            ->name('two-factor.regenerate-recovery-codes');
    });
});
