<?php
declare(strict_types=1);

final class ProgramRepository extends Repository
{
    public function all(): array
    {
        return $this->db()->query(
            'SELECT p.*, d.name AS department_name, d.code AS department_code
             FROM programs p
             JOIN departments d ON d.id = p.department_id
             ORDER BY d.name ASC, p.degree ASC, p.name ASC'
        )->fetchAll();
    }

    public function departments(): array
    {
        return $this->db()->query(
            'SELECT id, name, code FROM departments ORDER BY name'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db()->prepare('SELECT * FROM programs WHERE id = ?');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function create(array $values): int
    {
        $statement = $this->db()->prepare(
            'INSERT INTO programs
                (department_id, name, degree, duration_years, required_credits)
             VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute($values);
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $values): void
    {
        $values[] = $id;
        $this->db()->prepare(
            'UPDATE programs
             SET department_id = ?,
                 name = ?,
                 degree = ?,
                 duration_years = ?,
                 required_credits = ?
             WHERE id = ?'
        )->execute($values);
    }

    public function delete(int $id): void
    {
        $this->db()->prepare('DELETE FROM programs WHERE id = ?')->execute([$id]);
    }

    public function hasDependencies(int $id): bool
    {
        foreach (['students' => 'program_id', 'courses' => 'program_id', 'applications' => 'program_id'] as $table => $column) {
            $statement = $this->db()->prepare(
                "SELECT 1 FROM {$table} WHERE {$column} = ? LIMIT 1"
            );
            $statement->execute([$id]);
            if ($statement->fetchColumn()) {
                return true;
            }
        }
        return false;
    }

    public function search(string $query, int $limit = 10): array
    {
        $statement = $this->db()->prepare(
            'SELECT id, name, degree
             FROM programs
             WHERE name LIKE :q_name
                OR degree LIKE :q_degree
             ORDER BY name
             LIMIT :limit'
        );
        $like = '%' . trim($query) . '%';
        $statement->bindValue(':q_name', $like);
        $statement->bindValue(':q_degree', $like);
        $statement->bindValue(':limit', min(20, max(1, $limit)), PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
