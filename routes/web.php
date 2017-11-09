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

Route::get('/', function () {
    return view('welcome2');
});

Route::get('/video', 'VideoController@index')->name("video.index");
Route::post('/video', 'VideoController@convert')->name("video.convert");
Route::post('/video/download', 'AudioController@download')->name("video.download");


Route::get('/audio', 'AudioController@index')->name('audio.index');
Route::post('/audio', 'AudioController@convert')->name("audio.convert");
Route::post('/audio/download', 'AudioController@download')->name("audio.download");

Route::get('/image', 'ImageController@index')->name('image.index');
Route::post('/image', 'ImageController@convert')->name('image.convert');
Route::post('/image/download', 'AudioController@download')->name("image.download");
