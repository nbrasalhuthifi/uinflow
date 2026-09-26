<?php
declare(strict_types=1);

final class AdmissionRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT
                a.*,
                ap.application_no,
                ap.full_name AS applicant_name,
                ap.phone,
                ap.email,
                p.name AS program_name
             FROM applications a
             JOIN applicants ap ON ap.id = a.applicant_id
             JOIN programs p ON p.id = a.program_id
             ORDER BY a.id DESC'
        )->fetchAll();
    }

    public function applicants(): array
    {
        return $this->db()->query(
            'SELECT id, application_no, full_name
             FROM applicants
             ORDER BY id DESC'
        )->fetchAll();
    }

    public function createApplicant(array $values): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO applicants
                (application_no, full_name, phone, email, national_id,
                 previous_degree, previous_gpa)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function createApplication(array $values): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO applications
                (applicant_id, program_id, application_type, status, notes)
             VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT a.*, ap.*, p.name AS program_name
             FROM applications a
             JOIN applicants ap ON ap.id = a.applicant_id
             JOIN programs p ON p.id = a.program_id
             WHERE a.id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }


    public function createStudent(array $values): int
    {
        $st=$this->db()->prepare("INSERT INTO students(student_no,full_name,national_id,phone,email,program_id,admission_date,status) VALUES(?,?,?,?,?,?,CURDATE(),'ACTIVE')");$st->execute($values);return (int)$this->db()->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db()->prepare(
            'UPDATE applications SET status = ? WHERE id = ?'
        )->execute([$status, $id]);
    }
}
