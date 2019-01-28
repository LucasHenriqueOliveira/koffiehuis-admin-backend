<?php

Route::group([

    'middleware' => 'api'

], function ($router) {

    Route::post('crawler', 'CrawlerController@process');
    Route::get('modelo', 'CrawlerController@modelo');
    Route::get('ano', 'CrawlerController@ano');
    Route::get('versao', 'CrawlerController@versao');
    Route::get('versao-ano', 'CrawlerController@versaoAno');

    Route::get('marcas', 'MarcasController@getMarcas');
    Route::get('marcas/{id}', 'MarcasController@getModelos');
    Route::get('anos/{id}', 'MarcasController@getAnos');
    Route::get('versao/{id}', 'MarcasController@getVersao');

    Route::get('manual-options', 'ManualController@getOptions');

    Route::post('manual', 'ManualController@save');
    Route::get('manual', 'ManualController@get');
    Route::delete('manual/{id}', 'ManualController@remove');
    Route::put('manual', 'ManualController@edit');

    Route::get('manual-itens', 'ManualController@getItemManual');
    Route::get('manual-itens/{id}', 'ManualController@getItemManualTitulo');
    Route::post('manual-itens', 'ManualController@saveItemManual');
    Route::delete('manual-itens/{id}', 'ManualController@removeItemManual');
    Route::put('manual-itens', 'ManualController@editItemManual');

    Route::get('manual-itens-fixo', 'ManualController@getItemManualFixo');
    Route::post('manual-itens-fixo', 'ManualController@saveItemManualFixo');
    Route::delete('manual-itens-fixo/{id}', 'ManualController@removeItemManualFixo');
    Route::put('manual-itens-fixo', 'ManualController@editItemManualFixo');
    
    Route::get('manual-item', 'ManualController@getItem');
    Route::post('manual-item', 'ManualController@saveItem');
    Route::delete('manual-item/{id}', 'ManualController@removeItem');
    Route::put('manual-item', 'ManualController@editItem');

    Route::post('manual-carro', 'ManualController@saveManualCarro');
    Route::get('manual-carro/{modelo}', 'ManualController@getManualCarro');
    Route::delete('manual-carro/{id_marca}/{id_modelo}/{ano}/{id_versao}', 'ManualController@removeManualCarro');
    Route::put('manual-carro', 'ManualController@editManualCarro');

    Route::get('list-manual/{modelo}', 'ManualController@getListManual');
    Route::get('last-manual', 'ManualController@lastManual');

    Route::get('status', 'StatusController@get');
    Route::post('status', 'StatusController@save');
    Route::put('status', 'StatusController@edit');
    Route::delete('status/{id}', 'StatusController@remove');

    Route::get('titulo', 'TituloController@get');
    Route::post('titulo', 'TituloController@save');
    Route::put('titulo', 'TituloController@edit');
    Route::delete('titulo/{id}', 'TituloController@remove');

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