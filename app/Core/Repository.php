<?php
declare(strict_types=1);

abstract class Repository
{
    protected function db(): PDO
    {
        return Database::connection();
    }
}
