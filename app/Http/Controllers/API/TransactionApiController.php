<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Response;
use DB;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Logs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Instalment;

class TransactionApiController extends Controller
{
    /*
    API Function to be called when a user borrows loan
     */
    public function purchaseNewLoan(Request $request){

        try{

            DB::beginTransaction(); 
            $ArrHeaders = getallheaders();
            $requestTokken = '';

            if(isset($ArrHeaders['authorization'])) {
                $requestTokken = explode(' ', $ArrHeaders['authorization'])[1];
            }

            if($request->type != '2' ){

                return response()->json([
                    'status'=> 0,
                    'message' => 'Invalid user role'
                ], 200);

            }else{
                
                $userDetails = User::where(['id' => $request->user_id, 'type' => $request->type ])->first()->toArray();                
                $newTransaction = new Transaction();
                $newTransaction->price = $request->principal_amount;
                $newTransaction->receiver_id = $request->user_id;
                $newTransaction->no_of_installments = $request->no_of_installments;
                $newTransaction->sender_id = $request->lender_id;
                $newTransaction->status = 1;
                $newTransaction->save();                
                
                if(!$newTransaction->id){ 
                                       
                    DB::rollback();
                    return response()->json([
                        'status'=> 0,
                        'message' => 'Something went wrong'
                    ], 200);
                    
                }else{

                    DB::commit();
                    $successData[] = array(
                        'user_id' => $newTransaction->receiver_id,
                        'amount' => $newTransaction->price, 
                        'no_of_installments' =>$newTransaction->no_of_installments,
                        'installment_amount' => floatval($newTransaction->price/$newTransaction->no_of_installments)                                                                                              
                    );
                    return response()->json([
                        'status'=> 1,
                        'message' => 'Transaction Completed.',
                        'data' => $successData
                    ], 200);
                    
                    
                }                                                                                                                     
                
            }
            
            
        }catch(\Exception $e){
            DB::rollback();            
            return response()->json([
                'status'=> 0,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'data' => []
            ], 200);
            
        }
    }

    public function getActiveTransactions(Request $request){

        try {     

            $user = User::where('id', $request->borrower_id)->first()->toArray();            
            if(intval($user['type']) == 2){

                $transactions = Transaction::where(['receiver_id' => $request->borrower_id, 'status' => 1])->orderBy('id', 'DESC')->get();
                $transactionList = [];
                foreach($transactions as $transaction){ 
                    $checkInstalmentCount = Instalment::Select('instalment_count')->where(['transaction_id' => $transaction->id])->orderBy('instalment_count', 'DESC')->first();
                
                    $checkInstalmentCount == null ? $checkInstalmentCount = 0 : $checkInstalmentCount = $checkInstalmentCount->instalment_count;     
                                   
                    $lender = User::where('id', $transaction->sender_id)->first()->toArray();
                        

                    $transactionList[] = array(                        

                        'transaction_id' => $transaction->id,
                        'lender_id' => $transaction->sender_id,
                        'receiver_id' => $transaction->receiver_id,
                        'amount' => $transaction->price,
                        'no_of_instalments' => $transaction->no_of_installments, 
                        'lender_name' => $lender['name'], 
                        'date' => $transaction->created_at->format('m/d/Y'),  
                        'status' => $transaction->status,   
                        'instalments_paid' => $checkInstalmentCount,                     
                                               

                    );
                }  
                
                return response()->json([
                    'status'=> 1,
                    'message' => 'Active Loan Transactions',
                    'data' => $transactionList
                ], 200);

                

            }else{

                return response()->json([
                    'status'=> 0,
                    'message' => 'Invalid user type',                    
                    'data' => []
                ], 200);
                


            }
            

        } catch (\Exception $e) {

            return response()->json([
                'status'=> 0,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'data' => []
            ], 200);
            
        }

    }

    public function getAllTransactions(Request $request){

        try {     

            $user = User::where('id', $request->borrower_id)->first()->toArray();            
            if(intval($user['type']) == 2){

                $transactions = Transaction::where(['receiver_id' => $request->borrower_id])->orderBy('id', 'DESC')->get();
                $transactionList = [];
                foreach($transactions as $transaction){    
                                   
                    $lender = User::where('id', $transaction->sender_id)->first()->toArray();
                        

                    $transactionList[] = array(                        

                        'transaction_id' => $transaction->id,
                        'lender_id' => $transaction->sender_id,
                        'receiver_id' => $transaction->receiver_id,
                        'amount' => $transaction->price,
                        'no_of_instalments' => $transaction->no_of_installments, 
                        'lender_name' => $lender['name'], 
                        'date' => $transaction->created_at->format('m/d/Y'),   
                        'status' => $transaction->status,                    
                                               

                    );
                }  
                
                return response()->json([
                    'status'=> 1,
                    'message' => 'Active Loan Transactions',
                    'data' => $transactionList
                ], 200);

                

            }else{

                return response()->json([
                    'status'=> 0,
                    'message' => 'Invalid user type',                    
                    'data' => []
                ], 200);
                


            }
            

        } catch (\Exception $e) {

            return response()->json([
                'status'=> 0,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'data' => []
            ], 200);
            
        }

    }
}
