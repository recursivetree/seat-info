<?php
Route::group([
    'namespace' => 'RecursiveTree\Seat\InfoPlugin\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'i'
], function () {
    Route::get('/{permalink}', [
        'as'   => 'info.permalink',
        'uses' => 'InfoController@permaLink',
    ]);
});

Route::group([
    'namespace' => 'RecursiveTree\Seat\InfoPlugin\Http\Controllers',
    'middleware' => ['web', 'auth'],
    'prefix' => 'info'
], function () {
    Route::get('/article/create', [
        'as'   => 'info.create',
        'uses' => 'InfoController@getCreateView',
        'middleware' => 'can:info.create_article'
    ]);

    //permission in controller
    Route::post('/article/save', [
        'as'   => 'info.save',
        'uses' => 'InfoController@getSaveInterface',
    ]);

    //permission in controller
    Route::get('/article/edit/{id}', [
        'as'   => 'info.edit_article',
        'uses' => 'InfoController@getEditView',
    ]);

    Route::get('/article/list', [
        'as'   => 'info.list',
        'uses' => 'InfoController@getListView',
    ]);

    Route::get('/personal', [
        'as'   => 'info.manage',
        'uses' => 'InfoController@getManageView',
        'middleware' => 'can:info.create_article'
    ]);

    Route::post('/personal/disable/donationinfo', [
        'as'   => 'info.disable_donation_info',
        'uses' => 'InfoController@disableDonationInfo',
    ]);


    //permission in controller
    Route::get('/article/view/{id}', [
        'as'   => 'info.view',
        'uses' => 'InfoController@getArticleView',
    ]);

    Route::post('/resource/upload', [
        'as'   => 'info.upload_resource',
        'uses' => 'InfoController@uploadResource',
        'middleware' => 'can:info.upload_resource'
    ]);

    //permission in controller
    Route::get('/resource/{id}', [
        'as'   => 'info.view_resource',
        'uses' => 'InfoController@viewResource',
    ]);

    //permission in controller
    Route::get('/resource/{id}/configure', [
        'as'   => 'info.configure_resource',
        'uses' => 'InfoController@configureResource',
    ]);

    //permission in controller
    Route::post('/resource/{id}/save', [
        'as'   => 'info.configure_resource_save',
        'uses' => 'InfoController@configureResourceSave',
    ]);

    //permission in controller
    Route::post('/resource/delete', [
        'as'   => 'info.delete_resource',
        'uses' => 'InfoController@deleteResource',
    ]);

    //permission in controller
    Route::post('/article/manage/delete', [
        'as'   => 'info.delete_article',
        'uses' => 'InfoController@deleteArticle',
    ]);

    Route::post('/article/manage/set/public', [
        'as'   => 'info.set_article_public',
        'uses' => 'InfoController@setArticlePublic',
        'middleware' => 'can:info.make_public'
    ]);

    Route::post('/article/manage/set/private', [
        'as'   => 'info.set_article_private',
        'uses' => 'InfoController@setArticlePrivate',
        'middleware' => 'can:info.make_public'
    ]);

    Route::post('/article/manage/set/pinned', [
        'as'   => 'info.set_article_pinned',
        'uses' => 'InfoController@setArticlePinned',
        'middleware' => 'can:info.pin_article'
    ]);

    Route::post('/article/manage/set/unpinned', [
        'as'   => 'info.set_article_unpinned',
        'uses' => 'InfoController@setArticleUnpinned',
        'middleware' => 'can:info.pin_article'
    ]);

    Route::post('/permalink/create', [
        'as'   => 'info.create_permalink',
        'uses' => 'InfoController@createPermaLink',
        'middleware' => 'can:info.edit_permalinks'
    ]);

    Route::post('/permalink/delete', [
        'as'   => 'info.delete_permalink',
        'uses' => 'InfoController@deletePermaLink',
        'middleware' => 'can:info.edit_permalinks'
    ]);

    Route::get('/about', [
        'as'   => 'info.about',
        'uses' => 'InfoController@about',
    ]);

    //permissions in controller
    Route::get('/integration/seat-fitting/fit', [
        'as'   => 'info.getFittingPluginFit',
        'uses' => 'InfoController@getFittingPluginFit',
    ]);
});