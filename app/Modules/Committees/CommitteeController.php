<?php
declare(strict_types=1);
final class CommitteeController extends Controller
{
    public static function index(): void { require_auth();$r=new CommitteeRepository();(new self())->view('committees/index',['items'=>$r->all(),'theses'=>$r->theses(),'users'=>$r->users()]); }
    public static function save(): void { require_role(['ADMIN','STAFF']);verify_csrf();try{(new CommitteeService())->create($_POST);flash('success','تم إنشاء اللجنة وأعضائها.');}catch(Throwable $e){flash('error',$e->getMessage());}redirect('/?page=committees'); }
    public static function defense(): void { require_role(['ADMIN','STAFF','COMMITTEE']);verify_csrf();try{$tid=(int)($_POST['thesis_id']??0);if((current_user()['role']??'')==='COMMITTEE'&&!CommitteeService::userCanActOnThesis($tid,(int)current_user()['id']))throw new RuntimeException('هذه الرسالة ليست ضمن لجانك.');(new CommitteeService())->defense($_POST);flash('success','تم حفظ نتيجة المناقشة وتحديث حالة الرسالة.');}catch(Throwable $e){flash('error',$e->getMessage());}redirect('/?page=committees'); }
}
