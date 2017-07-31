<?php
Route::group(
    ['prefix'     => 'image', 'as' => 'image.', 'namespace' => 'Minhbang\Image\Controllers',
     'middleware' => config('image.middlewares.api')],
    function () {
        Route::get('data', ['as' => 'data', 'uses' => 'ApiController@data']);
        Route::get('browse/{multi?}/{except?}', ['as' => 'browse', 'uses' => 'ApiController@browse']);
        Route::post('store', ['as' => 'store', 'uses' => 'ApiController@store']);
        Route::post('delete', ['as' => 'delete', 'uses' => 'ApiController@delete']);
    }
);

// Backend ---
Route::group(
    ['prefix'     => 'backend/image', 'as' => 'backend.image.', 'namespace' => 'Minhbang\Image\Controllers',
     'middleware' => config('image.middlewares.backend')],
    function () {
        Route::get('/', ['as' => 'index', 'uses' => 'BackendController@index']);
        Route::get('data', ['as' => 'data', 'uses' => 'BackendController@data']);
        Route::get('upload', ['as' => 'upload', 'uses' => 'BackendController@upload']);
        Route::get('{image}', ['as' => 'show', 'uses' => 'BackendController@show']);
        Route::get('{image}/edit', ['as' => 'edit', 'uses' => 'BackendController@edit']);
        Route::post('{image}/quick_update', ['as' => 'quick_update', 'uses' => 'BackendController@quickUpdate']);
        Route::post('{image}', ['as' => 'update', 'uses' => 'BackendController@update']);
        Route::delete('{image}', ['as' => 'destroy', 'uses' => 'BackendController@destroy']);
        Route::delete('batch/{ids}', ['as' => 'destroy_batch', 'uses' => 'BackendController@destroyBatch']);
    }
);