<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TransactionApiController;
use App\Http\Controllers\API\InstalmentController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register_user', [UserController::class, 'registerUser']); 
Route::post('login_user', [UserController::class, 'userLogin']);  
Route::get('get_all_lenders', [UserController::class, 'listMoneyLenders']); 
Route::post('purchase_new_loan', [TransactionApiController::class, 'purchaseNewLoan']);
Route::post('pay_instalment', [InstalmentController::class, 'payInstalment']);
Route::get('get_active_loan_transactions', [TransactionApiController::class, 'getActiveTransactions']);   
Route::get('get_all_transactions', [TransactionApiController::class, 'getAllTransactions']); 
Route::get('get_loan_requests', [TransactionApiController::class, 'listLenderRequests']);   
Route::post('approve_transaction', [TransactionApiController::class, 'approveTransactionByLender']);
Route::get('instalment_details', [InstalmentController::class, 'getInstalments']);




