<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = (string) ($_POST['_csrf'] ?? '');

    if (!hash_equals(csrf_token(), $token)) {
        throw new RuntimeException('انتهت صلاحية النموذج. أعد تحميل الصفحة وحاول مرة أخرى.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function consume_flash(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_auth(): void
{
    if (!current_user()) {
        redirect('/?page=login');
    }
}

function require_role(array $roles): void
{
    require_auth();

    $role = current_user()['role'] ?? '';

    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        exit('ليس لديك صلاحية لتنفيذ هذه العملية.');
    }
}

function status_label(string $status): string
{
    return [
        'ACTIVE' => 'نشط',
        'GRADUATED' => 'متخرج',
        'SUSPENDED' => 'موقوف',
        'DRAFT' => 'مسودة',
        'APPROVED' => 'معتمدة',
        'REGISTERED' => 'مسجل',
        'PASSED' => 'ناجح',
        'FAILED' => 'راسب',
        'PENDING' => 'قيد الانتظار',
        'REJECTED' => 'مرفوض',
        'SUBMITTED' => 'مقدم',
        'UNDER_REVIEW' => 'قيد المراجعة',
        'READY_FOR_DEFENSE' => 'جاهز للمناقشة',
        'DEFENDED' => 'تمت المناقشة',
        'GRADUATED_THESIS' => 'مكتملة',
        'CANCELLED' => 'ملغاة',
        'PASS' => 'ناجح',
        'PASS_WITH_CHANGES' => 'ناجح مع تعديلات',
        'FAIL' => 'راسب',
        'IN_PROGRESS' => 'قيد التنفيذ',
        'COMPLETED' => 'مكتملة',
        'NEW' => 'قبول جديد',
        'TRANSFER' => 'تحويل',
        'MASTER' => 'ماجستير',
        'PHD' => 'دكتوراه',
        'ADMIN' => 'مدير النظام',
        'STAFF' => 'موظف',
        'SUPERVISOR' => 'مشرف',
        'COMMITTEE' => 'عضو لجنة',
        'PRIMARY' => 'مشرف رئيسي',
        'CO_SUPERVISOR' => 'مشرف مشارك',
    ][$status] ?? $status;
}

function contact_links(?string $phone, ?string $email, string $name = ''): array
{
    $phone = preg_replace('/[^0-9]/', '', (string) $phone);

    return [
        'whatsapp' => $phone !== ''
            ? 'https://wa.me/' . $phone . '?text=' . rawurlencode('مرحبًا ' . $name)
            : '',
        'email' => $email !== '' ? 'mailto:' . $email : '',
    ];
}

function initial_letter(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return 'U';
    }
    return function_exists('mb_substr')
        ? (string) mb_substr($value, 0, 1)
        : strtoupper(substr($value, 0, 1));
}

function can(string $permission): bool
{
    $user = current_user();
    if (!$user) return false;
    if (($user['role'] ?? '') === 'ADMIN') return true;
    static $cache = [];
    $key = (int) ($user['id'] ?? 0) . ':' . $permission;
    if (isset($cache[$key])) return $cache[$key];
    try {
        $db = Database::connection();
        $q = $db->prepare('SELECT 1 FROM role_permissions WHERE role = ? AND permission = ? UNION SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = ? LIMIT 1');
        $q->execute([$user['role'], $permission, $user['id'], $permission]);
        return $cache[$key] = (bool) $q->fetchColumn();
    } catch (Throwable) {
        return $cache[$key] = false;
    }
}

function require_permission(string $permission): void
{
    require_auth();
    if (!can($permission)) {
        http_response_code(403);
        exit('ليس لديك صلاحية لتنفيذ هذه العملية.');
    }
}

function post_only(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method Not Allowed');
    }
}

function audit(string $action, string $entity, ?int $entityId = null, array $details = []): void
{
    try {
        (new AuditService())->record($action, $entity, $entityId, $details);
    } catch (Throwable) { }
}

function app_base_url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}
