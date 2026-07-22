<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function token(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $now = time();
        $ttl = (int) config('auth.jwt_ttl_minutes');

        $payload = [
            'iss' => config('app.name'),
            'sub' => (string) $user->id,
            'roles' => ['user'],
            'iat' => $now,
            'exp' => $now + ($ttl * 60),
        ];

        $token = JWT::encode(
            $payload,
            config('auth.jwt_secret'),
            'HS256'
        );

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
        ]);
    }
}
