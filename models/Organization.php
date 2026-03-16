<?php
/**
 * Organization Model
 * 
 * Database interaction for the organizations table.
 */

require_once __DIR__ . '/../config/database.php';

class Organization
{
    /**
     * Get all organizations, optionally filtered by search term
     */
    public static function getAll(string $search = '', string $status = ''): array
    {
        $pdo = getDBConnection();
        $sql = "SELECT * FROM organizations WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE :search OR code LIKE :search2)";
            $params['search'] = "%$search%";
            $params['search2'] = "%$search%";
        }

        if ($status !== '' && in_array($status, ['active', 'inactive'])) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get active organizations (for dropdowns)
     */
    public static function getActive(): array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT id, name, code FROM organizations WHERE status = 'active' ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Find organization by ID
     */
    public static function findById(int $id): ?array
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT * FROM organizations WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Check if organization code already exists (optionally exclude an ID for edits)
     */
    public static function codeExists(string $code, ?int $excludeId = null): bool
    {
        $pdo = getDBConnection();
        $sql = "SELECT COUNT(*) FROM organizations WHERE code = :code";
        $params = ['code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Create a new organization
     */
    public static function create(array $data): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO organizations (name, code, description, status) 
             VALUES (:name, :code, :description, :status)"
        );
        $stmt->execute([
            'name'        => $data['name'],
            'code'        => strtoupper(trim($data['code'])),
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Update an existing organization
     */
    public static function update(int $id, array $data): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare(
            "UPDATE organizations 
             SET name = :name, code = :code, description = :description, status = :status
             WHERE id = :id"
        );
        return $stmt->execute([
            'id'          => $id,
            'name'        => $data['name'],
            'code'        => strtoupper(trim($data['code'])),
            'description' => $data['description'] ?? null,
            'status'      => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Delete an organization by ID
     */
    public static function delete(int $id): bool
    {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("DELETE FROM organizations WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count all organizations
     */
    public static function count(): int
    {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM organizations");
        return (int) $stmt->fetchColumn();
    }
}
