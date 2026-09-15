<?php
declare(strict_types=1);

class EntryExitLog
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO entry_exit_logs
             (user_id, vehicle_id, visitor_id, otp_id, plate_number, log_type, verified_by, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['vehicle_id'] ?? null,
            $data['visitor_id'] ?? null,
            $data['otp_id'] ?? null,
            strtoupper(trim($data['plate_number'])),
            $data['log_type'],
            $data['verified_by'] ?? null,
            $data['notes'] ?? null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function list(array $filters = [], int $limit = 100): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['log_type'])) {
            $where[] = 'l.log_type = ?';
            $params[] = $filters['log_type'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'l.user_id = ?';
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['today'])) {
            $where[] = 'DATE(l.logged_at) = CURDATE()';
        }
        if (!empty($filters['from'])) {
            $where[] = 'DATE(l.logged_at) >= ?';
            $params[] = $filters['from'];
        }
        if (!empty($filters['to'])) {
            $where[] = 'DATE(l.logged_at) <= ?';
            $params[] = $filters['to'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(l.plate_number LIKE ? OR u.full_name LIKE ? OR v.full_name LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q);
        }
        $sql = 'SELECT l.*, u.full_name AS owner_name, sec.full_name AS verifier_name, vis.full_name AS visitor_name
                FROM entry_exit_logs l
                LEFT JOIN users u ON u.id = l.user_id
                LEFT JOIN users sec ON sec.id = l.verified_by
                LEFT JOIN visitors vis ON vis.id = l.visitor_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY l.logged_at DESC LIMIT ' . (int) $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countToday(string $type = ''): int
    {
        if ($type !== '') {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM entry_exit_logs WHERE DATE(logged_at) = CURDATE() AND log_type = ?"
            );
            $stmt->execute([$type]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM entry_exit_logs WHERE DATE(logged_at) = CURDATE()");
        }
        return (int) $stmt->fetchColumn();
    }

    public function countAll(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM entry_exit_logs')->fetchColumn();
    }

    public function dailyCounts(int $days = 7): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT DATE(logged_at) AS d, log_type, COUNT(*) AS total
             FROM entry_exit_logs
             WHERE logged_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(logged_at), log_type
             ORDER BY d ASC"
        );
        $stmt->execute([$days - 1]);
        return $stmt->fetchAll();
    }

    public function lastEntryForPlate(string $plate): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM entry_exit_logs
             WHERE plate_number = ? AND log_type = 'entry'
             ORDER BY logged_at DESC LIMIT 1"
        );
        $stmt->execute([strtoupper(trim($plate))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
