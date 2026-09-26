<?php
declare(strict_types=1);

final class StudentRepository extends Repository
{
    public function paginate(string $query, int $page, int $perPage = 25): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(10, $perPage));
        $offset = ($page - 1) * $perPage;
        $like = '%' . $query . '%';

        $count = $this->db()->prepare(
            "SELECT COUNT(*)
             FROM students s
             WHERE s.full_name LIKE :q_name
                OR s.student_no LIKE :q_no
                OR COALESCE(s.email, '') LIKE :q_email
                OR COALESCE(s.phone, '') LIKE :q_phone"
        );
        $count->execute([
            'q_name' => $like,
            'q_no' => $like,
            'q_email' => $like,
            'q_phone' => $like,
        ]);
        $total = (int) $count->fetchColumn();

        $statement = $this->db()->prepare(
            "SELECT
                s.*,
                p.name AS program_name,
                p.degree
             FROM students s
             JOIN programs p ON p.id = s.program_id
             WHERE s.full_name LIKE :q_name
                OR s.student_no LIKE :q_no
                OR COALESCE(s.email, '') LIKE :q_email
                OR COALESCE(s.phone, '') LIKE :q_phone
             ORDER BY s.id DESC
             LIMIT :limit OFFSET :offset"
        );
        $statement->bindValue(':q_name', $like, PDO::PARAM_STR);
        $statement->bindValue(':q_no', $like, PDO::PARAM_STR);
        $statement->bindValue(':q_email', $like, PDO::PARAM_STR);
        $statement->bindValue(':q_phone', $like, PDO::PARAM_STR);
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare(
            'SELECT s.*, p.name AS program_name
             FROM students s
             JOIN programs p ON p.id = s.program_id
             WHERE s.id = ?'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function duplicateNo(string $number, int $ignoreId = 0): bool
    {
        $statement = $this->db()->prepare(
            'SELECT 1 FROM students
             WHERE student_no = ? AND id <> ? LIMIT 1'
        );
        $statement->execute([$number, $ignoreId]);
        return (bool) $statement->fetchColumn();
    }

    public function create(array $values): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO students
                (student_no, full_name, national_id, phone, email, photo_path,
                 program_id, admission_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $values): void
    {
        $values[] = $id;
        $statement = $this->db()->prepare(
            'UPDATE students
             SET student_no = ?,
                 full_name = ?,
                 national_id = ?,
                 phone = ?,
                 email = ?,
                 photo_path = ?,
                 program_id = ?,
                 admission_date = ?,
                 status = ?
             WHERE id = ?'
        );
        $statement->execute($values);
    }

    public function documentPaths(int $id): array
    {
        $statement = $this->db()->prepare(
            "SELECT file_path FROM documents
             WHERE student_id = ?
                OR thesis_id IN (SELECT id FROM theses WHERE student_id = ?)"
        );
        $statement->execute([$id, $id]);
        return array_values(array_filter(array_map(
            static fn ($row) => (string) ($row['file_path'] ?? ''),
            $statement->fetchAll()
        )));
    }

    /**
     * Delete the complete student academic tree in dependency order.
     * This is intentionally restricted to the StudentService's ADMIN-only action.
     */
    public function deleteCascade(int $id): void
    {
        $db = $this->db();

        $queries = [
            'DELETE FROM documents WHERE student_id = ?',
            'DELETE FROM documents WHERE thesis_id IN (SELECT id FROM theses WHERE student_id = ?)',
            'DELETE FROM defenses WHERE thesis_id IN (SELECT id FROM theses WHERE student_id = ?)',
            'DELETE FROM committee_members WHERE committee_id IN (SELECT id FROM committees WHERE thesis_id IN (SELECT id FROM theses WHERE student_id = ?))',
            'DELETE FROM committees WHERE thesis_id IN (SELECT id FROM theses WHERE student_id = ?)',
            'DELETE FROM theses WHERE student_id = ?',
            'DELETE FROM research_proposals WHERE student_id = ?',
            'DELETE FROM student_courses WHERE student_id = ?',
            'DELETE FROM study_plans WHERE student_id = ?',
            'DELETE FROM student_supervisors WHERE student_id = ?',
            'DELETE FROM students WHERE id = ?',
        ];

        foreach ($queries as $sql) {
            $statement = $db->prepare($sql);
            $statement->execute([$id]);
        }
    }

    public function delete(int $id): void
    {
        $this->deleteCascade($id);
    }

    public function search(string $query, int $limit = 10): array
    {
        $limit = min(20, max(1, $limit));
        $like = '%' . trim($query) . '%';
        $statement = $this->db()->prepare(
            "SELECT id, student_no, full_name, phone, email
             FROM students
             WHERE status = 'ACTIVE'
               AND (
                    full_name LIKE :q_name
                    OR student_no LIKE :q_no
                    OR COALESCE(email, '') LIKE :q_email
               )
             ORDER BY full_name
             LIMIT :limit"
        );
        $statement->bindValue(':q_name', $like);
        $statement->bindValue(':q_no', $like);
        $statement->bindValue(':q_email', $like);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
