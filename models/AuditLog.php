<?php
declare(strict_types=1);

class AuditLog
{
    public function __construct(private PDO $pdo)
    {
    }

    public function list(array $filters = [], int $limit = 150): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['action'])) {
            $where[] = 'a.action = ?';
            $params[] = $filters['action'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(a.action LIKE ? OR a.details LIKE ? OR u.full_name LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q);
        }
        $sql = 'SELECT a.*, u.full_name, u.email
                FROM audit_logs a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY a.created_at DESC LIMIT ' . (int) $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
