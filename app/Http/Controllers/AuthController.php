<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

        if(!auth()->user()->is_active){
            auth()->logout();
            return response()->json([
                'status' => false,
                'message' => 'you account is inactive, please contact support'
            ], Response::HTTP_FORBIDDEN);
        }

        $cookie = cookie('auth_token', $token, 60 * 24 * 7, secure: true);

        return $this->tokenResponse($token)->withCookie($cookie);
    }
    public function signup(Request $request): JsonResponse
    {
        $request = array_merge($request, [
            "id" => Uuid::uuid4()->toString(),
            "password" => Hash::make($request["password"]),
        ]);
        try {
            $user = User::create(Arr::only($request, ["id", "first_name", "last_name", "username", "date_of_birth", "email", "password", "phone_number"]));
            $user->update(["profile_picture_url" => "https://ui-avatars.com/api/?name={$user->first_name}+{$user->last_name}&background=random&format=png"]);
            $user->assignRole('user');
            return $user;
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when signing up: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }
    public function logout(): JsonResponse
    {
        auth()->logout();
        cookie('auth_token', null);
        return response()->json(['message' => 'logged out  successfully'], Response::HTTP_OK);
    }
    public function refresh(): JsonResponse
    {
        $token = auth()->refresh();
        $cookie = cookie('auth_token', $token, 60 * 24 * 7, secure: true);
        return $this->tokenResponse($token)->withCookie($cookie);
    }
    protected function tokenResponse($token) : JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'login successfully',
            'token_type'   => 'bearer',
            'access_token' => $token,
            'user'         => new UserResource(auth()->user()),
            'expires_in'   => auth()->factory()->getTTL() * 60
        ]);
    }
}
