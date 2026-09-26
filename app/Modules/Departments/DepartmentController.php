<?php
declare(strict_types=1);

final class DepartmentController extends Controller
{
    public static function index(): void
    {
        require_role(['ADMIN', 'STAFF']);
        (new self())->view('departments/index', [
            'items' => (new DepartmentRepository())->all(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            Validator::required($_POST, ['name' => 'اسم القسم', 'code' => 'رمز القسم']);
            $repository = new DepartmentRepository();
            $id = (int) ($_POST['id'] ?? 0);
            $action = $id > 0 ? 'UPDATE' : 'CREATE';
            if ($id > 0) {
                $repository->update($id, (string) $_POST['name'], (string) $_POST['code']);
            } else {
                $id = $repository->create((string) $_POST['name'], (string) $_POST['code']);
            }
            (new AuditService())->record($action, 'department', $id);
            flash('success', 'تم حفظ القسم بنجاح.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=departments');
    }

    public static function delete(): void
    {
        require_role(['ADMIN']);
        verify_csrf();
        try {
            (new DepartmentRepository())->delete((int) ($_POST['id'] ?? 0));
            flash('success', 'تم حذف القسم.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=departments');
    }
}
