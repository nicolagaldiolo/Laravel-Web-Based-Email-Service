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


Route::get('test', function(){
    phpinfo();
    //dd()
    //if (function_exists('imap_open')) {
    //    echo "IMAP functions are available.<br />\n";
    //} else {
    //    echo "IMAP functions are not available.<br />\n";
    //}
    //return imap_open();
});
Route::get('auth/login', 'Auth\AuthController@showLoginForm');
Route::get('auth/logout', 'Auth\AuthController@getLogout')->name('logout');
Route::post('auth/login', 'Auth\AuthController@postLoginGMail')->name('login');

Route::group(['middleware' => ['auth']], function(){

    Route::get('inbox', 'InboxController@getInbox')->name('inbox');
    Route::get('inbox/delete/{id}', 'InboxController@getDelete')->where('id', '[0-9]+')->name('delete');

    Route::get('read/{id}', 'InboxController@getMessage')->where('id', '[0-9]+')->name('read');
    Route::get('read/{id}/attachment/{partId}', 'InboxController@getAttachment')->where('id', '[0-9]+')->where('partId', '[0-9]+(\.[0-9]+)*')->name('read.attachment');

    Route::get('compose/{id?}', 'InboxController@getCompose')->where('id', '[0-9]+')->name('compose');
    Route::post('compose/send', 'InboxController@postSend')->name('compose.send');


});