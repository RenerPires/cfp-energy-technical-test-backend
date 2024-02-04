<?php

namespace App\Models\Filters;

use Lacodix\LaravelModelFilter\Filters\StringFilter;

class EmailFilter extends StringFilter
{
    protected string $field = 'email';
    protected string $queryName = 'email';
}
