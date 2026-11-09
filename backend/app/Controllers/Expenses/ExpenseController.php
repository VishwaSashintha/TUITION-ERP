<?php

declare(strict_types=1);

namespace App\Controllers\Expenses;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Expense;
use App\Services\AccountingService;
use App\Services\AuditService;

class ExpenseController extends Controller
{
    private Expense $expenseModel;
    private AccountingService $accountingService;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->expenseModel      = new Expense();
        $this->accountingService = new AccountingService();
        $this->audit              = new AuditService();
        $this->db                 = Database::getInstance();
    }

    public function index(Request $request): void
    {
        $tenantId   = current_tenant_id();
        $categoryId = (int) $request->query('category_id', 0);
        $startDate  = $request->query('start_date', date('Y-m-01'));
        $endDate    = $request->query('end_date', date('Y-m-d'));

        $sql = "SELECT e.*, ec.name AS category_name, br.name AS branch_name, u.name AS created_by_name
                FROM `expenses` e
                JOIN `expense_categories` ec ON e.category_id = ec.id
                JOIN `branches` br ON e.branch_id = br.id
                JOIN `users` u ON e.created_by = u.id
                WHERE e.`tenant_id` = :tid AND e.`expense_date` BETWEEN :start AND :end AND e.`deleted_at` IS NULL";
        $params = [
            'tid'   => $tenantId,
            'start' => $startDate,
            'end'   => $endDate,
        ];

        if ($categoryId > 0) {
            $sql .= " AND e.`category_id` = :cid";
            $params['cid'] = $categoryId;
        }

        $sql .= " ORDER BY e.`expense_date` DESC, e.`id` DESC";
        $expenses = $this->db->query($sql, $params);

        $categories = $this->db->query("SELECT id, name FROM `expense_categories` WHERE `tenant_id` = :tid AND `is_active` = 1", ['tid' => $tenantId]);

        //Summaries
        $summary = $this->db->queryOne(
            "SELECT 
                COALESCE(SUM(amount), 0.00) AS total_expense,
                COUNT(*) AS count_expense
             FROM `expenses` 
             WHERE `tenant_id` = :tid AND `expense_date` BETWEEN :start AND :end AND `deleted_at` IS NULL",
            ['tid' => $tenantId, 'start' => $startDate, 'end' => $endDate]
        );

        $this->render('expenses/index', [
            'title'      => 'Operational Expenses',
            'activeMenu' => 'expenses',
            'expenses'   => $expenses,
            'categories' => $categories,
            'summary'    => $summary,
            'filters'    => [
                'category_id' => $categoryId,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
            ]
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = current_tenant_id();

        $categories = $this->db->query("SELECT id, name FROM `expense_categories` WHERE `tenant_id` = :tid AND `is_active` = 1 ORDER BY name ASC", ['tid' => $tenantId]);
        $branches   = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $accounts   = $this->db->query("SELECT id, account_name, account_type, current_balance FROM `financial_accounts` WHERE `tenant_id` = :tid AND `is_active` = 1", ['tid' => $tenantId]);

        $this->render('expenses/create', [
            'title'      => 'Record Institute Expense',
            'activeMenu' => 'expenses',
            'categories' => $categories,
            'branches'   => $branches,
            'accounts'   => $accounts,
        ]);
    }

    public function store(Request $request): void
    {
        $rules = [
            'category_id'  => 'required|numeric',
            'branch_id'    => 'required|numeric',
            'amount'       => 'required|numeric',
            'expense_date' => 'required',
            'payee'        => 'required',
            'description'  => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $tenantId   = current_tenant_id();
        $categoryId = (int) $request->input('category_id');
        $branchId   = (int) $request->input('branch_id');
        $amount     = (float) $request->input('amount');
        $expenseDate = (string) $request->input('expense_date');
        $payee      = trim((string) $request->input('payee'));
        $desc       = trim((string) $request->input('description'));
        $accountId  = (int) $request->input('account_id', 0);

        try {
            $expenseId = $this->db->transaction(function () use ($tenantId, $branchId, $categoryId, $amount, $expenseDate, $payee, $desc, $accountId) {
                $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
                $tCode  = strtoupper($tenant['code'] ?? 'TP');
                $expenseNumber = "{$tCode}-EXP-" . date('Ym') . "-" . substr(uniqid(), -4);

                $id = $this->expenseModel->create([
                    'tenant_id'      => $tenantId,
                    'branch_id'      => $branchId,
                    'category_id'    => $categoryId,
                    'expense_number' => $expenseNumber,
                    'expense_date'   => $expenseDate,
                    'payee'          => $payee,
                    'amount'         => $amount,
                    'paid_amount'    => $amount,
                    'balance_amount' => 0.00,
                    'payment_status' => 'paid',
                    'description'    => $desc,
                    'created_by'     => auth_id() ?? 1,
                ]);

                if ($accountId > 0) {
                    $acc = $this->db->queryOne("SELECT current_balance FROM `financial_accounts` WHERE `id` = :id AND `tenant_id` = :tid", ['id' => $accountId, 'tid' => $tenantId]);
                    if ($acc) {
                        $newBal = max(0.00, round((float)$acc['current_balance'] - $amount, 2));
                        $this->db->execute("UPDATE `financial_accounts` SET `current_balance` = :bal WHERE `id` = :id", ['bal' => $newBal, 'id' => $accountId]);
                    }
                }

                //Ledger
                $this->accountingService->recordExpenseJournal($id);

                $this->audit->log('expense_recorded', 'expenses', $id, "Expense {$expenseNumber} for Rs. {$amount} recorded and posted to general ledger");

                return $id;
            });

            $this->redirect(url('expenses'), 'success', 'Expense recorded and journal entry posted successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to record expense: ' . $e->getMessage(), $request->all());
        }
    }

    public function trialBalance(Request $request): void
    {
        $tenantId = current_tenant_id();
        $tb = $this->accountingService->getTrialBalance();

        $this->render('expenses/trial_balance', [
            'title'        => 'General Ledger Trial Balance',
            'activeMenu'   => 'accounting',
            'trialBalance' => $tb,
        ]);
    }

    public function profitLoss(Request $request): void
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate   = $request->query('end_date', date('Y-m-d'));

        $pnl = $this->accountingService->getProfitAndLossStatement($startDate, $endDate);

        $this->render('expenses/profit_loss', [
            'title'      => 'Profit & Loss Statement (Income Statement)',
            'activeMenu' => 'accounting',
            'pnl'        => $pnl,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ]);
    }

    public function cashBank(Request $request): void
    {
        $tenantId = current_tenant_id();

        $accounts = $this->db->query(
            "SELECT fa.*, b.name AS branch_name
             FROM `financial_accounts` fa
             LEFT JOIN `branches` b ON fa.branch_id = b.id
             WHERE fa.`tenant_id` = :tid AND fa.`is_active` = 1
             ORDER BY fa.`account_type` ASC, fa.`id` ASC",
            ['tid' => $tenantId]
        );

        $totalCash = 0.00;
        $totalBank = 0.00;
        foreach ($accounts as $acc) {
            if ($acc['account_type'] === 'cash') {
                $totalCash += (float) $acc['current_balance'];
            } else {
                $totalBank += (float) $acc['current_balance'];
            }
        }

        $recentTransactions = $this->db->query(
            "SELECT p.receipt_number AS ref, p.payment_date AS tx_date, p.amount, p.payment_method, 'Cash Receipt' AS tx_type, st.full_name AS party
             FROM `payments` p
             JOIN `students` st ON p.student_id = st.id
             WHERE p.`tenant_id` = :tid AND p.`status` = 'confirmed'
             ORDER BY p.`id` DESC LIMIT 10",
            ['tid' => $tenantId]
        );

        $this->render('expenses/cash_bank', [
            'title'              => 'Cash & Bank Account Balances',
            'activeMenu'         => 'accounting',
            'accounts'           => $accounts,
            'totalCash'          => $totalCash,
            'totalBank'          => $totalBank,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function balanceSheet(Request $request): void
    {
        $asOfDate = $request->query('as_of', date('Y-m-d'));
        $statement = $this->accountingService->getBalanceSheet($asOfDate);

        $this->render('expenses/balance_sheet', [
            'title'      => 'Balance Sheet (Statement of Financial Position)',
            'activeMenu' => 'accounting',
            'sheet'      => $statement,
            'asOfDate'   => $asOfDate,
        ]);
    }
}

