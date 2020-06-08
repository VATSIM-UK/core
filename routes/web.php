<?php

// use App\Libraries\VatsimApi;
// use GuzzleHttp\Client;
// use Vatsim\Xml\Facades\XML as VatsimXML;

// $client = (new Client);

// $new_r = (new VatsimApi($client))->ratingsFor(1258635);
// $old_r = VatsimXML::getData(1258635, 'idstatusint');

//$new_r = (new VatsimApi($client))->previousRatingFor(1258635);
//$old_r = VatsimXML::getData(1258635, 'idstatusprat');

// dd($old_r, $new_r);

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-public.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-main.php');
});

Route::group(['domain' => config('app.url')], function () {
    require base_path('routes/web-admin.php');
});
