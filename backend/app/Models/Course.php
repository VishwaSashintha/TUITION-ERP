<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Course extends Model
{
    protected string $table = 'courses';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;
}

