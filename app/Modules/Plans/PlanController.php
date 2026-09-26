<?php
declare(strict_types=1);

final class PlanController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('plans/index', [
            'items' => (new PlanRepository())->all(),
            'years' => (new PlanRepository())->years(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new PlanService())->create($_POST);
            flash('success', 'تم إنشاء الخطة الدراسية.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=plans');
    }

    public static function status(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new PlanService())->status(
                (int) ($_POST['id'] ?? 0),
                (string) ($_POST['status'] ?? '')
            );
            flash('success', 'تم تحديث حالة الخطة.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=plans');
    }

    public static function delete(): void
    {
        require_role(['ADMIN']);
        verify_csrf();
        try {
            (new PlanService())->delete((int) ($_POST['id'] ?? 0));
            flash('success', 'تم حذف الخطة.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=plans');
    }
}
