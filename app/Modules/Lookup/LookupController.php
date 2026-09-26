<?php
declare(strict_types=1);

final class LookupController extends Controller
{
    public static function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!current_user()) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'items' => [], 'message' => 'انتهت جلسة الدخول. أعد تسجيل الدخول.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $entity = trim((string) ($_GET['entity'] ?? ''));
            $query = trim((string) ($_GET['q'] ?? ''));
            $studentId = (int) ($_GET['student_id'] ?? 0);
            $limit = min(20, max(1, (int) ($_GET['limit'] ?? 10)));

            if (strlen($query) < 1) {
                echo json_encode(
                    ['ok' => true, 'items' => []],
                    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
                );
                return;
            }

            $items = match ($entity) {
                'students' => (new StudentRepository())->search($query, $limit),
                'programs' => (new ProgramRepository())->search($query, $limit),
                'courses' => (new CourseRepository())->search($query, $studentId, $limit),
                'supervisors' => (new SupervisorRepository())->search($query, $limit),
                'plans' => (new PlanRepository())->searchForStudent($query, $studentId, $limit),
                default => throw new InvalidArgumentException('نوع البحث غير مدعوم.'),
            };

            $result = array_map(
                static function (array $item) use ($entity): array {
                    $label = match ($entity) {
                        'students' => $item['student_no'] . ' — ' . $item['full_name'],
                        'programs' => $item['name'] . ' — ' . status_label($item['degree']),
                        'courses' => $item['code'] . ' — ' . $item['name'],
                        'supervisors' => $item['full_name'] . ' — ' . ($item['specialization'] ?? 'مشرف'),
                        'plans' => $item['year_name'] . ' — ' . status_label($item['status']),
                        default => (string) $item['id'],
                    };

                    return [
                        'id' => (int) $item['id'],
                        'label' => $label,
                        'code' => $item['code'] ?? null,
                        'name' => $item['name'] ?? null,
                        'credits' => isset($item['credits']) ? (int) $item['credits'] : null,
                        'is_required' => isset($item['is_required']) ? (int) $item['is_required'] : null,
                        'year_name' => $item['year_name'] ?? null,
                        'status' => $item['status'] ?? null,
                    ];
                },
                $items
            );

            echo json_encode(
                ['ok' => true, 'items' => $result],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (Throwable $exception) {
            http_response_code(400);
            echo json_encode(
                [
                    'ok' => false,
                    'items' => [],
                    'message' => APP_DEBUG
                        ? $exception->getMessage()
                        : 'تعذر تنفيذ البحث من الخادم. تحقق من قاعدة البيانات ثم حاول مرة أخرى.',
                ],
                JSON_UNESCAPED_UNICODE
            );
        }
    }
}
