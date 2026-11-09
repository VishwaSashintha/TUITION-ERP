<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class FinancialAccount extends Model
{
    protected string $table = 'financial_accounts';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
}

