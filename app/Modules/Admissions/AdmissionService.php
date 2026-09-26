<?php
declare(strict_types=1);

final class AdmissionService
{
    public function create(array $data): int
    {
        Validator::required($data, [
            'application_no' => 'رقم الطلب',
            'full_name' => 'اسم المتقدم',
            'program_id' => 'البرنامج',
        ]);
        Validator::email(trim((string) ($data['email'] ?? '')));

        $repository = new AdmissionRepository();

        return Database::transaction(function () use ($repository, $data): int {
            $applicantId = $repository->createApplicant([
                trim($data['application_no']),
                trim($data['full_name']),
                trim((string) ($data['phone'] ?? '')),
                trim((string) ($data['email'] ?? '')),
                trim((string) ($data['national_id'] ?? '')),
                trim((string) ($data['previous_degree'] ?? '')),
                ($data['previous_gpa'] ?? '') === '' ? null : (float) $data['previous_gpa'],
            ]);

            $applicationId = $repository->createApplication([
                $applicantId,
                (int) $data['program_id'],
                $data['application_type'] ?? 'NEW',
                'PENDING',
                trim((string) ($data['notes'] ?? '')),
            ]);

            (new AuditService())->record('CREATE', 'application', $applicationId);
            return $applicationId;
        });
    }


    public function enroll(int $applicationId): int
    {
        $r=new AdmissionRepository();$a=$r->find($applicationId);if(!$a)throw new InvalidArgumentException('طلب القبول غير موجود.');if($a['status']!=='APPROVED')throw new InvalidArgumentException('يجب اعتماد طلب القبول أولًا.');
        $db=Database::connection();$q=$db->prepare("SELECT id FROM students WHERE (national_id<>'' AND national_id=?) OR (email<>'' AND email=?) LIMIT 1");$q->execute([$a['national_id'],$a['email']]);if($q->fetchColumn())throw new InvalidArgumentException('المتقدم مسجل كطالب بالفعل.');
        $year=date('Y');$studentNo='PG-'.$year.'-'.str_pad((string)((int)$db->query("SELECT COUNT(*) FROM students WHERE student_no LIKE 'PG-$year-%'")->fetchColumn()+1),4,'0',STR_PAD_LEFT);$id=$r->createStudent([$studentNo,$a['full_name'],$a['national_id'],$a['phone'],$a['email'],$a['program_id']]);audit('ENROLL','application',$applicationId,['student_id'=>$id,'student_no'=>$studentNo]);return $id;
    }

    public function status(int $id, string $status): void
    {
        if (!in_array($status, ['PENDING', 'APPROVED', 'REJECTED'], true)) {
            throw new InvalidArgumentException('حالة الطلب غير صالحة.');
        }

        $repository = new AdmissionRepository();
        $application = $repository->find($id);

        if (!$application) {
            throw new InvalidArgumentException('طلب القبول غير موجود.');
        }

        $repository->setStatus($id, $status);
        (new AuditService())->record('STATUS', 'application', $id);
    }
}
