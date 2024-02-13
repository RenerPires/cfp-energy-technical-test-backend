<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'phone_number' => $this->phone_number,
            'date_of_birth' => $this->date_of_birth,
            'email' => $this->email,
            'profile_picture_url' => $this->profile_picture_url,
            'is_active' => $this->is_active,
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'created_at' => $this->created_at,
        ];
    }
}
