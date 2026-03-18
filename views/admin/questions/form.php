<!-- Question Create/Edit Form View -->
<?php
$isEdit = isset($question) && isset($question['id']);
$formAction = $isEdit
    ? url('admin/questions.php?action=edit&id=' . $question['id'])
    : url('admin/questions.php?action=create');
$formTitle = $isEdit ? 'Edit Question' : 'Add Question';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0" style="font-weight: 700; font-size: 1.75rem;"><?= $formTitle ?></h1>
    <a href="<?= url('admin/questions.php') ?>" class="btn btn-outline-secondary">
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
            <!-- Question Bank -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="question_bank_id" class="form-label">Question Bank <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errors['question_bank_id']) ? 'is-invalid' : '' ?>"
                            id="question_bank_id" name="question_bank_id" required>
                        <option value="">Select Question Bank</option>
                        <?php foreach ($questionBanks as $qb): ?>
                            <option value="<?= $qb['id'] ?>" <?= ($data['question_bank_id'] ?? '') == $qb['id'] ? 'selected' : '' ?>>
                                <?= e($qb['title']) ?> — <?= e($qb['organization_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['question_bank_id'])): ?>
                        <div class="invalid-feedback"><?= e($errors['question_bank_id']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Question Text -->
            <div class="mb-3">
                <label for="question_text" class="form-label">Question Text <span class="text-danger">*</span></label>
                <textarea class="form-control <?= isset($errors['question_text']) ? 'is-invalid' : '' ?>"
                          id="question_text" name="question_text" rows="3" required
                          placeholder="Enter the question..."><?= e($data['question_text'] ?? '') ?></textarea>
                <?php if (isset($errors['question_text'])): ?>
                    <div class="invalid-feedback"><?= e($errors['question_text']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Options -->
            <div class="row">
                <?php foreach (['A', 'B', 'C', 'D'] as $letter): ?>
                    <?php $field = 'option_' . strtolower($letter); ?>
                    <div class="col-md-6 mb-3">
                        <label for="<?= $field ?>" class="form-label">
                            Option <?= $letter ?> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold"><?= $letter ?></span>
                            <input type="text" 
                                   class="form-control <?= isset($errors[$field]) ? 'is-invalid' : '' ?>"
                                   id="<?= $field ?>" name="<?= $field ?>"
                                   value="<?= e($data[$field] ?? '') ?>"
                                   placeholder="Enter option <?= $letter ?>..." required>
                            <?php if (isset($errors[$field])): ?>
                                <div class="invalid-feedback"><?= e($errors[$field]) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Correct Answer -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="correct_option" class="form-label">Correct Answer <span class="text-danger">*</span></label>
                    <select class="form-select <?= isset($errors['correct_option']) ? 'is-invalid' : '' ?>"
                            id="correct_option" name="correct_option" required>
                        <option value="">Select correct answer</option>
                        <?php foreach (['A', 'B', 'C', 'D'] as $letter): ?>
                            <option value="<?= $letter ?>" <?= ($data['correct_option'] ?? '') === $letter ? 'selected' : '' ?>>
                                <?= $letter ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['correct_option'])): ?>
                        <div class="invalid-feedback"><?= e($errors['correct_option']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Explanation -->
            <div class="mb-3">
                <label for="explanation" class="form-label">Explanation</label>
                <textarea class="form-control" id="explanation" name="explanation" rows="2"
                          placeholder="Optional: explain why this is the correct answer..."><?= e($data['explanation'] ?? '') ?></textarea>
            </div>

            <!-- Submit -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Question' : 'Create Question' ?>
                </button>
                <a href="<?= url('admin/questions.php') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
