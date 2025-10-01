<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GetLokasiController;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });



// Route::post('/merchants', 'API\MerchantClientController@detailQris')->name('merchant_client');


// Route::get('/merchants', 'API\client\MerchantClientController@apiIndex')->name('api.merchant.index');  
// Route::post('/merchants/store', 'API\client\MerchantClientController@apiStore')->name('api.merchant.store');  
// Route::post('/qris/get', 'API\client\QrisClientController@get')->name('api.qris.get');  
// Route::post('/refund/get', 'API\client\RefundClientController@get')->name('api.refund.get');  


Route::post('/detail_qris', 'API\QrisController@detailQris')->name('detail_qris');
// Route::get('/getLokasi', 'API\GetLokasiController@getLokasi')->name('getLokasi');
Route::get('/getLokasi/{provinsi}', 'API\GetLokasiController@getLokasi')->name('getLokasi');

Route::group(['middleware' => ['cors']], function () {
    //
    Route::post('/register', 'API\AuthController@register');
    Route::post('/gettoken', 'API\AuthController@getToken')->name('gettoken');
    
    Route::group(['middleware' => ['check.auth']], function () {
        Route::get('/product', 'API\ProductController@index')->name('api.product');
        Route::post('/qris', 'API\QrisController@store')->name('api.qris');
        Route::post('/refund', 'API\RefundController@store')->name('api.refund');
        Route::get('/tranmain', 'API\TranmainController@get')->name('api.tranmainget');
        
        Route::get('/merchants', 'API\client\MerchantClientController@apiIndex')->name('api.merchant.index');  
        Route::post('/merchants/store', 'API\client\MerchantClientController@apiStore')->name('api.merchant.store');  
        Route::post('/qris/get', 'API\client\QrisClientController@get')->name('api.qris.get');  
        Route::post('/refund/hit', 'API\client\RefundClientController@get')->name('api.refund.hit');  
        Route::post('/transaction/get', 'API\client\TransactionClientController@index')->name('api.transaction.get');  
        

        Route::middleware('auth:api')->post('password/change', 'API\AuthController@changePassword');

        
    });

});
