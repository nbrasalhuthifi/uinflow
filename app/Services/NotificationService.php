<?php
declare(strict_types=1);
final class NotificationService
{
    public function queueForStudent(int $studentId,string $channel,string $subject,string $message): void {
        Validator::id($studentId,'الطالب');if(!in_array($channel,['IN_APP','EMAIL','WHATSAPP'],true))throw new InvalidArgumentException('قناة الإشعار غير صالحة.');$db=Database::connection();$q=$db->prepare("SELECT u.id FROM users u JOIN supervisors sv ON sv.user_id=u.id JOIN student_supervisors ss ON ss.supervisor_id=sv.id WHERE ss.student_id=? AND u.is_active=1");$q->execute([$studentId]);$users=$q->fetchAll(PDO::FETCH_COLUMN);$q=$db->prepare('SELECT email,phone FROM students WHERE id=?');$q->execute([$studentId]);$student=$q->fetch()?:[];
        if($channel==='IN_APP'&&!$users){$users=[null];}
        foreach($users as $uid){$st=$db->prepare('INSERT INTO notifications(user_id,student_id,channel,subject,message,status) VALUES(?,?,?,?,?,?)');$st->execute([$uid?:null,$studentId,$channel,$subject,$message,$channel==='IN_APP'?'SENT':'QUEUED']);if($channel==='EMAIL'&&!empty($student['email']))$this->sendEmail($student['email'],$subject,$message,(int)$db->lastInsertId());if($channel==='WHATSAPP'&&!empty($student['phone']))$this->sendWhatsApp($student['phone'],$message,(int)$db->lastInsertId());}
    }
    private function sendEmail(string $to,string $subject,string $message,int $id): void { $from=(string)envv('MAIL_FROM','no-reply@localhost');$headers='From: '.$from."\r\nContent-Type: text/plain; charset=UTF-8\r\n";$ok=function_exists('mail')&&@mail($to,$subject,$message,$headers);Database::connection()->prepare('UPDATE notifications SET status=?,sent_at=IF(?,NOW(),sent_at) WHERE id=?')->execute([$ok?'SENT':'FAILED',$ok,$id]); }
    private function sendWhatsApp(string $phone,string $message,int $id): void { $webhook=(string)envv('WHATSAPP_WEBHOOK','');if($webhook==='')return; $ch=curl_init($webhook);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode(['phone'=>$phone,'message'=>$message],JSON_UNESCAPED_UNICODE),CURLOPT_TIMEOUT=>8]);$ok=curl_exec($ch)!==false&&curl_getinfo($ch,CURLINFO_HTTP_CODE)<400;curl_close($ch);Database::connection()->prepare('UPDATE notifications SET status=?,sent_at=IF(?,NOW(),sent_at) WHERE id=?')->execute([$ok?'SENT':'FAILED',$ok,$id]); }
    public function contactLinks(array $student): array { return contact_links($student['phone']??'',$student['email']??'',$student['full_name']??''); }
}
