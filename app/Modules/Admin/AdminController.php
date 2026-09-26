<?php
declare(strict_types=1);

final class AdminController extends Controller
{
    public static function users(): void
    {
        require_permission('users.view');
        $db = Database::connection();
        $users = $db->query("SELECT id, full_name, email, role, is_active, created_at, last_login_at FROM users ORDER BY id DESC")->fetchAll();
        $permissions = $db->query("SELECT permission FROM role_permissions GROUP BY permission ORDER BY permission")->fetchAll(PDO::FETCH_COLUMN);
        $custom = $db->query("SELECT up.*, u.full_name FROM user_permissions up JOIN users u ON u.id=up.user_id ORDER BY u.full_name, up.permission")->fetchAll();
        $rolePermissions=[]; foreach($db->query('SELECT role,permission FROM role_permissions ORDER BY role,permission')->fetchAll() as $rp){$rolePermissions[$rp['role']][]=$rp['permission'];}
        (new self())->view('admin/users', compact('users','permissions','custom','rolePermissions'));
    }

    public static function saveUser(): void
    {
        require_permission('users.manage'); post_only(); verify_csrf();
        $id = (int)($_POST['id'] ?? 0); $name = trim((string)($_POST['full_name'] ?? '')); $email = strtolower(trim((string)($_POST['email'] ?? ''))); $role=(string)($_POST['role']??'STAFF'); $active=(int)($_POST['is_active']??1);
        if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || !in_array($role,['ADMIN','STAFF','SUPERVISOR','COMMITTEE'],true)) { flash('error','بيانات المستخدم غير صالحة.'); redirect('/?page=admin-users'); }
        try {
            $db=Database::connection();
            if ($id>0) {
                $q=$db->prepare('UPDATE users SET full_name=?, email=?, role=?, is_active=? WHERE id=?'); $q->execute([$name,$email,$role,$active,$id]);
                if (($password=trim((string)($_POST['password']??'')))!=='') { $db->prepare('UPDATE users SET password=?, password_changed_at=NOW() WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),$id]); }
                audit('UPDATE','users',$id);
            } else {
                $password=(string)($_POST['password']??''); if(strlen($password)<8) throw new InvalidArgumentException('كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.');
                $q=$db->prepare('INSERT INTO users(full_name,email,password,role,is_active,password_changed_at) VALUES(?,?,?,?,?,NOW())'); $q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role,$active]); audit('CREATE','users',(int)$db->lastInsertId());
            }
            flash('success','تم حفظ المستخدم.');
        } catch(Throwable $e) { flash('error',$e instanceof PDOException && $e->getCode()==='23000'?'البريد الإلكتروني مستخدم بالفعل.':$e->getMessage()); }
        redirect('/?page=admin-users');
    }


    public static function rolePermissions(): void
    {
        require_permission('permissions.manage'); post_only(); verify_csrf(); $role=(string)($_POST['role']??''); $selected=array_values(array_filter((array)($_POST['permissions']??[]),fn($x)=>is_string($x)&&$x!==''));
        if(!in_array($role,['ADMIN','STAFF','SUPERVISOR','COMMITTEE'],true)) { flash('error','الدور غير صالح.'); redirect('/?page=admin-users'); }
        $db=Database::connection(); Database::transaction(function() use($db,$role,$selected){$db->prepare('DELETE FROM role_permissions WHERE role=?')->execute([$role]);$st=$db->prepare('INSERT INTO role_permissions(role,permission) VALUES(?,?)');foreach($selected as $perm)$st->execute([$role,$perm]);});audit('UPDATE','role_permissions',null,['role'=>$role]);flash('success','تم تحديث صلاحيات الدور.');redirect('/?page=admin-users');
    }

    public static function permissions(): void
    {
        require_permission('permissions.manage'); post_only(); verify_csrf();
        $userId=(int)($_POST['user_id']??0); $permission=trim((string)($_POST['permission']??''));
        if($userId<1 || $permission==='') { flash('error','بيانات الصلاحية غير صالحة.'); redirect('/?page=admin-users'); }
        $db=Database::connection(); $db->prepare('INSERT IGNORE INTO user_permissions(user_id,permission) VALUES(?,?)')->execute([$userId,$permission]); audit('GRANT','user_permissions',$userId,['permission'=>$permission]); flash('success','تم منح الصلاحية للمستخدم.'); redirect('/?page=admin-users');
    }

    public static function revokePermission(): void
    {
        require_permission('permissions.manage'); post_only(); verify_csrf(); $userId=(int)($_POST['user_id']??0); $permission=trim((string)($_POST['permission']??''));
        Database::connection()->prepare('DELETE FROM user_permissions WHERE user_id=? AND permission=?')->execute([$userId,$permission]); audit('REVOKE','user_permissions',$userId,['permission'=>$permission]); flash('success','تم سحب الصلاحية.'); redirect('/?page=admin-users');
    }

    public static function academic(): void
    {
        require_permission('academic.manage');
        $db=Database::connection();
        $years=$db->query('SELECT * FROM academic_years ORDER BY name DESC')->fetchAll();
        $semesters=$db->query('SELECT s.*, a.name year_name FROM semesters s JOIN academic_years a ON a.id=s.academic_year_id ORDER BY a.name DESC, s.id DESC')->fetchAll();
        (new self())->view('academic/index',compact('years','semesters'));
    }
    public static function saveYear(): void
    {
        require_permission('academic.manage'); post_only(); verify_csrf(); $id=(int)($_POST['id']??0); $name=trim((string)($_POST['name']??'')); $current=(int)($_POST['is_current']??0);
        if(!preg_match('/^\d{4}\/\d{4}$/',$name)) { flash('error','صيغة العام الأكاديمي يجب أن تكون 2026/2027.'); redirect('/?page=academic'); }
        $db=Database::connection(); Database::transaction(function() use($db,$id,$name,$current){ if($current) $db->exec('UPDATE academic_years SET is_current=0'); if($id) $db->prepare('UPDATE academic_years SET name=?,is_current=? WHERE id=?')->execute([$name,$current,$id]); else $db->prepare('INSERT INTO academic_years(name,is_current) VALUES(?,?)')->execute([$name,$current]); }); audit($id?'UPDATE':'CREATE','academic_years',$id?:null); flash('success','تم حفظ العام الأكاديمي.'); redirect('/?page=academic');
    }
    public static function saveSemester(): void
    {
        require_permission('academic.manage'); post_only(); verify_csrf(); $id=(int)($_POST['id']??0); $year=(int)($_POST['academic_year_id']??0); $name=trim((string)($_POST['name']??'')); $start=$_POST['starts_on']??null; $end=$_POST['ends_on']??null; $current=(int)($_POST['is_current']??0);
        if(!$year||$name==='') { flash('error','بيانات الفصل ناقصة.'); redirect('/?page=academic'); }
        $db=Database::connection(); Database::transaction(function() use($db,$id,$year,$name,$start,$end,$current){ if($current) $db->exec('UPDATE semesters SET is_current=0'); if($id) $db->prepare('UPDATE semesters SET academic_year_id=?,name=?,starts_on=?,ends_on=?,is_current=? WHERE id=?')->execute([$year,$name,$start?:null,$end?:null,$current,$id]); else $db->prepare('INSERT INTO semesters(academic_year_id,name,starts_on,ends_on,is_current) VALUES(?,?,?,?,?)')->execute([$year,$name,$start?:null,$end?:null,$current]); }); audit($id?'UPDATE':'CREATE','semesters',$id?:null); flash('success','تم حفظ الفصل الدراسي.'); redirect('/?page=academic');
    }

    public static function notifications(): void
    {
        require_auth(); $db=Database::connection(); $uid=(int)current_user()['id']; $items=$db->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 100'); $items->execute([$uid]); (new self())->view('admin/notifications',['items'=>$items->fetchAll()]);
    }
    public static function readNotification(): void
    {
        require_auth(); post_only(); verify_csrf(); $id=(int)($_POST['id']??0); $db=Database::connection(); $db->prepare('UPDATE notifications SET read_at=NOW(),status=CASE WHEN status=\'QUEUED\' THEN \'SENT\' ELSE status END,sent_at=COALESCE(sent_at,NOW()) WHERE id=? AND user_id=?')->execute([$id,(int)current_user()['id']]); redirect('/?page=notifications');
    }

    public static function audit(): void
    {
        require_permission('audit.view'); $q=trim((string)($_GET['q']??'')); $db=Database::connection(); $sql='SELECT a.*,u.full_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id'; $params=[]; if($q!==''){ $sql.=' WHERE a.action LIKE ? OR a.entity LIKE ? OR a.details LIKE ? OR u.full_name LIKE ?'; $x='%'.$q.'%'; $params=[$x,$x,$x,$x]; } $sql.=' ORDER BY a.id DESC LIMIT 200'; $st=$db->prepare($sql);$st->execute($params); (new self())->view('admin/audit',['items'=>$st->fetchAll(),'query'=>$q]);
    }

    public static function search(): void
    {
        require_auth(); $q=trim((string)($_GET['q']??'')); $items=[]; if($q!==''){ $like='%'.$q.'%'; $db=Database::connection(); $st=$db->prepare("SELECT id,student_no code,full_name label,'طالب' type FROM students WHERE student_no LIKE ? OR full_name LIKE ? UNION ALL SELECT id,code,name,'مقرر' FROM courses WHERE code LIKE ? OR name LIKE ? UNION ALL SELECT id,application_no,full_name,'متقدم' FROM applicants WHERE application_no LIKE ? OR full_name LIKE ? ORDER BY label LIMIT 30"); $st->execute([$like,$like,$like,$like,$like,$like]); $items=$st->fetchAll(); } (new self())->view('search/index',compact('items','q'));
    }


    public static function assignSupervisor(): void
    {
        require_permission('students.manage'); post_only(); verify_csrf(); $student=(int)($_POST['student_id']??0);$supervisor=(int)($_POST['supervisor_id']??0);$role=(string)($_POST['supervisor_role']??'PRIMARY');
        if(!$student||!$supervisor||!in_array($role,['PRIMARY','CO_SUPERVISOR'],true)){flash('error','بيانات الإسناد غير صالحة.');redirect('/?page=profile&id='.$student);} $db=Database::connection();
        if($role==='PRIMARY'){$q=$db->prepare("SELECT 1 FROM student_supervisors WHERE student_id=? AND supervisor_role='PRIMARY' LIMIT 1");$q->execute([$student]);if($q->fetchColumn())throw new RuntimeException('للطالب مشرف رئيسي بالفعل. أزل الإسناد الحالي أولًا.');}
        $q=$db->prepare('INSERT INTO student_supervisors(student_id,supervisor_id,supervisor_role,assigned_at) VALUES(?,?,?,CURDATE())');try{$q->execute([$student,$supervisor,$role]);audit('ASSIGN','student_supervisor',$student,['supervisor_id'=>$supervisor,'role'=>$role]);flash('success','تم إسناد المشرف.');}catch(Throwable $e){flash('error','تعذر إسناد المشرف: '.$e->getMessage());}redirect('/?page=profile&id='.$student);
    }
    public static function removeSupervisor(): void
    { require_permission('students.manage'); post_only(); verify_csrf();$id=(int)($_POST['id']??0);$student=(int)($_POST['student_id']??0);Database::connection()->prepare('DELETE FROM student_supervisors WHERE id=?')->execute([$id]);audit('UNASSIGN','student_supervisor',$id);flash('success','تم إلغاء إسناد المشرف.');redirect('/?page=profile&id='.$student); }

    public static function profile(): void
    {
        require_auth(); $id=(int)($_GET['id']??0); $db=Database::connection(); $st=$db->prepare("SELECT s.*,p.name program_name,p.degree,p.required_credits,d.name department_name FROM students s JOIN programs p ON p.id=s.program_id JOIN departments d ON d.id=p.department_id WHERE s.id=?"); $st->execute([$id]); $student=$st->fetch(); if(!$student){http_response_code(404);exit('الطالب غير موجود.');}
        $st=$db->prepare("SELECT c.*,sc.grade,sc.status,sc.registered_at FROM student_courses sc JOIN courses c ON c.id=sc.course_id WHERE sc.student_id=? ORDER BY sc.id DESC");$st->execute([$id]);$courses=$st->fetchAll();
        $st=$db->prepare("SELECT ss.*,u.full_name supervisor_name FROM student_supervisors ss JOIN supervisors sv ON sv.id=ss.supervisor_id JOIN users u ON u.id=sv.user_id WHERE ss.student_id=?");$st->execute([$id]);$supervisors=$st->fetchAll();$availableSupervisors=$db->query("SELECT sv.id,u.full_name,sv.specialization FROM supervisors sv JOIN users u ON u.id=sv.user_id WHERE u.is_active=1 ORDER BY u.full_name")->fetchAll();
        $st=$db->prepare("SELECT * FROM research_proposals WHERE student_id=? ORDER BY id DESC");$st->execute([$id]);$research=$st->fetchAll();
        $st=$db->prepare("SELECT * FROM theses WHERE student_id=? ORDER BY id DESC");$st->execute([$id]);$theses=$st->fetchAll();
        (new self())->view('students/profile',compact('student','courses','supervisors','availableSupervisors','research','theses'));
    }
}
