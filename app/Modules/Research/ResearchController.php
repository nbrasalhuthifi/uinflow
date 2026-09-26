<?php
declare(strict_types=1);

final class ResearchController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('research/index', [
            'items' => (new ResearchRepository())->all(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new ResearchService())->create($_POST);
            flash('success', 'تم تسجيل المقترح البحثي.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=research');
    }

    public static function status(): void
    {
        require_role(['ADMIN', 'STAFF', 'SUPERVISOR']);
        verify_csrf();
        try {
            (new ResearchService())->status(
                (int) ($_POST['id'] ?? 0),
                (string) ($_POST['status'] ?? '')
            );
            flash('success', 'تم تحديث حالة البحث.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=research');
    }
}
