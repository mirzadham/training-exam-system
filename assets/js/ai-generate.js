/**
 * AI Question Generation — Frontend Logic
 * 
 * Handles the Generate via AI modal workflow:
 *   State 1: Upload form
 *   State 2: Loading spinner
 *   State 3: Review & edit generated questions
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── DOM References ─────────────────────────────────────────
    const modal            = document.getElementById('aiGenerateModal');
    if (!modal) return; // Not on the questions list page

    const formState        = document.getElementById('aiFormState');
    const loadingState     = document.getElementById('aiLoadingState');
    const reviewState      = document.getElementById('aiReviewState');
    const errorAlert       = document.getElementById('aiErrorAlert');
    const errorText        = document.getElementById('aiErrorText');
    const generateForm     = document.getElementById('aiGenerateForm');
    const reviewContainer  = document.getElementById('aiReviewContainer');
    const reviewCount      = document.getElementById('aiReviewCount');
    const saveAllBtn       = document.getElementById('aiSaveAllBtn');
    const cancelReviewBtn  = document.getElementById('aiCancelReview');
    const csrfToken        = document.getElementById('csrf_token_field')?.value 
                              || document.querySelector('input[name="csrf_token"]')?.value 
                              || '';

    const reviewFooter     = document.getElementById('aiReviewFooter');

    // ── State Management ───────────────────────────────────────
    function showState(state) {
        formState.classList.add('d-none');
        loadingState.classList.add('d-none');
        reviewState.classList.add('d-none');
        errorAlert.classList.add('d-none');
        state.classList.remove('d-none');

        // Show/hide review footer
        reviewFooter.style.display = (state === reviewState) ? '' : 'none';
    }

    function showError(message) {
        errorText.textContent = message;
        errorAlert.classList.remove('d-none');
    }

    function resetModal() {
        showState(formState);
        generateForm.reset();
        reviewContainer.innerHTML = '';
        errorAlert.classList.add('d-none');
    }

    // Reset when modal is closed
    modal.addEventListener('hidden.bs.modal', resetModal);

    // ── Generate Handler ───────────────────────────────────────
    generateForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        errorAlert.classList.add('d-none');

        const bankId       = document.getElementById('aiBankId').value;
        const pdfFile      = document.getElementById('aiPdfFile').files[0];
        const numQuestions = document.getElementById('aiNumQuestions').value;

        // Client-side validation
        if (!bankId) {
            showError('Please select a question bank.');
            return;
        }
        if (!pdfFile) {
            showError('Please select a PDF file.');
            return;
        }
        if (pdfFile.type !== 'application/pdf') {
            showError('Only PDF files are accepted.');
            return;
        }
        if (pdfFile.size > 20 * 1024 * 1024) {
            showError('File size exceeds 20 MB limit.');
            return;
        }

        // Build form data
        const formData = new FormData();
        formData.append('pdf_file', pdfFile);
        formData.append('num_questions', numQuestions);
        formData.append('csrf_token', csrfToken);

        showState(loadingState);

        try {
            const response = await fetch(AI_GENERATE_URL, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (!data.success) {
                showState(formState);
                showError(data.error || 'An unknown error occurred.');
                return;
            }

            // Show review UI
            renderReviewQuestions(data.questions, bankId);
            showState(reviewState);

        } catch (err) {
            showState(formState);
            showError('Network error: ' + err.message);
        }
    });

    // ── Render Review Questions ────────────────────────────────
    function renderReviewQuestions(questions, bankId) {
        reviewContainer.innerHTML = '';
        reviewContainer.dataset.bankId = bankId;
        reviewCount.textContent = questions.length;

        questions.forEach(function (q, index) {
            const card = createQuestionCard(q, index);
            reviewContainer.appendChild(card);
        });

        updateQuestionNumbers();
    }

    function createQuestionCard(q, index) {
        const card = document.createElement('div');
        card.className = 'card mb-3 ai-question-card';
        card.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <strong class="ai-q-number">Question #${index + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger ai-remove-btn" title="Remove this question">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Question Text</label>
                    <textarea class="form-control ai-field" data-field="question_text" rows="2">${escapeHtml(q.question_text)}</textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Option A</label>
                        <input type="text" class="form-control ai-field" data-field="option_a" value="${escapeAttr(q.option_a)}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Option B</label>
                        <input type="text" class="form-control ai-field" data-field="option_b" value="${escapeAttr(q.option_b)}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Option C</label>
                        <input type="text" class="form-control ai-field" data-field="option_c" value="${escapeAttr(q.option_c)}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Option D</label>
                        <input type="text" class="form-control ai-field" data-field="option_d" value="${escapeAttr(q.option_d)}">
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Correct Answer</label>
                        <select class="form-select ai-field" data-field="correct_option">
                            <option value="A" ${q.correct_option === 'A' ? 'selected' : ''}>A</option>
                            <option value="B" ${q.correct_option === 'B' ? 'selected' : ''}>B</option>
                            <option value="C" ${q.correct_option === 'C' ? 'selected' : ''}>C</option>
                            <option value="D" ${q.correct_option === 'D' ? 'selected' : ''}>D</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Explanation <small class="text-muted">(optional)</small></label>
                        <input type="text" class="form-control ai-field" data-field="explanation" value="${escapeAttr(q.explanation || '')}">
                    </div>
                </div>
            </div>
        `;

        // Remove button handler
        card.querySelector('.ai-remove-btn').addEventListener('click', function () {
            card.remove();
            updateQuestionNumbers();
        });

        return card;
    }

    function updateQuestionNumbers() {
        const cards = reviewContainer.querySelectorAll('.ai-question-card');
        reviewCount.textContent = cards.length;
        cards.forEach(function (card, i) {
            card.querySelector('.ai-q-number').textContent = 'Question #' + (i + 1);
        });

        // Disable save if no questions remain
        saveAllBtn.disabled = cards.length === 0;
    }

    // ── Save All Handler ──────────────────────────────────────
    saveAllBtn.addEventListener('click', async function () {
        const cards    = reviewContainer.querySelectorAll('.ai-question-card');
        const bankId   = reviewContainer.dataset.bankId;
        const questions = [];

        cards.forEach(function (card) {
            const q = {};
            card.querySelectorAll('.ai-field').forEach(function (field) {
                q[field.dataset.field] = field.value;
            });
            questions.push(q);
        });

        if (questions.length === 0) {
            showError('No questions to save.');
            return;
        }

        saveAllBtn.disabled = true;
        saveAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        try {
            const response = await fetch(AI_SAVE_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    question_bank_id: bankId,
                    questions: questions,
                }),
            });

            const data = await response.json();

            if (data.success) {
                // Close modal and reload page to show new questions
                const bsModal = bootstrap.Modal.getInstance(modal);
                bsModal.hide();
                
                // Show success message and reload
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Successfully saved ${data.count} AI-generated question(s).
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                
                const mainContent = document.querySelector('.admin-main');
                if (mainContent) {
                    mainContent.insertAdjacentHTML('afterbegin', '<div class="px-0 pt-0">' + alertHtml + '</div>');
                }

                // Reload after a short delay so user sees the message
                setTimeout(function () {
                    window.location.href = window.location.pathname + '?bank=' + bankId;
                }, 800);
            } else {
                showError(data.error || 'Failed to save questions.');
                saveAllBtn.disabled = false;
                saveAllBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save All Questions';
            }

        } catch (err) {
            showError('Network error: ' + err.message);
            saveAllBtn.disabled = false;
            saveAllBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Save All Questions';
        }
    });

    // ── Cancel Review → Back to Form ──────────────────────────
    cancelReviewBtn.addEventListener('click', function () {
        if (confirm('Discard all generated questions and start over?')) {
            resetModal();
        }
    });

    // ── Utility Functions ─────────────────────────────────────
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});
