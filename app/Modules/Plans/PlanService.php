<?php
declare(strict_types=1);

final class PlanService
{
    public function create(array $data): int
    {
        Validator::required($data, [
            'student_id' => 'الطالب',
            'academic_year_id' => 'السنة الأكاديمية',
        ]);

        $studentId = (int) $data['student_id'];
        $yearId = (int) $data['academic_year_id'];
        $repository = new PlanRepository();

        if (!$repository->studentExists($studentId)) {
            throw new InvalidArgumentException('الطالب غير موجود أو متخرج.');
        }
        if (!$repository->yearExists($yearId)) {
            throw new InvalidArgumentException('السنة الأكاديمية غير موجودة.');
        }
        if ($repository->duplicate($studentId, $yearId)) {
            throw new InvalidArgumentException('توجد خطة بالفعل لهذا الطالب في هذه السنة.');
        }

        $id = $repository->create($studentId, $yearId);
        (new AuditService())->record('CREATE', 'study_plan', $id);
        return $id;
    }

    public function status(int $id, string $status): void
    {
        if (!in_array($status, ['DRAFT', 'APPROVED', 'IN_PROGRESS', 'COMPLETED'], true)) {
            throw new InvalidArgumentException('حالة الخطة غير صالحة.');
        }

        $repository = new PlanRepository();
        $plan = $repository->find($id);
        if (!$plan) {
            throw new InvalidArgumentException('الخطة غير موجودة.');
        }

        $repository->setStatus($id, $status);
        (new AuditService())->record('STATUS', 'study_plan', $id);
    }

    public function delete(int $id): void
    {
        $repository = new PlanRepository();
        if (!$repository->find($id)) {
            throw new InvalidArgumentException('الخطة غير موجودة.');
        }
        if ($repository->hasEnrollments($id)) {
            throw new InvalidArgumentException('لا يمكن حذف خطة تحتوي على مقررات مسجلة.');
        }
        $repository->delete($id);
        (new AuditService())->record('DELETE', 'study_plan', $id);
    }
}
