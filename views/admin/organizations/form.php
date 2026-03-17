<!-- Organization Create/Edit Form View -->
<?php
// Determine if editing or creating
$isEdit = isset($org) && isset($org['id']);
$formAction = $isEdit
    ? url('admin/organizations.php?action=edit&id=' . $org['id'])
    : url('admin/organizations.php?action=create');
$formTitle = $isEdit ? 'Edit Organization' : 'Add Organization';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building me-2"></i><?= $formTitle ?></h2>
    <a href="<?= url('admin/organizations.php') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<!-- General Error -->
<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-2"></i><?= e($errors['general']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= $formAction ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <!-- Organization Name -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Organization Name <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                           id="name" 
                           name="name" 
                           value="<?= e($data['name'] ?? '') ?>"
                           placeholder="e.g. PHG Skills Development"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Organization Code -->
                <div class="col-md-3 mb-3">
                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                           id="code" 
                           name="code" 
                           value="<?= e($data['code'] ?? '') ?>"
                           placeholder="e.g. PHG-SKILLS"
                           required>
                    <?php if (isset($errors['code'])): ?>
                        <div class="invalid-feedback"><?= e($errors['code']) ?></div>
                    <?php endif; ?>
                    <div class="form-text">Letters, numbers, hyphens, underscores only. Will be stored uppercase.</div>
                </div>

                <!-- Status -->
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>" 
                            id="status" name="status">
                        <option value="active" <?= ($data['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($data['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" 
                          id="description" 
                          name="description" 
                          rows="3" 
                          placeholder="Optional description of the organization..."><?= e($data['description'] ?? '') ?></textarea>
            </div>

            <!-- Submit -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Organization' : 'Create Organization' ?>
                </button>
                <a href="<?= url('admin/organizations.php') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
