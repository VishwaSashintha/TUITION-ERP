<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class JournalLine extends Model
{
    protected string $table = 'journal_lines';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;
    protected bool $hasUpdatedAt = false;
}

