<?php
declare(strict_types=1);

final class RoleMiddleware
{
    public static function check(array $roles): void
    {
        require_role($roles);
    }
}
