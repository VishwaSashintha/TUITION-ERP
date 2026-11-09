<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AcademicYear extends Model
{
    protected string $table = 'academic_years';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

