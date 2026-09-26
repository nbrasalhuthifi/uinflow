<?php
declare(strict_types=1);

final class GraduationService
{
    public function approve(int $studentId): void
    {
        Validator::id($studentId, 'الطالب');
        $repository = new GraduationRepository();
        $student = $repository->student($studentId);

        if (!$student) {
            throw new InvalidArgumentException('الطالب غير موجود.');
        }
        if ($student['status'] === 'GRADUATED') {
            throw new InvalidArgumentException('الطالب متخرج بالفعل.');
        }

        $candidate = $repository->eligibility($studentId);
        if (!$candidate) {
            throw new InvalidArgumentException('تعذر التحقق من ملف التخرج للطالب.');
        }

        $missing = [];
        if ((int) $candidate['earned_credits'] < (int) $candidate['required_credits']) {
            $missing[] = 'الساعات الدراسية المطلوبة';
        }
        if ($candidate['thesis_status'] !== 'DEFENDED') {
            $missing[] = 'مناقشة الرسالة بنجاح';
        }
        if (!in_array($candidate['defense_result'], ['PASS', 'PASS_WITH_CHANGES'], true)) {
            $missing[] = 'نتيجة المناقشة الناجحة';
        }

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'لا يمكن اعتماد التخرج. المتطلبات غير المكتملة: ' . implode('، ', $missing) . '.'
            );
        }

        Database::transaction(function () use ($repository, $studentId): void {
            $fresh = $repository->eligibility($studentId);
            if (!$fresh || (int) $fresh['ready'] !== 1) {
                throw new InvalidArgumentException(
                    'لا يمكن اعتماد التخرج لأن متطلبات التخرج غير مكتملة.'
                );
            }

            $repository->graduate($studentId);
            if (!empty($fresh['thesis_id'])) {
                $repository->graduateThesis((int) $fresh['thesis_id']);
            }
            (new AuditService())->record('GRADUATE', 'student', $studentId);
        });
    }

}
