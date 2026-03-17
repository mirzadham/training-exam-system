<!-- Organization List View -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building me-2"></i>Organizations</h2>
    <a href="<?= url('admin/organizations.php?action=create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Organization
    </a>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= e($search) ?>" placeholder="Search by name or code...">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="<?= url('admin/organizations.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Organization Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($organizations)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-building display-4"></i>
                <p class="mt-3">No organizations found.</p>
                <a href="<?= url('admin/organizations.php?action=create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Add Your First Organization
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($organizations as $i => $org): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= e($org['name']) ?></strong>
                                    <?php if ($org['description']): ?>
                                        <br><small class="text-muted"><?= e(mb_strimwidth($org['description'], 0, 80, '...')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?= e($org['code']) ?></span></td>
                                <td>
                                    <?php if ($org['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= date('d M Y', strtotime($org['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <a href="<?= url('admin/organizations.php?action=edit&id=' . $org['id']) ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" 
                                          action="<?= url('admin/organizations.php?action=delete&id=' . $org['id']) ?>" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this organization?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted">
                <small>Showing <?= count($organizations) ?> organization(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>
