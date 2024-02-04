<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUsers(): JsonResponse
    {
        try {
            $users = UserService::getAllUsers();
        } catch (Exception $exception) {
            return response()->json([
                'status' => false,
                'errors' => $exception->getMessage()
            ], $exception->getCode());
        }

        return (UserResource::collection($users))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }
    public function getUserById(string $userId): JsonResponse
    {
            $validated = Validator::make(
                ['userId' => $userId],
                ['userId' => 'required|string|uuid'],
            );

            if($validated->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validated->messages()
                ], 422);
            }

        try {
            $user = UserService::findUserById($userId);
        } catch (NotFoundResourceException $exception) {
            return response()->json([
                'status' => false,
                'errors' => $exception->getMessage()
            ], $exception->getCode());
        }

        return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);

    }
    public function createUser(Request $request): JsonResponse
    {
        $payload = $request->only(["first_name", "last_name", "username", "date_of_birth", "email", "password", "phone_number"]);

        $validated = Validator::make($payload, [
            'first_name' => 'required|string|min:3|max:30',
            'last_name' => 'required|string|min:3|max:30',
            'username' => 'required|string|min:3|max:15|regex:/^\S*$/u|unique:users,username',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:users,email',
            'password' => [
                Password::min(8)   // must be at least 8 characters in length
                    ->mixedCase()       // must contain mixed case
                    ->numbers()         // must contain at least one digit
                    ->symbols()         // must contain a special character
                    ->uncompromised(),  // must not be a known compromised password
            ],
            'phone_number' => [
                'required',
                'regex:/^\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$/'
            ],
        ]);

        if($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validated->messages()
            ], 422);
        }

        try {
            $user = UserService::createUser($payload);
            $user->assignRole('user');
        } catch (Exception $exception) {
            return response()->json([
                'status' => false,
                'errors' => $exception->getMessage()
            ], $exception->getCode());
        }

        return response()
                ->json(["data" => $user], Response::HTTP_CREATED)
                ->header("Location", $user->id);
    }
    public function updateUser(string $userId, Request $request): JsonResponse
    {
        $payload = $request->only(["first_name", "last_name", "username", "date_of_birth", "email", "phone_number"]);

        $validated = Validator::make(array_merge($payload, ["userId" => $userId]), [
            'userId' => 'required|string|uuid',
            'first_name' => 'string|min:3|max:30',
            'last_name' => 'string|min:3|max:30',
            'username' => 'string|min:3|max:15|regex:/^\S*$/u|unique:users,username,' . $userId,
            'date_of_birth' => 'date',
            'email' => 'email|unique:users,email,' . $userId,
            'phone_number' => [
                'regex:/^\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$/'
            ],
        ]);

        if($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validated->messages()
            ], 422);
        }

        try {
            $model = UserService::updateUser($userId, $payload);
        } catch (NotFoundResourceException|Exception $exception) {
            return response()->json([
                'status' => false,
                "errors" => $exception->getMessage()
            ], $exception->getCode());
        }

        return response()->json(["data" => $model], Response::HTTP_OK);
    }
    public function deleteUser(string $userId): JsonResponse
    {
        $validated = Validator::make([$userId], [
            'userId' => 'required|string|uuid',
        ]);

        if($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validated->messages()
            ], 422);
        }

        try {
            UserService::deleteUser($userId);
        } catch (NotFoundResourceException|Exception $exception) {
            return response()->json([
                'status' => false,
                'errors' => $exception->getMessage()
            ], $exception->getCode());
        }
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
