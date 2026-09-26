<?php
declare(strict_types=1);

final class StudentService
{
    public function save(array $data): int
    {
        Validator::required($data, [
            'student_no' => 'الرقم الجامعي',
            'full_name' => 'اسم الطالب',
            'program_id' => 'البرنامج',
            'status' => 'الحالة',
        ]);
        Validator::email(trim((string) ($data['email'] ?? '')));

        $id = (int) ($data['id'] ?? 0);
        $repository = new StudentRepository();

        if ($repository->duplicateNo(trim($data['student_no']), $id)) {
            throw new InvalidArgumentException('الرقم الجامعي مستخدم مسبقًا.');
        }

        $photoPath = trim((string) ($data['existing_photo_path'] ?? ''));
        if (!empty($_FILES['photo']['name'])) {
            $photoPath = self::storePhoto($_FILES['photo'], 'students', $id);
        }

        $values = [
            trim($data['student_no']),
            trim($data['full_name']),
            trim((string) ($data['national_id'] ?? '')),
            trim((string) ($data['phone'] ?? '')),
            trim((string) ($data['email'] ?? '')),
            $photoPath !== '' ? $photoPath : null,
            (int) $data['program_id'],
            ($data['admission_date'] ?? '') !== '' ? $data['admission_date'] : null,
            $data['status'],
        ];

        $audit = new AuditService();

        return Database::transaction(function () use ($repository, $audit, $id, $values): int {
            if ($id > 0) {
                if (!$repository->find($id)) {
                    throw new InvalidArgumentException('الطالب غير موجود.');
                }
                $repository->update($id, $values);
                $audit->record('UPDATE', 'student', $id);
                return $id;
            }

            $newId = $repository->create($values);
            $audit->record('CREATE', 'student', $newId);
            return $newId;
        });
    }

    private static function storePhoto(array $file, string $folder, int $recordId): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('تعذر رفع الصورة.');
        }

        if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('حجم الصورة يجب ألا يتجاوز 2MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mime])) {
            throw new InvalidArgumentException('الصورة يجب أن تكون JPG أو PNG أو WEBP.');
        }

        $name = $recordId > 0 ? (string) $recordId : bin2hex(random_bytes(8));
        $relative = 'uploads/' . $folder . '/' . $name . '-' . bin2hex(random_bytes(4)) . '.' . $extensions[$mime];
        $absolute = dirname(__DIR__, 3) . '/public/' . $relative;

        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $absolute)) {
            throw new RuntimeException('تعذر حفظ الصورة على الخادم.');
        }

        return $relative;
    }

    public function delete(int $id): void
    {
        Validator::id($id, 'الطالب');
        $repository = new StudentRepository();
        $student = $repository->find($id);

        if (!$student) {
            throw new InvalidArgumentException('الطالب غير موجود.');
        }

        // Capture files before the database cascade removes their document rows.
        $files = $repository->documentPaths($id);
        if (!empty($student['photo_path'])) {
            $files[] = (string) $student['photo_path'];
        }

        Database::transaction(function () use ($repository, $id): void {
            // Explicit dependency-order deletion keeps this working on upgraded
            // databases even if an old foreign key was created without CASCADE.
            $repository->deleteCascade($id);
            (new AuditService())->record('DELETE', 'student', $id, [
                'cascade' => true,
                'message' => 'حذف الطالب وجميع سجلاته الأكاديمية المرتبطة.',
            ]);
        });

        // Database deletion succeeded; now remove private/uploaded files.
        $root = realpath(dirname(__DIR__, 3));
        if ($root) {
            foreach (array_unique($files) as $relative) {
                $relative = ltrim(str_replace('\\', '/', $relative), '/');
                $absolute = realpath($root . '/' . $relative);
                $publicRoot = realpath($root . '/public');
                if ($absolute && $publicRoot && str_starts_with($absolute, $publicRoot . DIRECTORY_SEPARATOR) && is_file($absolute)) {
                    @unlink($absolute);
                }
            }
        }
    }
}
