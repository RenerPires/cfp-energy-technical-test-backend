<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Mockery\Exception;
use OpenApi\Annotations as OA;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authenticate on project to access the resources"
 * )
 **/
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="john.doe@email.com",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="password",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="login successfully",
     *             ),
     *             @OA\Property(
     *                 property="token_type",
     *                 type="string",
     *                 example="bearer",
     *             ),
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 ref="#/components/schemas/User",
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="integer",
     *                 example=3600,
     *             ),
     *         ),
     *     ),
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="User registration",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                     example="John",
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                     example="Doe",
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     example="john.doe@email.com",
     *                 ),
     *                 @OA\Property(
     *                     property="phone_number",
     *                     type="string",
     *                     example="+5511999999999",
     *                 ),
     *                 @OA\Property(
     *                     property="date_of_birth",
     *                     type="string",
     *                     format="date",
     *                     example="1999-02-10",
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     example="johndoe",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="password",
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/User",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=false,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="validation error",
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *
     *             ),
     *         ),
     *     ),
     * )
     */
    public function signup(Request $request): JsonResponse
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
                'unique:users,phone_number',
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
            $payload = array_merge($payload, [
                "id" => Uuid::uuid4()->toString(),
                "password" => Hash::make($payload["password"]),
            ]);
            try {
                $user = User::create(Arr::only($payload, ["id", "first_name", "last_name", "username", "date_of_birth", "email", "password", "phone_number"]));
                $user->update(["profile_picture_url" => "https://ui-avatars.com/api/?name={$user->first_name}+{$user->last_name}&background=random&format=png"]);
                $user->assignRole('user');
            } catch (\Exception $exception) {
                throw new Exception("unexpected error when signing up: {$exception->getMessage()}", Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (AccessDeniedHttpException|Exception $exception) {
            return response()->json([
                'status' => false,
                'errors' => $exception->getMessage()
            ], $exception->getCode());
        }

        return response()
            ->json(["data" => $user], Response::HTTP_CREATED)
            ->header("Location", $user->id);
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Logged in users details",
     *     security={"Token"},
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response="200",
         *         description="Success",
     *         ref="#/components/schemas/User",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized",
     *             ),
     *         ),
     *     ),
     *
     * )
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response="200",
     *         description="Logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Logged out successfully",
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthenticated",
     *             ),
     *         ),
     *     ),
     * )
     */
    public function logout(): JsonResponse
    {
        auth()->logout();
        cookie('auth_token', null);
        return response()->json(['message' => 'logged out  successfully'], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh access token",
     *     security={"Token"},
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response="200",
     *         description="Successful refresh",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true,
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="login successfully",
     *             ),
     *             @OA\Property(
     *                 property="token_type",
     *                 type="string",
     *                 example="bearer",
     *             ),
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9",
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 ref="#/components/schemas/User",
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="integer",
     *                 example=3600,
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Unauthorized",
     *             ),
     *         ),
     *     ),
     * )
     */
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
