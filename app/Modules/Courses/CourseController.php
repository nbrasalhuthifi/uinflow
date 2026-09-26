<?php
declare(strict_types=1);

final class CourseController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('courses/index', [
            'items' => (new CourseRepository())->all(),
            'programs' => (new ProgramRepository())->all(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new CourseService())->save($_POST);
            flash('success', 'تم حفظ المقرر.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=courses');
    }

    public static function delete(): void
    {
        require_role(['ADMIN']);
        verify_csrf();
        try {
            (new CourseService())->delete((int) ($_POST['id'] ?? 0));
            flash('success', 'تم حذف المقرر.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=courses');
    }
}
