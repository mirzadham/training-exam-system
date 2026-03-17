<?php
/**
 * Participant Model
 * 
 * Database interaction for the participants table.
 */

require_once __DIR__ . '/../config/database.php';

class Participant
{
    /**
     * Find participant by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find participant by IC number
     */
    public static function findByIC(string $ic): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM participants WHERE ic_number = :ic LIMIT 1");
        $stmt->execute(['ic' => $ic]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if IC number exists
     */
    public static function icExists(string $ic): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE ic_number = :ic");
        $stmt->execute(['ic' => $ic]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Create a new participant
     */
    public static function create(array $data): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO participants (full_name, ic_number, organization_id)
             VALUES (:full_name, :ic_number, :organization_id)"
        );
        $stmt->execute([
            'full_name'       => $data['full_name'],
            'ic_number'       => preg_replace('/[^0-9]/', '', $data['ic_number']),
            'organization_id' => (int) $data['organization_id'],
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Get all participants with organization name, optionally filtered
     */
    public static function getAll(string $search = '', string $orgFilter = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT p.*, o.name AS organization_name, o.code AS organization_code
                FROM participants p
                JOIN organizations o ON p.organization_id = o.id
                WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.full_name LIKE :search OR p.ic_number LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }

        if ($orgFilter !== '' && is_numeric($orgFilter)) {
            $sql .= " AND p.organization_id = :org_id";
            $params['org_id'] = (int) $orgFilter;
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count all participants
     */
    public static function count(): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM participants");
        return (int) $stmt->fetchColumn();
    }
}
