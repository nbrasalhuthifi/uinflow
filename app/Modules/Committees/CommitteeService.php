<?php
declare(strict_types=1);

final class CommitteeService
{
    public static function userCanActOnThesis(int $thesisId, int $userId): bool
    {
        $q=Database::connection()->prepare('SELECT 1 FROM committee_members cm JOIN committees c ON c.id=cm.committee_id WHERE c.thesis_id=? AND cm.user_id=? LIMIT 1');
        $q->execute([$thesisId,$userId]); return (bool)$q->fetchColumn();
    }

    public function create(array $data): int
    {
        Validator::required($data, [
            'thesis_id' => 'الرسالة',
            'committee_type' => 'نوع اللجنة',
            'meeting_date' => 'تاريخ الاجتماع',
        ]);

        $members = array_values(array_filter(array_map('intval',(array)($data['member_ids']??[])),static fn(int $id):bool=>$id>0));
        $memberRoles=(array)($data['member_roles']??[]);

        if (count($members) < 1) {
            throw new InvalidArgumentException('يجب إضافة عضو واحد على الأقل للجنة.');
        }

        $type = (string) $data['committee_type'];
        if (!in_array($type, ['PROPOSAL', 'DEFENSE'], true)) {
            throw new InvalidArgumentException('نوع اللجنة غير صالح.');
        }

        $repository = new CommitteeRepository();
        $thesis = $repository->thesis((int) $data['thesis_id']);

        if (!$thesis) {
            throw new InvalidArgumentException('الرسالة غير موجودة.');
        }
        if ($type === 'DEFENSE' && $thesis['status'] !== 'READY_FOR_DEFENSE') {
            throw new InvalidArgumentException('لا يمكن إنشاء لجنة مناقشة قبل أن تصبح الرسالة جاهزة للمناقشة.');
        }
        if ($repository->duplicateType((int) $data['thesis_id'], $type)) {
            throw new InvalidArgumentException('توجد لجنة من هذا النوع للرسالة مسبقًا.');
        }

        return Database::transaction(function () use ($repository, $data, $members, $memberRoles): int {
            $committeeId = $repository->create(
                (int) $data['thesis_id'],
                (string) $data['committee_type'],
                (string) $data['meeting_date']
            );

            foreach (array_unique($members) as $memberId) {
                $repository->addMember($committeeId,$memberId,trim((string)($memberRoles[$memberId]??'عضو لجنة')));
            }

            if ($repository->memberCount($committeeId) < 1) {
                throw new RuntimeException('تعذر حفظ أعضاء اللجنة.');
            }

            (new AuditService())->record('CREATE', 'committee', $committeeId);
            return $committeeId;
        });
    }

    public function defense(array $data): void
    {
        Validator::required($data, [
            'thesis_id' => 'الرسالة',
            'defense_date' => 'تاريخ المناقشة',
            'result' => 'النتيجة',
        ]);

        $result = (string) $data['result'];
        if (!in_array($result, ['PENDING', 'PASS', 'PASS_WITH_CHANGES', 'FAIL'], true)) {
            throw new InvalidArgumentException('نتيجة المناقشة غير صالحة.');
        }

        $repository = new CommitteeRepository();
        $thesis = $repository->thesis((int) $data['thesis_id']);
        if (!$thesis) {
            throw new InvalidArgumentException('الرسالة غير موجودة.');
        }

        $defenseCommitteeExists = $repository->duplicateType(
            (int) $data['thesis_id'],
            'DEFENSE'
        );
        if (!$defenseCommitteeExists) {
            throw new InvalidArgumentException('يجب إنشاء لجنة مناقشة أولًا.');
        }

        Database::transaction(function () use ($repository, $data, $result): void {
            $repository->saveDefense([
                (int) $data['thesis_id'],
                $data['defense_date'],
                trim((string) ($data['location'] ?? '')),
                $result,
                trim((string) ($data['notes'] ?? '')),
            ]);

            $thesisStatus = $result === 'FAIL'
                ? 'ACTIVE'
                : ($result === 'PENDING' ? 'READY_FOR_DEFENSE' : 'DEFENDED');

            $this->setThesisStatus(
                (int) $data['thesis_id'],
                $thesisStatus
            );

            (new AuditService())->record(
                'DEFENSE_RESULT',
                'thesis',
                (int) $data['thesis_id']
            );
        });
    }

    private function setThesisStatus(int $thesisId, string $status): void
    {
        Database::connection()->prepare(
            'UPDATE theses SET status = ? WHERE id = ?'
        )->execute([$status, $thesisId]);
    }
}
