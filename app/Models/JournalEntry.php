<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class JournalEntry extends Model
{
    protected string $table = 'journal_entries';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false; // Financial journals are immutable
    protected bool $hasUpdatedAt = false;

    /**
     * Post a balanced double-entry journal entry.
     * Enforces mathematical balance: Total Debits == Total Credits.
     *
     * @param array $entryData
     * @param array $lines Array of ['account_id' => int, 'line_description' => string, 'debit' => float, 'credit' => float]
     * @return int Inserted journal entry ID
     */
    public function postBalancedEntry(array $entryData, array $lines): int
    {
        $totalDebit  = 0.00;
        $totalCredit = 0.00;

        foreach ($lines as $line) {
            $totalDebit  += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        // Check journal balance with minor-unit precision
        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new \RuntimeException(
                sprintf('Accounting imbalance: Total Debits (Rs. %.2f) must equal Total Credits (Rs. %.2f).', $totalDebit, $totalCredit)
            );
        }

        if (round($totalDebit, 2) <= 0.00) {
            throw new \RuntimeException('Journal entry cannot have a zero or negative total amount.');
        }

        return $this->db->transaction(function () use ($entryData, $lines) {
            $tenantId = $this->getActiveTenantId();

            if (!isset($entryData['tenant_id'])) {
                $entryData['tenant_id'] = $tenantId;
            }
            if (!isset($entryData['journal_number'])) {
                $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
                $tCode = strtoupper($tenant['code'] ?? 'TP');
                $entryData['journal_number'] = "{$tCode}-JRN-" . date('Ym') . "-" . substr(uniqid(), -4);
            }

            $journalEntryId = $this->create($entryData);

            $jlStmt = $this->db->getConnection()->prepare(
                "INSERT INTO `journal_lines` (`tenant_id`, `journal_entry_id`, `account_id`, `line_description`, `debit`, `credit`, `created_at`) 
                 VALUES (:tid, :je_id, :acc_id, :desc, :deb, :cred, NOW())"
            );

            foreach ($lines as $line) {
                $jlStmt->execute([
                    'tid'    => $tenantId,
                    'je_id'  => $journalEntryId,
                    'acc_id' => (int) $line['account_id'],
                    'desc'   => $line['line_description'] ?? null,
                    'deb'    => (float) ($line['debit'] ?? 0),
                    'cred'   => (float) ($line['credit'] ?? 0),
                ]);
            }

            return $journalEntryId;
        });
    }

    /**
     * Find journal entry with all line items and accounts.
     */
    public function findWithLines(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        $entry = $this->db->queryOne(
            "SELECT je.*, br.name AS branch_name, u.name AS creator_name 
             FROM `journal_entries` je 
             LEFT JOIN `branches` br ON je.branch_id = br.id 
             JOIN `users` u ON je.created_by = u.id 
             WHERE je.`id` = :id AND je.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$entry) {
            return null;
        }

        $lines = $this->db->query(
            "SELECT jl.*, coa.account_code, coa.account_name, coa.account_type 
             FROM `journal_lines` jl 
             JOIN `chart_of_accounts` coa ON jl.account_id = coa.id 
             WHERE jl.`journal_entry_id` = :je_id AND jl.`tenant_id` = :tid",
            ['je_id' => $id, 'tid' => $tenantId]
        );

        $entry['lines'] = $lines;
        return $entry;
    }
}

