<?php
declare(strict_types=1);

final class ThesisRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT
                t.*,
                s.student_no,
                s.full_name,
                d.result AS defense_result
             FROM theses t
             JOIN students s ON s.id = t.student_id
             LEFT JOIN defenses d ON d.thesis_id = t.id
             ORDER BY t.id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM theses WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function studentHasThesis(int $studentId): bool
    {
        $statement = $this->db()->prepare(
            "SELECT 1 FROM theses
             WHERE student_id = ?
               AND status NOT IN ('CANCELLED', 'GRADUATED')
             LIMIT 1"
        );
        $statement->execute([$studentId]);
        return (bool) $statement->fetchColumn();
    }

    public function create(array $values): int
    {
        $statement = $this->db()->prepare(
            "INSERT INTO theses
                (student_id, proposal_id, title, start_date, status)
             VALUES (?, ?, ?, ?, 'ACTIVE')"
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->prepare(
            'UPDATE theses SET status = ? WHERE id = ?'
        )->execute([$status, $id]);
    }
}
