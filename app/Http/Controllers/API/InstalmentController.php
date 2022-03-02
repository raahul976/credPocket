<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Instalment;
use Illuminate\Http\Response;
use DB;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Logs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InstalmentController extends Controller
{
    /*
    Function to be called when a borrower deposits an instalment amount
    */
    public function payInstalment(Request $request){
        
        try {
            
            DB::beginTransaction();
            $loanTransaction = Transaction::Where(['id' => $request->transaction_id, 'sender_id' => $request->lender_id, 'receiver_id' => $request->borrower_id])->first()->toArray();
            if( empty($loanTransaction) ){

                DB::rollback();
                return response()->json([
                    'status'=> 0,
                    'message' => 'No Loan transaction found',
                    'data' => []
                ], 200);
                
            }else{                
                
                $checkInstalmentCount = Instalment::Select('instalment_count')->where(['transaction_id' => $request->transaction_id])->orderBy('instalment_count', 'DESC')->first();
                $checkInstalmentCount == null ? $checkInstalmentCount = 0 : $checkInstalmentCount->instalment_count;  
                //dd($checkInstalmentCount->instalment_count);           
                if( $loanTransaction['no_of_installments'] > $checkInstalmentCount->instalment_count ){                    
                    $instalment = new Instalment();
                    $instalment->transaction_id = $request->transaction_id;
                    $instalment->lender_id = $request->lender_id;
                    $instalment->borrower_id = $request->borrower_id;
                    $instalment->instalment_amount = $request->instalment_amount;
                    $instalment->instalment_count = intval($checkInstalmentCount->instalment_count) + 1 ;                   
                    $instalment->created_at = Carbon::now();
                    $instalment->save();
                    
                    if(!$instalment->id){ 
                        
                        DB::rollback();
                        return response()->json([
                            'status'=> 0,
                            'message' => 'Something went wrong'
                        ], 200);
                        
                    }else{
                        
                        DB::commit();
                        $successData[] = array(
                            
                            'instalment_id' => $instalment->id,
                            'amount' => $instalment->instalment_amount,
                            //'remaining_instalment' => int($loanTransaction['no_of_installments'] - $instalment->instalment_count),                       
                            
                        );
                        
                        return response()->json([
                            
                            'status'=> 1,
                            'message' => 'Instalment Paid Succesfully.',
                            'data' => $successData
                            
                        ], 200);
                        
                        
                    } 
                    
                    
                }else{
                    
                    $trans = Transaction::findOrFail($request->transaction_id);                  
                    $changeStatusOfTrans = $trans->update(["status" => 0]);
                    DB::commit();
                    return response()->json([
                        'status'=> 0,
                        'message' => 'Loan has already been paid',
                        'data' => []
                    ], 200);
                    
                }
                
                
            }
            
            
            
        } catch (\Exception $e) {
            
            DB::rollback();
            return response()->json([
                'status'=> 0,
                'error' => $e->getMessage(),
                'message' => 'Something went wrong'
            ], 200);
            
        }
        
    }
}
