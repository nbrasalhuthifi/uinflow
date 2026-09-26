<?php
declare(strict_types=1);
final class EnrollmentService
{
    public function create(array $data): int {
        Validator::required($data,['plan_id'=>'الخطة الدراسية','course_id'=>'المقرر']); $planId=(int)$data['plan_id'];$courseId=(int)$data['course_id'];$repo=new EnrollmentRepository();$plan=$repo->findPlan($planId);
        if(!$plan) throw new InvalidArgumentException('الخطة الدراسية غير موجودة.'); if(!in_array($plan['status'],['APPROVED','IN_PROGRESS'],true)) throw new InvalidArgumentException('لا يمكن تسجيل مقرر في خطة غير معتمدة أو قيد التنفيذ.'); if($plan['student_status']!=='ACTIVE') throw new InvalidArgumentException('لا يمكن تسجيل مقرر لطالب غير نشط.'); if(!$repo->courseForProgram($courseId,(int)$plan['program_id'])) throw new InvalidArgumentException('المقرر لا يتبع برنامج الطالب.'); if($repo->duplicate($planId,$courseId)) throw new InvalidArgumentException('المقرر مسجل مسبقًا في هذه الخطة.');
        $id=$repo->create((int)$plan['student_id'],$courseId,$planId,(int)($data['semester_id']??0)?:null); audit('CREATE','student_course',$id); return $id;
    }
    public function update(int $id, ?float $grade, string $status): void { if(!in_array($status,['REGISTERED','PASSED','FAILED'],true)) throw new InvalidArgumentException('حالة المقرر غير صالحة.'); if($grade!==null&&($grade<0||$grade>100)) throw new InvalidArgumentException('الدرجة يجب أن تكون بين 0 و100.'); $repo=new EnrollmentRepository();$row=$repo->find($id);if(!$row)throw new InvalidArgumentException('التسجيل غير موجود.');if($status==='PASSED'&&($grade===null||$grade<50))throw new InvalidArgumentException('لا يمكن اعتبار المقرر ناجحًا دون درجة نجاح.');if($status==='FAILED'&&$grade!==null&&$grade>=50)throw new InvalidArgumentException('درجة 50 فأعلى لا تتوافق مع حالة راسب.');$repo->update($id,$grade,$status);audit('UPDATE','student_course',$id,['grade'=>$grade,'status'=>$status]); }
    public function delete(int $id): void { $repo=new EnrollmentRepository();if(!$repo->find($id))throw new InvalidArgumentException('التسجيل غير موجود.');$repo->delete($id);audit('DELETE','student_course',$id); }
}
