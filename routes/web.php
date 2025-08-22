<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PosApiController;
use App\Http\Middleware\CheckPosAccess;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
    
});

Route::get('sales/{sale}/receipt', [ReceiptController::class, 'print'])->name('sales.receipt.print');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        //return view('dashboard');
         return redirect()->route('pos.index');
    })->name('dashboard');
});

//Ruta pos

Route::middleware(['auth', CheckPosAccess::class])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('index');
    Route::get('/sales', [PosController::class, 'salesList'])->name('sales.list');
    Route::get('/accounts-receivable', [PosController::class, 'accountsReceivable'])->name('accounts.receivable');
    Route::get('/accounts-receivable/{client}', [PosController::class, 'clientStatement'])->name('accounts.client.statement');

});