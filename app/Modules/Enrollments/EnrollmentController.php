<?php
declare(strict_types=1);

final class EnrollmentController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('enrollments/index', [
            'items' => (new EnrollmentRepository())->all(),
            'semesters' => Database::connection()->query("SELECT s.*, a.name year_name FROM semesters s JOIN academic_years a ON a.id=s.academic_year_id ORDER BY s.is_current DESC, s.id DESC")->fetchAll(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new EnrollmentService())->create($_POST);
            flash('success', 'تم تسجيل المقرر.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=enrollments');
    }

    public static function update(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            $grade = trim((string) ($_POST['grade'] ?? ''));
            (new EnrollmentService())->update(
                (int) ($_POST['id'] ?? 0),
                $grade === '' ? null : (float) $grade,
                (string) ($_POST['status'] ?? '')
            );
            flash('success', 'تم تحديث تسجيل المقرر.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=enrollments');
    }

    public static function delete(): void
    {
        require_role(['ADMIN']);
        verify_csrf();
        try {
            (new EnrollmentService())->delete((int) ($_POST['id'] ?? 0));
            flash('success', 'تم حذف التسجيل.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=enrollments');
    }
}
