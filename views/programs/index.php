<?php
$currentPage = 'programs';
require __DIR__ . '/../layouts/header.php';
$grouped = [];
foreach ($items as $item) {
    $grouped[$item['department_name']][] = $item;
}
?>
<div class="page-head">
    <div>
        <span class="eyebrow">ACADEMIC STRUCTURE</span>
        <h1>البرامج الأكاديمية</h1>
        <p>إدارة برامج الدراسات العليا حسب القسم والدرجة ومتطلبات التخرج.</p>
    </div>
    <div class="page-actions">
        <a class="btn" href="/?page=departments">إدارة الأقسام</a>
        <button class="btn primary" onclick="openModal('programModal')">+ برنامج جديد</button>
    </div>
</div>

<section class="metric-grid compact-grid">
    <div class="metric"><span>إجمالي البرامج</span><strong><?= count($items) ?></strong></div>
    <div class="metric"><span>عدد الأقسام</span><strong><?= count($grouped) ?></strong></div>
    <div class="metric"><span>ماجستير</span><strong><?= count(array_filter($items, fn($x) => $x['degree'] === 'MASTER')) ?></strong></div>
    <div class="metric"><span>دكتوراه</span><strong><?= count(array_filter($items, fn($x) => $x['degree'] === 'PHD')) ?></strong></div>
</section>

<?php if (!$items): ?>
    <section class="empty-state card">
        <div class="empty-icon">▦</div>
        <h2>لا توجد برامج بعد</h2>
        <p>أضف قسمًا أولًا، ثم أنشئ البرنامج التابع له.</p>
        <a class="btn primary" href="/?page=departments">إضافة قسم</a>
    </section>
<?php endif; ?>

<?php foreach ($grouped as $departmentName => $departmentPrograms): ?>
<section class="department-program-section">
    <div class="section-heading-row">
        <div>
            <span class="eyebrow">ACADEMIC DEPARTMENT</span>
            <h2><?= e($departmentName) ?></h2>
        </div>
        <span class="tag"><?= count($departmentPrograms) ?> برنامج</span>
    </div>
    <div class="card-grid program-grid">
        <?php foreach ($departmentPrograms as $item): ?>
            <article class="program-card">
                <div class="program-card-top">
                    <div class="program-icon"><?= e(initial_letter($item['name'])) ?></div>
                    <span class="status <?= e($item['degree']) ?>"><?= e(status_label($item['degree'])) ?></span>
                </div>
                <div class="program-code"><?= e($item['department_code']) ?></div>
                <h2><?= e($item['name']) ?></h2>
                <p>برنامج <?= e(status_label($item['degree'])) ?> في <?= e($item['department_name']) ?></p>
                <div class="program-meta">
                    <span><b><?= e($item['duration_years']) ?></b> سنوات</span>
                    <span><b><?= (int) $item['required_credits'] ?></b> ساعة مطلوبة</span>
                </div>
                <button class="btn small" onclick='editProgram(<?= json_encode($item, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>تعديل البرنامج</button>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<div class="modal" id="programModal">
    <div class="modal-box">
        <div class="modal-head">
            <div><span class="eyebrow">PROGRAM SETUP</span><h2>بيانات البرنامج</h2></div>
            <button class="icon-btn" onclick="closeModal('programModal')">×</button>
        </div>
        <form id="programForm" method="post" action="/?page=programs&action=save" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id">
            <label class="full">القسم الأكاديمي
                <select name="department_id" required>
                    <option value="">اختر القسم</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?> (<?= e($department['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="full">اسم البرنامج<input name="name" required placeholder="مثال: هندسة البرمجيات"></label>
            <div class="full radio-group-card">
                <span class="field-title">الدرجة العلمية</span>
                <label class="radio-option"><input type="radio" name="degree" value="MASTER" checked><span><b>ماجستير</b><small>برنامج دراسات عليا للماجستير</small></span></label>
                <label class="radio-option"><input type="radio" name="degree" value="PHD"><span><b>دكتوراه</b><small>برنامج بحثي للدكتوراه</small></span></label>
            </div>
            <label>مدة البرنامج<input name="duration_years" type="number" step="0.5" min="0.5" value="2"></label>
            <label>الساعات المطلوبة<input name="required_credits" type="number" min="1" value="6"></label>
            <div class="form-actions full"><button class="btn primary">حفظ البرنامج</button></div>
        </form>
    </div>
</div>
<script>
function editProgram(data) {
    fillForm('programForm', data);
    const radio = document.querySelector('#programForm input[name="degree"][value="' + data.degree + '"]');
    if (radio) radio.checked = true;
    openModal('programModal');
}
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
