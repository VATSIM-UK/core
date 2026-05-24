<?php

use App\Http\Controllers\Auth\TwoFactorSetupController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
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
        Route::get('setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');

        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->name('two-factor.confirm-password');

        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
            ->name('two-factor.confirm-password.store');

        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [$authMiddleware, 'password.confirm']
            : [$authMiddleware];

        Route::post('enable', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.enable');

        Route::post('confirm', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.confirm');

        Route::delete('disable', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.disable');

        Route::get('qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.qr-code');

        Route::get('recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post('recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.regenerate-recovery-codes');
    });
});
