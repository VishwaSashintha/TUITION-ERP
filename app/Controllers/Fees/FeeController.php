<?php

declare(strict_types=1);

namespace App\Controllers\Fees;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\AuditService;
use App\Services\BillingService;

class FeeController extends Controller
{
    private BillingService $billingService;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->billingService = new BillingService();
        $this->audit          = new AuditService();
        $this->db             = Database::getInstance();
    }

    /**
     * List all invoices with status and period filters.
     */
    public function invoices(Request $request): void
    {
        $tenantId = current_tenant_id();
        $status   = $request->query('status', '');
        $period   = $request->query('fee_period', '');
        $search   = trim((string) $request->query('search', ''));

        $sql = "SELECT inv.*, st.full_name AS student_name, st.admission_number, br.name AS branch_name
                FROM `invoices` inv
                JOIN `students` st ON inv.student_id = st.id
                JOIN `branches` br ON inv.branch_id = br.id
                WHERE inv.`tenant_id` = :tid AND inv.`deleted_at` IS NULL";
        $params = ['tid' => $tenantId];

        if (!empty($status)) {
            $sql .= " AND inv.`status` = :st";
            $params['st'] = $status;
        }

        if (!empty($period)) {
            $sql .= " AND inv.`fee_period` = :period";
            $params['period'] = $period;
        }

        if (!empty($search)) {
            $sql .= " AND (st.full_name LIKE :s1 OR st.admission_number LIKE :s2 OR inv.invoice_number LIKE :s3)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
        }

        $sql .= " ORDER BY inv.`id` DESC LIMIT 100";
        $invoices = $this->db->query($sql, $params);

        // Calculate KPI summary
        $summary = $this->db->queryOne(
            "SELECT 
                COALESCE(SUM(total_amount), 0.00) AS total_invoiced,
                COALESCE(SUM(paid_amount), 0.00) AS total_collected,
                COALESCE(SUM(balance_amount), 0.00) AS total_outstanding,
                COUNT(*) AS count_total,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS count_paid,
                SUM(CASE WHEN status = 'partially_paid' THEN 1 ELSE 0 END) AS count_partial,
                SUM(CASE WHEN status = 'overdue' OR (due_date < CURDATE() AND balance_amount > 0) THEN 1 ELSE 0 END) AS count_overdue
             FROM `invoices`
             WHERE `tenant_id` = :tid AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        );

        $this->render('fees/invoices', [
            'title'      => 'Fee Invoices & Billing',
            'activeMenu' => 'fees',
            'invoices'   => $invoices,
            'summary'    => $summary,
            'filters'    => [
                'status'     => $status,
                'fee_period' => $period,
                'search'     => $search,
            ]
        ]);
    }

    /**
     * Show single invoice details.
     */
    public function showInvoice(Request $request, string $id = ''): void
    {
        $id = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId = current_tenant_id();

        $invoice = $this->db->queryOne(
            "SELECT inv.*, st.full_name AS student_name, st.admission_number, st.phone, st.email, br.name AS branch_name
             FROM `invoices` inv
             JOIN `students` st ON inv.student_id = st.id
             JOIN `branches` br ON inv.branch_id = br.id
             WHERE inv.`id` = :id AND inv.`tenant_id` = :tid AND inv.`deleted_at` IS NULL",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$invoice) {
            $this->redirect(url('fees/invoices'), 'error', 'Invoice not found.');
            return;
        }

        $items = $this->db->query(
            "SELECT ii.*, b.name AS batch_name 
             FROM `invoice_items` ii 
             LEFT JOIN `batches` b ON ii.batch_id = b.id 
             WHERE ii.`invoice_id` = :id AND ii.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );

        $allocations = $this->db->query(
            "SELECT pa.*, p.payment_number, p.payment_date, p.payment_method, p.receipt_number
             FROM `payment_allocations` pa
             JOIN `payments` p ON pa.payment_id = p.id
             WHERE pa.`invoice_id` = :id AND pa.`tenant_id` = :tid
             ORDER BY p.`payment_date` DESC",
            ['id' => $id, 'tid' => $tenantId]
        );

        $this->render('fees/invoice_show', [
            'title'       => 'Invoice #' . $invoice['invoice_number'],
            'activeMenu'  => 'fees',
            'invoice'     => $invoice,
            'items'       => $items,
            'allocations' => $allocations,
        ]);
    }

    /**
     * Display form to create new invoice.
     */
    public function createInvoice(Request $request): void
    {
        $tenantId = current_tenant_id();
        $studentId = (int) $request->query('student_id', 0);

        $students = $this->db->query(
            "SELECT id, full_name, admission_number FROM `students` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY full_name ASC",
            ['tid' => $tenantId]
        );

        $batches = $this->db->query(
            "SELECT id, name, monthly_fee FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name ASC",
            ['tid' => $tenantId]
        );

        $this->render('fees/invoice_create', [
            'title'      => 'Generate Fee Invoice',
            'activeMenu' => 'fees',
            'students'   => $students,
            'batches'    => $batches,
            'selectedStudentId' => $studentId,
        ]);
    }

    /**
     * Store generated invoice.
     */
    public function storeInvoice(Request $request): void
    {
        $rules = [
            'student_id' => 'required|numeric',
            'fee_period' => 'required',
            'due_date'   => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $studentId = (int) $request->input('student_id');
        $feePeriod = (string) $request->input('fee_period');
        $dueDate   = (string) $request->input('due_date');
        $notes     = $request->input('notes');

        $descriptions = (array) $request->input('item_description', []);
        $amounts      = (array) $request->input('item_amount', []);
        $discounts    = (array) $request->input('item_discount', []);
        $batchIds     = (array) $request->input('item_batch_id', []);

        $items = [];
        foreach ($descriptions as $idx => $desc) {
            $amt = (float) ($amounts[$idx] ?? 0);
            if ($amt <= 0) continue;
            $items[] = [
                'description' => trim($desc),
                'amount'      => $amt,
                'discount'    => (float) ($discounts[$idx] ?? 0),
                'batch_id'    => !empty($batchIds[$idx]) ? (int) $batchIds[$idx] : null,
            ];
        }

        if (empty($items)) {
            $this->back('error', 'Please provide at least one valid invoice line item with an amount.', $request->all());
            return;
        }

        try {
            $invoiceId = $this->billingService->createInvoice($studentId, $feePeriod, $dueDate, $items, $notes);
            $this->redirect(url("fees/invoices"), 'success', 'Invoice generated successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Invoice generation failed: ' . $e->getMessage(), $request->all());
        }
    }

    /**
     * Collect payment modal/action.
     */
    public function collectPayment(Request $request): void
    {
        $rules = [
            'student_id'     => 'required|numeric',
            'amount'         => 'required|numeric',
            'payment_method' => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $studentId       = (int) $request->input('student_id');
        $amount          = (float) $request->input('amount');
        $paymentMethod   = (string) $request->input('payment_method');
        $referenceNumber = $request->input('reference_number');
        $notes           = $request->input('notes');
        $invoiceId       = (int) $request->input('invoice_id', 0);

        $allocations = [];
        if ($invoiceId > 0) {
            $allocations[] = [
                'invoice_id' => $invoiceId,
                'amount'     => $amount,
            ];
        } else {
            // Distribute automatically across student's oldest unpaid invoices
            $unpaidInvoices = $this->db->query(
                "SELECT id, balance_amount FROM `invoices` 
                 WHERE `student_id` = :stuid AND `tenant_id` = :tid AND `status` IN ('issued', 'partially_paid', 'overdue') AND `deleted_at` IS NULL 
                 ORDER BY `due_date` ASC, `id` ASC",
                ['stuid' => $studentId, 'tid' => current_tenant_id()]
            );

            $remainingToAllocate = $amount;
            foreach ($unpaidInvoices as $inv) {
                if ($remainingToAllocate <= 0) break;
                $invBal = (float) $inv['balance_amount'];
                $alloc = min($remainingToAllocate, $invBal);
                $allocations[] = [
                    'invoice_id' => (int) $inv['id'],
                    'amount'     => $alloc,
                ];
                $remainingToAllocate -= $alloc;
            }
        }

        try {
            $paymentId = $this->billingService->recordPayment(
                $studentId,
                $amount,
                $paymentMethod,
                $allocations,
                $referenceNumber,
                $notes
            );

            $payment = $this->db->queryOne("SELECT receipt_number FROM `payments` WHERE `id` = :id", ['id' => $paymentId]);
            $this->redirect(url("fees/receipt?id={$paymentId}"), 'success', "Payment recorded successfully. Receipt #{$payment['receipt_number']} generated.");
        } catch (\Throwable $e) {
            $this->back('error', 'Payment recording failed: ' . $e->getMessage());
        }
    }

    /**
     * Display printable receipt.
     */
    public function receipt(Request $request, string $id = ''): void
    {
        $id = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId = current_tenant_id();

        $payment = $this->db->queryOne(
            "SELECT p.*, st.full_name AS student_name, st.admission_number, st.phone, st.address, br.name AS branch_name, br.phone AS branch_phone, br.address AS branch_address, u.name AS cashier_name
             FROM `payments` p
             JOIN `students` st ON p.student_id = st.id
             JOIN `branches` br ON p.branch_id = br.id
             LEFT JOIN `users` u ON p.collected_by = u.id
             WHERE p.`id` = :id AND p.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$payment) {
            $this->redirect(url('fees/invoices'), 'error', 'Receipt / Payment record not found.');
            return;
        }

        $allocations = $this->db->query(
            "SELECT pa.*, inv.invoice_number, inv.fee_period, inv.total_amount, inv.balance_amount
             FROM `payment_allocations` pa
             JOIN `invoices` inv ON pa.invoice_id = inv.id
             WHERE pa.`payment_id` = :pid AND pa.`tenant_id` = :tid",
            ['pid' => $id, 'tid' => $tenantId]
        );

        $tenant = $this->db->queryOne("SELECT name, email, phone, logo_url FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);

        $this->render('fees/receipt', [
            'title'       => 'Receipt #' . $payment['receipt_number'],
            'activeMenu'  => 'fees',
            'payment'     => $payment,
            'allocations' => $allocations,
            'tenant'      => $tenant,
        ]);
    }

    /**
     * Fee defaulters & outstanding dues report.
     */
    public function outstanding(Request $request): void
    {
        $tenantId = current_tenant_id();

        $defaulters = $this->db->query(
            "SELECT inv.id, inv.invoice_number, inv.fee_period, inv.due_date, inv.total_amount, inv.paid_amount, inv.balance_amount,
                    DATEDIFF(CURDATE(), inv.due_date) AS days_overdue,
                    st.id AS student_id, st.full_name AS student_name, st.admission_number, st.phone,
                    g.full_name AS guardian_name, g.phone AS guardian_phone
             FROM `invoices` inv
             JOIN `students` st ON inv.student_id = st.id
             LEFT JOIN `student_guardians` sg ON st.id = sg.student_id AND sg.is_primary = 1
             LEFT JOIN `guardians` g ON sg.guardian_id = g.id
             WHERE inv.`tenant_id` = :tid AND inv.`balance_amount` > 0.00 AND inv.`status` != 'cancelled' AND inv.`deleted_at` IS NULL
             ORDER BY inv.`due_date` ASC",
            ['tid' => $tenantId]
        );

        $totalOutstanding = array_reduce($defaulters, fn($carry, $item) => $carry + (float)$item['balance_amount'], 0.00);

        $this->render('fees/outstanding', [
            'title'            => 'Fee Defaulters & Outstanding Balances',
            'activeMenu'       => 'fees',
            'defaulters'       => $defaulters,
            'totalOutstanding' => $totalOutstanding,
        ]);
    }

    /**
     * Process refund against payment.
     */
    public function refund(Request $request): void
    {
        $paymentId = (int) $request->input('payment_id');
        $amount    = (float) $request->input('amount');
        $reason    = trim((string) $request->input('reason'));

        if (empty($reason)) {
            $this->back('error', 'A justification reason is required for processing a refund.');
            return;
        }

        try {
            $this->billingService->refundPayment($paymentId, $amount, $reason);
            $this->back('success', "Refund of Rs. " . number_format($amount, 2) . " processed successfully with accounting adjustments.");
        } catch (\Throwable $e) {
            $this->back('error', 'Refund failed: ' . $e->getMessage());
        }
    }
}

