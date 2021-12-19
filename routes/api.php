<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('register','Api\AuthController@register');
Route::post('login','Api\AuthController@login');

Route::get('email/verify/{id}', 'Api\VerificationController@verify')->name('verificationapi.verify');
Route::get('email/resend', 'Api\VerificationController@resend')->name('verificationapi.resend');

//Product
Route::get('product','Api\ProductController@index');
Route::get('product/{id}','Api\ProductController@show');

Route::group(['middleware'=>'auth:api'],function(){

    // CRUD USER
    Route::get('user', 'Api\AuthController@index');
    Route::get('user/{id}', 'Api\AuthController@show');
    Route::delete('user/{id}', 'Api\AuthController@destroy');
    Route::put('user/{id}', 'Api\AuthController@update');
    Route::put('changepassword/{id}', 'Api\AuthController@updatePassword');
    Route::post('changeprofile/{id}', 'Api\AuthController@uploadProfilePict');
    Route::post('logout', 'Api\AuthController@logout');

    //CRUD PRODUCT
    Route::post('product', 'Api\ProductController@store');
    Route::delete('product/{id}', 'Api\ProductController@destroy');
    Route::put('product/{id}', 'Api\ProductController@update');
    Route::post('product/product_img/{id}', 'Api\ProductController@uploadPicture');

    //PLACEORDER
    Route::get('placeorder', 'Api\PlaceOrderController@index');
    Route::get('placeorder/{id}', 'Api\PlaceOrderController@show');
    Route::get('orderuser/{id_user}', 'Api\PlaceOrderController@showUserOrder');
    Route::post('placeorder', 'Api\PlaceOrderController@store');
    Route::delete('placeorder/{id}', 'Api\PlaceOrderController@destroy');
    Route::put('placeorder/{id}', 'Api\PlaceOrderController@update');
    Route::put('updatecart/{id}', 'Api\PlaceOrderController@updateCart');

    //TRANSACTION
    Route::get('transaction', 'Api\TransactionController@index');
    Route::post('transaction', 'Api\TransactionController@store');

    //BRANCH
    Route::get('branch', 'Api\BranchController@index');
    Route::get('branch/{id}', 'Api\BranchController@show');
    Route::post('branch', 'Api\BranchController@store');
    Route::put('branch/{id}', 'Api\BranchController@update');
    Route::delete('branch/{id}', 'Api\BranchController@destroy');
});
