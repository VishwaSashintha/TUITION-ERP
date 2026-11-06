<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subject extends Model
{
    protected string $table = 'subjects';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;
}

