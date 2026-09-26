<?php $currentPage = 'theses'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">THESIS CONTROL</span>
<h1>الرسائل العلمية</h1>
<p>حالة الرسالة مرتبطة بمراحل الإعداد والمناقشة والتخرج.</p>
</div>
<button class="btn primary" onclick="openModal('thesisModal')">+ رسالة جديدة</button>
</div>
<section class="panel table-panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>الطالب</th>
<th>العنوان</th>
<th>البداية</th>
<th>حالة الرسالة</th>
<th>النتيجة</th>
<th>الإجراء</th>
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
<?= e($item['title']) ?>
</td>
<td>
<?= e($item['start_date']) ?>
</td>
<td>
<span class="status <?= e($item['status']) ?>">
<?= e(status_label($item['status'])) ?>
</span>
</td>
<td>
<?= e(status_label($item['defense_result'] ?? 'PENDING')) ?>
</td>
<td>
<form method="post" action="/?page=theses&action=status" class="inline-form">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
<select name="status">
<option value="ACTIVE">نشطة</option>
<option value="READY_FOR_DEFENSE">جاهزة للمناقشة</option>
<option value="CANCELLED">ملغاة</option>
</select>
<button class="btn small">تحديث</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<div class="modal" id="thesisModal">
<div class="modal-box">
<div class="modal-head">
<h2>إنشاء ملف رسالة</h2>
<button class="icon-btn" onclick="closeModal('thesisModal')">×</button>
</div>
<form method="post" action="/?page=theses&action=save" class="form-grid">
<?= csrf_field() ?>
<div class="full">
<?php $lookupEntity='students';$lookupName='student_id';$lookupLabel='الطالب';$lookupField='student_id';require __DIR__ . '/../components/lookup.php'; ?>
</div>
<label class="full">عنوان الرسالة<input name="title" required>
</label>
<label>تاريخ البداية<input name="start_date" type="date" required>
</label>
<div class="form-actions full">
<button class="btn primary">إنشاء الرسالة</button>
</div>
</form>
</div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
