<?php
/**
 * Pagination Helper
 * 
 * Provides a reusable paginate() function for server-side pagination.
 */

/**
 * Calculate pagination metadata.
 *
 * @param int $totalItems  Total number of items matching the current filters
 * @param int $currentPage Requested page number (1-indexed)
 * @param int $perPage     Items per page (default 10)
 * @return array           Keys: totalItems, totalPages, currentPage, perPage, offset
 */
function paginate(int $totalItems, int $currentPage = 1, int $perPage = 10): array
{
    // Clamp per_page to allowed values
    $allowed = [10, 25, 50];
    if (!in_array($perPage, $allowed)) {
        $perPage = 10;
    }

    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'totalItems'  => $totalItems,
        'totalPages'  => $totalPages,
        'currentPage' => $currentPage,
        'perPage'     => $perPage,
        'offset'      => $offset,
    ];
}
