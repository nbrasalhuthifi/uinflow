<?php
declare(strict_types=1);
final class ReportController extends Controller
{
    public static function index(): void { require_permission('reports.view'); $filters=['program_id'=>(int)($_GET['program_id']??0),'status'=>trim((string)($_GET['status']??''))];$repo=new ReportRepository();(new self())->view('reports/index',['report'=>$repo->overview($filters),'programs'=>(new ProgramRepository())->all(),'filters'=>$filters]); }
    public static function export(): void { require_permission('reports.view'); $filters=['program_id'=>(int)($_GET['program_id']??0),'status'=>trim((string)($_GET['status']??''))];$rows=(new ReportRepository())->studentsReport($filters);header('Content-Type:text/csv; charset=utf-8');header('Content-Disposition:attachment; filename="uniflow-students-'.date('Ymd-His').'.csv"');$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,['رقم الطالب','الاسم','البرنامج','الحالة','الساعات المكتسبة','المعدل']);foreach($rows as $r)fputcsv($out,[$r['student_no'],$r['full_name'],$r['program_name'],status_label($r['status']),$r['earned_credits'],$r['gpa']]);fclose($out);exit; }
}
