<?php
declare(strict_types=1);

final class AuditService
{
    public function record(string $action, string $entity, ?int $entityId = null, array $details = []): void
    {
        $userId = current_user()['id'] ?? null;
        $statement = Database::connection()->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, details, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $userId,
            $action,
            $entity,
            $entityId,
            $details ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
        ]);
    }
}
