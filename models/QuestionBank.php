<?php
/**
 * QuestionBank Model
 * 
 * Database interaction for the question_banks table.
 */

require_once __DIR__ . '/../config/database.php';

class QuestionBank
{
    /**
     * Get all question banks with organization name, optionally filtered
     */
    public static function getAll(string $search = '', string $orgFilter = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT qb.*, o.name AS organization_name, o.code AS organization_code,
                       (SELECT COUNT(*) FROM questions q WHERE q.question_bank_id = qb.id) AS question_count
                FROM question_banks qb
                JOIN organizations o ON qb.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (qb.title LIKE :search OR o.name LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }

        if ($orgFilter !== '' && is_numeric($orgFilter)) {
            $sql .= " AND qb.organization_id = :org_id";
            $params['org_id'] = (int) $orgFilter;
        }

        $sql .= " ORDER BY o.name ASC, qb.title ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get active question bank for an organization
     */
    public static function getActiveForOrganization(int $orgId): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM question_banks 
             WHERE organization_id = :org_id AND is_active = 1 AND status = 'active' 
             LIMIT 1"
        );
        $stmt->execute(['org_id' => $orgId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM question_banks WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find by ID with org info
     */
    public static function findByIdWithOrg(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "SELECT qb.*, o.name AS organization_name 
             FROM question_banks qb
             JOIN organizations o ON qb.organization_id = o.id
             WHERE qb.id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Create a new question bank
     */
    public static function create(array $data): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO question_banks (organization_id, title, description, duration_minutes, is_active, status)
             VALUES (:organization_id, :title, :description, :duration_minutes, :is_active, :status)"
        );
        $stmt->execute([
            'organization_id'  => $data['organization_id'],
            'title'            => $data['title'],
            'description'      => $data['description'] ?: null,
            'duration_minutes' => !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            'is_active'        => $data['is_active'] ?? 0,
            'status'           => $data['status'] ?? 'active',
        ]);

        $id = (int) $pdo->lastInsertId();

        // If marked active, deactivate other banks for same org
        if (!empty($data['is_active'])) {
            self::deactivateOthers($data['organization_id'], $id);
        }

        return $id;
    }

    /**
     * Update an existing question bank
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE question_banks 
             SET organization_id = :organization_id, title = :title, description = :description,
                 duration_minutes = :duration_minutes, is_active = :is_active, status = :status
             WHERE id = :id"
        );
        $result = $stmt->execute([
            'id'               => $id,
            'organization_id'  => $data['organization_id'],
            'title'            => $data['title'],
            'description'      => $data['description'] ?: null,
            'duration_minutes' => !empty($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            'is_active'        => $data['is_active'] ?? 0,
            'status'           => $data['status'] ?? 'active',
        ]);

        // If marked active, deactivate other banks for same org
        if (!empty($data['is_active'])) {
            self::deactivateOthers($data['organization_id'], $id);
        }

        return $result;
    }

    /**
     * Deactivate all other banks for the same org (only one active per org)
     */
    private static function deactivateOthers(int $orgId, int $exceptId): void
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE question_banks SET is_active = 0 
             WHERE organization_id = :org_id AND id != :except_id"
        );
        $stmt->execute(['org_id' => $orgId, 'except_id' => $exceptId]);
    }

    /**
     * Delete a question bank
     */
    public static function delete(int $id): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM question_banks WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count all question banks
     */
    public static function count(): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM question_banks");
        return (int) $stmt->fetchColumn();
    }
}
