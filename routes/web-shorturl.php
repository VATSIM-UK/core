<?php

Route::any('/', function () {
    return 'vats.im homepage';
});

Route::any('{request_url}', function ($request_url) {
    // check 'Request::path();' against model 'Route'
    $success = App\Models\Short\ShortURL::where('url', $request_url)->first();
    // if successful, redirect, else throw 404
    if ($success) {
        header("Location: {$success->forward_url}");
        exit();
    } else {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
});
