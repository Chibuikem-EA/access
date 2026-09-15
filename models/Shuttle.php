<?php
declare(strict_types=1);

class Shuttle
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shuttles (vehicle_id, driver_id, route_name, capacity, shuttle_status, last_status_at, notes)
             VALUES (?, ?, ?, ?, ?, NOW(), ?)'
        );
        $stmt->execute([
            $data['vehicle_id'],
            $data['driver_id'],
            $data['route_name'] ?? null,
            $data['capacity'] ?? null,
            $data['shuttle_status'] ?? 'offline',
            $data['notes'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function forDriver(int $driverId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, v.plate_number, v.make, v.model, v.status AS vehicle_status
             FROM shuttles s JOIN vehicles v ON v.id = s.vehicle_id
             WHERE s.driver_id = ?
             ORDER BY s.updated_at DESC'
        );
        $stmt->execute([$driverId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, v.plate_number, v.make, v.model, u.full_name AS driver_name
             FROM shuttles s
             JOIN vehicles v ON v.id = s.vehicle_id
             JOIN users u ON u.id = s.driver_id
             WHERE s.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateStatus(int $id, string $status, ?string $notes = null): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shuttles SET shuttle_status = ?, last_status_at = NOW(), notes = COALESCE(?, notes) WHERE id = ?'
        );
        $stmt->execute([$status, $notes, $id]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shuttles SET route_name = ?, capacity = ?, notes = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['route_name'] ?? null,
            $data['capacity'] ?? null,
            $data['notes'] ?? null,
            $id,
        ]);
    }

    public function listAll(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 's.shuttle_status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(v.plate_number LIKE ? OR s.route_name LIKE ? OR u.full_name LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q);
        }
        $sql = 'SELECT s.*, v.plate_number, v.make, v.model, u.full_name AS driver_name, u.email AS driver_email
                FROM shuttles s
                JOIN vehicles v ON v.id = s.vehicle_id
                JOIN users u ON u.id = s.driver_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY s.updated_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByStatus(): array
    {
        $stmt = $this->pdo->query(
            'SELECT shuttle_status, COUNT(*) AS total FROM shuttles GROUP BY shuttle_status'
        );
        return $stmt->fetchAll();
    }
}
