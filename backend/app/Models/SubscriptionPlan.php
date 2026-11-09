<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class SubscriptionPlan extends Model
{
    protected string $table = 'subscription_plans';
    protected bool $tenantScoped = false; // Global platform table
    protected bool $supportsSoftDeletes = false;
}

