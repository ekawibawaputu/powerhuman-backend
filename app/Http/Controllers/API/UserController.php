<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    //
    public function login (Request $request)
    {
        try {
            // Todo: validate request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Todo: Find user by email address
            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials)) {
                return ResponseFormatter::error("Unautorized", 401);
            }

            $user = User::where('email', $request->email)->first();
            if(!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid password');
            }
            // Todo: Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // Todo: Return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Login success');
        } catch (Exception $e) {
            return ResponseFormatter::error('Authentication failed');
        }
    }

    public function register (Request $request)
    {
        try {
            //Todo: validate request
            $request->validate([
               'name' => ['required','string','max:255'],
               'email' => ['required','string','email','max:255','unique:users'],
               'password' => ['required','string', new Password]
            ]);

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            // Generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // Return response
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Register success');
        } catch (Exception $error) {
            //Return error response
            return ResponseFormatter::error($error->getMessage());
        }
    }

    public function logout(Request $request)
    {
        // Todo: Revoke token
        $token = $request->user()->currentAccessToken()->delete();

        // Todo: Return response
        return ResponseFormatter::success($token, 'Logout Success');
    }

    public function fetch(Request $request)
    {
        // Todo: Get user
        $user = $request->user();

        // Todo:return Response
        return ResponseFormatter::success($user, 'Fetch Success');
    }
}
