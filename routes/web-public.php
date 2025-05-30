<?php

Route::group([
    'as' => 'site.',
    'namespace' => 'Site',
], function () {
    Route::get('/')->uses('HomePageController')->name('home');
    Route::get('/join')->uses('JoinPageController')->name('join');
    Route::get('/staff')->uses('StaffPageController')->name('staff');
    Route::get('/airports')->uses('AirportController@index')->name('airports');
    Route::get('/airports/{ukAirportByICAO}')->uses('AirportController@show')->name('airport.view');
    Route::get('/VATSIM_UK_Sector_Provider.txt')->uses('EuroScopeSectorProvider');

    Route::group([
        'as' => 'atc.',
        'prefix' => 'atc',
    ], function () {
        Route::get('/')->uses('ATCPagesController@viewLanding')->name('landing');
        Route::get('/new-controller')->uses('ATCPagesController@viewNewController')->name('newController');
        Route::get('/endorsements')->uses('ATCPagesController@viewEndorsements')->name('endorsements');
        Route::get('/heathrow')->uses('ATCPagesController@viewHeathrow')->name('heathrow');
        Route::get('/becoming-a-mentor')->uses('ATCPagesController@viewBecomingAMentor')->name('mentor');
        Route::get('/bookings')->uses('ATCPagesController@viewBookings')->name('bookings');
    });

    Route::group([
        'as' => 'pilots.',
        'prefix' => 'pilots',
    ], function () {
        Route::get('/')->uses('PilotPagesController@viewLanding')->name('landing');
        Route::get('/ratings')->uses('PilotPagesController@viewRatings')->name('ratings');
        Route::get('/becoming-a-mentor')->uses('PilotPagesController@viewBecomingAMentor')->name('mentor');
        Route::get('/oceanic')->uses('PilotPagesController@viewOceanic')->name('oceanic');
        Route::get('/stand-guide')->uses('PilotPagesController@viewStandGuide')->name('stands');
        Route::get('/the-flying-programme')->uses('PilotPagesController@viewTheFlyingProgramme')->name('tfp');
    });

    Route::group([
        'as' => 'operations.',
        'prefix' => 'operations',
    ], function () {
        Route::get('/')->uses('OperationsPagesController@viewLanding')->name('landing');
        Route::get('/sectors')->uses('OperationsPagesController@viewSectors')->name('sectors');
    });

    Route::group([
        'as' => 'community.',
        'prefix' => 'community',
    ], function () {
        Route::get('/vt-guide')->uses('CommunityPagesController@viewVtGuide')->name('vt-guide');
        Route::get('/terms-and-conditions')->uses('CommunityPagesController@viewTerms')->name('terms');
        Route::get('/privacy-policy')->uses('CommunityPagesController@viewPrivacy')->name('privacy');
        Route::get('/teamspeak')->uses('CommunityPagesController@viewTeamspeak')->name('teamspeak');
    });

    Route::group([
        'as' => 'marketing.',
        'prefix' => 'marketing',
    ], function () {
        Route::get('/branding')->uses('MarketingPagesController@viewBranding')->name('branding');
    });
});
