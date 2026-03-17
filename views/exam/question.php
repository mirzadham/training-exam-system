<!-- Exam Question View -->
<?php
$progressPercent = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;
?>

<!-- Timer & Progress Header -->
<div class="card mb-3 border-primary">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-4">
                <strong><i class="bi bi-journal-text me-1"></i><?= e($attempt['bank_title']) ?></strong>
            </div>
            <div class="col-md-4 text-center">
                <span class="badge bg-primary fs-6" id="timer">
                    <i class="bi bi-clock me-1"></i><span id="timer-display">Loading...</span>
                </span>
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted">
                    Answered: <strong><?= $answeredCount ?></strong> / <?= $totalQuestions ?>
                </small>
            </div>
        </div>
        <div class="progress mt-2" style="height: 4px;">
            <div class="progress-bar" role="progressbar" style="width: <?= $progressPercent ?>%"
                 aria-valuenow="<?= $progressPercent ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Question Area -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-bold">Question <?= $currentQ ?> of <?= $totalQuestions ?></span>
                <?php if ($currentAnswer['selected_option']): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Answered</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-circle me-1"></i>Not Answered</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Question Text -->
                <h5 class="card-title mb-4"><?= e($currentAnswer['question_text']) ?></h5>

                <!-- Answer Form -->
                <form method="POST" action="<?= url('exam.php?q=' . $currentQ) ?>" id="answer-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="save_answer" value="1">
                    <input type="hidden" name="question_id" value="<?= $currentAnswer['question_id'] ?>">
                    <input type="hidden" name="next_q" id="next_q" value="<?= min($currentQ + 1, $totalQuestions) ?>">

                    <?php foreach (['A', 'B', 'C', 'D'] as $letter): ?>
                        <?php
                        $optionField = 'option_' . strtolower($letter);
                        $isSelected = $currentAnswer['selected_option'] === $letter;
                        $cardClass = $isSelected ? 'border-primary bg-primary bg-opacity-10' : '';
                        ?>
                        <div class="card mb-2 option-card <?= $cardClass ?>" style="cursor: pointer;"
                             onclick="selectOption('<?= $letter ?>')">
                            <div class="card-body py-2 px-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="selected_option" id="option_<?= $letter ?>"
                                           value="<?= $letter ?>" <?= $isSelected ? 'checked' : '' ?>>
                                    <label class="form-check-label w-100" for="option_<?= $letter ?>">
                                        <strong class="me-2"><?= $letter ?>.</strong>
                                        <?= e($currentAnswer[$optionField]) ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <?php if ($currentQ > 1): ?>
                                <button type="submit" class="btn btn-outline-secondary" 
                                        onclick="document.getElementById('next_q').value = <?= $currentQ - 1 ?>;">
                                    <i class="bi bi-arrow-left me-1"></i>Previous
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($currentQ < $totalQuestions): ?>
                                <button type="submit" class="btn btn-primary">
                                    Save & Next <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary" 
                                        onclick="document.getElementById('next_q').value = <?= $totalQuestions ?>;">
                                    <i class="bi bi-check me-1"></i>Save Answer
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Question Navigator Sidebar -->
    <div class="col-lg-3 mt-3 mt-lg-0">
        <div class="card">
            <div class="card-header fw-bold">
                <i class="bi bi-grid-3x3 me-1"></i>Question Navigator
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($allAnswers as $ans): ?>
                        <?php
                        $btnClass = 'btn-outline-secondary';
                        if ($ans['question_order'] == $currentQ) {
                            $btnClass = 'btn-primary';
                        } elseif ($ans['selected_option'] !== null) {
                            $btnClass = 'btn-success';
                        }
                        ?>
                        <a href="<?= url('exam.php?q=' . $ans['question_order']) ?>"
                           class="btn btn-sm <?= $btnClass ?>" style="min-width: 38px;">
                            <?= $ans['question_order'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="d-flex justify-content-between small text-muted">
                    <span><span class="badge bg-success">&nbsp;</span> Answered</span>
                    <span><span class="badge bg-outline-secondary border">&nbsp;</span> Unanswered</span>
                    <span><span class="badge bg-primary">&nbsp;</span> Current</span>
                </div>

                <!-- Submit Button -->
                <hr>
                <form method="POST" action="<?= url('submit_exam.php') ?>" id="submit-exam-form">
                    <?= csrf_field() ?>
                    <div class="d-grid">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#submitExamModal">
                            <i class="bi bi-send me-1"></i>Submit Exam
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Submit Exam Modal -->
<div class="modal fade" id="submitExamModal" tabindex="-1" aria-labelledby="submitExamModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submitExamModalLabel">Submit Exam</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to submit? You cannot change your answers after submission.</p>
        <p class="mb-0">Answered: <strong><?= $answeredCount ?></strong> / <?= $totalQuestions ?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" onclick="document.getElementById('submit-exam-form').submit();">Submit Exam</button>
      </div>
    </div>
  </div>
</div>

<!-- Timer & Auto-save JavaScript -->
<script>
(function() {
    let remainingSeconds = <?= $remainingSeconds ?>;
    const timerDisplay = document.getElementById('timer-display');
    const timerBadge = document.getElementById('timer');

    function formatTime(seconds) {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        if (h > 0) {
            return h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function updateTimer() {
        if (remainingSeconds <= 0) {
            timerDisplay.textContent = '00:00';
            timerBadge.classList.remove('bg-primary', 'bg-warning');
            timerBadge.classList.add('bg-danger');
            // Auto-submit
            alert('Time is up! Your exam will be auto-submitted.');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url('submit_exam.php') ?>';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = document.getElementById('csrf_token_field').value;
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
            return;
        }

        timerDisplay.textContent = formatTime(remainingSeconds);

        // Warning color when < 5 minutes
        if (remainingSeconds <= 300) {
            timerBadge.classList.remove('bg-primary');
            timerBadge.classList.add('bg-danger');
            if (remainingSeconds <= 60) {
                timerBadge.classList.add('timer-pulse');
            }
        } else if (remainingSeconds <= 600) {
            timerBadge.classList.remove('bg-primary');
            timerBadge.classList.add('bg-warning', 'text-dark');
        }

        remainingSeconds--;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
})();

// Option card click handler
function selectOption(letter) {
    const radio = document.getElementById('option_' + letter);
    radio.checked = true;

    // Visual feedback
    document.querySelectorAll('.option-card').forEach(card => {
        card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
    });
    radio.closest('.option-card').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');

    // AJAX auto-save
    const formData = new FormData();
    formData.append('question_id', document.querySelector('input[name="question_id"]').value);
    formData.append('selected_option', letter);
    formData.append('csrf_token', document.getElementById('csrf_token_field').value);

    fetch('<?= url('save_answer.php') ?>', {
        method: 'POST',
        body: formData
    }).catch(err => console.warn('Auto-save failed:', err));
}
</script>
