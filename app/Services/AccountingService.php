<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\JournalEntry;

/**
 * TuitionPro ERP — Double-Entry Accounting & Ledger Engine
 *
 * Implements strict double-entry bookkeeping:
 * - Every transaction posts balanced debits and credits.
 * - Generates Trial Balance, General Ledger, and P&L Statements.
 */
class AccountingService
{
    private Database $db;
    private AuditService $audit;
    private JournalEntry $journalModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->audit        = new AuditService();
        $this->journalModel = new JournalEntry();
    }

    /**
     * Post journal entry for an issued fee invoice.
     * Debit: Accounts Receivable (1100)
     * Credit: Tuition Fee Revenue (4010)
     */
    public function recordInvoiceJournal(int $invoiceId): int
    {
        $tenantId = current_tenant_id();
        $inv = $this->db->queryOne(
            "SELECT * FROM `invoices` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $invoiceId, 'tid' => $tenantId]
        );
        if (!$inv) {
            throw new \RuntimeException('Invoice not found.');
        }

        $arAccount  = $this->getAccountByCode('1100');
        $revAccount = $this->getAccountByCode('4010');

        $amount = (float) $inv['total_amount'];

        $entryData = [
            'tenant_id'     => $tenantId,
            'branch_id'     => $inv['branch_id'],
            'entry_date'    => $inv['issue_date'],
            'description'   => "Student Fee Invoicing — Invoice {$inv['invoice_number']}",
            'source_module' => 'fee_invoice',
            'source_id'     => $invoiceId,
            'status'        => 'posted',
            'created_by'    => auth_id() ?? 1,
        ];

        $lines = [
            [
                'account_id'       => $arAccount['id'],
                'line_description' => "Receivable from student #{$inv['student_id']}",
                'debit'            => $amount,
                'credit'           => 0.00,
            ],
            [
                'account_id'       => $revAccount['id'],
                'line_description' => "Tuition revenue earned for period {$inv['fee_period']}",
                'debit'            => 0.00,
                'credit'           => $amount,
            ],
        ];

        return $this->journalModel->postBalancedEntry($entryData, $lines);
    }

    /**
     * Post journal entry for a collected student payment.
     * Debit: Cash in Hand (1010) or Main Bank Account (1020)
     * Credit: Accounts Receivable (1100)
     */
    public function recordPaymentJournal(int $paymentId): int
    {
        $tenantId = current_tenant_id();
        $pay = $this->db->queryOne(
            "SELECT * FROM `payments` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $paymentId, 'tid' => $tenantId]
        );
        if (!$pay) {
            throw new \RuntimeException('Payment not found.');
        }

        $cashAccountCode = ($pay['payment_method'] === 'cash') ? '1010' : '1020';
        $debitAccount    = $this->getAccountByCode($cashAccountCode);
        $arAccount       = $this->getAccountByCode('1100');

        $amount = (float) $pay['amount'];

        $entryData = [
            'tenant_id'     => $tenantId,
            'branch_id'     => $pay['branch_id'],
            'entry_date'    => $pay['payment_date'],
            'description'   => "Student Payment Collection — Receipt {$pay['receipt_number']}",
            'source_module' => 'payment',
            'source_id'     => $paymentId,
            'status'        => 'posted',
            'created_by'    => auth_id() ?? 1,
        ];

        $lines = [
            [
                'account_id'       => $debitAccount['id'],
                'line_description' => "Funds received via {$pay['payment_method']}",
                'debit'            => $amount,
                'credit'           => 0.00,
            ],
            [
                'account_id'       => $arAccount['id'],
                'line_description' => "Settlement of receivables for student #{$pay['student_id']}",
                'debit'            => 0.00,
                'credit'           => $amount,
            ],
        ];

        return $this->journalModel->postBalancedEntry($entryData, $lines);
    }

    /**
     * Post journal entry for an operational expense.
     * Debit: Specific Expense Account (e.g. 5020 Classroom Rent)
     * Credit: Cash in Hand (1010) or Operating Bank (1020)
     */
    public function recordExpenseJournal(int $expenseId): int
    {
        $tenantId = current_tenant_id();
        $exp = $this->db->queryOne(
            "SELECT * FROM `expenses` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $expenseId, 'tid' => $tenantId]
        );
        if (!$exp) {
            throw new \RuntimeException('Expense not found.');
        }

        // Map expense category to expense account
        $expenseAccount = $this->getAccountByCode('5020'); // Default rent / operational expense
        $cashAccount    = $this->getAccountByCode('1010');

        $amount = (float) $exp['paid_amount'];
        if ($amount <= 0.00) {
            $amount = (float) $exp['amount'];
        }

        $entryData = [
            'tenant_id'     => $tenantId,
            'branch_id'     => $exp['branch_id'],
            'entry_date'    => $exp['expense_date'],
            'description'   => "Expense Payment — {$exp['expense_number']}: {$exp['description']}",
            'source_module' => 'expense',
            'source_id'     => $expenseId,
            'status'        => 'posted',
            'created_by'    => auth_id() ?? 1,
        ];

        $lines = [
            [
                'account_id'       => $expenseAccount['id'],
                'line_description' => "Expense incurred: {$exp['description']}",
                'debit'            => $amount,
                'credit'           => 0.00,
            ],
            [
                'account_id'       => $cashAccount['id'],
                'line_description' => "Disbursement to payee {$exp['payee']}",
                'debit'            => 0.00,
                'credit'           => $amount,
            ],
        ];

        return $this->journalModel->postBalancedEntry($entryData, $lines);
    }

    /**
     * Generate Trial Balance to mathematically verify that Debits == Credits.
     */
    public function getTrialBalance(): array
    {
        $tenantId = current_tenant_id();
        $rows = $this->db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_type, coa.normal_balance,
                    COALESCE(SUM(jl.debit), 0) AS total_debit,
                    COALESCE(SUM(jl.credit), 0) AS total_credit
             FROM `chart_of_accounts` coa 
             LEFT JOIN `journal_lines` jl ON coa.id = jl.account_id AND jl.tenant_id = :tid_jl 
             WHERE coa.`tenant_id` = :tid_coa AND coa.`is_active` = 1 
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_type, coa.normal_balance 
             ORDER BY coa.account_code ASC",
            ['tid_jl' => $tenantId, 'tid_coa' => $tenantId]
        );

        $grandDebit  = 0.00;
        $grandCredit = 0.00;

        foreach ($rows as &$r) {
            $deb  = (float) $r['total_debit'];
            $cred = (float) $r['total_credit'];
            $grandDebit  += $deb;
            $grandCredit += $cred;
        }

        return [
            'accounts'     => $rows,
            'total_debit'  => round($grandDebit, 2),
            'total_credit' => round($grandCredit, 2),
            'is_balanced'  => (round($grandDebit, 2) === round($grandCredit, 2)),
        ];
    }

    /**
     * Generate Profit and Loss (Income Statement) Summary.
     */
    public function getProfitAndLossStatement(?string $startDate = null, ?string $endDate = null): array
    {
        $tenantId = current_tenant_id();
        $params   = ['tid' => $tenantId];

        $dateFilter = "";
        if ($startDate && $endDate) {
            $dateFilter = "AND je.entry_date BETWEEN :start AND :end";
            $params['start'] = $startDate;
            $params['end']   = $endDate;
        }

        // Revenues
        $revenues = $this->db->query(
            "SELECT coa.account_name, COALESCE(SUM(jl.credit - jl.debit), 0) AS balance 
             FROM `chart_of_accounts` coa 
             JOIN `journal_lines` jl ON coa.id = jl.account_id 
             JOIN `journal_entries` je ON jl.journal_entry_id = je.id 
             WHERE coa.`tenant_id` = :tid AND coa.`account_type` = 'revenue' AND je.`status` = 'posted' {$dateFilter} 
             GROUP BY coa.id, coa.account_name",
            $params
        );

        // Expenses
        $expenses = $this->db->query(
            "SELECT coa.account_name, COALESCE(SUM(jl.debit - jl.credit), 0) AS balance 
             FROM `chart_of_accounts` coa 
             JOIN `journal_lines` jl ON coa.id = jl.account_id 
             JOIN `journal_entries` je ON jl.journal_entry_id = je.id 
             WHERE coa.`tenant_id` = :tid AND coa.`account_type` = 'expense' AND je.`status` = 'posted' {$dateFilter} 
             GROUP BY coa.id, coa.account_name",
            $params
        );

        $totalRevenue = 0.00;
        foreach ($revenues as $r) {
            $totalRevenue += (float) $r['balance'];
        }

        $totalExpense = 0.00;
        foreach ($expenses as $e) {
            $totalExpense += (float) $e['balance'];
        }

        return [
            'revenues'      => $revenues,
            'total_revenue' => round($totalRevenue, 2),
            'expenses'      => $expenses,
            'total_expense' => round($totalExpense, 2),
            'net_profit'    => round($totalRevenue - $totalExpense, 2),
        ];
    }

    /**
     * Generate Balance Sheet (Statement of Financial Position).
     * Verifies Assets = Liabilities + Equity (including current period retained earnings).
     */
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $tenantId = current_tenant_id();
        $params   = ['tid' => $tenantId];

        $dateFilter = "";
        if ($asOfDate) {
            $dateFilter = "AND je.entry_date <= :as_of";
            $params['as_of'] = $asOfDate;
        }

        // 1. Assets (Normal Balance = Debit)
        $assets = $this->db->query(
            "SELECT coa.account_code, coa.account_name, COALESCE(SUM(jl.debit - jl.credit), 0) AS balance 
             FROM `chart_of_accounts` coa 
             LEFT JOIN `journal_lines` jl ON coa.id = jl.account_id 
             LEFT JOIN `journal_entries` je ON jl.journal_entry_id = je.id AND je.`status` = 'posted' {$dateFilter}
             WHERE coa.`tenant_id` = :tid AND coa.`account_type` = 'asset' AND coa.`is_active` = 1
             GROUP BY coa.id, coa.account_code, coa.account_name
             ORDER BY coa.account_code ASC",
            $params
        );

        // 2. Liabilities (Normal Balance = Credit)
        $liabilities = $this->db->query(
            "SELECT coa.account_code, coa.account_name, COALESCE(SUM(jl.credit - jl.debit), 0) AS balance 
             FROM `chart_of_accounts` coa 
             LEFT JOIN `journal_lines` jl ON coa.id = jl.account_id 
             LEFT JOIN `journal_entries` je ON jl.journal_entry_id = je.id AND je.`status` = 'posted' {$dateFilter}
             WHERE coa.`tenant_id` = :tid AND coa.`account_type` = 'liability' AND coa.`is_active` = 1
             GROUP BY coa.id, coa.account_code, coa.account_name
             ORDER BY coa.account_code ASC",
            $params
        );

        // 3. Equity (Normal Balance = Credit)
        $equity = $this->db->query(
            "SELECT coa.account_code, coa.account_name, COALESCE(SUM(jl.credit - jl.debit), 0) AS balance 
             FROM `chart_of_accounts` coa 
             LEFT JOIN `journal_lines` jl ON coa.id = jl.account_id 
             LEFT JOIN `journal_entries` je ON jl.journal_entry_id = je.id AND je.`status` = 'posted' {$dateFilter}
             WHERE coa.`tenant_id` = :tid AND coa.`account_type` = 'equity' AND coa.`is_active` = 1
             GROUP BY coa.id, coa.account_code, coa.account_name
             ORDER BY coa.account_code ASC",
            $params
        );

        // 4. Current Period Net Income (Revenues - Expenses)
        $pnl = $this->getProfitAndLossStatement(null, $asOfDate);
        $netIncome = (float) $pnl['net_profit'];

        $totalAssets = 0.00;
        foreach ($assets as $a) {
            $totalAssets += (float) $a['balance'];
        }

        $totalLiabilities = 0.00;
        foreach ($liabilities as $l) {
            $totalLiabilities += (float) $l['balance'];
        }

        $totalEquity = 0.00;
        foreach ($equity as $eq) {
            $totalEquity += (float) $eq['balance'];
        }

        $totalLiabilitiesAndEquity = round($totalLiabilities + $totalEquity + $netIncome, 2);
        $totalAssets = round($totalAssets, 2);

        return [
            'as_of_date'                   => $asOfDate ?: date('Y-m-d'),
            'assets'                       => $assets,
            'total_assets'                 => $totalAssets,
            'liabilities'                  => $liabilities,
            'total_liabilities'            => round($totalLiabilities, 2),
            'equity'                       => $equity,
            'total_equity'                 => round($totalEquity, 2),
            'net_income'                   => round($netIncome, 2),
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced'                  => (abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01),
        ];
    }

    private function getAccountByCode(string $code): array
    {
        $tenantId = current_tenant_id();
        $account = $this->db->queryOne(
            "SELECT * FROM `chart_of_accounts` WHERE `tenant_id` = :tid AND `account_code` = :code LIMIT 1",
            ['tid' => $tenantId, 'code' => $code]
        );
        if (!$account) {
            throw new \RuntimeException("Chart of account '{$code}' not found.");
        }
        return $account;
    }
}

