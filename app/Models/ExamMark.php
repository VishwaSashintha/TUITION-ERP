<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ExamMark extends Model
{
    protected string $table = 'exam_marks';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

