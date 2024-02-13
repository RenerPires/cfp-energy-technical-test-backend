<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="CFP Energy - User Management Api",
 *     version="1.0",
 *      @OA\Contact(
 *          email="rener.gbrl.p@gmail.com"
 *      ),
 * ),
 *  @OA\Server(
 *      description="Local Url",
 *      url="http://localhost:80/"
 *  ),
 *
 * @OA\SecurityScheme(
 *      type="apiKey",
 *      description="Use your email / password combo to obtain a token (e.g.: Bearer {{token}})",
 *      name="Token",
 *      in="header",
 *      scheme="http",
 *      securityScheme="Credentials Based",
 *      @OA\Flow(
 *          flow="password",
 *          authorizationUrl="/auth/login",
 *          refreshUrl="/auth/refresh",
 *          scopes={}
 *      )
 *  )
 *
 * @OA\SecurityScheme(
 *       type="http",
 *       description="Cookie Based Authentication",
 *       name="Cookie",
 *       in="cookie",
 *       scheme="http",
 *       securityScheme="Cookie",
 *       @OA\Flow(
 *           flow="password",
 *           authorizationUrl="/auth/login",
 *           refreshUrl="/auth/refresh",
 *           scopes={}
 *       )
 *   )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    protected static function userFromToken(): User
    {
        return auth()->user();
    }
}
