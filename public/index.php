<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Helpers/helpers.php';
require_once __DIR__ . '/../app/Core/Repository.php';
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Validator.php';
require_once __DIR__ . '/../app/Services/AuditService.php';
require_once __DIR__ . '/../app/Services/NotificationService.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

spl_autoload_register(static function (string $class): void {
    $directories = [
        __DIR__ . '/../app/Modules/Auth/',
        __DIR__ . '/../app/Modules/Dashboard/',
        __DIR__ . '/../app/Modules/Students/',
        __DIR__ . '/../app/Modules/Programs/',
        __DIR__ . '/../app/Modules/Departments/',
        __DIR__ . '/../app/Modules/Courses/',
        __DIR__ . '/../app/Modules/Admissions/',
        __DIR__ . '/../app/Modules/Plans/',
        __DIR__ . '/../app/Modules/Enrollments/',
        __DIR__ . '/../app/Modules/Supervisors/',
        __DIR__ . '/../app/Modules/Research/',
        __DIR__ . '/../app/Modules/Theses/',
        __DIR__ . '/../app/Modules/Committees/',
        __DIR__ . '/../app/Modules/Graduation/',
        __DIR__ . '/../app/Modules/Reports/',
        __DIR__ . '/../app/Modules/Lookup/',
        __DIR__ . '/../app/Modules/Admin/',
        __DIR__ . '/../app/Modules/Documents/',
        __DIR__ . '/../app/Middleware/',
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

$routes = require __DIR__ . '/../routes/web.php';
$page = trim((string) ($_GET['page'] ?? 'dashboard'));
$action = trim((string) ($_GET['action'] ?? 'index'));

try {
    if ($page === 'login') {
        AuthController::login();
        exit;
    }

    if ($page === 'logout') {
        AuthController::logout();
        exit;
    }

    if ($page === 'dashboard') {
        DashboardController::index();
        exit;
    }

    if (!isset($routes[$page][$action])) {
        http_response_code(404);
        echo '404 — الصفحة المطلوبة غير موجودة.';
        exit;
    }

    [$class, $method] = $routes[$page][$action];
    $class::$method();
} catch (Throwable $exception) {
    $logLine = sprintf(
        "[%s] %s in %s:%d
%s
",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    file_put_contents(
        __DIR__ . '/../storage/logs/app.log',
        $logLine,
        FILE_APPEND
    );

    http_response_code(500);

    if (APP_DEBUG) {
        echo '<pre>' . e($exception->getMessage()) . '</pre>';
    } else {
        echo 'حدث خطأ داخلي. راجع storage/logs/app.log لمعرفة السبب.';
    }
}
