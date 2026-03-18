<!-- Question Bank List View -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0" style="font-weight: 700; font-size: 1.75rem;">Question Banks</h1>
    <a href="<?= url('admin/question_banks.php?action=create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Question Bank
    </a>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?= e($search) ?>" placeholder="Search by title or organization...">
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
                <a href="<?= url('admin/question_banks.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($questionBanks)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-folder display-4"></i>
                <p class="mt-3">No question banks found.</p>
                <a href="<?= url('admin/question_banks.php?action=create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Add Your First Question Bank
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Organization</th>
                            <th class="text-center">Questions</th>
                            <th class="text-center">Duration</th>
                            <th class="text-center">Active</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questionBanks as $i => $bank): ?>
                            <tr>
                                <td><?= $pagination['offset'] + $i + 1 ?></td>
                                <td>
                                    <strong><?= e($bank['title']) ?></strong>
                                    <?php if ($bank['description']): ?>
                                        <br><small class="text-muted"><?= e(mb_strimwidth($bank['description'], 0, 60, '...')) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= e($bank['organization_code']) ?></span>
                                    <?= e($bank['organization_name']) ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= url('admin/questions.php?bank=' . $bank['id']) ?>" class="text-decoration-none">
                                        <?= $bank['question_count'] ?> <i class="bi bi-arrow-right-short"></i>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?= $bank['duration_minutes'] ? $bank['duration_minutes'] . ' min' : '<span class="text-muted">—</span>' ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($bank['is_active']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($bank['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('admin/questions.php?action=create&bank_id=' . $bank['id']) ?>"
                                       class="btn btn-sm btn-outline-success" title="Add Question">
                                        <i class="bi bi-plus-lg"></i>
                                    </a>
                                    <a href="<?= url('admin/question_banks.php?action=edit&id=' . $bank['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="<?= url("admin/question_banks.php?action=delete&id={$bank['id']}") ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this question bank and all its questions?');">
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
            <div class="card-footer p-0">
                <?php require VIEWS_PATH . '/layout/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
