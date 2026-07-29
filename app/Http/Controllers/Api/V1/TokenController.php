<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IssueTokenRequest;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TokenController extends Controller
{
    public function store(IssueTokenRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $now = time();
        $ttl = (int) config('jwt.ttl_minutes');
        $secret = (string) config('jwt.secret');

        if (strlen($secret) < 32) {
            abort(500, 'JWT signing key is not configured securely.');
        }

        $payload = [
            'iss' => config('jwt.issuer'),
            'aud' => config('jwt.audience'),
            'sub' => (string) $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'roles' => $user->roles ?? ['user'],
            'jti' => (string) Str::uuid(),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + ($ttl * 60),
        ];

        $token = JWT::encode(
            $payload,
            $secret,
            'HS256'
        );

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
        ]);
    }
}
