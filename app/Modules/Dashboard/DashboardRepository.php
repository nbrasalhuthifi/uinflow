<?php
declare(strict_types=1);

final class DashboardRepository extends Repository
{
    public function stats(): array
    {
        $queries = [
            'students' => 'SELECT COUNT(*) FROM students',
            'active_students' => "SELECT COUNT(*) FROM students WHERE status = 'ACTIVE'",
            'applications' => "SELECT COUNT(*) FROM applications WHERE status = 'PENDING'",
            'research' => "SELECT COUNT(*) FROM research_proposals WHERE status IN ('SUBMITTED', 'UNDER_REVIEW')",
            'defenses' => "SELECT COUNT(*) FROM defenses WHERE result = 'PENDING'",
            'graduation' => "SELECT COUNT(*) FROM students WHERE status = 'GRADUATED'",
            'no_supervisor' => "SELECT COUNT(*) FROM students s LEFT JOIN student_supervisors ss ON ss.student_id=s.id AND ss.supervisor_role='PRIMARY' WHERE s.status='ACTIVE' AND ss.id IS NULL",
            'ready_graduation' => "SELECT COUNT(*) FROM students s JOIN programs p ON p.id=s.program_id JOIN theses t ON t.student_id=s.id AND t.status='DEFENDED' WHERE s.status='ACTIVE' AND (SELECT COALESCE(SUM(c.credits),0) FROM student_courses sc JOIN courses c ON c.id=sc.course_id WHERE sc.student_id=s.id AND sc.status='PASSED') >= p.required_credits",
            'upcoming_defenses' => "SELECT COUNT(*) FROM defenses WHERE result='PENDING' AND defense_date >= CURDATE()",
        ];

        $stats = [];
        foreach ($queries as $key => $sql) {
            $stats[$key] = (int) $this->db()->query($sql)->fetchColumn();
        }
        return $stats;
    }

    public function timeline(): array
    {
        return $this->db()->query(
            "SELECT
                al.action,
                al.entity,
                al.entity_id,
                al.created_at,
                COALESCE(u.full_name, 'النظام') AS user_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.id DESC
             LIMIT 12"
        )->fetchAll();
    }
}
