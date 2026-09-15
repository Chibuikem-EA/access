<?php
declare(strict_types=1);

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, phone, role, student_id, staff_id, department, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['full_name'],
            strtolower(trim($data['email'])),
            $data['password_hash'],
            $data['phone'] ?? null,
            $data['role'],
            $data['student_id'] ?? null,
            $data['staff_id'] ?? null,
            $data['department'] ?? null,
            $data['status'] ?? 'pending',
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET full_name = ?, phone = ?, department = ?, student_id = ?, staff_id = ? WHERE id = ?'
        );
        $stmt->execute([
            $data['full_name'],
            $data['phone'] ?? null,
            $data['department'] ?? null,
            $data['student_id'] ?? null,
            $data['staff_id'] ?? null,
            $id,
        ]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$hash, $id]);
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function setRole(int $id, string $role): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
        $stmt->execute([$role, $id]);
    }

    public function touchLogin(int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $stmt->execute([$id]);
    }

    public function list(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filters['role'])) {
            $where[] = 'role = ?';
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $q = '%' . $filters['q'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        $sql = 'SELECT * FROM users WHERE ' . implode(' AND ', $where) .
               ' ORDER BY created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByStatus(?string $status = null): int
    {
        if ($status) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE status = ?');
            $stmt->execute([$status]);
        } else {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM users');
        }
        return (int) $stmt->fetchColumn();
    }

    public function countByRole(): array
    {
        $stmt = $this->pdo->query('SELECT role, COUNT(*) AS total FROM users GROUP BY role');
        return $stmt->fetchAll();
    }
}
