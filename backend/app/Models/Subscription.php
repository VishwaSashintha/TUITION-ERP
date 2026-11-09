<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Subscription extends Model
{
    protected string $table = 'subscriptions';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

