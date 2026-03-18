<?php
/**
 * ExamAttempt Model
 * 
 * Database interaction for exam_attempts and attempt_answers tables.
 */

require_once __DIR__ . '/../config/database.php';

class ExamAttempt
{
    /**
     * Create a new exam attempt and its answer rows
     * 
     * @param int $participantId
     * @param int $bankId
     * @param array $questions  Array of question rows from the bank
     * @return int  The new attempt ID
     */
    public static function create(int $participantId, int $bankId, array $questions): int
    {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        try {
            // Create the attempt record
            $stmt = $pdo->prepare(
                "INSERT INTO exam_attempts (participant_id, question_bank_id, total_questions, status)
                 VALUES (:pid, :bid, :total, 'in_progress')"
            );
            $stmt->execute([
                'pid'   => $participantId,
                'bid'   => $bankId,
                'total' => count($questions),
            ]);
            $attemptId = (int) $pdo->lastInsertId();

            // Shuffle questions for random order
            shuffle($questions);

            // Create answer rows with randomized order
            $stmt = $pdo->prepare(
                "INSERT INTO attempt_answers (attempt_id, question_id, question_order)
                 VALUES (:aid, :qid, :qorder)"
            );
            foreach ($questions as $order => $q) {
                $stmt->execute([
                    'aid'    => $attemptId,
                    'qid'    => $q['id'],
                    'qorder' => $order + 1,
                ]);
            }

            $pdo->commit();
            return $attemptId;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Find attempt by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT ea.*, qb.title AS bank_title, qb.duration_minutes, qb.organization_id,
                    p.full_name AS participant_name
             FROM exam_attempts ea
             JOIN question_banks qb ON ea.question_bank_id = qb.id
             JOIN participants p ON ea.participant_id = p.id
             WHERE ea.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if participant already has an in_progress attempt
     */
    public static function getInProgress(int $participantId, int $bankId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM exam_attempts 
             WHERE participant_id = :pid AND question_bank_id = :bid AND status = 'in_progress'
             LIMIT 1"
        );
        $stmt->execute(['pid' => $participantId, 'bid' => $bankId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if participant has a completed attempt for this bank
     */
    public static function hasCompleted(int $participantId, int $bankId): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM exam_attempts 
             WHERE participant_id = :pid AND question_bank_id = :bid AND status IN ('submitted', 'time_up')"
        );
        $stmt->execute(['pid' => $participantId, 'bid' => $bankId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Get all answer rows for an attempt, ordered by question_order,
     * joined with question data
     */
    public static function getAnswers(int $attemptId): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT aa.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
                    q.correct_option, q.explanation
             FROM attempt_answers aa
             JOIN questions q ON aa.question_id = q.id
             WHERE aa.attempt_id = :aid
             ORDER BY aa.question_order ASC"
        );
        $stmt->execute(['aid' => $attemptId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single answer row by attempt and question order
     */
    public static function getAnswerByOrder(int $attemptId, int $order): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT aa.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
                    q.correct_option, q.explanation
             FROM attempt_answers aa
             JOIN questions q ON aa.question_id = q.id
             WHERE aa.attempt_id = :aid AND aa.question_order = :qorder
             LIMIT 1"
        );
        $stmt->execute(['aid' => $attemptId, 'qorder' => $order]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Save a participant's answer for a question
     */
    public static function saveAnswer(int $attemptId, int $questionId, string $selectedOption): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE attempt_answers 
             SET selected_option = :option
             WHERE attempt_id = :aid AND question_id = :qid"
        );
        return $stmt->execute([
            'option' => $selectedOption,
            'aid'    => $attemptId,
            'qid'    => $questionId,
        ]);
    }

    /**
     * Calculate the remaining time in seconds for an attempt
     */
    public static function getRemainingSeconds(array $attempt): int
    {
        $durationMinutes = $attempt['duration_minutes'];

        // If no explicit duration, use 1 min per question
        if (!$durationMinutes) {
            $durationMinutes = max($attempt['total_questions'], 5);
        }

        $startedAt = strtotime($attempt['started_at']);
        $endAt = $startedAt + ($durationMinutes * 60);
        $remaining = $endAt - time();

        return max(0, $remaining);
    }

    /**
     * Submit an attempt — calculate scores and update the record
     */
    public static function submit(int $attemptId, string $status = 'submitted'): array
    {
        $pdo = getDBConnection();

        // Calculate scores
        $answers = self::getAnswers($attemptId);
        $total = count($answers);
        $correct = 0;
        $wrong = 0;
        $unanswered = 0;

        foreach ($answers as $ans) {
            if ($ans['selected_option'] === null) {
                $unanswered++;
            } elseif ($ans['selected_option'] === $ans['correct_option']) {
                $correct++;
                // Mark as correct
                $upd = $pdo->prepare("UPDATE attempt_answers SET is_correct = 1 WHERE id = :id");
                $upd->execute(['id' => $ans['id']]);
            } else {
                $wrong++;
            }
        }

        $scorePercent = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
        $result = classifyResult($scorePercent);

        // Update the attempt record
        $stmt = $pdo->prepare(
            "UPDATE exam_attempts 
             SET correct_count = :correct, wrong_count = :wrong, unanswered_count = :unanswered,
                 score_percent = :score, result = :result, status = :status, submitted_at = NOW()
             WHERE id = :id"
        );
        $stmt->execute([
            'correct'    => $correct,
            'wrong'      => $wrong,
            'unanswered' => $unanswered,
            'score'      => $scorePercent,
            'result'     => $result,
            'status'     => $status,
            'id'         => $attemptId,
        ]);

        return [
            'total'       => $total,
            'correct'     => $correct,
            'wrong'       => $wrong,
            'unanswered'  => $unanswered,
            'score'       => $scorePercent,
            'result'      => $result,
        ];
    }

    /**
     * Get all attempts (for admin reporting)
     */
    public static function getAll(string $search = '', string $orgFilter = '', string $statusFilter = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT ea.*, p.full_name AS participant_name, p.ic_number,
                       qb.title AS bank_title, o.name AS organization_name, o.code AS organization_code
                FROM exam_attempts ea
                JOIN participants p ON ea.participant_id = p.id
                JOIN question_banks qb ON ea.question_bank_id = qb.id
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.full_name LIKE :search OR p.ic_number LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }

        if ($orgFilter !== '' && is_numeric($orgFilter)) {
            $sql .= " AND qb.organization_id = :org_id";
            $params['org_id'] = (int) $orgFilter;
        }

        if ($statusFilter !== '' && in_array($statusFilter, ['in_progress', 'submitted', 'time_up'])) {
            $sql .= " AND ea.status = :status";
            $params['status'] = $statusFilter;
        }

        $sql .= " ORDER BY ea.started_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count attempts
     */
    public static function count(): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM exam_attempts");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Count attempts matching filters (for pagination)
     */
    public static function countFiltered(string $search = '', string $orgFilter = '', string $statusFilter = ''): int
    {
        $pdo = getDBConnection();
        $sql = "SELECT COUNT(*)
                FROM exam_attempts ea
                JOIN participants p ON ea.participant_id = p.id
                JOIN question_banks qb ON ea.question_bank_id = qb.id
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.full_name LIKE :search OR p.ic_number LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }

        if ($orgFilter !== '' && is_numeric($orgFilter)) {
            $sql .= " AND qb.organization_id = :org_id";
            $params['org_id'] = (int) $orgFilter;
        }

        if ($statusFilter !== '' && in_array($statusFilter, ['in_progress', 'submitted', 'time_up'])) {
            $sql .= " AND ea.status = :status";
            $params['status'] = $statusFilter;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get paginated attempts (for admin reporting)
     */
    public static function getPaginated(string $search = '', string $orgFilter = '', string $statusFilter = '', int $limit = 10, int $offset = 0): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT ea.*, p.full_name AS participant_name, p.ic_number,
                       qb.title AS bank_title, o.name AS organization_name, o.code AS organization_code
                FROM exam_attempts ea
                JOIN participants p ON ea.participant_id = p.id
                JOIN question_banks qb ON ea.question_bank_id = qb.id
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.full_name LIKE :search OR p.ic_number LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }

        if ($orgFilter !== '' && is_numeric($orgFilter)) {
            $sql .= " AND qb.organization_id = :org_id";
            $params['org_id'] = (int) $orgFilter;
        }

        if ($statusFilter !== '' && in_array($statusFilter, ['in_progress', 'submitted', 'time_up'])) {
            $sql .= " AND ea.status = :status";
            $params['status'] = $statusFilter;
        }

        $sql .= " ORDER BY ea.started_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
