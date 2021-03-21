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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('auth/register', 'UserController@register');
Route::post('auth/login', 'UserController@login');

/*
Users routes
*/
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('user/list', 'UserController@getAllUsers');
    Route::get('user/profile', 'UserController@getUserProfile');
    Route::get('user/rented-books', 'BookController@rentedBooks');
});

/*
Books routes
*/
Route::group(['middleware' => 'jwt.auth'], function () {
    Route::post('add/book', 'BookController@addBook');
    Route::get('book/list', 'BookController@getAllBooks');
    Route::get('book/details/{book_id}', 'BookController@getBookDetails');
});

/*
Renting and returning books routes
*/
Route::group(['middleware' => 'jwt.auth'], function(){
	Route::post('book/rent', 'BookController@rentBook');
	Route::delete('book/return/{book_id}', 'BookController@returnBook');
});
