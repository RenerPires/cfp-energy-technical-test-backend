<?php

namespace App\Models;

use App\Models\Filters\DateOfBirthAfterFilter;
use App\Models\Filters\DateOfBirthBeforeFilter;
use App\Models\Filters\EmailFilter;
use App\Models\Filters\FirstNameFilter;
use App\Models\Filters\LastNameFilter;
use App\Models\Filters\UsernameFilter;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lacodix\LaravelModelFilter\Traits\HasFilters;
use Lacodix\LaravelModelFilter\Traits\IsSearchable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

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
        'created_at',
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
        LastNameFilter::class
    ];
}
