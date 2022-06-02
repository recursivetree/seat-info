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

    Route::get('/article/edit/{id}', [
        'as'   => 'info.edit_article',
        'uses' => 'InfoController@getEditView',
        'middleware' => 'can:info.article.edit,id'
    ]);

    Route::get('/article/list', [
        'as'   => 'info.list',
        'uses' => 'InfoController@getListView',
    ]);

    Route::get('/manage', [
        'as'   => 'info.manage',
        'uses' => 'InfoController@getManageView',
        'middleware' => 'can:info.manage_article'
    ]);

    Route::get('/article/view/{id}', [
        'as'   => 'info.view',
        'uses' => 'InfoController@getArticleView',
        'middleware' => 'can:info.article.view,id'
    ]);

    Route::post('/resource/upload', [
        'as'   => 'info.upload_resource',
        'uses' => 'InfoController@uploadResource',
        'middleware' => 'can:info.edit_resource'
    ]);

    Route::get('/resource/{id}', [
        'as'   => 'info.view_resource',
        'uses' => 'InfoController@viewResource',
    ]);

    Route::post('/resource/delete', [
        'as'   => 'info.delete_resource',
        'uses' => 'InfoController@deleteResource',
        'middleware' => 'can:info.delete_resource'
    ]);

    Route::post('/article/manage/set_home_article', [
        'as'   => 'info.set_home_article',
        'uses' => 'InfoController@setHomeArticle',
        'middleware' => 'can:info.configure_home_article'
    ]);

    Route::post('/article/manage/delete', [
        'as'   => 'info.delete_article',
        'uses' => 'InfoController@deleteArticle',
        'middleware' => 'can:info.article.edit,id'
    ]);

    Route::post('/article/manage/unset_home_article', [
        'as'   => 'info.unset_home_article',
        'uses' => 'InfoController@unsetHomeArticle',
        'middleware' => 'can:info.configure_home_article'
    ]);

    Route::post('/article/manage/set_public', [
        'as'   => 'info.set_article_public',
        'uses' => 'InfoController@setArticlePublic',
        'middleware' => 'can:info.article.edit,id'
    ]);

    Route::post('/article/manage/set_private', [
        'as'   => 'info.set_article_private',
        'uses' => 'InfoController@setArticlePrivate',
        'middleware' => 'can:info.article.edit,id'
    ]);

    Route::get('/about', [
        'as'   => 'info.about',
        'uses' => 'InfoController@about',
    ]);
});