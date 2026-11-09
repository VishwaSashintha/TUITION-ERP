<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Announcement extends Model
{
    protected string $table = 'announcements';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

