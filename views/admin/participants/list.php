<!-- Participants List View -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people me-2"></i>Participants</h2>
    <div>
        <a href="<?= url('admin/participants.php?' . http_build_query(array_merge($_GET, ['export' => 'csv']))) ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export to CSV
        </a>
    </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?= e($search) ?>" placeholder="Search by name or IC...">
            </div>
            <div class="col-md-4">
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
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="<?= url('admin/participants.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($participants)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-people display-4"></i>
                <p class="mt-3">No participants registered yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>IC Number</th>
                            <th>Organization</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Course</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= e($p['full_name']) ?></strong></td>
                                <td><code><?= e($p['ic_number']) ?></code></td>
                                <td>
                                    <span class="badge bg-secondary"><?= e($p['organization_code']) ?></span>
                                    <?= e($p['organization_name']) ?>
                                </td>
                                <td><?= $p['email'] ? e($p['email']) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= $p['phone'] ? e($p['phone']) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= $p['course_name'] ? e($p['course_name']) : '<span class="text-muted">—</span>' ?></td>
                                <td><small><?= date('d M Y H:i', strtotime($p['created_at'])) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted">
                <small>Showing <?= count($participants) ?> participant(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>
