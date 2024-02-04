<?php

namespace App\Services;

use App\Models\User;
use ErrorException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class UserService
{
    private static function doesUserExist(array $criteria): User|null
    {
        $query = User::query();
        foreach ($criteria as $field => $value) {
            $query->orWhere($field, $value);
        }
        return $query->first();
    }
    public static function getAllUsers(): LengthAwarePaginator
    {
        return User::paginate();
    }
    public static function findUserById(string $userId): User
    {
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("User with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }

        return $user;
    }
    public static function createUser($payload): User
    {
        $payload = array_merge($payload, [
            "id" => Uuid::uuid4()->toString(),
            "password" => Hash::make($payload["password"]),
        ]);

        try {
            return User::create(Arr::only($payload, ["id", "first_name", "last_name", "username", "date_of_birth", "email", "password", "phone_number"]));
        } catch (\Exception $exception) {
            throw new Exception("Erro inesperado ao criar usuário: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function updateUser($userId, $payload): User
    {
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("User with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }

        try {
            $user->update($payload);
        } catch (\Exception $exception) {
            throw new Exception("Erro inesperado ao criar usuário: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $user;
    }
    public static function deleteUser($userId): void
    {
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("User with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }

        $user->delete();
    }
    public static function inactivateUser($userId)
    {
        $user = User::find($userId);

        if(!$user) {
            throw new NotFoundResourceException("User with id {$userId} not found", 404);
        }

        $user->update(["is_active" => false]);

        return $user;
    }
    public static function activateUser($userId)
    {
        $user = User::find($userId);

        if(!$user) {
            throw new NotFoundResourceException("User with id {$userId} not found", 404);
        }

        $user->update(["is_active" => true]);

        return $user;
    }
}
