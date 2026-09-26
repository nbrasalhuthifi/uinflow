<?php
declare(strict_types=1);

final class CommitteeRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT
                c.*,
                t.title AS thesis_title,
                s.full_name AS student_name,
                d.result AS defense_result
             FROM committees c
             JOIN theses t ON t.id = c.thesis_id
             JOIN students s ON s.id = t.student_id
             LEFT JOIN defenses d ON d.thesis_id = t.id
             ORDER BY c.meeting_date DESC, c.id DESC'
        )->fetchAll();
    }

    public function theses(): array
    {
        return $this->db()->query(
            "SELECT t.id, t.title, s.student_no, s.full_name
             FROM theses t
             JOIN students s ON s.id = t.student_id
             WHERE t.status IN ('READY_FOR_DEFENSE', 'DEFENDED')
             ORDER BY t.id DESC"
        )->fetchAll();
    }

    public function users(): array
    {
        return $this->db()->query(
            "SELECT id, full_name, role
             FROM users
             WHERE role IN ('SUPERVISOR', 'COMMITTEE', 'ADMIN')
               AND is_active = 1
             ORDER BY full_name"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM committees WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function duplicateType(int $thesisId, string $type): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM committees
             WHERE thesis_id = ? AND committee_type = ? LIMIT 1'
        );
        $statement->execute([$thesisId, $type]);
        return (bool) $statement->fetchColumn();
    }

    public function create(int $thesisId, string $type, string $date): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO committees
                (thesis_id, committee_type, meeting_date)
             VALUES (?, ?, ?)'
        );
        $statement->execute([$thesisId, $type, $date]);
        return (int) $this->db()->lastInsertId();
    }

    public function addMember(int $committeeId, int $userId, string $role): void
    {
        $this->db()->prepare(
            'INSERT INTO committee_members
                (committee_id, user_id, member_role)
             VALUES (?, ?, ?)'
        )->execute([$committeeId, $userId, $role]);
    }

    public function memberCount(int $committeeId): int
    {
        $statement = $this->db()->prepare(
            'SELECT COUNT(*) FROM committee_members WHERE committee_id = ?'
        );
        $statement->execute([$committeeId]);
        return (int) $statement->fetchColumn();
    }

    public function thesis(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM theses WHERE id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function defense(int $thesisId): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT * FROM defenses WHERE thesis_id = ?'
        );
        $statement->execute([$thesisId]);
        return $statement->fetch() ?: null;
    }

    public function saveDefense(array $values): void
    {
        $statement = $this->db()->prepare(
            'INSERT INTO defenses
                (thesis_id, defense_date, location, result, notes)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                defense_date = VALUES(defense_date),
                location = VALUES(location),
                result = VALUES(result),
                notes = VALUES(notes)'
        );
        $statement->execute($values);
    }
}
