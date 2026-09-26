<?php $currentPage = 'plans'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">ACADEMIC PLANNING</span>
<h1>الخطط الدراسية</h1>
<p>خطة سنوية مستقلة لكل طالب مع حالات واضحة قبل التسجيل.</p>
</div>
<button class="btn primary" onclick="openModal('planModal')">+ إنشاء خطة</button>
</div>
<section class="panel table-panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>الطالب</th>
<th>البرنامج</th>
<th>السنة</th>
<th>الحالة</th>
<th>إدارة الحالة</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td>
<strong>
<?= e($item['student_no']) ?>
</strong>
<br>
<?= e($item['full_name']) ?>
</td>
<td>
<?= e($item['program_name']) ?>
</td>
<td>
<?= e($item['year_name']) ?>
</td>
<td>
<span class="status <?= e($item['status']) ?>">
<?= e(status_label($item['status'])) ?>
</span>
</td>
<td>
<form method="post" action="/?page=plans&action=status" class="inline-form">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
<select name="status">
<option value="DRAFT">مسودة</option>
<option value="APPROVED">معتمدة</option>
<option value="IN_PROGRESS">قيد التنفيذ</option>
<option value="COMPLETED">مكتملة</option>
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
<div class="modal" id="planModal">
<div class="modal-box">
<div class="modal-head">
<h2>إنشاء خطة دراسية</h2>
<button class="icon-btn" onclick="closeModal('planModal')">×</button>
</div>
<form method="post" action="/?page=plans&action=save" class="form-grid">
<?= csrf_field() ?>
<div class="full">
<?php $lookupEntity='students';$lookupName='student_id';$lookupLabel='الطالب';$lookupField='student_id';require __DIR__ . '/../components/lookup.php'; ?>
</div>
<label class="full">السنة الأكاديمية<select name="academic_year_id" required>
<?php foreach ($years as $year): ?>
<option value="<?= (int) $year['id'] ?>">
<?= e($year['name']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
<div class="form-actions full">
<button class="btn primary">إنشاء الخطة</button>
</div>
</form>
</div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
