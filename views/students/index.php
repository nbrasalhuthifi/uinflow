<?php
$currentPage = 'students';
require __DIR__ . '/../layouts/header.php';
$items = $result['items'];
?>
<div class="page-head">
    <div>
<span class="eyebrow">ACADEMIC RECORDS</span>
<h1>الطلاب</h1>
<p>ملفات الطلبة مع بحث سريع يعمل على مستوى قاعدة البيانات.</p>
</div>
    <button class="btn primary" type="button" onclick="openModal('studentModal')">+ طالب جديد</button>
</div>

<form class="search-bar" method="get" action="/">
    <input type="hidden" name="page" value="students">
    <span>⌕</span>
    <input name="q" value="<?= e($query) ?>" placeholder="ابحث بالاسم أو الرقم الجامعي أو البريد أو الهاتف...">
    <button class="btn primary" type="submit">بحث</button>
    <?php if ($query !== ''): ?>
<a class="btn" href="/?page=students">مسح</a>
<?php endif; ?>
</form>

<section class="panel table-panel">
    <div class="panel-head">
<div>
<h2>سجل الطلبة</h2>
<small>
<?= (int) $result['total'] ?> ملف</small>
</div>
<span class="tag">Server-side search</span>
</div>
    <div class="table-wrap">
        <table>
            <thead>
<tr>
<th>الرقم</th>
<th>الطالب</th>
<th>البرنامج</th>
<th>التواصل</th>
<th>الحالة</th>
<th>إجراء</th>
</tr>
</thead>
            <tbody>
            <?php foreach ($items as $student): $links = contact_links($student['phone'], $student['email'], $student['full_name']); ?>
                <tr>
                    <td>
<strong>
<?= e($student['student_no']) ?>
</strong>
</td>
                    <td>
<div class="person">
<div class="avatar sm">
<?php if (!empty($student['photo_path'])): ?>
<img src="/<?= e($student['photo_path']) ?>" alt="">
<?php else: ?>
<?= e(initial_letter($student['full_name'])) ?>
<?php endif; ?>
</div>
<div>
<strong>
<?= e($student['full_name']) ?>
</strong>
<small>
<?= e($student['email']) ?>
</small>
</div>
</div>
</td>
                    <td>
<span class="subtle">
<?= e($student['program_name']) ?>
</span>
</td>
                    <td class="contact-actions">
                        <?php if ($links['whatsapp']): ?>
<a href="<?= e($links['whatsapp']) ?>" target="_blank" rel="noopener" title="WhatsApp">WA</a>
<?php endif; ?>
                        <?php if ($links['email']): ?>
<a href="<?= e($links['email']) ?>" title="Email">@</a>
<?php endif; ?>
                    </td>
                    <td>
<span class="status <?= e($student['status']) ?>">
<?= e(status_label($student['status'])) ?>
</span>
</td>
                    <td>
                        <div class="contact-actions">
                            <button class="btn small" type="button" onclick='editStudent(<?= json_encode($student, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>تعديل</button>
                            <?php if ((current_user()['role'] ?? '') === 'ADMIN'): ?>
                            <form method="post" action="/?page=students&action=delete" class="inline-form" onsubmit="return confirm('سيتم حذف الطالب وجميع سجلاته الأكاديمية والوثائق المرتبطة به نهائيًا. هذا الإجراء لا يمكن التراجع عنه. هل تريد المتابعة؟');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                                <button class="btn small danger" type="submit">حذف نهائي</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?>
<tr>
<td colspan="6">
<div class="empty">لا توجد نتائج مطابقة.</div>
</td>
</tr>
<?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php if ($result['pages'] > 1): ?>
<nav class="pagination">
    <?php if ($result['page'] > 1): ?>
<a class="btn" href="/?page=students&q=<?= urlencode($query) ?>&p=<?= $result['page'] - 1 ?>">السابق</a>
<?php endif; ?>
    <span>صفحة <?= (int) $result['page'] ?> من <?= (int) $result['pages'] ?>
</span>
    <?php if ($result['page'] < $result['pages']): ?>
<a class="btn" href="/?page=students&q=<?= urlencode($query) ?>&p=<?= $result['page'] + 1 ?>">التالي</a>
<?php endif; ?>
</nav>
<?php endif; ?>

<div class="modal" id="studentModal">
    <div class="modal-box">
        <div class="modal-head">
<div>
<span class="eyebrow">STUDENT PROFILE</span>
<h2>ملف الطالب</h2>
</div>
<button class="icon-btn" type="button" onclick="closeModal('studentModal')">×</button>
</div>
        <form method="post" action="/?page=students&action=save" class="form-grid" id="studentForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
<input type="hidden" name="id">
            <label>الرقم الجامعي<input name="student_no" required>
</label>
            <label>الاسم الكامل<input name="full_name" required>
</label>
            <label>الرقم الوطني<input name="national_id">
</label>
            <label>الهاتف<input name="phone">
</label>
            <label>البريد<input name="email" type="email">
</label>
            <label>صورة الطالب <span class="optional">اختياري</span><input name="photo" type="file" accept="image/jpeg,image/png,image/webp">
</label>
            <input type="hidden" name="existing_photo_path">
            <label>تاريخ القبول<input name="admission_date" type="date">
</label>
            <label class="full">البرنامج<select name="program_id" required>
<?php foreach ($programs as $program): ?>
<option value="<?= (int) $program['id'] ?>">
<?= e($program['name']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
            <label>الحالة<select name="status">
<option value="ACTIVE">نشط</option>
<option value="SUSPENDED">موقوف</option>
<option value="GRADUATED">متخرج</option>
</select>
</label>
            <div class="form-actions full">
<button class="btn primary" type="submit">حفظ الطالب</button>
<button class="btn" type="button" onclick="closeModal('studentModal')">إلغاء</button>
</div>
        </form>
    </div>
</div>
<script>
function editStudent(data) {
    fillForm('studentForm', data);
    openModal('studentModal');
}
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
