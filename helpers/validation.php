<?php
/**
 * Validation Helper Functions
 * 
 * Simple server-side validation utilities.
 */

/**
 * Check if a value is not empty (after trimming)
 */
function isRequired(string $value): bool
{
    return trim($value) !== '';
}

/**
 * Check if a value is a valid email
 */
function isValidEmail(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if a value meets minimum length
 */
function isMinLength(string $value, int $min): bool
{
    return mb_strlen(trim($value)) >= $min;
}

/**
 * Check if a value does not exceed maximum length
 */
function isMaxLength(string $value, int $max): bool
{
    return mb_strlen(trim($value)) <= $max;
}

/**
 * Sanitize a string input
 */
function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate Malaysian IC number format (12 digits)
 */
function isValidIC(string $value): bool
{
    // Remove dashes if any, then check for exactly 12 digits
    $cleaned = str_replace('-', '', trim($value));
    return preg_match('/^\d{12}$/', $cleaned) === 1;
}
