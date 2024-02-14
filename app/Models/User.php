<?php

namespace App\Models;

use App\Models\Filters\DateOfBirthAfterFilter;
use App\Models\Filters\DateOfBirthBeforeFilter;
use App\Models\Filters\EmailFilter;
use App\Models\Filters\FirstNameFilter;
use App\Models\Filters\LastNameFilter;
use App\Models\Filters\StatusFilter;
use App\Models\Filters\UsernameFilter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lacodix\LaravelModelFilter\Traits\HasFilters;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use OpenApi\Annotations as OA;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema(
 *   @OA\Xml(name="User"),
 *   @OA\Property(format="string", title="id", default="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx", description="user id", property="id"),
 *   @OA\Property(format="string", title="first name", default="John", description="first name", property="first_name"),
 *   @OA\Property(format="string", title="last name", default="Doe", description="last name", property="last_name"),
 *   @OA\Property(format="string", title="username", default="johndoe", description="username", property="username"),
 *   @OA\Property(format="string", title="phone_number", default="+5511999999999", description="phone number", property="phone_number"),
 *   @OA\Property(format="string", title="date_of_birth", default="yyyy-mm-dd", description="date of birth", property="date_of_birth"),
 *   @OA\Property(format="string", title="email", default="john.doe@email.com", description="email", property="email"),
 *   @OA\Property(format="string", title="profile_picture_url", default="https://ui-avatars.com/api/?name=John+Doe&background=random&format=png", description="user's profile picture url", property="profile_picture_url"),
 *   @OA\Property(format="string", title="is_active", default="true", description="status of user", property="is_active"),
 *   @OA\Property(format="array", title="roles", default={"user"}, description="roles", property="roles"),
 *   @OA\Property(format="array", title="permissions", default={"view-users"}, description="permissions", property="permissions"),
 *   @OA\Property(format="date", title="created_at", default="yyyy-mm-dd", description="date of creation of the user", property="created_at")
 * )
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles, CanResetPassword, IsSearchable, HasFilters;

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'username',
        'password',
        'phone_number',
        'date_of_birth',
        'email',
        'profile_picture_url',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name')
        ];
    }

    protected array $searchable = [
        'first_name',
        'last_name',
        'username'
    ];

    protected array $filters = [
        DateOfBirthBeforeFilter::class,
        DateOfBirthAfterFilter::class,
        UsernameFilter::class,
        EmailFilter::class,
        FirstNameFilter::class,
        LastNameFilter::class,
        StatusFilter::class
    ];
}
