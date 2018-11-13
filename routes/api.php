<?php

Route::group([

    'middleware' => 'api'

], function ($router) {

    Route::post('crawler', 'CrawlerController@process');

    Route::get('marcas', 'MarcasController@getMarcas');
    Route::get('marcas/{id}', 'MarcasController@getModelos');

    Route::get('manual-options', 'ManualController@getOptions');

    Route::post('manual', 'ManualController@save');
    Route::get('manual', 'ManualController@get');
    Route::delete('manual/{id}', 'ManualController@remove');
    Route::put('manual', 'ManualController@edit');

    Route::get('manual-item', 'ManualController@getItem');
    Route::post('manual-item', 'ManualController@saveItem');
    Route::delete('manual-item/{id}', 'ManualController@removeItem');
    Route::put('manual-item', 'ManualController@editItem');

    Route::get('status', 'StatusController@get');
    Route::post('status', 'StatusController@save');
    Route::put('status', 'StatusController@edit');
    Route::delete('status/{id}', 'StatusController@remove');

    Route::get('uso', 'UsoController@get');
    Route::post('uso', 'UsoController@save');
    Route::put('uso', 'UsoController@edit');
    Route::delete('uso/{id}', 'UsoController@remove');

    Route::get('user', 'UsuarioController@getUsers');

    Route::get('carro', 'CarroController@getCars');

    Route::get('dashboard', 'DashboardController@get');

    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
    Route::post('sendPasswordResetLink', 'ResetPasswordController@sendEmail');
    Route::post('resetPassword', 'ChangePasswordController@process');

});