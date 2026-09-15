<?php
declare(strict_types=1);

class Otp
{
    public function __construct(private PDO $pdo)
    {
    }

    public function expireStale(): void
    {
        $this->pdo->exec("UPDATE otps SET status = 'expired' WHERE status = 'active' AND expires_at < NOW()");
    }

    public function create(int $userId, int $vehicleId, int $expiryMinutes, int $length = 6, string $purpose = 'entry'): array
    {
        $this->expireStale();

        // Revoke previous active OTPs for this user/vehicle
        $revoke = $this->pdo->prepare(
            "UPDATE otps SET status = 'revoked' WHERE user_id = ? AND vehicle_id = ? AND status = 'active'"
        );
        $revoke->execute([$userId, $vehicleId]);

        $code = $this->generateCode($length);
        $stmt = $this->pdo->prepare(
            'INSERT INTO otps (user_id, vehicle_id, otp_code, purpose, expires_at, status)
             VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), \'active\')'
        );
        $stmt->execute([$userId, $vehicleId, $code, $purpose, $expiryMinutes]);
        $id = (int) $this->pdo->lastInsertId();
        return $this->findById($id) ?? [];
    }

    private function generateCode(int $length): string
    {
        $max = (10 ** $length) - 1;
        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.*, v.plate_number, u.full_name, u.email
             FROM otps o
             JOIN vehicles v ON v.id = o.vehicle_id
             JOIN users u ON u.id = o.user_id
             WHERE o.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findActiveByCode(string $code): ?array
    {
        $this->expireStale();
        $stmt = $this->pdo->prepare(
            "SELECT o.*, v.plate_number, v.status AS vehicle_status, u.full_name, u.email, u.role, u.status AS user_status
             FROM otps o
             JOIN vehicles v ON v.id = o.vehicle_id
             JOIN users u ON u.id = o.user_id
             WHERE o.otp_code = ? AND o.status = 'active' AND o.expires_at >= NOW()
             LIMIT 1"
        );
        $stmt->execute([trim($code)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function markUsed(int $id): void
    {
        $stmt = $this->pdo->prepare("UPDATE otps SET status = 'used', used_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function forUser(int $userId, int $limit = 50): array
    {
        $this->expireStale();
        $stmt = $this->pdo->prepare(
            'SELECT o.*, v.plate_number
             FROM otps o JOIN vehicles v ON v.id = o.vehicle_id
             WHERE o.user_id = ?
             ORDER BY o.created_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function activeForUser(int $userId): ?array
    {
        $this->expireStale();
        $stmt = $this->pdo->prepare(
            "SELECT o.*, v.plate_number
             FROM otps o JOIN vehicles v ON v.id = o.vehicle_id
             WHERE o.user_id = ? AND o.status = 'active' AND o.expires_at >= NOW()
             ORDER BY o.created_at DESC LIMIT 1"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listAll(array $filters = [], int $limit = 100): array
    {
        $this->expireStale();
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[] = 'o.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(o.otp_code LIKE ? OR v.plate_number LIKE ? OR u.full_name LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            array_push($params, $q, $q, $q);
        }
        $sql = 'SELECT o.*, v.plate_number, u.full_name
                FROM otps o
                JOIN vehicles v ON v.id = o.vehicle_id
                JOIN users u ON u.id = o.user_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY o.created_at DESC LIMIT ' . (int) $limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countToday(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM otps WHERE DATE(created_at) = CURDATE()");
        return (int) $stmt->fetchColumn();
    }
}
