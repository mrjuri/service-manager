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

$user = \App\Model\User::take(1)->first();

Auth::routes([
    'register' => !$user ? true : false,
    'reset' => false,
]);

if (!$user) {
    Route::get('/', function (){
        return redirect('/register');
    })->name('home');

} else {

    Route::get('/', 'Dashboard@view')
         ->name('home');
}

Route::get('/service', 'Service@index')
     ->name('service.list');
Route::get('/service/create', 'Service@create')
     ->name('service.create');
Route::post('/service/store', 'Service@store')
     ->name('service.store');
Route::get('/service/edit/{id}', 'Service@edit')
     ->name('service.edit');
Route::post('/service/update/{id}', 'Service@update')
     ->name('service.update');
Route::get('/service/destroy/{id}', 'Service@destroy')
     ->name('service.destroy');

Route::get('/customer', 'Customer@index')
     ->name('customer.list');
Route::get('/customer/create', 'Customer@create')
     ->name('customer.create');
Route::post('/customer/store', 'Customer@store')
     ->name('customer.store');
Route::get('/customer/edit/{id}', 'Customer@edit')
     ->name('customer.edit');
Route::post('/customer/update/{id}', 'Customer@update')
     ->name('customer.update');
Route::get('/customer/destroy/{id}', 'Customer@destroy')
     ->name('customer.destroy');
Route::get('/customer/renew/{id}', 'Customer@renew')
     ->name('customer.renew');

/*Route::get('/setting', 'Setting@create')
     ->name('setting.create');
Route::post('/setting/store', 'Setting@store')
     ->name('setting.store');*/

Route::get('/mail/service-expiration/all', 'Email@sendExpirationList')
     ->name('email.exp.all');
Route::get('/mail/service-expiration/{id}', 'Email@sendExpirationService')
     ->name('email.exp');
Route::get('/mail/show/{view}/{sid}', 'Email@show')
     ->name('email.show');

Route::post('/fattureincloud/create', 'FattureInCloudAPI@create')
     ->name('fattureincloud.api.create');

Route::get('/payment/checkout/{sid}', 'Payment@show')
     ->name('payment.checkout');
Route::post('/payment/checkout/update/{sid}', 'Payment@update')
     ->name('payment.update');
Route::get('/payment/confirm/{sid}', 'Payment@confirm')
     ->name('payment.confirm');

Route::get('/gsheets/update', 'GoogleSheetsAPI@update');
