<?php $currentPage = 'departments'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
    <div>
        <span class="eyebrow">ACADEMIC UNITS</span>
        <h1>الأقسام الأكاديمية</h1>
        <p>أضف الأقسام أو عدّلها بدون الحاجة لتعديل قاعدة البيانات يدويًا.</p>
    </div>
    <button class="btn primary" onclick="newDepartment()">+ قسم جديد</button>
</div>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>القسم</th><th>الرمز</th><th>البرامج</th><th>الإجراءات</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><strong><?= e($item['name']) ?></strong></td>
                    <td><span class="code-chip"><?= e($item['code']) ?></span></td>
                    <td><?= (int) $item['programs_count'] ?></td>
                    <td class="table-actions">
                        <button class="btn small" onclick='editDepartment(<?= json_encode($item, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>تعديل</button>
                        <?php if ((int) $item['programs_count'] === 0): ?>
                        <form method="post" action="/?page=departments&action=delete" onsubmit="return confirm('حذف هذا القسم؟');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button class="btn small danger" type="submit">حذف</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<div class="modal" id="departmentModal">
    <div class="modal-box small-modal">
        <div class="modal-head"><h2>بيانات القسم</h2><button class="icon-btn" onclick="closeModal('departmentModal')">×</button></div>
        <form id="departmentForm" method="post" action="/?page=departments&action=save" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id">
            <label class="full">اسم القسم<input name="name" required placeholder="قسم علوم الحاسوب"></label>
            <label class="full">رمز القسم<input name="code" required maxlength="30" placeholder="CS"></label>
            <div class="form-actions full"><button class="btn primary">حفظ</button></div>
        </form>
    </div>
</div>
<script>
function newDepartment() { fillForm('departmentForm', {id:'',name:'',code:''}); openModal('departmentModal'); }
function editDepartment(data) { fillForm('departmentForm', data); openModal('departmentModal'); }
</script>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
