<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('login', 'AuthController@login')->name('login');
Route::get('documents', 'API\DocumentController@index');
Route::get('manuals', 'API\ManualController@index');
Route::get('aircrafts', 'API\AircraftController@index');
Route::get('documents/search/{search}', 'API\DocumentController@search');

Route::group(['middleware' => 'auth:sanctum'], function () {
  Route::post('logout', 'AuthController@logout')->name('logout');
  Route::get('user', 'AuthController@user');
});

//Route::group(['middleware' => 'web'], function () {
//  Route::get('token', 'AuthController@token')->name('api.token');
//});
