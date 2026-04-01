<!-- Questions List View -->

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0" style="font-weight: 700; font-size: 1.75rem;">Questions</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-info" id="btnOpenImportText">
            <i class="bi bi-clipboard-data me-1"></i>Import AI Text
        </button>
        <button type="button" class="btn btn-outline-primary" id="btnOpenGenerateApi">
            <i class="bi bi-stars me-1"></i>Generate via API
        </button>
        <a href="<?= url('admin/questions.php?action=create' . ($bankFilter ? '&bank_id=' . e($bankFilter) : '')) ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Add Question
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
                                <td><?= $pagination['offset'] + $i + 1 ?></td>
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
            <div class="card-footer p-0">
                <?php require VIEWS_PATH . '/layout/pagination.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- AI Generate Modal                                          -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="aiGenerateModal" tabindex="-1" aria-labelledby="aiGenerateModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aiGenerateModalLabel">
                    <i class="bi bi-stars me-1 text-primary"></i>Generate Questions via AI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Error Alert -->
                <div class="alert alert-danger d-none" id="aiErrorAlert" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <span id="aiErrorText"></span>
                </div>

                <!-- ── State 1: Input Forms Containers ─────────────────── -->
                <div id="aiFormState">
                    
                    <!-- PDF Upload Form -->
                    <div id="aiFormStatePdf">
                        <form id="aiGenerateForm">
                            <div class="mb-3">
                                <label for="aiBankId" class="form-label fw-semibold">Question Bank <span class="text-danger">*</span></label>
                                <select class="form-select" id="aiBankId" required>
                                    <option value="">— Select a Question Bank —</option>
                                    <?php foreach ($questionBanks as $qb): ?>
                                        <option value="<?= $qb['id'] ?>">
                                            <?= e($qb['title']) ?> (<?= e($qb['organization_name']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="aiPdfFile" class="form-label fw-semibold">Learning Material (PDF) <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="aiPdfFile" accept=".pdf" required>
                                <div class="form-text">Upload a PDF document. Max 20 MB.</div>
                            </div>
                            <div class="mb-4">
                                <label for="aiNumQuestions" class="form-label fw-semibold">Number of Questions</label>
                                <input type="number" class="form-control" id="aiNumQuestions" 
                                       value="10" min="1" max="50" style="max-width: 120px;">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-stars me-1"></i>Generate Questions
                            </button>
                        </form>
                    </div>
                    
                    <!-- Paste AI Text Form -->
                    <div id="aiFormStateText" class="d-none">
                        <form id="aiImportTextForm">
                            <div class="mb-3">
                                <label for="aiImportBankId" class="form-label fw-semibold">Question Bank <span class="text-danger">*</span></label>
                                <select class="form-select" id="aiImportBankId" required>
                                    <option value="">— Select a Question Bank —</option>
                                    <?php foreach ($questionBanks as $qb): ?>
                                        <option value="<?= $qb['id'] ?>">
                                            <?= e($qb['title']) ?> (<?= e($qb['organization_name']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3 p-3 bg-light border rounded">
                                <p class="mb-2 fw-semibold"><i class="bi bi-info-circle me-1"></i>How to use this:</p>
                                <ol class="mb-2 small">
                                    <li>Copy the prompt below.</li>
                                    <li>Paste it into ChatGPT, Claude, or Gemini along with your learning materials.</li>
                                    <li>Copy the AI's response and paste it into the text box below.</li>
                                </ol>
                                <div class="mb-2">
                                    <textarea class="form-control bg-white text-sm" id="aiPromptTemplate" rows="4" readonly>Generate 10 multiple-choice questions based on this topic. MUST use exactly this format for each question, separated by a blank line. Do not include markdown formatting:
Q: [Question Text]
A: [Option A]
B: [Option B]
C: [Option C]
D: [Option D]
Answer: [A, B, C, or D]
Explanation: [Optional explanation]</textarea>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="copyPromptBtn" title="Copy Prompt">
                                    <i class="bi bi-clipboard"></i> Copy Prompt
                                </button>
                                <div id="copySuccessMsg" class="form-text text-success d-none d-inline-block ms-2 pt-1"><i class="bi bi-check-circle-fill me-1"></i>Copied to clipboard!</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="aiRawText" class="form-label fw-semibold">Paste AI Output Here <span class="text-danger">*</span></label>
                                <textarea class="form-control font-monospace text-sm" id="aiRawText" rows="6" required placeholder="Q: What is the capital of France?&#10;A: Berlin&#10;B: Paris&#10;C: Madrid&#10;D: Rome&#10;Answer: B"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right-circle me-1"></i>Parse & Review Questions
                            </button>
                        </form>
                    </div>
                </div>

                <!-- ── State 2: Loading Spinner ─────────────── -->
                <div id="aiLoadingState" class="d-none text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted fs-5 mb-1">Analyzing document &amp; generating questions...</p>
                    <p class="text-muted small">This may take up to a minute depending on the document size.</p>
                </div>

                <!-- ── State 3: Review & Edit ───────────────── -->
                <div id="aiReviewState" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-primary fs-6 me-2"><span id="aiReviewCount">0</span> questions</span>
                            <span class="text-muted">Review and edit before saving</span>
                        </div>
                    </div>
                    <div id="aiReviewContainer">
                        <!-- Dynamically generated question cards -->
                    </div>
                </div>
            </div>

            <!-- Review state footer (only visible during review) -->
            <div class="modal-footer" id="aiReviewFooter" style="display: none;">
                <button type="button" class="btn btn-outline-secondary" id="aiCancelReview">
                    <i class="bi bi-arrow-left me-1"></i>Start Over
                </button>
                <button type="button" class="btn btn-success" id="aiSaveAllBtn">
                    <i class="bi bi-check-lg me-1"></i>Save All Questions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- AI Generate JS Config -->
<script>
    const AI_GENERATE_URL = '<?= url("admin/api_generate_questions.php") ?>';
    const AI_SAVE_URL     = '<?= url("admin/api_save_questions.php") ?>';
</script>

