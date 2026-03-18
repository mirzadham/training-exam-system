<!-- Admin Results List View -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0" style="font-weight: 700; font-size: 1.75rem;">Exam Results</h1>
    <div>
        <a href="<?= url('admin/results.php?' . http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export to CSV
        </a>
    </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="search" class="form-label">Search Participant</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?= e($search) ?>" placeholder="Name or IC...">
            </div>
            <div class="col-md-3">
                <label for="org" class="form-label">Organization</label>
                <select class="form-select" id="org" name="org">
                    <option value="">All Organizations</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?= $org['id'] ?>" <?= $orgFilter == $org['id'] ? 'selected' : '' ?>>
                            <?= e($org['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="in_progress" <?= $statusFilter == 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="submitted" <?= $statusFilter == 'submitted' ? 'selected' : '' ?>>Submitted (Manual)</option>
                    <option value="time_up" <?= $statusFilter == 'time_up' ? 'selected' : '' ?>>Time Up (Auto)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary w-50">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="<?= url('admin/results.php') ?>" class="btn btn-outline-secondary w-50">
                    Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($attempts)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-clipboard2-x display-4"></i>
                <p class="mt-3">No exam attempts found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Participant</th>
                            <th>Organization</th>
                            <th>Bank</th>
                            <th class="text-center">Score</th>
                            <th class="text-center">Result</th>
                            <th>Status/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $a): ?>
                            <tr>
                                <td>
                                    <strong><?= e($a['participant_name']) ?></strong><br>
                                    <small class="text-muted">IC: <?= e($a['ic_number']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= e($a['organization_code']) ?></span><br>
                                    <small><?= e($a['organization_name']) ?></small>
                                </td>
                                <td><small><?= e($a['bank_title']) ?></small></td>
                                <td class="text-center">
                                    <?php if ($a['status'] === 'in_progress'): ?>
                                        <span class="text-muted">—</span>
                                    <?php else: ?>
                                        <span class="fs-5 fw-bold"><?= number_format((float)$a['score_percent'], 1) ?>%</span><br>
                                        <span class="badge bg-light text-dark border">
                                            <span class="text-success"><?= $a['correct_count'] ?></span> /
                                            <span class="text-danger"><?= $a['wrong_count'] ?></span> /
                                            <span class="text-warning"><?= $a['unanswered_count'] ?></span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    if ($a['status'] === 'in_progress') {
                                        echo '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>In Progress</span>';
                                    } else {
                                        $badge = 'bg-secondary';
                                        if ($a['result'] === 'pass') $badge = 'bg-success';
                                        elseif ($a['result'] === 'fail') $badge = 'bg-danger';
                                        
                                        echo '<span class="badge ' . $badge . ' text-uppercase fs-6">' . e($a['result']) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($a['status'] === 'in_progress'): ?>
                                        <small class="text-primary d-block">Started:</small>
                                        <small><?= date('d M Y, h:i A', strtotime($a['started_at'])) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted d-block">
                                            <?= $a['status'] === 'time_up' ? '<i class="bi bi-clock-history text-danger me-1"></i>Auto-Submit' : '<i class="bi bi-person-check text-success me-1"></i>Manual' ?>
                                        </small>
                                        <small><?= date('d M Y, h:i A', strtotime($a['submitted_at'])) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-0">
                <?php require VIEWS_PATH . '/layout/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
