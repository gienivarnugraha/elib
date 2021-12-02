<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::group(['namespace' => 'API'], function () {
  Route::get('search', 'SearchController@search')->name('api.search');

  Route::group(['middleware' =>  ['auth:sanctum']], function () {
    Route::get('notifications/{page}', 'NotificationController@paginate')->where('page', '[0-9]+');
    Route::get('notifications', 'NotificationController@index');

    //Route::get('manuals/{id}/order/', 'ManualController@orderHistory');
    Route::get('documents/file/{id}', 'DocumentController@openFile');
    Route::post('documents/file/', 'DocumentController@addFile');
    Route::put('documents/status/{id}', 'DocumentController@status');
    Route::post('documents/revisions', 'DocumentController@addRevision');
    Route::get('documents/kpi/', 'DocumentController@countKpi');

    Route::get('manuals/file/{id}', 'ManualController@openFile');
    Route::post('manuals/file/', 'ManualController@addFile');
    Route::post('manuals/revisions', 'ManualController@addRevision');
    Route::put('manuals/status/{id}', 'ManualController@status');
    Route::post('manuals/order', 'ManualController@addOrder');

    Route::put('users/status/{id}', 'UserController@status');

    Route::apiResource('documents', 'DocumentController')->except(['index']);
    Route::apiResource('aircrafts', 'AircraftController')->except(['index']);
    Route::apiResource('manuals', 'ManualController')->except(['index']);

    Route::apiResources([
      'users' => 'UserController',
    ]);
  });
});

/* Route::fallback(function () {
  return response()->json([
    'error' => 'Page Not Found. '
  ], 404);
}); */
//404 JSON response for page not found
/* ; */
