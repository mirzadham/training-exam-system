<?php
/**
 * General Helper Functions
 * 
 * Utility functions used across the application.
 */

/**
 * Redirect to a URL
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Escape output for HTML display (prevent XSS)
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get base URL path
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Get asset URL path
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Flash message helpers (session-based)
 * Used to show success/error messages after redirects.
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Classify exam result based on score percentage
 */
function classifyResult(float $scorePercent): string
{
    if ($scorePercent > SCORE_EXCELLENT_PERCENT) {
        return RESULT_EXCELLENT;
    } elseif ($scorePercent >= SCORE_PASS_PERCENT) {
        return RESULT_PASS;
    }
    return RESULT_FAIL;
}

/**
 * Get old form input (for repopulating forms after validation failure)
 */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['old_input'][$key] ?? $default);
}

/**
 * Set old input values into session
 */
function setOldInput(array $data): void
{
    $_SESSION['old_input'] = $data;
}

/**
 * Clear old input from session
 */
function clearOldInput(): void
{
    unset($_SESSION['old_input']);
}
