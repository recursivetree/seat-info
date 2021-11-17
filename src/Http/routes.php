<?php
Route::group([
    'namespace' => 'RecursiveTree\Seat\InfoPlugin\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'info'
], function () {

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
});