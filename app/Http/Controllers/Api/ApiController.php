<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class ApiController extends Controller
{
    // POST [ name, email, password ]
    public function register(Request $request){

        // Validation
        $request->validate([
            "name" => "required|string", 
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed"
        ]);

        //Create User
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);


        return response()->json([
            "status" => true,
            "message" => "User Registered Successfully",
            "data" => []
        ]);
    }

    //POST [ email, password ]
    public function login(Request $request){

        // Validation
        $request->validate([
            "email" => "required|email|string",
            "password" => "required"
        ]);

        // Email Check
        $user = User::where("email", $request->email)->first();

        if(!empty($user)){
            if(Hash::check($request->password, $user->password)){
                
              
                //  $user = Auth::user();
                $token = $user->createToken('MyAppToken')->accessToken;


                

                return response([
                    "status" => true,
                    "message" => "Login Successful",
                    "token" => $token,
                    "data" => []
                    
                ]);


            }else{

                return response([
                    "status" => false,
                    "message" => "Wrong Password",
                    "data" => []
                ]);


            }
        }else{

                return response([
                    "status" => false,
                    "message" => "Email Dont Exist",
                    "data" => []
                ]);
        }

        // Auth Token

    }

    //GET [ Auth: Token ]
    public function profile(Request $request){

        $userData = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile information",
            "data" => $userData

        ]);


    }

    //GET [ Auth: Token ]
    public function logout( Request $request){

        $token = auth()->user()->token();
        $token->revoke();
        return response()->json([
            "status" => true,
            "token_data" => $token,
            "message" => "user logout successfully"
        ]);
    }
}
