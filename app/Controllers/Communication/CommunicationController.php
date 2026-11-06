<?php

declare(strict_types=1);

namespace App\Controllers\Communication;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Announcement;
use App\Models\Notification;
use App\Services\AuditService;

class CommunicationController extends Controller
{
    private Announcement $announcementModel;
    private Notification $notificationModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->announcementModel = new Announcement();
        $this->notificationModel = new Notification();
        $this->audit             = new AuditService();
        $this->db                = Database::getInstance();
    }

    /**
     * List announcements.
     */
    public function announcements(Request $request): void
    {
        $tenantId = current_tenant_id();

        $announcements = $this->db->query(
            "SELECT a.*, u.name AS author_name, b.name AS batch_name, br.name AS branch_name
             FROM `announcements` a
             JOIN `users` u ON a.created_by = u.id
             LEFT JOIN `batches` b ON a.batch_id = b.id
             LEFT JOIN `branches` br ON a.branch_id = br.id
             WHERE a.`tenant_id` = :tid
             ORDER BY a.`published_at` DESC",
            ['tid' => $tenantId]
        );

        $batches = $this->db->query("SELECT id, name FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active'", ['tid' => $tenantId]);
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid", ['tid' => $tenantId]);

        $this->render('communication/announcements', [
            'title'         => 'Institute Announcements & Notice Board',
            'activeMenu'    => 'communication',
            'announcements' => $announcements,
            'batches'       => $batches,
            'branches'      => $branches,
        ]);
    }

    /**
     * Post a new announcement.
     */
    public function storeAnnouncement(Request $request): void
    {
        $rules = [
            'title'           => 'required',
            'content'         => 'required',
            'target_audience' => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $tenantId = current_tenant_id();

        try {
            $id = $this->announcementModel->create([
                'tenant_id'       => $tenantId,
                'branch_id'       => $request->input('branch_id') ? (int)$request->input('branch_id') : null,
                'batch_id'        => $request->input('batch_id') ? (int)$request->input('batch_id') : null,
                'title'           => trim((string)$request->input('title')),
                'content'         => trim((string)$request->input('content')),
                'target_audience' => (string)$request->input('target_audience'),
                'is_published'    => 1,
                'published_at'    => date('Y-m-d H:i:s'),
                'created_by'      => auth_id() ?? 1,
            ]);

            $this->audit->log('announcement_posted', 'announcements', $id, "Announcement '{$request->input('title')}' published to {$request->input('target_audience')}");
            $this->redirect(url('communication/announcements'), 'success', 'Announcement published to institute notice board.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to publish announcement: ' . $e->getMessage());
        }
    }

    /**
     * Notifications feed for logged-in user.
     */
    public function notifications(Request $request): void
    {
        $tenantId = current_tenant_id();
        $userId   = auth_id() ?? 1;

        $notifications = $this->db->query(
            "SELECT * FROM `notifications` WHERE `tenant_id` = :tid AND `user_id` = :uid ORDER BY `id` DESC LIMIT 50",
            ['tid' => $tenantId, 'uid' => $userId]
        );

        $this->render('communication/notifications', [
            'title'         => 'Notifications',
            'activeMenu'    => 'notifications',
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request): void
    {
        $id       = (int) $request->input('id');
        $tenantId = current_tenant_id();
        $userId   = auth_id() ?? 1;

        $this->db->execute(
            "UPDATE `notifications` SET `is_read` = 1, `read_at` = NOW() WHERE `id` = :id AND `user_id` = :uid AND `tenant_id` = :tid",
            ['id' => $id, 'uid' => $userId, 'tid' => $tenantId]
        );

        $this->json(['success' => true]);
    }
}

