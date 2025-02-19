<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {

    Route::post('/employee/create', [App\Http\Controllers\EmployeeController::class, '_create']);

    //Ledger Account
    Route::post('/ledger/account/create', [App\Http\Controllers\LedgerAccountController::class, '_create']);
    Route::post('/ledger/account/update/{id}',[App\Http\Controllers\LedgerAccountController::class, '_update']);
    Route::post('/ledger/account/delete',[App\Http\Controllers\LedgerAccountController::class, '_delete']);
    Route::get('/ledger/accounts',[App\Http\Controllers\LedgerAccountController::class, '_list']);

    //Ledger
    Route::post('/ledger/{id}/create', [App\Http\Controllers\LedgerController::class, '_create']);
    Route::post('/ledger/update/{id}',[App\Http\Controllers\LedgerController::class, '_update']);
    Route::post('/ledger/delete',[App\Http\Controllers\LedgerController::class, '_delete']);
    Route::get('/ledgers',[App\Http\Controllers\LedgerController::class, '_list']);
    
    //Ledger Entry
    Route::post('/ledger/{ledger_id}/entry/add',[App\Http\Controllers\LedgerEntryController::class, '_create']);
    Route::post('/ledger/entry/update/{id}',[App\Http\Controllers\LedgerEntryController::class, '_update']);
    Route::post('/ledger/entry/delete',[App\Http\Controllers\LedgerEntryController::class, '_delete']);
    Route::get('/ledger/{ledger_id}/entries',[App\Http\Controllers\LedgerEntryController::class, '_list']);
    
    /****** REVIEW *****/
    Route::prefix('review')->group(function () {
        
        
        //Ledger
        Route::get('/ledgers', [App\Http\Controllers\Review\LedgerController::class, '_list']);
        Route::post('/ledger/approve', [App\Http\Controllers\Review\LedgerController::class, '_approve']);
        Route::post('/ledger/reject',[App\Http\Controllers\Review\LedgerController::class, '_reject']);
    });
});