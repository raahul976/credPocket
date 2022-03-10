<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Logs;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserController extends Controller
{

    /*
    API Function to be called when a user registers
     */
    public function registerUser(Request $request){       
               
        try{
            DB::beginTransaction();            
            if(in_array(null, $request->all() )){
                return response()->json([
                    'status'=> 0,
                    'message' => 'Please provide valid input'
                ], 200);
            }
            else{
                 $userCheck = User::Where('email', $request->email)->get();
                if(!$userCheck->isEmpty()){
                    return response()->json([
                        'status'=> 0,
                        'message' => 'Email already Exists',
                        'data' => []
                    ], 200);
                }
                $token = Hash::make(Str::random(25)).Hash::make(Str::random(25));
                $user = new User;
                $user->name = $request->name;
                $user->mobile = $request->mobile;       
                $user->email = $request->email;                    
                $user->type = $request->type;
                $user->password = encrypt($request->password);
                $user->api_token = $token;             
                $user->save();
                
                if(!$user->id){
                    DB::rollback();
                    return response()->json([
                        'status'=> 0,
                        'message' => 'Something went wrong'
                    ], 200);
                }
                else{                    
                    DB::commit();                    
                    $successData[] = array(
                        'user_id' => $user->id,
                        'name' => $user->name, 
                        'email' =>$user->email,
                        'mobile' => $user->mobile,
                        'api_token' => $user->api_token,                                                                                              
                    );
                    return response()->json([
                        'status'=> 1,
                        'message' => 'Registration Successful.',
                        'data' => $successData
                    ], 200);
                }
            }
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json([
                'status'=> 0,
                'error' => $e->getMessage(),
                'message' => 'Something went wrong'
            ], 200);
        } 
        
    }

    /*
    API Function to be called on user login
     */
    public function userLogin(Request $request){

        try{            
            
            $ArrHeaders = getallheaders();
            $requestTokken = '';
            if(isset($ArrHeaders['authorization'])) {
                $requestTokken = explode(' ', $ArrHeaders['authorization'])[1];
            }
            $checkUserTokken = User::where('mobile',$request->mobile)->first();
            $decrypted_pass = decrypt($checkUserTokken->password);             
            if($request->password != $decrypted_pass ){
                return response()->json([
                    'status'=> 0,
                    'message' => 'Invalid Username Or Password'
                ], 200);
            }
            else{

                $token = Hash::make(Str::random(25)).Hash::make(Str::random(25));                
                $customerUpdateQry = User::where('mobile', $request->mobile)->where('type', $request->type)->update(['api_token' => $token]);
                $customerDetails = User::where('mobile', $request->mobile)->where('type', $request->type)->first();
                if($customerDetails == null){

                    return response()->json([
                        'status'=> 0,
                        'message' => 'Invalid user type provided'
                    ], 200);

                }             
                $customerData[] = array(
                    'user_id' => $customerDetails->id,
                    'mobile' =>$customerDetails->mobile,
                    'name' => $customerDetails->name,
                    'mobile' => $customerDetails->mobile,
                    'email' => $customerDetails->email,                    
                );
                
            }
            return response()->json([
                'status'=> 1,
                'message' => 'Logged In ',
                'data' => array(array( 
                    'user_data' => $customerData,
                    'api_token' => $token
                )),
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'status'=> 0,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
                'data' => []
            ], 200);
        }

    }

    /* 
    List all users with type as (1) i.e. money lenders
    */
    public function listMoneyLenders(){
        try{

            $lenders = User::where('type', 1)->get();            
            if($lenders->isEmpty()){
                return response()->json([
                    'status'=> 1,
                    'message' => 'No money lenders to list',
                    'data' =>[]
                ], 200);
            }
            $lendersList = [];
            foreach($lenders as $lender){
                $lendersList[] = array(
                    'user_id' => $lender->id,
                    'user_name' => $lender->name
                );
            }
            return response()->json([
                'status'=> 1,
                'message' => 'Money Lenders list',
                'data' => $lendersList
            ], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'status'=> 0,
                'error' => $e->getMessage(),
                'message' => 'Something went wrong'
            ], 200);
        }
    }
    
}
