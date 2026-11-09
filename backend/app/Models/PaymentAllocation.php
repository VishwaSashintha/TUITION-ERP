<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class PaymentAllocation extends Model
{
    protected string $table = 'payment_allocations';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
    protected bool $hasUpdatedAt = false;
}

