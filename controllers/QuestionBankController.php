<?php
/**
 * QuestionBank Controller
 * 
 * Handles question bank CRUD with validation.
 */

require_once __DIR__ . '/../models/QuestionBank.php';
require_once __DIR__ . '/../models/Organization.php';

class QuestionBankController
{
    /**
     * Validate question bank form data
     */
    public static function validate(array $data): array
    {
        $errors = [];

        if (!isRequired($data['title'] ?? '')) {
            $errors['title'] = 'Title is required.';
        } elseif (!isMaxLength($data['title'], 255)) {
            $errors['title'] = 'Title must not exceed 255 characters.';
        }

        if (empty($data['organization_id'])) {
            $errors['organization_id'] = 'Please select an organization.';
        } else {
            $org = Organization::findById((int) $data['organization_id']);
            if (!$org) {
                $errors['organization_id'] = 'Selected organization does not exist.';
            }
        }

        if (!empty($data['duration_minutes']) && (!is_numeric($data['duration_minutes']) || (int) $data['duration_minutes'] < 1)) {
            $errors['duration_minutes'] = 'Duration must be a positive number.';
        }

        return $errors;
    }

    /**
     * Handle create
     */
    public static function store(array $data): array
    {
        $errors = self::validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $id = QuestionBank::create($data);
            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create question bank.']];
        }
    }

    /**
     * Handle update
     */
    public static function update(int $id, array $data): array
    {
        $errors = self::validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            QuestionBank::update($id, $data);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to update question bank.']];
        }
    }

    /**
     * Handle delete
     */
    public static function destroy(int $id): array
    {
        $bank = QuestionBank::findById($id);
        if (!$bank) {
            return ['success' => false, 'error' => 'Question bank not found.'];
        }

        try {
            QuestionBank::delete($id);
            return ['success' => true];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return ['success' => false, 'error' => 'Cannot delete this question bank because it has related exam attempts.'];
            }
            return ['success' => false, 'error' => 'Failed to delete question bank.'];
        }
    }
}
