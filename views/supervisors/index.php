<?php $currentPage = 'supervisors'; require __DIR__ . '/../layouts/header.php'; ?>
<div class="page-head">
    <div>
        <span class="eyebrow">MENTOR NETWORK</span>
        <h1>المشرفون</h1>
        <p>إنشاء حساب المشرف وبياناته الأكاديمية من نفس النموذج، مع صورة اختيارية.</p>
    </div>
    <button class="btn primary" onclick="openModal('supervisorModal')">+ مشرف جديد</button>
</div>
<section class="card-grid">
<?php foreach ($items as $item): ?>
    <article class="person-card">
        <div class="avatar lg">
            <?php if (!empty($item['photo_path'])): ?>
                <img src="/<?= e($item['photo_path']) ?>" alt="">
            <?php else: ?>
                <?= e(initial_letter($item['full_name'])) ?>
            <?php endif; ?>
        </div>
        <div>
            <h2><?= e($item['full_name']) ?></h2>
            <p><?= e($item['academic_rank']) ?></p>
            <small><?= e($item['specialization'] ?: 'لم يحدد التخصص') ?></small>
            <a href="mailto:<?= e($item['email']) ?>" class="contact-link"><?= e($item['email']) ?></a>
        </div>
    </article>
<?php endforeach; ?>
<?php if (!$items): ?>
    <div class="panel empty full">لا يوجد مشرفون بعد. أضف أول مشرف من الزر أعلاه.</div>
<?php endif; ?>
</section>

<div class="modal" id="supervisorModal">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <span class="eyebrow">NEW SUPERVISOR</span>
                <h2>إضافة مشرف وحساب دخول</h2>
            </div>
            <button class="icon-btn" onclick="closeModal('supervisorModal')">×</button>
        </div>
        <form method="post" action="/?page=supervisors&action=save" class="form-grid" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label>اسم المشرف
                <input name="full_name" required placeholder="د. أحمد محمد">
            </label>
            <label>البريد الإلكتروني
                <input name="email" type="email" required placeholder="ahmad@example.com">
            </label>
            <label>كلمة مرور الحساب
                <input name="password" type="password" required minlength="6" placeholder="6 أحرف على الأقل">
            </label>
            <label>الرتبة الأكاديمية
                <input name="academic_rank" required placeholder="أستاذ مشارك">
            </label>
            <label class="full">التخصص
                <input name="specialization" placeholder="علوم الحاسوب / نظم المعلومات">
            </label>
            <label class="full">صورة المشرف <span class="optional">اختياري</span>
                <input name="photo" type="file" accept="image/jpeg,image/png,image/webp">
            </label>
            <div class="form-note full">سيُنشأ للمشرف حساب دخول مستقل بدور «مشرف» ويمكنه استخدام بريده وكلمة المرور.</div>
            <div class="form-actions full">
                <button class="btn primary">إنشاء المشرف</button>
                <button type="button" class="btn" onclick="closeModal('supervisorModal')">إلغاء</button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
