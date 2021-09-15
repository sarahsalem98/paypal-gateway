<?php

use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/','App\Http\Controllers\PaypalController@index')->name('index');

Route::get('/paypal_chechout','App\Http\Controllers\PaypalController@getExpressCheckout')->name('paypal_chechout');
Route::get('/paypal/ec-checkout-success','App\Http\Controllers\PaypalController@getExpressCheckoutSuccess');