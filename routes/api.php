<?php

if (App::environment('production')) {
    URL::forceScheme('https');
}

Route::group([

    'middleware' => 'api'

], function ($router) {

    Route::get('dashboard', 'DashboardController@get');
    Route::post('remove', 'DashboardController@remove');

    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('sendPasswordResetLink', 'ResetPasswordController@sendEmail');
    Route::post('resetPassword', 'ChangePasswordController@process');

});