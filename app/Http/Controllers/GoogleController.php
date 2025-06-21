<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class GoogleController extends Controller
{

    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Validate the code parameter
            if (!$request->has('code')) {
                return response()->json(['error' => 'Authorization code missing'], 400);
            }

            // Retrieve Google user
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find user by google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // If user exists, generate JWT token
                $token = JWTAuth::fromUser($user);
            } else {
                // If user does not exist, create a new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'user',
                    'image' => $googleUser->avatar ?? null,
                ]);

                // Generate JWT token for new user
                $token = JWTAuth::fromUser($user);
            }

            // Return token and user details
            return response()->json([
                'message' => 'Google authentication successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'image' => $user->image,
                    'phone' => $user->phone,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }
}
