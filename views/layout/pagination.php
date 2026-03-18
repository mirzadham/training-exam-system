<?php
/**
 * Reusable Pagination View Partial
 * 
 * Expects: $pagination array (from paginate() helper)
 * Preserves existing $_GET params (search, filters) across page links.
 */
if (!isset($pagination) || $pagination['totalItems'] === 0) return;

$totalPages  = $pagination['totalPages'];
$currentPage = $pagination['currentPage'];
$perPage     = $pagination['perPage'];
$totalItems  = $pagination['totalItems'];
$offset      = $pagination['offset'];

// Build base query params (preserve existing filters)
$queryParams = $_GET;
unset($queryParams['page']); // will be added per-link
unset($queryParams['per_page']); // handled by the selector

// Calculate "Showing X to Y of Z"
$showingFrom = $offset + 1;
$showingTo   = min($offset + $perPage, $totalItems);
?>

<div class="pagination-bar d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2">
    <!-- Left: Showing info -->
    <div class="pagination-info text-muted">
        <small>Showing <strong><?= $showingFrom ?></strong> to <strong><?= $showingTo ?></strong> of <strong><?= $totalItems ?></strong> entries</small>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Per-page selector -->
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted text-nowrap">Per page:</small>
            <select class="form-select form-select-sm pagination-per-page" style="width: auto;" onchange="updatePerPage(this.value)">
                <?php foreach ([10, 25, 50] as $opt): ?>
                    <option value="<?= $opt ?>" <?= $perPage == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Page navigation -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <!-- Previous -->
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildPageUrl($queryParams, $currentPage - 1, $perPage) ?>" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <?php
                // Determine visible page range with ellipsis
                $range = 2; // pages shown around current
                $startPage = max(1, $currentPage - $range);
                $endPage = min($totalPages, $currentPage + $range);
                ?>

                <?php if ($startPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPageUrl($queryParams, 1, $perPage) ?>">1</a>
                    </li>
                    <?php if ($startPage > 2): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                    <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="<?= buildPageUrl($queryParams, $p, $perPage) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= buildPageUrl($queryParams, $totalPages, $perPage) ?>"><?= $totalPages ?></a>
                    </li>
                <?php endif; ?>

                <!-- Next -->
                <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= buildPageUrl($queryParams, $currentPage + 1, $perPage) ?>" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // reset to first page
    window.location.href = url.toString();
}
</script>

<?php
/**
 * Build a URL for a specific page, preserving other query params.
 */
function buildPageUrl(array $params, int $page, int $perPage): string
{
    $params['page'] = $page;
    $params['per_page'] = $perPage;
    return '?' . http_build_query($params);
}
?>
