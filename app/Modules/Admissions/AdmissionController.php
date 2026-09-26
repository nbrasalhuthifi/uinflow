<?php
declare(strict_types=1);

final class AdmissionController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('admissions/index', [
            'items' => (new AdmissionRepository())->all(),
            'programs' => (new ProgramRepository())->all(),
        ]);
    }

    public static function save(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new AdmissionService())->create($_POST);
            flash('success', 'تم إنشاء طلب القبول.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=admissions');
    }

    public static function enroll(): void { require_role(['ADMIN','STAFF']);verify_csrf();try{ $id=(new AdmissionService())->enroll((int)($_POST['id']??0));flash('success','تم تحويل الطلب المقبول إلى طالب: '.$id);}catch(Throwable $e){flash('error',$e->getMessage());}redirect('/?page=admissions'); }

    public static function status(): void
    {
        require_role(['ADMIN', 'STAFF']);
        verify_csrf();
        try {
            (new AdmissionService())->status(
                (int) ($_POST['id'] ?? 0),
                (string) ($_POST['status'] ?? '')
            );
            flash('success', 'تم تحديث حالة طلب القبول.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        redirect('/?page=admissions');
    }
}
