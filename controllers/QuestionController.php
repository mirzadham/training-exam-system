<?php
/**
 * Question Controller
 * 
 * Handles question CRUD with validation.
 */

require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../models/QuestionBank.php';

class QuestionController
{
    /**
     * Validate question form data
     */
    public static function validate(array $data): array
    {
        $errors = [];

        if (empty($data['question_bank_id'])) {
            $errors['question_bank_id'] = 'Please select a question bank.';
        } else {
            $bank = QuestionBank::findById((int) $data['question_bank_id']);
            if (!$bank) {
                $errors['question_bank_id'] = 'Selected question bank does not exist.';
            }
        }

        if (!isRequired($data['question_text'] ?? '')) {
            $errors['question_text'] = 'Question text is required.';
        }

        foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $opt) {
            if (!isRequired($data[$opt] ?? '')) {
                $label = strtoupper(substr($opt, -1));
                $errors[$opt] = "Option $label is required.";
            }
        }

        if (empty($data['correct_option']) || !in_array($data['correct_option'], ['A', 'B', 'C', 'D'])) {
            $errors['correct_option'] = 'Please select the correct answer (A, B, C, or D).';
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
            $id = Question::create($data);
            return ['success' => true, 'id' => $id];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to create question.']];
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
            Question::update($id, $data);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'errors' => ['general' => 'Failed to update question.']];
        }
    }

    /**
     * Handle delete
     */
    public static function destroy(int $id): array
    {
        $question = Question::findById($id);
        if (!$question) {
            return ['success' => false, 'error' => 'Question not found.'];
        }

        try {
            Question::delete($id);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to delete question.'];
        }
    }
}
