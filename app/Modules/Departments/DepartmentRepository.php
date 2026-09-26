<?php
declare(strict_types=1);

final class DepartmentRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT d.id, d.name, d.code, COUNT(p.id) AS programs_count
             FROM departments d
             LEFT JOIN programs p ON p.department_id = d.id
             GROUP BY d.id, d.name, d.code
             ORDER BY d.name'
        )->fetchAll();
    }

    public function create(string $name, string $code): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO departments (name, code) VALUES (?, ?)'
        );
        $statement->execute([trim($name), strtoupper(trim($code))]);
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, string $name, string $code): void
    {
        $statement = $this->db()->prepare(
            'UPDATE departments SET name = ?, code = ? WHERE id = ?'
        );
        $statement->execute([trim($name), strtoupper(trim($code)), $id]);
    }

    public function delete(int $id): void
    {
        $check = $this->db()->prepare(
            'SELECT COUNT(*) FROM programs WHERE department_id = ?'
        );
        $check->execute([$id]);
        if ((int) $check->fetchColumn() > 0) {
            throw new InvalidArgumentException('لا يمكن حذف القسم لأنه مرتبط ببرامج أكاديمية. انقل البرامج أولًا.');
        }

        $this->db()->prepare('DELETE FROM departments WHERE id = ?')->execute([$id]);
    }
}
