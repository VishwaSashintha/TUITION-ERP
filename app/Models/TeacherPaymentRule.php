<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TeacherPaymentRule extends Model
{
    protected string $table = 'teacher_payment_rules';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

