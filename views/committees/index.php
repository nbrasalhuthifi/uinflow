<?php $currentPage = 'committees'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">DEFENSE BOARD</span>
<h1>اللجان والمناقشات</h1>
<p>اللجنة أولًا، ثم نتيجة المناقشة؛ بعدها ينتقل الطالب إلى مسار التخرج.</p>
</div>
<button class="btn primary" onclick="openModal('committeeModal')">+ لجنة</button>
</div>
<section class="panel table-panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>الطالب</th>
<th>الرسالة</th>
<th>النوع</th>
<th>الموعد</th>
<th>النتيجة</th>
<th>الإجراء</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td>
<?= e($item['student_name']) ?>
</td>
<td>
<?= e($item['thesis_title']) ?>
</td>
<td>
<?= e($item['committee_type']) ?>
</td>
<td>
<?= e($item['meeting_date']) ?>
</td>
<td>
<span class="status <?= e($item['defense_result'] ?? 'PENDING') ?>">
<?= e(status_label($item['defense_result'] ?? 'PENDING')) ?>
</span>
</td>
<td>
<button class="btn small" onclick="openDefense(<?= (int) $item['thesis_id'] ?>)">نتيجة المناقشة</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<div class="modal" id="committeeModal">
<div class="modal-box">
<div class="modal-head">
<h2>تشكيل لجنة</h2>
<button class="icon-btn" onclick="closeModal('committeeModal')">×</button>
</div>
<form method="post" action="/?page=committees&action=save" class="form-grid">
<?= csrf_field() ?>
<label class="full">الرسالة<select name="thesis_id" required>
<?php foreach ($theses as $thesis): ?>
<option value="<?= (int) $thesis['id'] ?>">
<?= e($thesis['student_no']) ?> — <?= e($thesis['full_name']) ?> — <?= e($thesis['title']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
<label>نوع اللجنة<select name="committee_type">
<option value="PROPOSAL">مقترح</option>
<option value="DEFENSE">مناقشة</option>
</select>
</label>
<label>التاريخ<input name="meeting_date" type="date" required>
</label>
<fieldset class="full">
<legend>أعضاء اللجنة</legend>
<?php foreach ($users as $user): ?>
<div class="member-row"><label class="member-check"><input type="checkbox" name="member_ids[]" value="<?= (int) $user['id'] ?>"> <?= e($user['full_name']) ?> — <?= e(status_label($user['role'])) ?></label><select name="member_roles[<?= (int)$user['id'] ?>]"><option>رئيس اللجنة</option><option>مشرف</option><option>ممتحن داخلي</option><option>ممتحن خارجي</option><option>مقرر</option></select></div>
<?php endforeach; ?>
</fieldset>
<div class="form-actions full">
<button class="btn primary">حفظ اللجنة</button>
</div>
</form>
</div>
</div>
<div class="modal" id="defenseModal">
<div class="modal-box">
<div class="modal-head">
<h2>نتيجة المناقشة</h2>
<button class="icon-btn" onclick="closeModal('defenseModal')">×</button>
</div>
<form method="post" action="/?page=committees&action=defense" class="form-grid">
<?= csrf_field() ?>
<input type="hidden" name="thesis_id" id="defenseThesisId">
<label>تاريخ المناقشة<input name="defense_date" type="date" required>
</label>
<label>المكان<input name="location">
</label>
<label class="full">النتيجة<select name="result">
<option value="PENDING">قيد الانتظار</option>
<option value="PASS">ناجح</option>
<option value="PASS_WITH_CHANGES">ناجح مع تعديلات</option>
<option value="FAIL">راسب</option>
</select>
</label>
<label class="full">ملاحظات<textarea name="notes">
</textarea>
</label>
<div class="form-actions full">
<button class="btn primary">حفظ النتيجة</button>
</div>
</form>
</div>
</div>
<script>function openDefense(id){document.getElementById('defenseThesisId').value=id;openModal('defenseModal');}</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
