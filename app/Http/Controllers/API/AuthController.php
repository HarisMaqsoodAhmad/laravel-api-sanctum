<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    // Function to register
    public function signup(Request $request){
        try{
            $validateUser = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|max:15',
            ]);
    
            // Check Validation
            if( $validateUser->fails() ){
                return response()->json([
                    'status'  => false, 
                    'message' => 'Validation Error',
                    'errors'  => $validateUser->errors(),
                ], 401);
            }
    
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
            ]);
    
            if( $user ){
                return response()->json([
                    'status'  => true,
                    'message' => 'User has been created successfully.',
                    'data ' => $user,
                ], 200);
            }
        } catch(Exception $e){
            return response()->json([
                'status'  => false, 
                'message' => 'Something Wrong with API',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    // Function to Login
    public function login(Request $request){
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);
    
            // Check Validation
            if( $validateUser->fails() ){
                return response()->json([
                    'status'  => false, 
                    'message' => 'Authentication Fails',
                    'errors'  => $validateUser->errors()->all(),
                ], 400);
            }

            
            if( Auth::attempt(['email' => $request->email, 'password' => $request->password]) ){
                $authUser = Auth::user();
                $token = $authUser->createToken("API Token")->plainTextToken;

                return response()->json([
                    'status'  => true, 
                    'message' => 'User Logged in Successfully',
                    'token' => $token,
                    'token_type' => 'bearer',
                ], 200);
            }
            return response()->json([
                'status'  => false, 
                'message' => "Email & Password doesn't matched",
            ], 401);
        } catch (Exception $e){
            return response()->json([
                'status'  => false, 
                'message' => 'Something Wrong with API',
                'errors'  => $e->getMessage(),
            ], 400);
        }
    }

    // Function to logout
    public function logout(Request $request){
        try{
            $user = $request->user();
            // Revoke all tokens...
            $user->tokens()->delete();

            return response()->json([
                'status'  => true, 
                'message' => 'User has been Logged out Successfully',
                'data' => $user,
            ], 200);
        } catch (Exception $e){
            return response()->json([
                'status'  => false,
                'message' => 'Something is wrong with API.',
                'error'  => $e->getMessage(),
            ], 400);
        }
    }
}
