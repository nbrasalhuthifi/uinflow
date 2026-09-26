<?php
declare(strict_types=1);

final class SupervisorController extends Controller
{
    public static function index(): void
    {
        require_auth();
        (new self())->view('supervisors/index', [
            'items' => (new SupervisorRepository())->all(),
        ]);
    }

    private static function storePhoto(array $file, int $recordId): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('تعذر رفع صورة المشرف.');
        }
        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('حجم الصورة يجب ألا يتجاوز 2MB.');
        }
        $imageInfo = @getimagesize($file['tmp_name']);
        $mime = is_array($imageInfo) ? ($imageInfo['mime'] ?? '') : '';
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($extensions[$mime])) {
            throw new InvalidArgumentException('الصورة يجب أن تكون JPG أو PNG أو WEBP.');
        }
        $relative = 'uploads/supervisors/' . $recordId . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$mime];
        $absolute = dirname(__DIR__, 3) . '/public/' . $relative;
        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0775, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $absolute)) {
            throw new RuntimeException('تعذر حفظ صورة المشرف على الخادم.');
        }
        return $relative;
    }

    public static function save(): void
    {
        require_role(['ADMIN']);
        verify_csrf();

        try {
            Validator::required($_POST, [
                'full_name' => 'اسم المشرف',
                'email' => 'البريد الإلكتروني',
                'password' => 'كلمة المرور',
                'academic_rank' => 'الرتبة الأكاديمية',
            ]);
            if (!filter_var(trim((string) $_POST['email']), FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('أدخل بريدًا إلكترونيًا صحيحًا.');
            }
            if (strlen((string) $_POST['password']) < 6) {
                throw new InvalidArgumentException('كلمة مرور حساب المشرف يجب ألا تقل عن 6 أحرف.');
            }
            $id = (new SupervisorRepository())->createFromDetails($_POST);

            if (!empty($_FILES['photo']['name'])) {
                $photoPath = self::storePhoto($_FILES['photo'], $id);
                (new SupervisorRepository())->updatePhoto($id, $photoPath);
            }
            (new AuditService())->record('CREATE', 'supervisor', $id);
            flash('success', 'تم إضافة المشرف.');
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }

        redirect('/?page=supervisors');
    }
}
