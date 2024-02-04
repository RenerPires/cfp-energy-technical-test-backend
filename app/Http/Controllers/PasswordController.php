<?php

namespace App\Http\Controllers;

use App\Services\PasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PasswordController extends Controller
{
    public function changeUserPassword(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required|string',
            'new_password' => 'required|string|confirmed',
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = self::userFromToken();
        $payload = $request->only(["password", "new_password"]);
        try {
            PasswordService::changePassword($user, $payload);
        } catch (BadRequestHttpException|Exception $exception) {
            return response()->json(["errors" => $exception->getMessage()], $exception->getCode());
        }
        return response()->json(["message" => "Password changed successfully"], Response::HTTP_OK);
    }
    public function forgotPassword(Request $request): JsonResponse
    {
        $payload = $request->only('email');

        $validateEmail = Validator::make($payload, [
            'email' => 'required|email'
        ]);

        if($validateEmail->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateEmail->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $token = PasswordService::forgotPassword($payload);
        $cookie = cookie('auth_token', $token, 60 * 24 * 7, secure: true);
        return response()->json([
                    'status' => true,
                    'message' => 'if you are registered using that credentials, you will receive a password reset email.'
                ], Response::HTTP_OK)->header("token-reset", $token)->withCookie($cookie);
    }
    public function resetPassword(string $token, Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required|string|confirmed',
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->only('password');

        try {
            $token = PasswordService::resetPassword($token, $payload);
        } catch (BadRequestHttpException|Exception $exception) {
            return response()->json(["errors" => $exception->getMessage()], $exception->getCode());
        }

        return response()->json([
            "status" => true,
            "message" => "Password reset successfully",
            "token" => $token
            ], Response::HTTP_OK);
    }
}

