<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Tenant extends Model
{
    protected string $table = 'tenants';
    protected bool $tenantScoped = false; // Tenants table holds the tenants themselves
    protected bool $supportsSoftDeletes = true;
}

