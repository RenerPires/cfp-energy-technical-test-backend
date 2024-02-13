<?php

namespace App\Services;

use App\Enums\PermissionTypes;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
    private static function userHaveAbilityTo(PermissionTypes $abilities): bool
    {
        return auth()->user()->can($abilities->value);
    }
    private static function isSelfMutation($userId): bool
    {
        return auth()->id() === $userId;
    }
    private static function resolvePublicFilepath($path): string
    {
        $storageUrl = ENV('CLOUD_URL');
        return "$storageUrl/$path";
    }
    public static function getAllUsers(): LengthAwarePaginator
    {
        if(!self::userHaveAbilityTo(PermissionTypes::viewUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to view users", code:Response::HTTP_FORBIDDEN);
        }
        try {
            return User::filterByQueryString()->searchByQueryString()->paginate();
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when searching for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function findUserById(string $userId): User
    {
        if(!self::userHaveAbilityTo(PermissionTypes::viewUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to view users", code:Response::HTTP_FORBIDDEN);
        }
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("user with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }
        try{
            return $user;
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when searching for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function createUser($payload): User
    {
        $payload = array_merge($payload, [
            "id" => Uuid::uuid4()->toString(),
            "password" => Hash::make($payload["password"]),
        ]);
        if(!self::userHaveAbilityTo(PermissionTypes::createUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to create users", code:Response::HTTP_FORBIDDEN);
        }
        try {
            $user = User::create(Arr::only($payload, ["id", "first_name", "last_name", "username", "date_of_birth", "email", "password", "phone_number"]));
            $user->update(["profile_picture_url" => "https://ui-avatars.com/api/?name={$user->first_name}+{$user->last_name}&background=random&format=png"]);
            $user->assignRole('user');
            return $user;
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when creating for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function updateUser($userId, $payload): User
    {
        if(!self::isSelfMutation($userId) && !self::userHaveAbilityTo(PermissionTypes::updateUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to edit users", code:Response::HTTP_FORBIDDEN);
        }
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("User with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }
        try {
            $user->update($payload);
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when updating for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $user;
    }
    public static function deleteUser($userId): void
    {
        if(!self::userHaveAbilityTo(PermissionTypes::deleteUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to delete users", code:Response::HTTP_FORBIDDEN);
        }
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("User with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }
        try{
            $user->delete();
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when deleting for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function inactivateUser($userId): void
    {
        if(!self::isSelfMutation($userId) && !self::userHaveAbilityTo(PermissionTypes::inactivateUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to inactivate users", code:Response::HTTP_FORBIDDEN);
        }
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("user with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }
        try {
            $user->update([
                "is_active" => false,
                "inactivated_at" => now()
            ]);
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when inactivating for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function activateUser($userId): void
    {
        if(!self::isSelfMutation($userId) && !self::userHaveAbilityTo(PermissionTypes::activateUsers)) {
            throw new AccessDeniedHttpException("you don't have permission to activate users", code:Response::HTTP_FORBIDDEN);
        }
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("user with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }
        try {
            $user->update([
                "is_active" => true,
                "inactivated_at" => null
            ]);
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when activating for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function setUserProfilePicture($userId, $payload): void
    {
        if(!self::isSelfMutation($userId)) {
            throw new AccessDeniedHttpException("you don't have permission to update other user's profile picture", code:Response::HTTP_FORBIDDEN);
        }
        try {
            $path = $payload['profile_picture']->store('profile-picture');
            $publicPath = self::resolvePublicFilepath($path);

            auth()->user()->update([
                'profile_picture_url' => $publicPath
            ]);

        } catch (\Exception $exception) {
            throw new Exception("unexpected error when updating for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public static function updateUserPermissions($userId, $payload): void
    {
        if(!self::userHaveAbilityTo(PermissionTypes::updatePermissions)) {
            throw new AccessDeniedHttpException("you don't have permission to update users permissions", code:Response::HTTP_FORBIDDEN);
        }
        if(!$user = self::doesUserExist(["id" => $userId])) {
            throw new NotFoundResourceException("user with id {$userId} not found", Response::HTTP_NOT_FOUND);
        }
        try {
            $user->syncPermissions($payload['permissions']);
        } catch (\Exception $exception) {
            throw new Exception("unexpected error when updating for users: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
