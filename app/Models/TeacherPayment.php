<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TeacherPayment extends Model
{
    protected string $table = 'teacher_payments';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
    protected bool $hasUpdatedAt = false;
}

