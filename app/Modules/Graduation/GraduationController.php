<?php
declare(strict_types=1);

final class GraduationController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('graduation/index', [
            'items' => (new GraduationRepository())->candidates(),
        ]);
    }

    public static function approve(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new GraduationService())->approve(
                (int) ($_POST['student_id'] ?? 0)
            );
            flash('success', 'تم اعتماد تخرج الطالب وتحديث الرسالة.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=graduation');
    }
}
