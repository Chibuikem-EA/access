<?php
declare(strict_types=1);

class Visitor
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO visitors (full_name, phone, plate_number, purpose, host_name, registered_by, status)
             VALUES (?, ?, ?, ?, ?, ?, \'on_campus\')'
        );
        $stmt->execute([
            $data['full_name'],
            $data['phone'] ?? null,
            strtoupper(trim($data['plate_number'])),
            $data['purpose'] ?? null,
            $data['host_name'] ?? null,
            $data['registered_by'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM visitors WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markExited(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE visitors SET status = 'exited', exit_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    public function list(array $filters = [], int $limit = 100): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'v.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['today'])) {
            $where[] = 'DATE(v.entry_at) = CURDATE()';
        }
        if (!empty($filters['q'])) {
            $where[] = '(v.full_name LIKE ? OR v.plate_number LIKE ? OR v.phone LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q);
        }
        $sql = 'SELECT v.*, u.full_name AS registrar_name
                FROM visitors v
                LEFT JOIN users u ON u.id = v.registered_by
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY v.entry_at DESC LIMIT ' . (int) $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countOnCampus(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM visitors WHERE status = 'on_campus'"
        )->fetchColumn();
    }
}
