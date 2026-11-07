<?php

declare(strict_types=1);

namespace App\Controllers\Academic;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\LearningMaterial;
use App\Services\AuditService;

class MaterialController extends Controller
{
    private LearningMaterial $materialModel;
    private Database $db;
    private AuditService $audit;

    public function __construct()
    {
        $this->materialModel = new LearningMaterial();
        $this->db            = Database::getInstance();
        $this->audit         = new AuditService();
    }

    public function index(Request $request): void
    {
        $tenantId  = current_tenant_id();
        $batchId   = (int) $request->query('batch_id', 0);
        $subjectId = (int) $request->query('subject_id', 0);
        $search    = trim((string) $request->query('search', ''));

        $sql = "SELECT lm.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name
                FROM `learning_materials` lm
                JOIN `batches` b ON lm.batch_id = b.id
                JOIN `subjects` s ON lm.subject_id = s.id
                LEFT JOIN `teachers` t ON lm.teacher_id = t.id
                WHERE lm.`tenant_id` = :tid";
        $params = ['tid' => $tenantId];

        if ($batchId > 0) {
            $sql .= " AND lm.`batch_id` = :bid";
            $params['bid'] = $batchId;
        }

        if ($subjectId > 0) {
            $sql .= " AND lm.`subject_id` = :sid";
            $params['sid'] = $subjectId;
        }

        if (!empty($search)) {
            $sql .= " AND (lm.`title` LIKE :s1 OR lm.`description` LIKE :s2)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $sql .= " ORDER BY lm.`id` DESC";
        $materials = $this->db->query($sql, $params);

        $batches  = $this->db->query("SELECT id, name FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $subjects = $this->db->query("SELECT id, name FROM `subjects` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('academic/materials/index', [
            'title'      => 'Learning Materials & Resources',
            'activeMenu' => 'materials',
            'materials'  => $materials,
            'batches'    => $batches,
            'subjects'   => $subjects,
            'filters'    => [
                'batch_id'   => $batchId,
                'subject_id' => $subjectId,
                'search'     => $search,
            ],
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = current_tenant_id();
        $batches  = $this->db->query("SELECT id, name, subject_id, teacher_id FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $subjects = $this->db->query("SELECT id, name FROM `subjects` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $teachers = $this->db->query("SELECT id, full_name FROM `teachers` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('academic/materials/create', [
            'title'      => 'Upload Learning Material',
            'activeMenu' => 'materials',
            'batches'    => $batches,
            'subjects'   => $subjects,
            'teachers'   => $teachers,
        ]);
    }

    public function store(Request $request): void
    {
        $rules = [
            'title'         => 'required|min:3|max:200',
            'batch_id'      => 'required|numeric',
            'material_type' => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $tenantId     = current_tenant_id();
        $batchId      = (int) $request->input('batch_id');
        $materialType = (string) $request->input('material_type');
        $title        = trim((string) $request->input('title'));
        $description  = trim((string) $request->input('description', ''));
        $externalLink = trim((string) $request->input('external_link', ''));

        //Lookup
        $batch = $this->db->queryOne("SELECT subject_id, teacher_id FROM `batches` WHERE `id` = :id AND `tenant_id` = :tid", ['id' => $batchId, 'tid' => $tenantId]);
        if (!$batch) {
            $this->back('error', 'Selected class batch not found.');
            return;
        }

        $subjectId = (int) $batch['subject_id'];
        $teacherId = !empty($request->input('teacher_id')) ? (int) $request->input('teacher_id') : ((int) ($batch['teacher_id'] ?? 1));

        $filePath = null;
        $fileSize = null;

        //Upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $maxBytes = (int) env('UPLOAD_MAX_SIZE_MB', 10) * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $this->back('error', 'Uploaded file exceeds the maximum allowed size of 10 MB.');
                return;
            }

            $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'zip'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                $this->back('error', "File type .{$ext} is not allowed. Supported formats: PDF, DOCX, PPTX, XLSX, Images, ZIP.");
                return;
            }

            $safeFileName = 'material_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination  = STORAGE_PATH . '/uploads/materials/' . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $filePath = 'storage/uploads/materials/' . $safeFileName;
                $fileSize = (int) $file['size'];
            }
        }

        $id = $this->materialModel->create([
            'tenant_id'     => $tenantId,
            'batch_id'      => $batchId,
            'subject_id'    => $subjectId,
            'teacher_id'    => $teacherId,
            'title'         => $title,
            'description'   => !empty($description) ? $description : null,
            'material_type' => $materialType,
            'file_path'     => $filePath,
            'file_size'     => $fileSize,
            'external_link' => !empty($externalLink) ? $externalLink : null,
            'is_published'  => 1,
        ]);

        $this->audit->log('material_uploaded', 'learning_materials', $id, "Learning material '{$title}' uploaded for batch #{$batchId}");

        $this->redirect(url('academic/materials'), 'success', 'Learning material uploaded successfully.');
    }

    public function download(Request $request, string $id = ''): void
    {
        $materialId = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId   = current_tenant_id();

        $material = $this->db->queryOne(
            "SELECT * FROM `learning_materials` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $materialId, 'tid' => $tenantId]
        );

        if (!$material || empty($material['file_path'])) {
            $this->redirect(url('academic/materials'), 'error', 'Material file not found.');
            return;
        }

        $fullPath = ROOT_PATH . '/' . ltrim($material['file_path'], '/');
        if (!file_exists($fullPath)) {
            $this->redirect(url('academic/materials'), 'error', 'Attachment file is missing from storage.');
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
