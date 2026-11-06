<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h2 class="h3 fw-bold mb-1">Your Notifications</h2>
        <p class="text-muted mb-0">System alerts, fee payment reminders, attendance updates, and announcements.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-bell-slash fs-1 d-block mb-2 opacity-50"></i>
                        <h6>You have no notifications at this time.</h6>
                        <small>New alerts and system messages will appear here.</small>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($notifications as $n): ?>
                            <li class="list-group-item p-3 <?= $n['is_read'] ? 'bg-white' : 'bg-light' ?>">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white bg-primary p-2 mt-1" style="width: 36px; height: 36px;">
                                        <i class="bi bi-bell-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0 text-dark"><?= e($n['title']) ?></h6>
                                            <small class="text-muted"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></small>
                                        </div>
                                        <p class="small text-muted mb-1"><?= e($n['message']) ?></p>
                                        <?php if (!empty($n['link_url'])): ?>
                                            <a href="<?= e($n['link_url']) ?>" class="btn btn-sm btn-link p-0 text-decoration-none">View Details &rarr;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

