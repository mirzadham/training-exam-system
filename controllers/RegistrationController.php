<?php
/**
 * Registration Controller
 * 
 * Handles participant registration with validation.
 */

require_once __DIR__ . '/../models/Participant.php';
require_once __DIR__ . '/../models/Organization.php';
require_once __DIR__ . '/../models/QuestionBank.php';

class RegistrationController
{
    /**
     * Validate registration form data
     */
    public static function validate(array $data): array
    {
        $errors = [];

        // Full name — required
        if (!isRequired($data['full_name'] ?? '')) {
            $errors['full_name'] = 'Full name is required.';
        } elseif (!isMaxLength($data['full_name'], 255)) {
            $errors['full_name'] = 'Full name must not exceed 255 characters.';
        }

        // IC number — required, valid format, unique
        $ic = preg_replace('/[^0-9]/', '', $data['ic_number'] ?? '');
        if (!isRequired($ic)) {
            $errors['ic_number'] = 'IC number is required.';
        } elseif (!isValidIC($ic)) {
            $errors['ic_number'] = 'IC number must be exactly 12 digits (e.g. 901225145678).';
        } elseif (Participant::icExists($ic)) {
            $errors['ic_number'] = 'This IC number has already been registered. Please contact your administrator.';
        }

        // Organization — required and must exist
        if (empty($data['organization_id'])) {
            $errors['organization_id'] = 'Please select your organization.';
        } else {
            $org = Organization::findById((int) $data['organization_id']);
            if (!$org || $org['status'] !== 'active') {
                $errors['organization_id'] = 'Selected organization is not available.';
            }
        }

        // Email — optional but must be valid if provided
        if (!empty($data['email']) && !isValidEmail($data['email'])) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // Phone — optional, basic check
        if (!empty($data['phone']) && !preg_match('/^[\d\s\+\-()]{7,20}$/', $data['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }

        return $errors;
    }

    /**
     * Handle registration
     */
    public static function register(array $data): array
    {
        $errors = self::validate($data);

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check that the selected org has an active question bank with questions
        $orgId = (int) $data['organization_id'];
        $activeBank = QuestionBank::getActiveForOrganization($orgId);

        if (!$activeBank) {
            return [
                'success' => false,
                'errors' => ['organization_id' => 'This organization does not have an active exam. Please contact your administrator.']
            ];
        }

        try {
            $participantId = Participant::create($data);
            return [
                'success'        => true,
                'participant_id' => $participantId,
                'bank_id'        => $activeBank['id'],
            ];
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'ic_number')) {
                return [
                    'success' => false,
                    'errors' => ['ic_number' => 'This IC number has already been registered.']
                ];
            }
            return ['success' => false, 'errors' => ['general' => 'Registration failed. Please try again.']];
        }
    }
}
