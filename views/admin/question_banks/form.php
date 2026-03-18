<!-- Question Bank Create/Edit Form View -->
<?php
$isEdit = isset($bank) && isset($bank['id']);
$formAction = $isEdit
    ? url('admin/question_banks.php?action=edit&id=' . $bank['id'])
    : url('admin/question_banks.php?action=create');
$formTitle = $isEdit ? 'Edit Question Bank' : 'Add Question Bank';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0" style="font-weight: 700; font-size: 1.75rem;"><?= $formTitle ?></h1>
    <a href="<?= url('admin/question_banks.php') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= e($errors['general']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $formAction ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <!-- Organization -->
                <div class="col-md-6 mb-3">
                    <label for="organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errors['organization_id']) ? 'is-invalid' : '' ?>"
                            id="organization_id" name="organization_id" required>
                        <option value="">Select Organization</option>
                        <?php foreach ($organizations as $org): ?>
                            <option value="<?= $org['id'] ?>" <?= ($data['organization_id'] ?? '') == $org['id'] ? 'selected' : '' ?>>
                                <?= e($org['name']) ?> (<?= e($org['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['organization_id'])): ?>
                        <div class="invalid-feedback"><?= e($errors['organization_id']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                           id="title" name="title" value="<?= e($data['title'] ?? '') ?>"
                           placeholder="e.g. Safety Assessment 2026" required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="invalid-feedback"><?= e($errors['title']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <!-- Duration -->
                <div class="col-md-4 mb-3">
                    <label for="duration_minutes" class="form-label">Duration (minutes)</label>
                    <input type="number" class="form-control <?= isset($errors['duration_minutes']) ? 'is-invalid' : '' ?>"
                           id="duration_minutes" name="duration_minutes"
                           value="<?= e($data['duration_minutes'] ?? '') ?>"
                           placeholder="e.g. 30" min="1">
                    <?php if (isset($errors['duration_minutes'])): ?>
                        <div class="invalid-feedback"><?= e($errors['duration_minutes']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Leave blank to auto-calculate (~1 min per question).</div>
                </div>

                <!-- Status -->
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= ($data['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <!-- Active Toggle -->
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                               <?= !empty($data['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">
                            <strong>Set as Active Bank</strong>
                            <br><small class="text-muted">Only one bank can be active per organization</small>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                          placeholder="Optional description..."><?= e($data['description'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Question Bank' : 'Create Question Bank' ?>
                </button>
                <a href="<?= url('admin/question_banks.php') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
