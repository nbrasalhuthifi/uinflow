<?php
$currentPage = 'reports';
require __DIR__ . '/../layouts/header.php';
$maxProgram = 1;
foreach ($report['students'] as $row) { $maxProgram = max($maxProgram, (int) $row['total']); }
$studentTotal = array_sum(array_map(fn($r) => (int) $r['total'], $report['student_status']));
$donutColors = ['#7657ff', '#2bd4c5', '#36d28c', '#f5bd58', '#ff657d'];
$cursor = 0.0;
$segments = [];
foreach ($report['student_status'] as $i => $row) {
    $value = $studentTotal > 0 ? ((int) $row['total'] / $studentTotal) * 100 : 0;
    $next = $cursor + $value;
    $segments[] = $donutColors[$i % count($donutColors)] . ' ' . round($cursor, 2) . '% ' . round($next, 2) . '%';
    $cursor = $next;
}
$donutGradient = $segments ? 'conic-gradient(' . implode(', ', $segments) . ')' : 'conic-gradient(var(--surface-3) 0 100%)';
?>
<div class="page-head report-head">
    <div>
        <span class="eyebrow">ANALYTICS CENTER</span>
        <h1>التقارير والتحليلات</h1>
        <p>لوحة مؤشرات مرتبة بصريًا تساعدك على قراءة حالة الدراسات العليا بسرعة.</p>
    </div>
    <div class="report-badge">بيانات مباشرة من النظام</div>
</div>

<form class="search-bar" method="get"><input type="hidden" name="page" value="reports"><select name="program_id"><option value="0">كل البرامج</option><?php foreach($programs as $p):?><option value="<?=$p['id']?>" <?=$filters['program_id']==$p['id']?'selected':''?>><?=e($p['name'])?></option><?php endforeach;?></select><select name="status"><option value="">كل حالات الطلاب</option><option value="ACTIVE" <?=$filters['status']==='ACTIVE'?'selected':''?>>نشط</option><option value="SUSPENDED" <?=$filters['status']==='SUSPENDED'?'selected':''?>>موقوف</option><option value="GRADUATED" <?=$filters['status']==='GRADUATED'?'selected':''?>>متخرج</option></select><button class="btn primary">تطبيق</button><a class="btn" href="/?page=reports&action=export&program_id=<?=$filters['program_id']?>&status=<?=urlencode($filters['status'])?>">تصدير CSV</a><span class="tag">متوسط المعدل: <?=e($report['average_gpa'])?></span></form>

<section class="report-grid">
    <article class="panel report-chart-card report-wide">
        <div class="panel-head"><div><span class="eyebrow">PROGRAMS</span><h2>توزيع الطلاب حسب البرنامج</h2></div></div>
        <div class="horizontal-chart">
            <?php foreach ($report['students'] as $row): $total=(int)$row['total']; $width=$total > 0 ? max(7, round(($total/$maxProgram)*100)) : 0; ?>
            <div class="chart-line">
                <div class="chart-label"><span><?= e($row['name']) ?></span><strong><?= $total ?></strong></div>
                <div class="chart-track"><i style="width:<?= $width ?>%"></i></div>
            </div>
            <?php endforeach; ?>
            <?php if (!$report['students']): ?><div class="empty">لا توجد برامج مسجلة.</div><?php endif; ?>
        </div>
    </article>

    <article class="panel donut-card">
        <div class="panel-head"><div><span class="eyebrow">STUDENTS</span><h2>حالة الطلاب</h2></div></div>
        <div class="donut-layout">
            <div class="donut-ring" style="background:<?= e($donutGradient) ?>"></div>
            <div class="legend-list">
                <?php foreach ($report['student_status'] as $row): ?><div><span class="legend-dot"></span><span><?= e(status_label($row['status'])) ?></span><strong><?= (int)$row['total'] ?></strong></div><?php endforeach; ?>
            </div>
        </div>
    </article>

    <article class="panel report-list-card">
        <div class="panel-head"><div><span class="eyebrow">ADMISSIONS</span><h2>حالات الطلبات</h2></div></div>
        <div class="stat-bars">
        <?php $max=1; foreach($report['applications'] as $r){$max=max($max,(int)$r['total']);} foreach($report['applications'] as $row): ?>
            <div class="stat-row"><span><?= e(status_label($row['status'])) ?></span><div><i style="width:<?= max(5, round(((int)$row['total']/$max)*100)) ?>%"></i></div><strong><?= (int)$row['total'] ?></strong></div>
        <?php endforeach; ?>
        </div>
    </article>

    <article class="panel report-list-card">
        <div class="panel-head"><div><span class="eyebrow">RESEARCH</span><h2>حالة الأبحاث</h2></div></div>
        <div class="stat-bars">
        <?php $max=1; foreach($report['research'] as $r){$max=max($max,(int)$r['total']);} foreach($report['research'] as $row): ?>
            <div class="stat-row"><span><?= e(status_label($row['status'])) ?></span><div><i style="width:<?= max(5, round(((int)$row['total']/$max)*100)) ?>%"></i></div><strong><?= (int)$row['total'] ?></strong></div>
        <?php endforeach; ?>
        </div>
    </article>

    <article class="panel report-list-card">
        <div class="panel-head"><div><span class="eyebrow">THESES</span><h2>حالة الرسائل</h2></div></div>
        <div class="status-matrix">
        <?php foreach ($report['theses'] as $row): ?><div class="status-tile"><span class="status <?= e($row['status']) ?>"><?= e(status_label($row['status'])) ?></span><strong><?= (int)$row['total'] ?></strong></div><?php endforeach; ?>
        </div>
    </article>
</section>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
