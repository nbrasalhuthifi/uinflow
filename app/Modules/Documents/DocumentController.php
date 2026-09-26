<?php
declare(strict_types=1);
final class DocumentController extends Controller
{
    public static function index(): void {
        require_permission('documents.view'); $sid=(int)($_GET['student_id']??0); $db=Database::connection();
        if($sid){$q=$db->prepare('SELECT d.*,u.full_name uploader FROM documents d LEFT JOIN users u ON u.id=d.uploaded_by WHERE d.student_id=? ORDER BY d.id DESC');$q->execute([$sid]);}
        else {$q=$db->query('SELECT d.*,s.student_no,s.full_name,u.full_name uploader FROM documents d LEFT JOIN students s ON s.id=d.student_id LEFT JOIN users u ON u.id=d.uploaded_by ORDER BY d.id DESC LIMIT 200');}
        (new self())->view('documents/index',['items'=>$q->fetchAll(),'studentId'=>$sid]);
    }
    public static function upload(): void {
        require_permission('documents.manage'); post_only(); verify_csrf(); $sid=(int)($_POST['student_id']??0);$title=trim((string)($_POST['title']??''));$type=trim((string)($_POST['document_type']??''));$file=$_FILES['file']??null;
        if(!$sid||$title===''||$type===''||!$file||$file['error']!==UPLOAD_ERR_OK){flash('error','أكمل بيانات المستند واختر ملفًا.');redirect('/?page=documents&student_id='.$sid);}
        if($file['size']>10*1024*1024){flash('error','الحد الأقصى لحجم الملف 10MB.');redirect('/?page=documents&student_id='.$sid);}
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$allowed=['application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx'];if(!isset($allowed[$mime])){flash('error','نوع الملف غير مسموح.');redirect('/?page=documents&student_id='.$sid);}
        $dir=__DIR__.'/../../../storage/uploads/'.date('Y/m');if(!is_dir($dir))mkdir($dir,0750,true);$name=bin2hex(random_bytes(16)).'.'.$allowed[$mime];$path=$dir.'/'.$name;if(!move_uploaded_file($file['tmp_name'],$path)){flash('error','تعذر حفظ الملف.');redirect('/?page=documents&student_id='.$sid);}
        $relative='storage/uploads/'.date('Y/m').'/'.$name;$db=Database::connection();$q=$db->prepare('INSERT INTO documents(student_id,document_type,title,file_path,uploaded_by) VALUES(?,?,?,?,?)');$q->execute([$sid,$type,$title,$relative,(int)current_user()['id']]);audit('UPLOAD','documents',(int)$db->lastInsertId());flash('success','تم رفع المستند بنجاح.');redirect('/?page=documents&student_id='.$sid);
    }
    public static function download(): void {
        require_permission('documents.view');$id=(int)($_GET['id']??0);$db=Database::connection();$q=$db->prepare('SELECT * FROM documents WHERE id=?');$q->execute([$id]);$doc=$q->fetch();if(!$doc){http_response_code(404);exit('المستند غير موجود.');}
        $root=realpath(__DIR__.'/../../../');$uploads=realpath($root.'/storage/uploads');$file=realpath($root.'/'.$doc['file_path']);if(!$file||!$uploads||!str_starts_with($file,$uploads)||!is_file($file)){http_response_code(404);exit('الملف غير موجود.');}$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file);header('Content-Type: '.$mime);header('Content-Disposition: inline; filename="'.rawurlencode($doc['title']).'"');header('Content-Length: '.filesize($file));readfile($file);exit;
    }
}
