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
    if(Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

//Route group that needs to be authed in order to access
Route::group(['middleware' => ['auth']], function() {
    //Admin Panel
    Route::get('/admin/add/bot', 'AdminController@displayBotRegistration');
    Route::post('/admin/add/bot', 'AdminController@storeBot');
    Route::get('/admin/remove/bot', 'AdminController@displayBotRemoval');
    Route::Post('/admin/remove/bot', 'AdminController@removeBot');
    Route::get('/admin/add/user', 'AdminController@displayUserRegistration');
    Route::post('/admin/add/user', 'AdminController@storeUser');
    Route::get('/admin/remove/user', 'AdminController@displayRemoveUser');
    Route::post('/admin/remove/user', 'AdminController@removeUser');

    //Relay Panel
    Route::get('/relay/new', 'RelayController@displayRelay');
    Route::post('/relay/new', 'RelayController@storeRelay');
});

Route::get('/login', 'Auth\LoginController@redirectToProvider')->name('login');
Route::get('/callback', 'Auth\LoginController@handleProviderCallback')->name('callback');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');