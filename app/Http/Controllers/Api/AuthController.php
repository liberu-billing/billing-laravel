

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function revokeToken(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Token revoked successfully']);
    }

    public function revokeAllTokens(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'All tokens revoked successfully']);
    }
}