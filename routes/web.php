<?php

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-public.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-main.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-admin.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-external.php');
});

Route::group(['domain' => config('app.url'), 'middleware' => ['web', 'auth']], function () {
    Route::post('training/timezone/detect', function (Illuminate\Http\Request $request) {
        $request->validate(['timezone' => 'required|string']);

        $tzService = app(App\Services\TimezoneService::class);
        $tzService->setBrowserTimezone($request->input('timezone'));

        // Auto-activate detected timezone only if user hasn't explicitly chosen one
        if (! $request->session()->has(App\Services\TimezoneService::SESSION_KEY)) {
            $tzService->setTimezone($request->input('timezone'));
        }

        return response()->json(['success' => true]);
    })->name('training.timezone.detect');
});
