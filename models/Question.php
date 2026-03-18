<?php
/**
 * Question Model
 * 
 * Database interaction for the questions table.
 */

require_once __DIR__ . '/../config/database.php';

class Question
{
    /**
     * Get all questions for a question bank
     */
    public static function getByBankId(int $bankId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE question_bank_id = :bank_id ORDER BY id ASC");
        $stmt->execute(['bank_id' => $bankId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all questions with bank and org info, optionally filtered
     */
    public static function getAll(string $search = '', string $bankFilter = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT q.*, qb.title AS bank_title, o.name AS organization_name
                FROM questions q
                JOIN question_banks qb ON q.question_bank_id = qb.id
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND q.question_text LIKE :search";
            $params['search'] = "%$search%";
        }

        if ($bankFilter !== '' && is_numeric($bankFilter)) {
            $sql .= " AND q.question_bank_id = :bank_id";
            $params['bank_id'] = (int) $bankFilter;
        }

        $sql .= " ORDER BY o.name ASC, qb.title ASC, q.id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new question
     */
    public static function create(array $data): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO questions (question_bank_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation)
             VALUES (:question_bank_id, :question_text, :option_a, :option_b, :option_c, :option_d, :correct_option, :explanation)"
        );
        $stmt->execute([
            'question_bank_id' => $data['question_bank_id'],
            'question_text'    => $data['question_text'],
            'option_a'         => $data['option_a'],
            'option_b'         => $data['option_b'],
            'option_c'         => $data['option_c'],
            'option_d'         => $data['option_d'],
            'correct_option'   => $data['correct_option'],
            'explanation'      => $data['explanation'] ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update a question
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE questions 
             SET question_bank_id = :question_bank_id, question_text = :question_text,
                 option_a = :option_a, option_b = :option_b, option_c = :option_c, option_d = :option_d,
                 correct_option = :correct_option, explanation = :explanation
             WHERE id = :id"
        );
        return $stmt->execute([
            'id'               => $id,
            'question_bank_id' => $data['question_bank_id'],
            'question_text'    => $data['question_text'],
            'option_a'         => $data['option_a'],
            'option_b'         => $data['option_b'],
            'option_c'         => $data['option_c'],
            'option_d'         => $data['option_d'],
            'correct_option'   => $data['correct_option'],
            'explanation'      => $data['explanation'] ?: null,
        ]);
    }

    /**
     * Delete a question
     */
    public static function delete(int $id): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count questions in a bank
     */
    public static function countByBank(int $bankId): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE question_bank_id = :bank_id");
        $stmt->execute(['bank_id' => $bankId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count questions matching filters (for pagination)
     */
    public static function countFiltered(string $search = '', string $bankFilter = ''): int
    {
        $pdo = getDBConnection();
        $sql = "SELECT COUNT(*)
                FROM questions q
                JOIN question_banks qb ON q.question_bank_id = qb.id
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND q.question_text LIKE :search";
            $params['search'] = "%$search%";
        }

        if ($bankFilter !== '' && is_numeric($bankFilter)) {
            $sql .= " AND q.question_bank_id = :bank_id";
            $params['bank_id'] = (int) $bankFilter;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get paginated questions
     */
    public static function getPaginated(string $search = '', string $bankFilter = '', int $limit = 10, int $offset = 0): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT q.*, qb.title AS bank_title, o.name AS organization_name
                FROM questions q
                JOIN question_banks qb ON q.question_bank_id = qb.id
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND q.question_text LIKE :search";
            $params['search'] = "%$search%";
        }

        if ($bankFilter !== '' && is_numeric($bankFilter)) {
            $sql .= " AND q.question_bank_id = :bank_id";
            $params['bank_id'] = (int) $bankFilter;
        }

        $sql .= " ORDER BY o.name ASC, qb.title ASC, q.id ASC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
