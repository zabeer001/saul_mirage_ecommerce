<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
            Log::info('Callback request received', ['request' => $request->all()]); // Log the full request

            if (!$request->has('code')) {
                Log::warning('Missing authorization code in callback');
                return response()->json(['error' => 'Authorization code missing'], 400);
            }

            $googleUser = Socialite::driver('google')->stateless()->user();
            Log::info('Google user data', ['user' => $googleUser->getAttributes()]); // Log Google user details

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                Log::info('Existing user found', ['user_id' => $user->id]);
                $token = JWTAuth::fromUser($user);
            } else {
                Log::info('Creating new user', ['email' => $googleUser->email]);
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'user',
                    'image' => $googleUser->avatar ?? null,
                ]);
                $token = JWTAuth::fromUser($user);
                Log::info('New user created', ['user_id' => $user->id]);
            }

            Log::info('JWT token generated', ['token' => $token]);
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
            Log::error('Error in handleGoogleCallback: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 500);
        }
    }
}
