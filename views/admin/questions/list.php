<!-- Questions List View -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-question-circle me-2"></i>Questions</h2>
    <a href="<?= url('admin/questions.php?action=create' . ($bankFilter ? '&bank_id=' . e($bankFilter) : '')) ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Question
    </a>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="<?= e($search) ?>" placeholder="Search question text...">
            </div>
            <div class="col-md-4">
                <label for="bank" class="form-label">Question Bank</label>
                <select class="form-select" id="bank" name="bank">
                    <option value="">All Banks</option>
                    <?php foreach ($questionBanks as $qb): ?>
                        <option value="<?= $qb['id'] ?>" <?= $bankFilter == $qb['id'] ? 'selected' : '' ?>>
                            <?= e($qb['title']) ?> (<?= e($qb['organization_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="<?= url('admin/questions.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($questions)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-question-circle display-4"></i>
                <p class="mt-3">No questions found.</p>
                <a href="<?= url('admin/questions.php?action=create') ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Add Your First Question
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Question</th>
                            <th>Bank</th>
                            <th class="text-center" style="width: 80px;">Answer</th>
                            <th class="text-end" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $i => $q): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <strong><?= e(mb_strimwidth($q['question_text'], 0, 100, '...')) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        A: <?= e(mb_strimwidth($q['option_a'], 0, 25, '…')) ?> |
                                        B: <?= e(mb_strimwidth($q['option_b'], 0, 25, '…')) ?> |
                                        C: <?= e(mb_strimwidth($q['option_c'], 0, 25, '…')) ?> |
                                        D: <?= e(mb_strimwidth($q['option_d'], 0, 25, '…')) ?>
                                    </small>
                                </td>
                                <td>
                                    <small><?= e($q['bank_title']) ?><br>
                                    <span class="text-muted"><?= e($q['organization_name']) ?></span></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6"><?= e($q['correct_option']) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('admin/questions.php?action=edit&id=' . $q['id']) ?>"
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="<?= url("admin/questions.php?action=delete&id={$q['id']}") ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this question?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="bank_id" value="<?= $q['question_bank_id'] ?>">
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
                <small>Showing <?= count($questions) ?> question(s)</small>
            </div>
        <?php endif; ?>
    </div>
</div>
