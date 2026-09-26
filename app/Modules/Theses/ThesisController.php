<?php
declare(strict_types=1);

final class ThesisController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('theses/index', [
            'items' => (new ThesisRepository())->all(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new ThesisService())->create($_POST);
            flash('success', 'تم إنشاء ملف الرسالة العلمية.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=theses');
    }

    public static function status(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new ThesisService())->status(
                (int) ($_POST['id'] ?? 0),
                (string) ($_POST['status'] ?? '')
            );
            flash('success', 'تم تحديث حالة الرسالة.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=theses');
    }
}
