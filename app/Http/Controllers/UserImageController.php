<?php

namespace App\Http\Controllers;

use App\Helpers\HelperMethods;
use App\Models\User;
use Illuminate\Http\Request;


class UserImageController extends Controller
{
    protected array $typeOfFields = ['imageFields'];

    protected $imageFields = ['image'];

    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'image' => 'nullable|max:2048', // probably should add 'image' rule if it's an image file
        ]);
    }
  
    public function update(Request $request)
    {
        try {
            $user = User::where('email', auth()->user()->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            $validated = $this->validateRequest($request);

            HelperMethods::populateModelFields(
                $user,
                $request,
                $validated,
                $this->typeOfFields,
                [
                    'imageFields' => $this->imageFields,
                ]
            );

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
}
