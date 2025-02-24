<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //Employee
    Route::get('/employee/create', [App\Http\Controllers\EmployeeController::class, 'create']);
    Route::get('/employee/{id}', [App\Http\Controllers\EmployeeController::class, 'display']);
    Route::get('/employees', [App\Http\Controllers\EmployeeController::class, 'list']);
    Route::get('/employee/bulk/upload', function(){
        return view('employee/bulk/upload');
    });
    Route::post('/employee/bulk/review', [App\Http\Controllers\EmployeeController::class, 'bulk_review']);

    //Ledger Account
    Route::get('/ledger/accounts', [App\Http\Controllers\LedgerAccountController::class, 'list'])->name('account_ledger');
    Route::get('/ledger/account/create', [App\Http\Controllers\LedgerAccountController::class, 'create'])->name('account_ledger');
    Route::get('/ledger/account/{id}', [App\Http\Controllers\LedgerAccountController::class, 'display'])->name('account_ledger');

    //Ledger
    Route::get('/ledger/{id}', [App\Http\Controllers\LedgerController::class, 'display'])->name('account_ledger');
    Route::get('/ledger/print/{id}',[App\Http\Controllers\LedgerController::class, 'print'])->name('account_ledger');

    //Ledger Entry
    Route::get('/ledger/entry/{id}', [App\Http\Controllers\LedgerEntryController::class, 'display'])->name('account_ledger');;


    /****** REVIEW *****/
    Route::get('/review',function(){

        return view('/review/dashboard');

    })->name('review');

    Route::prefix('/review')->group(function () {
        
        
        //Ledger Entries
        Route::get('/ledger/entry/{id}', [App\Http\Controllers\Review\LedgerEntryController::class, 'display'])->name('review');
        Route::get('/ledger/entries', [App\Http\Controllers\Review\LedgerEntryController::class, 'list'])->name('review');

        
        //Ledgers
        Route::get('/ledgers', [App\Http\Controllers\Review\LedgerController::class, 'list'])->name('review');
        Route::get('/ledger/{id}', [App\Http\Controllers\Review\LedgerController::class, 'display'])->name('review');
        
        

        
    });
});


Route::get('adarna.js', function(){

    $response = Response::make(File::get(base_path('node_modules/adarna/build/adarna.js')), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});


Route::get('adarna.js.map', function(){

    $response = Response::make(File::get(base_path('node_modules/adarna/build/adarna.min.js.map')), 200);
    $response->header("Content-Type", 'text/javascript');

    return $response;
});

require __DIR__.'/auth.php';
