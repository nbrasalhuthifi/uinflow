<?php $currentPage = 'graduation'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
<div>
<span class="eyebrow">GRADUATION GATE</span>
<h1>اعتماد التخرج</h1>
<p>لا تظهر حالة «جاهز» إلا بعد تحقق الساعات والمناقشة والنتيجة.</p>
</div>
</div>
<section class="card-grid">
<?php foreach ($items as $item): ?>
<article class="graduation-card <?= (int) $item['ready'] === 1 ? 'ready' : '' ?>">
<div class="grad-top">
<span class="status <?= (int) $item['ready'] === 1 ? 'APPROVED' : 'PENDING' ?>">
<?= (int) $item['ready'] === 1 ? 'مستوفٍ' : 'غير مستوفٍ' ?>
</span>
<strong>
<?= e($item['student_no']) ?>
</strong>
</div>
<h2>
<?= e($item['full_name']) ?>
</h2>
<p>
<?= e($item['program_name']) ?>
</p>
<div class="grad-metrics">
<div>
<small>الساعات</small>
<strong>
<?= e($item['earned_credits']) ?> / <?= e($item['required_credits']) ?>
</strong>
</div>
<div>
<small>الرسالة</small>
<strong>
<?= e(status_label($item['thesis_status'] ?? 'PENDING')) ?>
</strong>
</div>
<div>
<small>المناقشة</small>
<strong>
<?= e(status_label($item['defense_result'] ?? 'PENDING')) ?>
</strong>
</div>
</div>
<?php if ((int) $item['ready'] === 1): ?>
<form method="post" action="/?page=graduation&action=approve">
<?= csrf_field() ?>
<input type="hidden" name="student_id" value="<?= (int) $item['id'] ?>">
<button class="btn primary wide" type="submit">اعتماد التخرج</button>
</form>
<?php else: ?>
<div class="blocked">
    المتطلبات غير مكتملة — لن يمكن اعتماد التخرج قبل اكتمال الساعات ونجاح المناقشة.
</div>
<?php endif; ?>
</article>
<?php endforeach; ?>
</section>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
