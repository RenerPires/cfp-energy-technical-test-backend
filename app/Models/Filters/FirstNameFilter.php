<?php

namespace App\Models\Filters;

use Lacodix\LaravelModelFilter\Filters\StringFilter;

class FirstNameFilter extends StringFilter
{
    protected string $field = 'first_name';
    protected string $queryName = 'first_name';
}
