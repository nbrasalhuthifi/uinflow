<?php
declare(strict_types=1);

final class ProgramService
{
    public function save(array $data): int
    {
        Validator::required($data, [
            'department_id' => 'القسم',
            'name' => 'اسم البرنامج',
            'degree' => 'الدرجة',
        ]);

        $id = (int) ($data['id'] ?? 0);
        $values = [
            (int) $data['department_id'],
            trim($data['name']),
            $data['degree'],
            (float) ($data['duration_years'] ?? 2),
            (int) ($data['required_credits'] ?? 6),
        ];
        $repository = new ProgramRepository();

        if ($id > 0 && !$repository->find($id)) {
            throw new InvalidArgumentException('البرنامج غير موجود.');
        }

        return Database::transaction(function () use ($repository, $id, $values): int {
            if ($id > 0) {
                $repository->update($id, $values);
                (new AuditService())->record('UPDATE', 'program', $id);
                return $id;
            }

            $newId = $repository->create($values);
            (new AuditService())->record('CREATE', 'program', $newId);
            return $newId;
        });
    }

    public function delete(int $id): void
    {
        $repository = new ProgramRepository();

        if (!$repository->find($id)) {
            throw new InvalidArgumentException('البرنامج غير موجود.');
        }

        if ($repository->hasDependencies($id)) {
            throw new InvalidArgumentException(
                'لا يمكن حذف البرنامج لأنه مرتبط بطلاب أو مقررات أو طلبات قبول.'
            );
        }

        $repository->delete($id);
        (new AuditService())->record('DELETE', 'program', $id);
    }
}
