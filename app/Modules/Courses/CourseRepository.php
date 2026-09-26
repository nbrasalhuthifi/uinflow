<?php
declare(strict_types=1);

final class CourseRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT c.*, p.name AS program_name,
                (SELECT GROUP_CONCAT(cp.program_id) FROM course_programs cp WHERE cp.course_id=c.id) AS shared_program_ids
             FROM courses c
             JOIN programs p ON p.id = c.program_id
             ORDER BY p.name, c.code'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare('SELECT c.*, GROUP_CONCAT(cp.program_id) AS shared_program_ids FROM courses c LEFT JOIN course_programs cp ON cp.course_id=c.id WHERE c.id = ? GROUP BY c.id');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function create(array $values): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO courses
                (program_id, code, name, credits, is_required)
             VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $values): void
    {
        $values[] = $id;
        $this->db()->prepare(
            'UPDATE courses
             SET program_id = ?, code = ?, name = ?, credits = ?, is_required = ?
             WHERE id = ?'
        )->execute($values);
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM courses WHERE id = ?')->execute([$id]);
    }

    public function hasEnrollments(int $id): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM student_courses WHERE course_id = ? LIMIT 1'
        );
        $statement->execute([$id]);
        return (bool) $statement->fetchColumn();
    }

    public function search(string $query, int $studentId = 0, int $limit = 10): array
    {
        $params = ['q' => '%' . trim($query) . '%'];
        $where = '';

        if ($studentId > 0) {
            $where = ' AND (c.program_id = (SELECT program_id FROM students WHERE id = :student_id) OR EXISTS (SELECT 1 FROM course_programs cp2 WHERE cp2.course_id=c.id AND cp2.program_id=(SELECT program_id FROM students WHERE id = :student_id)))';
            $params['student_id'] = $studentId;
        }

        $statement = $this->db()->prepare(
            "SELECT c.id, c.code, c.name, c.credits, c.is_required
             FROM courses c
             WHERE (c.code LIKE :q_code OR c.name LIKE :q_name)
             {$where}
             ORDER BY c.code
             LIMIT :limit"
        );
        $statement->bindValue(':q_code', $params['q']);
        $statement->bindValue(':q_name', $params['q']);
        if (isset($params['student_id'])) {
            $statement->bindValue(':student_id', $params['student_id'], PDO::PARAM_INT);
        }
        $statement->bindValue(':limit', min(20, max(1, $limit)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
