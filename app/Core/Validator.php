<?php
declare(strict_types=1);

final class Validator
{
    public static function required(array $data, array $fields): void
    {
        foreach ($fields as $field => $label) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                throw new InvalidArgumentException("الحقل {$label} مطلوب.");
            }
        }
    }

    public static function id(int $id, string $label = 'المعرف'): void
    {
        if ($id < 1) {
            throw new InvalidArgumentException("{$label} غير صالح.");
        }
    }

    public static function email(string $email): void
    {
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('البريد الإلكتروني غير صالح.');
        }
    }
}
