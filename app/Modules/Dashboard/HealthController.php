<?php
declare(strict_types=1);

final class HealthController extends Controller
{
    public static function index(): void
    {
        require_auth();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = Database::connection();
            $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

            echo json_encode(
                [
                    'ok' => true,
                    'application' => APP_NAME,
                    'database' => DB_NAME,
                    'database_port' => DB_PORT,
                    'tables' => count($tables),
                    'php' => PHP_VERSION,
                ],
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
        } catch (Throwable $exception) {
            http_response_code(503);
            echo json_encode(
                [
                    'ok' => false,
                    'message' => 'قاعدة البيانات غير متاحة.',
                ],
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            );
        }
    }
}
