<?php
declare(strict_types=1);

final class ProgramController extends Controller
{
    public static function index(): void
    {
        require_auth();
        $repository = new ProgramRepository();
        (new self())->view('programs/index', [
            'items' => $repository->all(),
            'departments' => $repository->departments(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new ProgramService())->save($_POST);
            flash('success', 'تم حفظ البرنامج.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=programs');
    }

    public static function delete(): void
    {
        require_role(['ADMIN']);
        verify_csrf();
        try {
            (new ProgramService())->delete((int) ($_POST['id'] ?? 0));
            flash('success', 'تم حذف البرنامج.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=programs');
    }
}
