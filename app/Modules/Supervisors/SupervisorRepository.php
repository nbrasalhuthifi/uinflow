<?php
declare(strict_types=1);

final class SupervisorRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT s.*, u.full_name, u.email
             FROM supervisors s
             JOIN users u ON u.id = s.user_id
             ORDER BY u.full_name'
        )->fetchAll();
    }

    public function createFromDetails(array $data): int
    {
        return Database::transaction(function (PDO $db) use ($data): int {
            $email = trim((string) $data['email']);
            $check = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->execute([$email]);
            if ($check->fetch()) {
                throw new InvalidArgumentException('البريد الإلكتروني مستخدم مسبقًا. استخدم بريدًا آخر.');
            }

            $user = $db->prepare(
                "INSERT INTO users (full_name, email, password, role, is_active)
                 VALUES (?, ?, ?, 'SUPERVISOR', 1)"
            );
            $user->execute([
                trim((string) $data['full_name']),
                $email,
                password_hash((string) $data['password'], PASSWORD_DEFAULT),
            ]);
            $userId = (int) $db->lastInsertId();

            $supervisor = $db->prepare(
                'INSERT INTO supervisors (user_id, academic_rank, specialization, photo_path)
                 VALUES (?, ?, ?, ?)'
            );
            $supervisor->execute([
                $userId,
                trim((string) $data['academic_rank']),
                trim((string) ($data['specialization'] ?? '')),
                null,
            ]);

            return (int) $db->lastInsertId();
        });
    }

    public function updatePhoto(int $id, string $photoPath): void
    {
        $statement = $this->db()->prepare('UPDATE supervisors SET photo_path = ? WHERE id = ?');
        $statement->execute([$photoPath, $id]);
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM supervisors WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function assignedStudents(int $supervisorId): array
    {
        $statement = $this->db()->prepare(
            'SELECT s.id, s.student_no, s.full_name
             FROM student_supervisors ss
             JOIN students s ON s.id = ss.student_id
             WHERE ss.supervisor_id = ?
             ORDER BY s.full_name'
        );
        $statement->execute([$supervisorId]);
        return $statement->fetchAll();
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM supervisors WHERE id = ?')->execute([$id]);
    }

    public function search(string $query, int $limit = 10): array
    {
        $statement = $this->db()->prepare(
            "SELECT s.id, u.full_name, s.specialization
             FROM supervisors s
             JOIN users u ON u.id = s.user_id
             WHERE u.full_name LIKE :q_name
                OR COALESCE(s.specialization, '') LIKE :q_specialization
             ORDER BY u.full_name
             LIMIT :limit"
        );
        $like = '%' . trim($query) . '%';
        $statement->bindValue(':q_name', $like);
        $statement->bindValue(':q_specialization', $like);
        $statement->bindValue(':limit', min(20, max(1, $limit)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
