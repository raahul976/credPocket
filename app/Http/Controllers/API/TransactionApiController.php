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
}
