<?php
/**
 * Organization Controller
 * 
 * Handles organization CRUD operations with validation.
 */

require_once __DIR__ . '/../models/Organization.php';

class OrganizationController
{
    /**
     * Validate organization form data
     * 
     * @return array Validation errors (empty if valid)
     */
    public static function validate(array $data, ?int $editId = null): array
    {
        $errors = [];

        // Name is required
        if (!isRequired($data['name'] ?? '')) {
            $errors['name'] = 'Organization name is required.';
        } elseif (!isMaxLength($data['name'], 255)) {
            $errors['name'] = 'Organization name must not exceed 255 characters.';
        }

        // Code is required and unique
        if (!isRequired($data['code'] ?? '')) {
            $errors['code'] = 'Organization code is required.';
        } elseif (!isMaxLength($data['code'], 50)) {
            $errors['code'] = 'Organization code must not exceed 50 characters.';
        } elseif (!preg_match('/^[A-Za-z0-9_-]+$/', trim($data['code']))) {
            $errors['code'] = 'Organization code may only contain letters, numbers, hyphens, and underscores.';
        } elseif (Organization::codeExists(strtoupper(trim($data['code'])), $editId)) {
            $errors['code'] = 'This organization code is already in use.';
        }

        // Status must be valid
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            $errors['status'] = 'Invalid status value.';
        }

        return $errors;
    }

    /**
     * Handle create action
     */
    public static function store(array $data): array
    {
        $errors = self::validate($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            $id = Organization::create($data);
            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create organization: ' . $e->getMessage()]];
        }
    }

    /**
     * Handle update action
     */
    public static function update(int $id, array $data): array
    {
        $errors = self::validate($data, $id);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            Organization::update($id, $data);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to update organization: ' . $e->getMessage()]];
        }
    }

    /**
     * Handle delete action
     */
    public static function destroy(int $id): array
    {
        $org = Organization::findById($id);
        if (!$org) {
            return ['success' => false, 'error' => 'Organization not found.'];
        }

        try {
            Organization::delete($id);
            return ['success' => true];
        } catch (PDOException $e) {
            // Foreign key constraint may prevent deletion
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                return ['success' => false, 'error' => 'Cannot delete this organization because it has related records (question banks, participants, etc.).'];
            }
            return ['success' => false, 'error' => 'Failed to delete organization.'];
        }
    }
}
