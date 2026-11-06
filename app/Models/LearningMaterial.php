<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class LearningMaterial extends Model
{
    protected string $table = 'learning_materials';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

