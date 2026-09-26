<?php
$currentPage = 'dashboard';
require __DIR__ . '/../layouts/header.php';
?>
<section class="hero">
    <div>
        <span class="eyebrow">GRADUATE ACADEMIC CONTROL</span>
        <h1>مركز قيادة الدراسات العليا</h1>
        <p>صورة واحدة لحالة القبول، التسجيل، البحث، المناقشات والتخرج.</p>
    </div>
    <div class="hero-orbit">
        <div class="orbit-ring">
</div>
        <div class="orbit-core">UF</div>
    </div>
</section>

<div class="metric-grid">
    <article class="metric">
<span>إجمالي الطلاب</span>
<strong>
<?= (int) $stats['students'] ?>
</strong>
<small>ملفات أكاديمية</small>
</article>
    <article class="metric accent">
<span>طلاب نشطون</span>
<strong>
<?= (int) $stats['active_students'] ?>
</strong>
<small>قيد الدراسة</small>
</article>
    <article class="metric">
<span>طلبات معلقة</span>
<strong>
<?= (int) $stats['applications'] ?>
</strong>
<small>تحتاج إجراء</small>
</article>
    <article class="metric">
<span>أبحاث قيد المراجعة</span>
<strong>
<?= (int) $stats['research'] ?>
</strong>
<small>مسار البحث</small>
</article>
    <article class="metric">
<span>مناقشات معلقة</span>
<strong>
<?= (int) $stats['defenses'] ?>
</strong>
<small>قرارات مطلوبة</small>
</article>
    <article class="metric success">
<span>الخريجون</span>
<strong>
<?= (int) $stats['graduation'] ?>
</strong>
<small>تم اعتمادهم</small>
</article>
    <article class="metric"><span>بدون مشرف</span><strong><?= (int)$stats['no_supervisor'] ?></strong><small>يحتاج تعيينًا</small></article>
    <article class="metric"><span>مستحقو التخرج</span><strong><?= (int)$stats['ready_graduation'] ?></strong><small>مستوفون للشروط</small></article>
    <article class="metric"><span>مناقشات قادمة</span><strong><?= (int)$stats['upcoming_defenses'] ?></strong><small>مواعيد مجدولة</small></article>
</div>

<div class="split-grid">
    <section class="panel feature-panel">
        <div class="panel-head">
            <div>
<span class="eyebrow">WORKFLOW</span>
<h2>رحلة الطالب</h2>
</div>
            <span class="live-dot">LIVE</span>
        </div>
        <div class="journey">
            <?php foreach (['القبول', 'الخطة', 'المقررات', 'البحث', 'الرسالة', 'المناقشة', 'التخرج'] as $index => $step): ?>
                <div class="journey-step">
<b>
<?= $index + 1 ?>
</b>
<span>
<?= e($step) ?>
</span>
</div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head">
<div>
<span class="eyebrow">AUDIT</span>
<h2>آخر العمليات</h2>
</div>
</div>
        <div class="timeline">
            <?php foreach ($timeline as $item): ?>
                <div class="timeline-item">
                    <span>
</span>
                    <div>
<strong>
<?= e($item['action']) ?> · <?= e($item['entity']) ?>
</strong>
<small>
<?= e($item['user_name']) ?> · <?= e($item['created_at']) ?>
</small>
</div>
                </div>
            <?php endforeach; ?>
            <?php if (!$timeline): ?>
<div class="empty">لا توجد عمليات مسجلة بعد.</div>
<?php endif; ?>
        </div>
    </section>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
