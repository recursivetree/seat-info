<?php
Route::group([
    'namespace' => 'RecursiveTree\Seat\InfoPlugin\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'info'
], function () {

    Route::get('/home', [
        'as'   => 'info.home',
        'uses' => 'InfoController@getHomeView',
    ]);

    Route::get('/create', [
        'as'   => 'info.create',
        'uses' => 'InfoController@getCreateView',
    ]);

    Route::post('/save', [
        'as'   => 'info.save',
        'uses' => 'InfoController@getSaveInterface',
    ]);

    Route::post('/delete', [
        'as'   => 'info.delete_article',
        'uses' => 'InfoController@getDeleteInterface',
    ]);

    Route::post('/edit', [
        'as'   => 'info.edit_article',
        'uses' => 'InfoController@getEditView',
    ]);

    Route::post('/set_home_article', [
        'as'   => 'info.set_home_article',
        'uses' => 'InfoController@getSetHomeArticleInterface',
    ]);

    Route::get('/list', [
        'as'   => 'info.list',
        'uses' => 'InfoController@getListView',
    ]);

    Route::get('/manage', [
        'as'   => 'info.manage',
        'uses' => 'InfoController@getManageView',
    ]);

    Route::get('/view/{id}', [
        'as'   => 'info.view',
        'uses' => 'InfoController@getArticleView',
    ]);

    Route::post('/upload_resource', [
        'as'   => 'info.upload_resource',
        'uses' => 'InfoController@uploadResource',
    ]);

    Route::get('/resource/{id}', [
        'as'   => 'info.view_resource',
        'uses' => 'InfoController@viewResource',
    ]);

    Route::post('/deleteResource', [
        'as'   => 'info.delete_resource',
        'uses' => 'InfoController@deleteResource',
    ]);
});