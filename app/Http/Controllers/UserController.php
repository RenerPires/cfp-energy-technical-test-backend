<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserController extends Controller
{
    public function getUsers(): JsonResponse
    {
        // TODO: Search by query params
        // TODO: Validate the request

        $users = UserService::getAllUsers();
        return (UserResource::collection($users))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
    }
    public function getUserById(string $userId): JsonResponse
    {
        // TODO: Validate the request

        try {
            $user = UserService::findUserById($userId);
        } catch (NotFoundResourceException $exception) {
            return response()->json(['errors' => $exception->getMessage()], $exception->getCode());
        }

        return (new UserResource($user))
                ->response()
                ->setStatusCode(Response::HTTP_OK);

    }
    public function createUser(Request $request): JsonResponse
    {
        // TODO: Validate the request

        $payload = $request->only(["first_name", "last_name", "username", "date_of_birth", "email", "password", "phone_number"]);

        try {
            $model = UserService::createUser($payload);
        } catch (Exception $exception) {
            return response()->json(["errors" => $exception->getMessage()], $exception->getCode());
        }

        return response()
                ->json(["data" => $model], Response::HTTP_CREATED)
                ->header("Location", $model->id);
    }
    public function updateUser(string $userId, Request $request): JsonResponse
    {
        // TODO: Validate the request

        $payload = $request->only(["first_name", "last_name", "username", "date_of_birth", "email", "phone_number"]);

        try {
            $model = UserService::updateUser($userId, $payload);
        } catch (NotFoundResourceException|Exception $exception) {
            return response()->json(["errors" => $exception->getMessage()], $exception->getCode());
        }

        return response()->json(["data" => $model], Response::HTTP_OK);
    }
    public function deleteUser(string $userId): JsonResponse
    {
        // TODO: Validate the request
        try {
            UserService::deleteUser($userId);
        } catch (NotFoundResourceException|Exception $exception) {
            return response()->json(["errors" => $exception->getMessage()], $exception->getCode());
        }
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
