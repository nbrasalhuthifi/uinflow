<?php
declare(strict_types=1);

final class ResearchRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT
                r.*,
                s.student_no,
                s.full_name,
                u.full_name AS supervisor_name
             FROM research_proposals r
             JOIN students s ON s.id = r.student_id
             LEFT JOIN supervisors sv ON sv.id = r.supervisor_id
             LEFT JOIN users u ON u.id = sv.user_id
             ORDER BY r.id DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM research_proposals WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function studentExists(int $id): bool
    {
        $statement = $this->db()->prepare(
            "SELECT 1 FROM students WHERE id = ? AND status = 'ACTIVE'"
        );
        $statement->execute([$id]);
        return (bool) $statement->fetchColumn();
    }

    public function supervisorAssignedToStudent(int $supervisorId, int $studentId): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1
             FROM student_supervisors
             WHERE supervisor_id = ? AND student_id = ?
             LIMIT 1'
        );
        $statement->execute([$supervisorId, $studentId]);
        return (bool) $statement->fetchColumn();
    }

    public function hasOpenProposal(int $studentId): bool
    {
        $statement = $this->db()->prepare(
            "SELECT 1 FROM research_proposals
             WHERE student_id = ?
               AND status IN ('SUBMITTED', 'UNDER_REVIEW', 'APPROVED')
             LIMIT 1"
        );
        $statement->execute([$studentId]);
        return (bool) $statement->fetchColumn();
    }

    public function create(array $values): int
    {
        $statement = $this->db()->prepare(
            "INSERT INTO research_proposals
                (student_id, supervisor_id, title, abstract, status, submitted_at)
             VALUES (?, ?, ?, ?, 'SUBMITTED', CURDATE())"
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->prepare(
            'UPDATE research_proposals SET status = ? WHERE id = ?'
        )->execute([$status, $id]);
    }
}
