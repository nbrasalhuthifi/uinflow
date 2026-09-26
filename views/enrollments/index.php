<?php
$currentPage = 'enrollments';
require __DIR__ . '/../layouts/header.php';
?>
<div class="page-head">
    <div>
        <span class="eyebrow">COURSE REGISTRATION</span>
        <h1>تسجيل المقررات</h1>
        <p>سجّل المقرر بخطوات واضحة: اختر الطالب، ثم خطته الدراسية، ثم المقرر المناسب لبرنامجه.</p>
    </div>
    <button class="btn primary" onclick="openModal('enrollmentModal')">+ تسجيل مقرر جديد</button>
</div>

<section class="registration-guide card">
    <div class="registration-step active"><b>1</b><div><strong>الطالب</strong><small>ابحث بالاسم أو الرقم</small></div></div>
    <div class="registration-arrow">←</div>
    <div class="registration-step"><b>2</b><div><strong>الخطة</strong><small>اختر الخطة المعتمدة</small></div></div>
    <div class="registration-arrow">←</div>
    <div class="registration-step"><b>3</b><div><strong>المقرر</strong><small>اختر مقرر برنامج الطالب</small></div></div>
    <div class="registration-arrow">←</div>
    <div class="registration-step"><b>4</b><div><strong>تأكيد</strong><small>راجع البيانات ثم سجّل</small></div></div>
</section>

<section class="panel table-panel">
    <div class="panel-head">
        <div>
            <h2>السجلات الحالية</h2>
            <p>يمكن تحديث الدرجة والحالة بعد التسجيل.</p>
        </div>
        <span class="tag"><?= count($items) ?> تسجيل</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>الطالب</th>
                <th>المقرر</th>
                <th>الخطة / السنة</th>
                <th>الساعات</th>
                <th>الدرجة</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$items): ?>
                <tr><td colspan="7" class="empty">لا توجد مقررات مسجلة حتى الآن.</td></tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><strong><?= e($item['student_no']) ?></strong><br><?= e($item['full_name']) ?></td>
                    <td><strong><?= e($item['code']) ?></strong><br><?= e($item['course_name']) ?></td>
                    <td><?= e($item['year_name']) ?></td>
                    <td><?= (int) $item['credits'] ?></td>
                    <td><?= $item['grade'] === null ? '—' : e($item['grade']) ?></td>
                    <td><span class="status <?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></span></td>
                    <td>
                        <form method="post" action="/?page=enrollments&action=update" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input class="tiny" name="grade" value="<?= e($item['grade']) ?>" placeholder="الدرجة" type="number" min="0" max="100" step="0.01">
                            <select name="status">
                                <option value="REGISTERED" <?= $item['status'] === 'REGISTERED' ? 'selected' : '' ?>>مسجل</option>
                                <option value="PASSED" <?= $item['status'] === 'PASSED' ? 'selected' : '' ?>>ناجح</option>
                                <option value="FAILED" <?= $item['status'] === 'FAILED' ? 'selected' : '' ?>>راسب</option>
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

