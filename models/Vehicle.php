<?php
declare(strict_types=1);

class Vehicle
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, u.full_name, u.email, u.role
             FROM vehicles v JOIN users u ON u.id = v.user_id
             WHERE v.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByPlate(string $plate): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT v.*, u.full_name, u.email, u.role
             FROM vehicles v JOIN users u ON u.id = v.user_id
             WHERE v.plate_number = ? LIMIT 1'
        );
        $stmt->execute([strtoupper(trim($plate))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM vehicles WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO vehicles (user_id, plate_number, make, model, color, vehicle_type, is_shuttle, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'],
            strtoupper(trim($data['plate_number'])),
            $data['make'] ?? null,
            $data['model'] ?? null,
            $data['color'] ?? null,
            $data['vehicle_type'] ?? 'car',
            !empty($data['is_shuttle']) ? 1 : 0,
            $data['status'] ?? 'pending',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE vehicles SET plate_number = ?, make = ?, model = ?, color = ?, vehicle_type = ? WHERE id = ?'
        );
        $stmt->execute([
            strtoupper(trim($data['plate_number'])),
            $data['make'] ?? null,
            $data['model'] ?? null,
            $data['color'] ?? null,
            $data['vehicle_type'] ?? 'car',
            $id,
        ]);
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE vehicles SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function list(array $filters = [], int $limit = 100): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'v.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'v.user_id = ?';
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(v.plate_number LIKE ? OR u.full_name LIKE ? OR v.make LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        $sql = 'SELECT v.*, u.full_name, u.email, u.role
                FROM vehicles v JOIN users u ON u.id = v.user_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY v.created_at DESC LIMIT ' . (int) $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(?string $status = null): int
    {
        if ($status) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM vehicles WHERE status = ?');
            $stmt->execute([$status]);
        } else {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM vehicles');
        }
        return (int) $stmt->fetchColumn();
    }

    public function approvedForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM vehicles WHERE user_id = ? AND status = 'approved' ORDER BY plate_number"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
