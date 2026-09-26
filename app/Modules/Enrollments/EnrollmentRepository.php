<?php
declare(strict_types=1);

final class EnrollmentRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT
                sc.*,
                s.student_no,
                s.full_name,
                c.code,
                c.name AS course_name,
                c.credits,
                p.id AS plan_id,
                ay.name AS year_name
             FROM student_courses sc
             JOIN students s ON s.id = sc.student_id
             JOIN courses c ON c.id = sc.course_id
             JOIN study_plans p ON p.id = sc.study_plan_id
             JOIN academic_years ay ON ay.id = p.academic_year_id
             ORDER BY sc.id DESC'
        )->fetchAll();
    }

    public function findPlan(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT sp.*, s.program_id, s.status AS student_status
             FROM study_plans sp
             JOIN students s ON s.id = sp.student_id
             WHERE sp.id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function courseForProgram(int $courseId, int $programId): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT c.* FROM courses c LEFT JOIN course_programs cp ON cp.course_id=c.id WHERE c.id = ? AND (c.program_id = ? OR cp.program_id = ?)'
        );
        $statement->execute([$courseId, $programId, $programId]);
        return $statement->fetch() ?: null;
    }

    public function duplicate(int $planId, int $courseId): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM student_courses
             WHERE study_plan_id = ? AND course_id = ? LIMIT 1'
        );
        $statement->execute([$planId, $courseId]);
        return (bool) $statement->fetchColumn();
    }

    public function create(int $studentId, int $courseId, int $planId, ?int $semesterId = null): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO student_courses
                (student_id, course_id, study_plan_id, semester_id)
             VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$studentId, $courseId, $planId, $semesterId]);
        return (int) $this->db()->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM student_courses WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function update(int $id, ?float $grade, string $status): void
    {
        $this->db()->prepare(
            'UPDATE student_courses SET grade = ?, status = ? WHERE id = ?'
        )->execute([$grade, $status, $id]);
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM student_courses WHERE id = ?')->execute([$id]);
    }
}
