<?php
/**
 * Public — Participant Registration
 * 
 * Registration form where participants enter their info
 * and select an organization before starting the exam.
 */

require_once __DIR__ . '/init.php';
require_once CONTROLLERS_PATH . '/RegistrationController.php';

$errors = [];
$data = [];
$organizations = Organization::getActive();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name'       => trim($_POST['full_name'] ?? ''),
        'ic_number'       => trim($_POST['ic_number'] ?? ''),
        'organization_id' => $_POST['organization_id'] ?? '',
        'email'           => trim($_POST['email'] ?? ''),
        'phone'           => trim($_POST['phone'] ?? ''),
        'course_name'     => trim($_POST['course_name'] ?? ''),
    ];

    $result = RegistrationController::register($data);

    if ($result['success']) {
        // Store participant info in session for exam flow
        $_SESSION['participant_id'] = $result['participant_id'];
        $_SESSION['bank_id'] = $result['bank_id'];

        setFlash('success', 'Registration successful! You may now start your exam.');
        redirect(url('exam.php'));
    } else {
        $errors = $result['errors'];
        setOldInput($data);
    }
}

$pageTitle = 'Register for Exam';
require_once VIEWS_PATH . '/layout/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Participant Registration</h4>
                <p class="mb-0 mt-1 opacity-75">Fill in your details below to register and take the exam</p>
            </div>
            <div class="card-body p-4">

                <!-- General Error -->
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= e($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <!-- Full Name -->
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text"
                                   class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                                   id="full_name" name="full_name"
                                   value="<?= e($data['full_name'] ?? '') ?>"
                                   placeholder="Enter your full name" required>
                            <?php if (isset($errors['full_name'])): ?>
                                <div class="invalid-feedback"><?= e($errors['full_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- IC Number -->
                    <div class="mb-3">
                        <label for="ic_number" class="form-label">IC Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text"
                                   class="form-control <?= isset($errors['ic_number']) ? 'is-invalid' : '' ?>"
                                   id="ic_number" name="ic_number"
                                   value="<?= e($data['ic_number'] ?? '') ?>"
                                   placeholder="e.g. 901225145678 (12 digits, no dashes)"
                                   maxlength="14" required>
                            <?php if (isset($errors['ic_number'])): ?>
                                <div class="invalid-feedback"><?= e($errors['ic_number']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Enter without dashes (e.g. 901225145678)</div>
                    </div>

                    <!-- Organization -->
                    <div class="mb-3">
                        <label for="organization_id" class="form-label">Organization <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                            <select class="form-select <?= isset($errors['organization_id']) ? 'is-invalid' : '' ?>"
                                    id="organization_id" name="organization_id" required>
                                <option value="">Select your organization</option>
                                <?php foreach ($organizations as $org): ?>
                                    <option value="<?= $org['id'] ?>" <?= ($data['organization_id'] ?? '') == $org['id'] ? 'selected' : '' ?>>
                                        <?= e($org['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['organization_id'])): ?>
                                <div class="invalid-feedback"><?= e($errors['organization_id']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">
                    <p class="text-muted"><small><i class="bi bi-info-circle me-1"></i>The following fields are optional.</small></p>

                    <div class="row">
                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email"
                                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       id="email" name="email"
                                       value="<?= e($data['email'] ?? '') ?>"
                                       placeholder="your.email@example.com">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="tel"
                                       class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                       id="phone" name="phone"
                                       value="<?= e($data['phone'] ?? '') ?>"
                                       placeholder="e.g. 01234567890">
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Course Name -->
                    <div class="mb-4">
                        <label for="course_name" class="form-label">Course / Program Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="course_name" name="course_name"
                                   value="<?= e($data['course_name'] ?? '') ?>"
                                   placeholder="e.g. Workplace Safety Training">
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Register & Start Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-3">
            <a href="<?= url('/') ?>" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
