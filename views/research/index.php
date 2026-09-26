<?php $currentPage = 'research'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">RESEARCH PIPELINE</span>
<h1>المقترحات البحثية</h1>
<p>لا يمكن إسناد البحث إلا لمشرف مرتبط فعليًا بالطالب.</p>
</div>
<button class="btn primary" onclick="openModal('researchModal')">+ مقترح بحث</button>
</div>
<section class="panel table-panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>الطالب</th>
<th>العنوان</th>
<th>المشرف</th>
<th>الحالة</th>
<th>تحديث</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td>
<?= e($item['student_no']) ?>
<br>
<?= e($item['full_name']) ?>
</td>
<td>
<strong>
<?= e($item['title']) ?>
</strong>
<br>
<small>
<?= e($item['submitted_at']) ?>
</small>
</td>
<td>
<?= e($item['supervisor_name'] ?? 'غير محدد') ?>
</td>
<td>
<span class="status <?= e($item['status']) ?>">
<?= e(status_label($item['status'])) ?>
</span>
</td>
<td>
<form method="post" action="/?page=research&action=status" class="inline-form">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
<select name="status">
<option value="SUBMITTED">مقدم</option>
<option value="UNDER_REVIEW">قيد المراجعة</option>
<option value="APPROVED">معتمد</option>
<option value="REJECTED">مرفوض</option>
</select>
<button class="btn small">حفظ</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<div class="modal" id="researchModal">
<div class="modal-box">
<div class="modal-head">
<h2>مقترح بحثي</h2>
<button class="icon-btn" onclick="closeModal('researchModal')">×</button>
</div>
<form method="post" action="/?page=research&action=save" class="form-grid">
<?= csrf_field() ?>
<div class="full">
<?php $lookupEntity='students';$lookupName='student_id';$lookupLabel='الطالب';$lookupField='student_id';require __DIR__ . '/../components/lookup.php'; ?>
</div>
<div class="full">
<?php $lookupEntity='supervisors';$lookupName='supervisor_id';$lookupLabel='المشرف';$lookupField='supervisor_id';require __DIR__ . '/../components/lookup.php'; ?>
</div>
<label class="full">العنوان<input name="title" required>
</label>
<label class="full">الملخص<textarea name="abstract">
</textarea>
</label>
<div class="form-actions full">
<button class="btn primary">تسجيل المقترح</button>
</div>
</form>
</div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
