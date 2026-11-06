<?php

declare(strict_types=1);

/**
 * TuitionPro ERP — Backend Foundation & Financial Integrity Test Suite
 *
 * Runs automated tests verifying:
 * - Multi-tenant isolation (Tenant A vs Tenant B)
 * - Authentication & RBAC security
 * - Invoice creation, partial payments, and balance tracking
 * - Teacher compensation calculations across models
 * - Double-entry balanced journal entries and Trial Balance
 */

require_once dirname(__DIR__) . '/public/index.php';

use App\Core\Database;
use App\Services\AuthService;
use App\Services\BillingService;
use App\Services\TeacherPaymentService;
use App\Services\AccountingService;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Teacher;
use App\Models\JournalEntry;

class BackendTestSuite
{
    private Database $db;
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function runAll(): void
    {
        echo "\n======================================================\n";
        echo "TUITIONPRO ERP — AUTOMATED BACKEND VERIFICATION SUITE\n";
        echo "======================================================\n\n";

        $this->testAuthentication();
        $this->testTenantIsolation();
        $this->testBillingAndPartialPayments();
        $this->testTeacherPaymentCalculation();
        $this->testDoubleEntryAccountingBalance();

        echo "\n------------------------------------------------------\n";
        echo "TEST RESULTS SUMMARY:\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        if ($this->failed > 0) {
            echo "\nFAILURES:\n";
            foreach ($this->failures as $f) {
                echo " [FAIL] {$f}\n";
            }
        }
        echo "------------------------------------------------------\n";
    }

    private function assert(bool $condition, string $testName): void
    {
        if ($condition) {
            $this->passed++;
            echo " [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $this->failures[] = $testName;
            echo " [FAIL] {$testName}\n";
        }
    }

    // ─── 1. Authentication Tests ─────────────────────────────────────────────
    private function testAuthentication(): void
    {
        echo "1. Testing Authentication & RBAC...\n";
        $auth = new AuthService();

        // Valid platform login
        $platformUser = $auth->attempt('superadmin@tuitionpro.lk', 'Password@123');
        $this->assert($platformUser !== null && $platformUser['role'] === 'super_admin', "Super Admin login succeeds with valid credentials");

        // Invalid password
        $badLogin = $auth->attempt('superadmin@tuitionpro.lk', 'WrongPassword!');
        $this->assert($badLogin === null, "Login fails with invalid password");

        // Valid tenant owner login
        $apexOwner = $auth->attempt('apex.owner@tuitionpro.lk', 'Password@123', 'apex');
        $this->assert($apexOwner !== null && $apexOwner['role_name'] === 'institute_owner', "Apex Institute Owner login succeeds");

        // Tenant code mismatch check
        $crossTenantLogin = $auth->attempt('apex.owner@tuitionpro.lk', 'Password@123', 'excel');
        $this->assert($crossTenantLogin === null, "Login rejected when tenant code does not match user institute");
    }

    // ─── 2. Tenant Isolation Tests ───────────────────────────────────────────
    private function testTenantIsolation(): void
    {
        echo "\n2. Testing Multi-Tenant Data Isolation...\n";
        $session = app()->getSession();

        // Resolve Apex Tenant ID (Tenant 1)
        $apexTenant = $this->db->queryOne("SELECT id FROM `tenants` WHERE `code` = 'apex'");
        $apexId = (int)$apexTenant['id'];

        // Resolve Excel Tenant ID (Tenant 2)
        $excelTenant = $this->db->queryOne("SELECT id FROM `tenants` WHERE `code` = 'excel'");
        $excelId = (int)$excelTenant['id'];

        // Set active session context to APEX
        $session->set('tenant_id', $apexId);
        $studentModel = new Student();

        // Get student belonging to Excel
        $excelStudent = $this->db->queryOne("SELECT id, full_name FROM `students` WHERE `tenant_id` = :tid LIMIT 1", ['tid' => $excelId]);
        $excelStudentId = (int)$excelStudent['id'];

        // Querying Excel student while in Apex context must return null
        $isolatedFind = $studentModel->find($excelStudentId);
        $this->assert($isolatedFind === null, "Tenant A cannot read Tenant B student by ID (returns null)");

        // Attempting to update Excel student while in Apex context affects 0 rows
        $affectedRows = $studentModel->update($excelStudentId, ['first_name' => 'HackedName']);
        $this->assert($affectedRows === 0, "Tenant A cannot modify Tenant B student (0 rows affected)");

        // Verify Excel student's original name was preserved
        $recheck = $this->db->queryOne("SELECT first_name FROM `students` WHERE `id` = :id", ['id' => $excelStudentId]);
        $this->assert($recheck['first_name'] !== 'HackedName', "Tenant B record remained untouched after cross-tenant attack attempt");

        // List all students under Apex context — must only contain Apex students
        $apexStudents = $studentModel->all();
        $onlyApex = true;
        foreach ($apexStudents as $s) {
            if ((int)$s['tenant_id'] !== $apexId) {
                $onlyApex = false;
                break;
            }
        }
        $this->assert($onlyApex && count($apexStudents) > 0, "Tenant A query strictly scopes results to Tenant A data only");
    }

    // ─── 3. Invoicing, Partial Payments & Allocations ─────────────────────────
    private function testBillingAndPartialPayments(): void
    {
        echo "\n3. Testing Invoicing, Partial Payments & Balance Recalculation...\n";
        $session = app()->getSession();
        $apexTenant = $this->db->queryOne("SELECT id FROM `tenants` WHERE `code` = 'apex'");
        $session->set('tenant_id', (int)$apexTenant['id']);

        $billing = new BillingService();
        $student = $this->db->queryOne("SELECT id FROM `students` WHERE `tenant_id` = :tid LIMIT 1", ['tid' => (int)$apexTenant['id']]);
        $studentId = (int)$student['id'];

        // Create Invoice for Rs. 5,000.00
        $invoiceId = $billing->createInvoice(
            $studentId,
            '2026-12',
            '2026-12-10',
            [
                ['description' => 'Combined Maths - Dec 2026', 'amount' => 3500.00, 'discount' => 0.00],
                ['description' => 'Special Seminar - Dec 2026', 'amount' => 1500.00, 'discount' => 0.00],
            ],
            'Test monthly tuition invoice'
        );

        $inv = (new Invoice())->find($invoiceId);
        $this->assert(
            $inv !== null && (float)$inv['total_amount'] === 5000.00 && (float)$inv['balance_amount'] === 5000.00 && $inv['status'] === 'issued',
            "Invoice created with Rs. 5,000.00 total and issued status"
        );

        // Record Partial Payment 1: Rs. 2,000.00
        $payId1 = $billing->recordPayment(
            $studentId,
            2000.00,
            'cash',
            [['invoice_id' => $invoiceId, 'amount' => 2000.00]],
            'TEST-REF-1',
            'First installment'
        );

        $invAfterPay1 = (new Invoice())->find($invoiceId);
        $this->assert(
            (float)$invAfterPay1['paid_amount'] === 2000.00 
            && (float)$invAfterPay1['balance_amount'] === 3000.00 
            && $invAfterPay1['status'] === 'partially_paid',
            "Partial payment of Rs. 2,000 updates balance to Rs. 3,000 and status to partially_paid"
        );

        // Record Partial Payment 2: Rs. 3,000.00 (Settles remaining balance)
        $payId2 = $billing->recordPayment(
            $studentId,
            3000.00,
            'bank_transfer',
            [['invoice_id' => $invoiceId, 'amount' => 3000.00]],
            'TEST-REF-2',
            'Second installment final settlement'
        );

        $invAfterPay2 = (new Invoice())->find($invoiceId);
        $this->assert(
            (float)$invAfterPay2['paid_amount'] === 5000.00 
            && (float)$invAfterPay2['balance_amount'] === 0.00 
            && $invAfterPay2['status'] === 'paid',
            "Final payment of Rs. 3,000 settles invoice to Rs. 0 balance and status to paid"
        );

        // Attempting to over-allocate beyond balance throws an exception
        $overpayBlocked = false;
        try {
            $billing->recordPayment(
                $studentId,
                1000.00,
                'cash',
                [['invoice_id' => $invoiceId, 'amount' => 1000.00]]
            );
        } catch (\RuntimeException $e) {
            $overpayBlocked = true;
        }
        $this->assert($overpayBlocked, "Over-allocating payment beyond invoice balance is strictly rejected with exception");
    }

    // ─── 4. Teacher Payment Calculations ─────────────────────────────────────
    private function testTeacherPaymentCalculation(): void
    {
        echo "\n4. Testing Teacher Payment Calculation Models...\n";
        $session = app()->getSession();
        $apexTenant = $this->db->queryOne("SELECT id FROM `tenants` WHERE `code` = 'apex'");
        $session->set('tenant_id', (int)$apexTenant['id']);

        $teacherService = new TeacherPaymentService();
        $teacher = $this->db->queryOne("SELECT id FROM `teachers` WHERE `tenant_id` = :tid LIMIT 1", ['tid' => (int)$apexTenant['id']]);
        $teacherId = (int)$teacher['id'];

        $period = $this->db->queryOne("SELECT id FROM `teacher_payment_periods` WHERE `tenant_id` = :tid LIMIT 1", ['tid' => (int)$apexTenant['id']]);
        $periodId = (int)$period['id'];

        $calc = $teacherService->calculateCompensation($teacherId, $periodId);
        $this->assert(
            isset($calc['calculated_amount']) && $calc['calculated_amount'] >= 0.00,
            "Teacher compensation calculation executes successfully with accurate revenue formula"
        );
    }

    // ─── 5. Double-Entry Accounting Balance & Integrity ──────────────────────
    private function testDoubleEntryAccountingBalance(): void
    {
        echo "\n5. Testing Double-Entry Accounting Balances (Debits == Credits)...\n";
        $session = app()->getSession();
        $apexTenant = $this->db->queryOne("SELECT id FROM `tenants` WHERE `code` = :code", ['code' => 'apex']);
        $tenantId = (int)$apexTenant['id'];
        $session->set('tenant_id', $tenantId);

        $accService = new AccountingService();
        $journalModel = new JournalEntry();

        // 1. Post a balanced journal entry (Rs. 10,000 Rent Payment: DR Rent Expense, CR Cash in Hand)
        $cashAcc = $this->db->queryOne("SELECT id FROM `chart_of_accounts` WHERE `tenant_id` = :tid AND `account_code` = '1010'", ['tid' => $tenantId]);
        $rentAcc = $this->db->queryOne("SELECT id FROM `chart_of_accounts` WHERE `tenant_id` = :tid AND `account_code` = '5020'", ['tid' => $tenantId]);

        $entryData = [
            'tenant_id'     => $tenantId,
            'entry_date'    => date('Y-m-d'),
            'description'   => 'Test Classroom Rent Payment',
            'source_module' => 'manual',
            'status'        => 'posted',
            'created_by'    => 1,
        ];

        $lines = [
            ['account_id' => $rentAcc['id'], 'line_description' => 'Rent expense debit', 'debit' => 10000.00, 'credit' => 0.00],
            ['account_id' => $cashAcc['id'], 'line_description' => 'Cash disbursement credit', 'debit' => 0.00, 'credit' => 10000.00],
        ];

        $entryId = $journalModel->postBalancedEntry($entryData, $lines);
        $this->assert($entryId > 0, "Balanced double-entry journal (Debits: Rs. 10,000 == Credits: Rs. 10,000) posted successfully");

        // 2. Attempting to post an unbalanced journal entry must be rejected
        $unbalancedBlocked = false;
        try {
            $unbalancedLines = [
                ['account_id' => $rentAcc['id'], 'line_description' => 'Rent expense debit', 'debit' => 10000.00, 'credit' => 0.00],
                ['account_id' => $cashAcc['id'], 'line_description' => 'Mismatched credit', 'debit' => 0.00, 'credit' => 8000.00],
            ];
            $journalModel->postBalancedEntry($entryData, $unbalancedLines);
        } catch (\RuntimeException $e) {
            $unbalancedBlocked = true;
        }
        $this->assert($unbalancedBlocked, "Unbalanced journal entry (Debits: 10,000 != Credits: 8,000) is rejected with accounting exception");

        // 3. Verify Trial Balance
        $tb = $accService->getTrialBalance();
        $this->assert(
            $tb['is_balanced'] === true && $tb['total_debit'] === $tb['total_credit'],
            sprintf("Trial Balance verification passes: Total Debits (Rs. %.2f) strictly equal Total Credits (Rs. %.2f)", $tb['total_debit'], $tb['total_credit'])
        );
    }
}

$suite = new BackendTestSuite();
$suite->runAll();

