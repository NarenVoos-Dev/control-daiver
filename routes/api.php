<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PosApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/pos/search-products', [PosApiController::class, 'searchProducts']);
    Route::post('/pos/store-sale', [PosApiController::class, 'storeSale']);
    Route::get('/pos/search-clients', [PosApiController::class, 'searchClients']);
    Route::post('/pos/store-client', [PosApiController::class, 'storeClient']);
    
    Route::get('/pos/clients/{client}/credit-details', [PosApiController::class, 'getClientCreditDetails']);

});