<div class="modal" id="enrollmentModal">
    <div class="modal-box registration-modal">
        <div class="modal-head">
            <div>
                <span class="eyebrow">NEW ENROLLMENT</span>
                <h2>تسجيل مقرر جديد</h2>
            </div>
            <button class="icon-btn" onclick="closeModal('enrollmentModal')">×</button>
        </div>

        <div class="registration-notice">
            <strong>قبل التسجيل</strong>
            <span>يجب أن تكون الخطة الدراسية معتمدة أو قيد التنفيذ، وأن يكون المقرر تابعًا لبرنامج الطالب.</span>
        </div>

        <form method="post" action="/?page=enrollments&action=save" class="form-grid" id="enrollmentForm">
            <?= csrf_field() ?>
            <div class="full registration-field">
                <div class="step-title"><b>1</b><div><strong>اختيار الطالب</strong><small>اكتب اسم الطالب أو رقمه</small></div></div>
                <?php
                $lookupEntity = 'students';
                $lookupName = 'student_id';
                $lookupLabel = 'الطالب';
                $lookupField = 'student_id';
                require __DIR__ . '/../components/lookup.php';
                ?>
            </div>

            <div class="full registration-field disabled-field" id="planField">
                <div class="step-title"><b>2</b><div><strong>اختيار الخطة الدراسية</strong><small>تظهر بعد اختيار الطالب</small></div></div>
                <div id="planLookupWrap"><div class="lookup-placeholder">اختر الطالب أولًا</div></div>
            </div>

            <div class="full registration-field disabled-field" id="courseField">
                <div class="step-title"><b>3</b><div><strong>اختيار المقرر</strong><small>يتم فلترة المقررات حسب برنامج الطالب</small></div></div>
                <div id="courseLookupWrap"><div class="lookup-placeholder">اختر الخطة أولًا</div></div>
            </div>

            <div class="full" id="registrationSummary" hidden>
                <div class="selection-summary">
                    <div><span>الطالب</span><strong id="selectedStudentText">—</strong></div>
                    <div><span>الخطة</span><strong id="selectedPlanText">—</strong></div>
                    <div><span>المقرر</span><strong id="selectedCourseText">—</strong></div>
                    <div><span>الساعات</span><strong id="selectedCreditsText">—</strong></div>
                </div>
            </div>

            <label class="full">الفصل الدراسي<select name="semester_id" required><option value="">اختر الفصل</option><?php foreach($semesters as $semester): ?><option value="<?=$semester['id']?>" <?=$semester['is_current']?'selected':'' ?>><?=e($semester['year_name'])?> — <?=e($semester['name'])?></option><?php endforeach; ?></select></label>

            <input type="hidden" name="plan_id" id="plan_id">
            <input type="hidden" name="course_id" id="course_id">
            <div class="form-actions full">
                <button class="btn primary" id="saveEnrollmentButton" disabled>تسجيل المقرر</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetEnrollmentFlow() {
    document.getElementById('planLookupWrap').innerHTML = '<div class="lookup-placeholder">اختر الطالب أولًا</div>';
    document.getElementById('courseLookupWrap').innerHTML = '<div class="lookup-placeholder">اختر الخطة أولًا</div>';
    document.getElementById('planField').classList.add('disabled-field');
    document.getElementById('courseField').classList.add('disabled-field');
    document.getElementById('plan_id').value = '';
    document.getElementById('course_id').value = '';
    document.getElementById('registrationSummary').hidden = true;
    document.getElementById('saveEnrollmentButton').disabled = true;
}

document.addEventListener('lookup:selected', function (event) {
    const detail = event.detail;

    if (detail.field === 'student_id') {
        document.getElementById('planField').classList.remove('disabled-field');
        document.getElementById('courseField').classList.add('disabled-field');
        document.getElementById('courseLookupWrap').innerHTML = '<div class="lookup-placeholder">اختر الخطة أولًا</div>';
        document.getElementById('course_id').value = '';
        document.getElementById('plan_id').value = '';
        document.getElementById('registrationSummary').hidden = false;
        document.getElementById('selectedStudentText').textContent = detail.label;
        document.getElementById('selectedPlanText').textContent = '—';
        document.getElementById('selectedCourseText').textContent = '—';
        document.getElementById('selectedCreditsText').textContent = '—';
        document.getElementById('saveEnrollmentButton').disabled = true;

        document.getElementById('planLookupWrap').innerHTML = `
            <div class="lookup" data-entity="plans" data-field="plan_id" data-depends-on="student_id">
                <label>الخطة الدراسية</label>
                <div class="lookup-box">
                    <input class="lookup-input" autocomplete="off" placeholder="ابحث عن السنة الأكاديمية...">
                    <input class="lookup-value" type="hidden" name="plan_id" data-lookup-field="plan_id">
                </div>
                <div class="lookup-results"></div>
            </div>`;
        return;
    }

    if (detail.field === 'plan_id') {
        document.getElementById('plan_id').value = detail.id;
        document.getElementById('courseField').classList.remove('disabled-field');
        document.getElementById('selectedPlanText').textContent = detail.label;
        document.getElementById('course_id').value = '';
        document.getElementById('selectedCourseText').textContent = '—';
        document.getElementById('selectedCreditsText').textContent = '—';
        document.getElementById('saveEnrollmentButton').disabled = true;

        document.getElementById('courseLookupWrap').innerHTML = `
            <div class="lookup" data-entity="courses" data-field="course_id" data-depends-on="student_id">
                <label>المقرر</label>
                <div class="lookup-box">
                    <input class="lookup-input" autocomplete="off" placeholder="ابحث برمز المقرر أو اسمه...">
                    <input class="lookup-value" type="hidden" name="course_id" data-lookup-field="course_id">
                </div>
                <div class="lookup-results"></div>
            </div>`;
        return;
    }

    if (detail.field === 'course_id') {
        document.getElementById('course_id').value = detail.id;
        document.getElementById('selectedCourseText').textContent = detail.label;
        document.getElementById('selectedCreditsText').textContent = detail.credits ? detail.credits + ' ساعة' : '—';
        document.getElementById('saveEnrollmentButton').disabled = false;
    }
});

document.getElementById('enrollmentForm').addEventListener('submit', function (event) {
    if (!document.getElementById('plan_id').value || !document.getElementById('course_id').value) {
        event.preventDefault();
        alert('أكمل اختيار الطالب والخطة والمقرر أولًا.');
    }
});
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
