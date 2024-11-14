<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{

    public function __construct()
{
    // Apply auth middleware except for the register method
    $this->middleware('auth:sanctum', ['except' => ['login', 'register']]);
}
    public function register(Request $request){
        $registerUserData = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|min:8'
        ]);
        $user = User::create([
            'name' => $registerUserData['name'],
            'email' => $registerUserData['email'],
            'role_id' => 1,
            'is_active' => 1,
            'is_deleted' => 1,
            'password' => Hash::make($registerUserData['password']),
        ]);
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User Created',
            'user' => $user,
            'token' => $token,  // Return token if needed
        ], 201);
        // return response()->json([
        //     'message' => 'User Created ',
        // ]);
    }

    public function login(Request $request){
        $loginUserData = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);
        $user = User::where('email',$loginUserData['email'])->first();
        if(!$user || !Hash::check($loginUserData['password'],$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $token = $user->createToken($user->name.'-AuthToken')->plainTextToken;
        return response()->json([
            'user' => $user,
            'access_token' => $token,

        ]);
    }

    public function logout() {
        auth()->user()->currentAccessToken()->delete();
    
        return response()->json([
            "message" => "logged out"
        ]);
    }
    
}

