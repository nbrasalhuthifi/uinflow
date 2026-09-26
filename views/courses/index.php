<?php $currentPage = 'courses'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">CURRICULUM ENGINE</span>
<h1>المقررات</h1>
<p>المقررات مرتبطة مباشرة بالبرامج لمنع التسجيل الخاطئ.</p>
</div>
<button class="btn primary" onclick="openModal('courseModal')">+ مقرر جديد</button>
</div>
<section class="panel table-panel">
<div class="panel-head">
<div>
<h2>كتالوج المقررات</h2>
</div>
<span class="tag">Program-bound</span>
</div>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>الرمز</th>
<th>المقرر</th>
<th>البرنامج</th>
<th>الساعات</th>
<th>النوع</th>
<th>إجراء</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
<td>
<strong>
<?= e($item['code']) ?>
</strong>
</td>
<td>
<?= e($item['name']) ?>
</td>
<td>
<?= e($item['program_name']) ?>
</td>
<td>
<?= (int) $item['credits'] ?>
</td>
<td>
<?= $item['is_required'] ? 'إجباري' : 'اختياري' ?>
</td>
<td>
<button class="btn small" onclick='editCourse(<?= json_encode($item, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>تعديل</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</section>
<div class="modal" id="courseModal">
<div class="modal-box">
<div class="modal-head">
<h2>بيانات المقرر</h2>
<button class="icon-btn" onclick="closeModal('courseModal')">×</button>
</div>
<form id="courseForm" method="post" action="/?page=courses&action=save" class="form-grid">
<?= csrf_field() ?>
<input type="hidden" name="id">
<label class="full">البرنامج<select name="program_id" required>
<?php foreach ($programs as $program): ?>
<option value="<?= (int) $program['id'] ?>">
<?= e($program['name']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
<label class="full">البرامج المشتركة <select name="shared_program_ids[]" multiple size="4"><?php foreach ($programs as $program): ?><option value="<?= (int)$program['id'] ?>"><?= e($program['name']) ?></option><?php endforeach; ?></select><small>يمكن جعل المقرر متاحًا لأكثر من برنامج.</small></label><label>رمز المقرر<input name="code" required>
</label>
<label>اسم المقرر<input name="name" required>
</label>
<label>الساعات<input name="credits" type="number" min="1" max="12" required>
</label>
<label class="check">
<input name="is_required" type="checkbox" value="1" checked> مقرر إجباري</label>
<div class="form-actions full">
<button class="btn primary">حفظ المقرر</button>
</div>
</form>
</div>
</div>
<script>function editCourse(data){fillForm('courseForm',data);document.querySelector('#courseForm [name="is_required"]').checked=Number(data.is_required)===1;const ids=String(data.shared_program_ids||'').split(',');document.querySelectorAll('#courseForm [name="shared_program_ids[]"] option').forEach(o=>o.selected=ids.includes(o.value));openModal('courseModal');}</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
