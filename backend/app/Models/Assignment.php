<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Assignment extends Model
{
    protected string $table = 'assignments';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

