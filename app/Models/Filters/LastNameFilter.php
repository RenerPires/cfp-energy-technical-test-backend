<?php

namespace App\Models\Filters;

use Lacodix\LaravelModelFilter\Filters\StringFilter;

class LastNameFilter extends StringFilter
{
    protected string $field = 'last_name';
}
