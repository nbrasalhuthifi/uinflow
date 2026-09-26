<?php
declare(strict_types=1);

final class AuthMiddleware
{
    public static function check(): void
    {
        require_auth();
    }
}
