<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only(["email", "password"]);

        $validateCredentials = Validator::make($credentials, [
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        if($validateCredentials->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateCredentials->errors()
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'wrong credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->tokenResponse($token);
    }
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }
    public function logout(Request $request): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => 'logged out  successfully'], Response::HTTP_OK);
    }
    public function refresh(): JsonResponse
    {
        return $this->tokenResponse(auth()->refresh());
    }
    protected function tokenResponse($token) : JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'login successfully',
            'token_type'   => 'bearer',
            'access_token' => $token,
            'user'         => auth()->user(),
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
