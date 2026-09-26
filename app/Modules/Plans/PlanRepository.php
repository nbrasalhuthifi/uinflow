<?php
declare(strict_types=1);

final class PlanRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT
                sp.*,
                s.student_no,
                s.full_name,
                ay.name AS year_name,
                p.name AS program_name
             FROM study_plans sp
             JOIN students s ON s.id = sp.student_id
             JOIN academic_years ay ON ay.id = sp.academic_year_id
             JOIN programs p ON p.id = s.program_id
             ORDER BY sp.id DESC'
        )->fetchAll();
    }

    public function years(): array
    {
        return $this->db()->query(
            'SELECT id, name FROM academic_years ORDER BY name DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM study_plans WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function studentExists(int $id): bool
    {
        $statement = $this->db()->prepare(
            "SELECT 1 FROM students WHERE id = ? AND status <> 'GRADUATED'"
        );
        $statement->execute([$id]);
        return (bool) $statement->fetchColumn();
    }

    public function yearExists(int $id): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM academic_years WHERE id = ?'
        );
        $statement->execute([$id]);
        return (bool) $statement->fetchColumn();
    }

    public function duplicate(int $studentId, int $yearId): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM study_plans
             WHERE student_id = ? AND academic_year_id = ? LIMIT 1'
        );
        $statement->execute([$studentId, $yearId]);
        return (bool) $statement->fetchColumn();
    }

    public function create(int $studentId, int $yearId): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO study_plans (student_id, academic_year_id)
             VALUES (?, ?)'
        );
        $statement->execute([$studentId, $yearId]);
        return (int) $this->db()->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->prepare(
            'UPDATE study_plans SET status = ? WHERE id = ?'
        )->execute([$status, $id]);
    }

    public function hasEnrollments(int $id): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM student_courses WHERE study_plan_id = ? LIMIT 1'
        );
        $statement->execute([$id]);
        return (bool) $statement->fetchColumn();
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM study_plans WHERE id = ?')->execute([$id]);
    }

    public function searchForStudent(string $query, int $studentId, int $limit = 10): array
    {
        $statement = $this->db()->prepare(
            'SELECT sp.id, ay.name AS year_name, sp.status
             FROM study_plans sp
             JOIN academic_years ay ON ay.id = sp.academic_year_id
             WHERE sp.student_id = :student_id
               AND ay.name LIKE :q
             ORDER BY ay.name DESC
             LIMIT :limit'
        );
        $statement->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $statement->bindValue(':q', '%' . trim($query) . '%');
        $statement->bindValue(':limit', min(20, max(1, $limit)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
