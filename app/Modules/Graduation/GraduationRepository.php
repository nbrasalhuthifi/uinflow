<?php
declare(strict_types=1);

final class GraduationRepository extends Repository
{
    public function candidates(): array
    {
        return $this->db()->query(
            "SELECT
                s.id,
                s.student_no,
                s.full_name,
                p.name AS program_name,
                p.required_credits,
                COALESCE((
                    SELECT SUM(c2.credits)
                    FROM student_courses sc2
                    JOIN courses c2 ON c2.id = sc2.course_id
                    WHERE sc2.student_id = s.id
                      AND sc2.status = 'PASSED'
                ), 0) AS earned_credits,
                t.id AS thesis_id,
                t.status AS thesis_status,
                d.result AS defense_result,
                CASE
                    WHEN COALESCE((
                        SELECT SUM(c3.credits)
                        FROM student_courses sc3
                        JOIN courses c3 ON c3.id = sc3.course_id
                        WHERE sc3.student_id = s.id
                          AND sc3.status = 'PASSED'
                    ), 0) >= p.required_credits
                    AND t.status = 'DEFENDED'
                    AND d.result IN ('PASS', 'PASS_WITH_CHANGES')
                    THEN 1 ELSE 0
                END AS ready
             FROM students s
             JOIN programs p ON p.id = s.program_id
             LEFT JOIN theses t ON t.id = (
                 SELECT MAX(t2.id) FROM theses t2 WHERE t2.student_id = s.id
             )
             LEFT JOIN defenses d ON d.id = (
                 SELECT MAX(d2.id) FROM defenses d2 WHERE d2.thesis_id = t.id
             )
             WHERE s.status <> 'GRADUATED'
             ORDER BY ready DESC, s.full_name"
        )->fetchAll();
    }

    public function eligibility(int $studentId): array
    {
        $statement = $this->db()->prepare(
            "SELECT
                s.id,
                s.student_no,
                s.full_name,
                p.required_credits,
                COALESCE((
                    SELECT SUM(c.credits)
                    FROM student_courses sc
                    JOIN courses c ON c.id = sc.course_id
                    WHERE sc.student_id = s.id
                      AND sc.status = 'PASSED'
                ), 0) AS earned_credits,
                t.id AS thesis_id,
                t.status AS thesis_status,
                d.result AS defense_result
             FROM students s
             JOIN programs p ON p.id = s.program_id
             LEFT JOIN theses t ON t.id = (
                 SELECT MAX(t2.id) FROM theses t2 WHERE t2.student_id = s.id
             )
             LEFT JOIN defenses d ON d.id = (
                 SELECT MAX(d2.id) FROM defenses d2 WHERE d2.thesis_id = t.id
             )
             WHERE s.id = ?"
        );
        $statement->execute([$studentId]);
        $row = $statement->fetch();
        if (!$row) {
            return [];
        }
        $row['ready'] = (int) $row['earned_credits'] >= (int) $row['required_credits']
            && $row['thesis_status'] === 'DEFENDED'
            && in_array($row['defense_result'], ['PASS', 'PASS_WITH_CHANGES'], true)
            ? 1 : 0;
        return $row;
    }

    public function student(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT
                s.*, p.name AS program_name, p.required_credits
             FROM students s
             JOIN programs p ON p.id = s.program_id
             WHERE s.id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function graduate(int $id): void
    {
        $this->db()->prepare(
            "UPDATE students SET status = 'GRADUATED' WHERE id = ?"
        )->execute([$id]);
    }

    public function graduateThesis(int $thesisId): void
    {
        $this->db()->prepare(
            "UPDATE theses SET status = 'GRADUATED' WHERE id = ?"
        )->execute([$thesisId]);
    }
}
