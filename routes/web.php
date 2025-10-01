<?php

use Illuminate\Support\Facades\Route;
use Mews\Captcha\Captcha;

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
    return view('auth.login');
});

Route::get('captcha/{config?}', [Captcha::class, 'create'])->name('captcha');
Route::get('captcha-refresh', function () {
    return response()->json(['captcha' => captcha_img()]);
});

Route::get('/get-kota/{provinsi}', function ($provinsi) {
    $kota = \App\Models\Wilayah::where('PROVINSI', $provinsi)->get();
    return response()->json($kota);
});

Route::get('/get-kecamatan', 'MerchantController@getKecamatan')->name('get.kecamatan');
Route::get('/get-kodepos', 'MerchantController@getKodePos')->name('get.kodepos');

Route::get('/transaction/detail/{id}', 'TransactionController@detail')->name('transactions.detail');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['auth']], function () {
    // Route::resource('roles','RoleController');
    // Route::resource('users','UserController');
    // Route::resource('products','ProductController');
    // Route::resource('permissions', 'PermissionController');

    //Product
    // Route::get('/products', 'ProductController@index')->name('products.index');
    // Route::get('/products/broadcast/{id}', 'ProductController@broadcast')->name('products.broadcast');
    // Route::get('/products/show/{id}', 'ProductController@show')->name('products.show');
    // Route::get('/products/{id}/edit', 'ProductController@edit')->name('products.edit');
    // Route::get('/products/create', 'ProductController@create')->name('products.create');
    // Route::put('/products/update/{product}', 'ProductController@update')->name('products.update');
    // Route::post('/products/store', 'ProductController@store')->name('products.store');
    // Route::delete('/products/{product}/destroy', 'ProductController@destroy')->name('products.destroy');

    //User
    Route::get('/users', 'UserController@index')->name('users.index');
    Route::post('/users', 'UserController@index')->name('users.search');
    Route::get('/users/show/{id}', 'UserController@show')->name('users.show');
    Route::get('/users/{id}/edit', 'UserController@edit')->name('users.edit');
    Route::get('/users/create', 'UserController@create')->name('users.create');
    Route::PUT('/users/update/{product}', 'UserController@update')->name('users.update');
    Route::post('/users/store', 'UserController@store')->name('users.store');
    Route::delete('/users/{product}/destroy', 'UserController@destroy')->name('users.destroy');

    //Role
    Route::get('/roles', 'RoleController@index')->name('roles.index');
    Route::get('/roles/show/{id}', 'RoleController@show')->name('roles.show');
    Route::get('/roles/{id}/edit', 'RoleController@edit')->name('roles.edit');
    Route::get('/roles/create', 'RoleController@create')->name('roles.create');
    Route::patch('/roles/update/{product}', 'RoleController@update')->name('roles.update');
    Route::post('/roles/store', 'RoleController@store')->name('roles.store');
    Route::delete('/roles/{product}/destroy', 'RoleController@destroy')->name('roles.destroy');

    //Permissions
    Route::get('/permissions', 'PermissionController@index')->name('permissions.index');
    Route::post('/permissions', 'PermissionController@index')->name('permissions.search');
    Route::get('/permissions/show/{id}', 'PermissionController@show')->name('permissions.show');
    Route::get('/permissions/{id}/edit', 'PermissionController@edit')->name('permissions.edit');
    Route::get('/permissions/create', 'PermissionController@create')->name('permissions.create');
    Route::put('/permissions/update/{permission}', 'PermissionController@update')->name('permissions.update');
    Route::post('/permissions/store', 'PermissionController@store')->name('permissions.store');
    Route::delete('/permissions/{permission}/destroy', 'PermissionController@destroy')->name('permissions.destroy');

    // QRIS
    Route::get('/qris', 'QrisController@index')->name('qris.index');
    Route::post('/qris/hit', 'QrisController@hit')->name('qris.hit');

    //Refund
    Route::get('/refund', 'RefundController@index')->name('refund.index');
    Route::post('/refund/hit', 'RefundController@hit')->name('refund.hit');

    //Merchant
    Route::get('/merchant', 'MerchantController@index')->name('merchant.index');
    Route::get('/merchantCategories', 'MerchantController@categories')->name('merchant.categories');
    Route::get('/merchantCategoriesCreate', 'MerchantController@categoriesCreate')->name('merchant.categoriesCreate');
    Route::get('/merchantCategoriesEdit{id}', 'MerchantController@categoriesEdit')->name('merchant.categoriesEdit');
    Route::put('/merchantCategories/update/{mcc}', 'MerchantController@update')->name('merchant.categoriesUpdate');
    Route::get('/merchant/show/{id}', 'MerchantController@show')->name('merchant.show');
    Route::get('/merchant/{id}/edit', 'MerchantController@edit')->name('merchant.edit');
    Route::get('/merchant/create', 'MerchantController@create')->name('merchant.create');
    Route::put('/merchant/update/{merchant}', 'MerchantController@update')->name('merchant.update');
    Route::post('/merchant/store', 'MerchantController@store')->name('merchant.store');
    Route::get('/merchant/{id}/delete', 'MerchantController@delete')->name('merchant.delete');
    Route::put('/merchant/destroy/{merchant}', 'MerchantController@destroy')->name('merchant.destroy');
    Route::post('/merchant/saldo', 'MerchantController@saldo')->name('merchant.saldo');
    Route::post('/merchant/mutasi', 'MerchantController@mutasi')->name('merchant.mutasi');
    Route::post('/merchant/cekNorek', 'MerchantController@rekening')->name('merchant.rekening');
    Route::post('/merchant/generateMid', 'MerchantController@generateMid')->name('merchant.generateMid');
    Route::get('/merchants/export', 'MerchantController@exportExcel')->name('merchants.export');
    Route::get('/merchantResend', 'MerchantController@resendShow')->name('merchant.resend');
    Route::get('/merchantResend/{nmid}', 'MerchantController@resendEmail')->name('merchant.resend.email');


    //transaction
    Route::get('/transaction', 'TransactionController@index')->name('transactions.index');
    Route::get('/transaction/get', 'TransactionController@data')->name('transactions.data');
    // Route::get('/transaction/detail', 'TransactionController@detail')->name('transactions.detail');
    // Route::get('/transaction', [TransactionController::class, 'index']);

    //Health Check Monitoring
    Route::get('/health', 'HealthController@index')->name('health.index');
    Route::get('/health/fetch-data', 'HealthController@fetchData')->name('health.fetchData');

});
