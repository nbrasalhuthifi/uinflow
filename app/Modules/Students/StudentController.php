<?php
declare(strict_types=1);

final class StudentController extends Controller
{
    public static function index(): void
    {
        require_auth();

        $query = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['p'] ?? 1));
        $repository = new StudentRepository();
        $result = $repository->paginate($query, $page, 25);

        (new self())->view('students/index', [
            'result' => $result,
            'query' => $query,
            'programs' => (new ProgramRepository())->all(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();

        try {
            $id = (new StudentService())->save($_POST);
            flash('success', 'تم حفظ ملف الطالب بنجاح.');
        } catch (Throwable $exception) {
            flash(
                'error',
                $exception instanceof InvalidArgumentException
                    ? $exception->getMessage()
                    : 'تعذر حفظ الطالب. راجع سجل الأخطاء إذا تكرر الخطأ.'
            );
        }

        redirect('/?page=students');
    }

    public static function delete(): void
    {
        require_role(['ADMIN']);
        verify_csrf();

        try {
            (new StudentService())->delete((int) ($_POST['id'] ?? 0));
            flash('success', 'تم حذف الطالب.');
        } catch (Throwable $exception) {
            flash(
                'error',
                $exception instanceof InvalidArgumentException
                    ? $exception->getMessage()
                    : 'تعذر حذف الطالب.'
            );
        }

        redirect('/?page=students');
    }
}
