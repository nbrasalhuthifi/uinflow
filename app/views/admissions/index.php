<?php $currentPage = 'admissions'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">ADMISSIONS PIPELINE</span>
<h1>القبول والدراسات العليا</h1>
<p>ابدأ بملف متقدم ثم تابع الطلب حتى القرار.</p>
</div>
<button class="btn primary" onclick="openModal('admissionModal')">+ طلب قبول</button>
</div>
<section class="panel table-panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>رقم الطلب</th>
<th>المتقدم</th>
<th>البرنامج</th>
<th>التواصل</th>
<th>الحالة</th>
<th>تحديث</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td>
<strong>
<?= e($item['application_no']) ?>
</strong>
</td>
<td>
<?= e($item['applicant_name']) ?>
</td>
<td>
<?= e($item['program_name']) ?>
</td>
<td>
<?= e($item['phone']) ?>
<br>
<small>
<?= e($item['email']) ?>
</small>
</td>
<td>
<span class="status <?= e($item['status']) ?>">
<?= e(status_label($item['status'])) ?>
</span>
</td>
<td>
<form method="post" action="/?page=admissions&action=status" class="inline-form">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
<select name="status">
<option value="PENDING">قيد الانتظار</option>
<option value="APPROVED">مقبول</option>
<option value="REJECTED">مرفوض</option>
</select>
<button class="btn small">حفظ</button><?php if ($item['status']==='APPROVED'): ?><form method="post" action="/?page=admissions&action=enroll" class="inline-form"><?php echo csrf_field(); ?><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="btn small primary">تحويل لطالب</button></form><?php endif; ?>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<div class="modal" id="admissionModal">
<div class="modal-box">
<div class="modal-head">
<h2>طلب قبول جديد</h2>
<button class="icon-btn" onclick="closeModal('admissionModal')">×</button>
</div>
<form method="post" action="/?page=admissions&action=save" class="form-grid">
<?= csrf_field() ?>
<label>رقم الطلب<input name="application_no" required>
</label>
<label>اسم المتقدم<input name="full_name" required>
</label>
<label>الهاتف<input name="phone">
</label>
<label>البريد<input name="email" type="email">
</label>
<label>الرقم الوطني<input name="national_id">
</label>
<label>المؤهل السابق<input name="previous_degree">
</label>
<label>المعدل السابق<input name="previous_gpa" type="number" step="0.01">
</label>
<label class="full">البرنامج<select name="program_id" required>
<?php foreach ($programs as $program): ?>
<option value="<?= (int) $program['id'] ?>">
<?= e($program['name']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
<label>نوع الطلب<select name="application_type">
<option value="NEW">قبول جديد</option>
<option value="TRANSFER">تحويل</option>
</select>
</label>
<label class="full">ملاحظات<textarea name="notes">
</textarea>
</label>
<div class="form-actions full">
<button class="btn primary">إنشاء الطلب</button>
</div>
</form>
</div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
