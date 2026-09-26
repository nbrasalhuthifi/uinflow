<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        self::$pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$pdo;
    }

    public static function transaction(callable $callback): mixed
    {
        $db = self::connection();
        $started = !$db->inTransaction();

        if ($started) {
            $db->beginTransaction();
        }

        try {
            $result = $callback($db);

            if ($started) {
                $db->commit();
            }

            return $result;
        } catch (Throwable $exception) {
            if ($started && $db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }
}
