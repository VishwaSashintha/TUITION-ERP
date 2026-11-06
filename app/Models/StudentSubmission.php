<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class StudentSubmission extends Model
{
    protected string $table = 'student_submissions';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

