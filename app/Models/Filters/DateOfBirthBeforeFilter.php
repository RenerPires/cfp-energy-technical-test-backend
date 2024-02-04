<?php

namespace App\Models\Filters;

use Lacodix\LaravelModelFilter\Enums\FilterMode;
use Lacodix\LaravelModelFilter\Filters\DateFilter;

class DateOfBirthBeforeFilter extends DateFilter
{
    public FilterMode $mode = FilterMode::LOWER_OR_EQUAL;
    protected string $field = 'date_of_birth';
    protected string $queryName = 'date_of_birth_before';
}
