<?php

namespace App\Models\Filters;

use Illuminate\Database\Eloquent\Builder;
use Lacodix\LaravelModelFilter\Filters\SelectFilter;

class StatusFilter  extends SelectFilter
{
    protected string $queryName = 'status';
    protected string $field = 'is_active';

    public function options(): array
    {
        return [
            'active',
            'inactive',
        ];
    }

    public function apply(Builder $query): Builder
    {
        return match($this->values[$this->field]) {
            'active' => $query->where($this->field, '=', 1 ),
            'inactive' => $query->where($this->field, '!=', 1),
            default => $query,
        };
    }
}
