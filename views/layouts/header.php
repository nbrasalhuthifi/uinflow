<?php
$currentPage = $currentPage ?? '';
$flashMessages = consume_flash();
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
<?= e(APP_NAME) ?>
</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
<div class="shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-mark">U</div>
            <div>
                <strong>UniFlow</strong>
                <small>Graduate Studies OS</small>
            </div>
        </div>

        <nav class="nav">
            <a class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="/?page=dashboard">
                <span>⌂</span>
<span>مركز القيادة</span>
            </a>
            <div class="nav-label">الإدارة الأكاديمية</div>
            <a class="nav-item <?= $currentPage === 'students' ? 'active' : '' ?>" href="/?page=students">
                <span>◉</span>
<span>الطلاب</span>
            </a>
            <a class="nav-item <?= $currentPage === 'admissions' ? 'active' : '' ?>" href="/?page=admissions">
                <span>↗</span>
<span>القبول والطلبات</span>
            </a>
            <a class="nav-item <?= $currentPage === 'departments' ? 'active' : '' ?>" href="/?page=departments">
                <span>⌘</span>
<span>الأقسام الأكاديمية</span>
            </a>
            <a class="nav-item <?= $currentPage === 'programs' ? 'active' : '' ?>" href="/?page=programs">
                <span>▦</span>
<span>البرامج</span>
            </a>
            <a class="nav-item <?= $currentPage === 'courses' ? 'active' : '' ?>" href="/?page=courses">
                <span>▤</span>
<span>المقررات</span>
            </a>
            <a class="nav-item <?= $currentPage === 'plans' ? 'active' : '' ?>" href="/?page=plans">
                <span>☷</span>
<span>الخطط الدراسية</span>
            </a>
            <a class="nav-item <?= $currentPage === 'enrollments' ? 'active' : '' ?>" href="/?page=enrollments">
                <span>＋</span>
<span>تسجيل المقررات</span>
            </a>
            <div class="nav-label">البحث والدراسات</div>
            <a class="nav-item <?= $currentPage === 'supervisors' ? 'active' : '' ?>" href="/?page=supervisors">
                <span>◎</span>
<span>المشرفون</span>
            </a>
            <a class="nav-item <?= $currentPage === 'research' ? 'active' : '' ?>" href="/?page=research">
                <span>⌁</span>
<span>المقترحات البحثية</span>
            </a>
            <a class="nav-item <?= $currentPage === 'theses' ? 'active' : '' ?>" href="/?page=theses">
                <span>▰</span>
<span>الرسائل العلمية</span>
            </a>
            <a class="nav-item <?= $currentPage === 'committees' ? 'active' : '' ?>" href="/?page=committees">
                <span>◇</span>
<span>اللجان والمناقشات</span>
            </a>
            <a class="nav-item <?= $currentPage === 'graduation' ? 'active' : '' ?>" href="/?page=graduation">
                <span>✦</span>
<span>التخرج</span>
            </a>
            <div class="nav-label">المتابعة</div>
            <a class="nav-item" href="/?page=search"><span>⌕</span><span>البحث الموحد</span></a>
            <?php if (can('documents.view')): ?><a class="nav-item <?= $currentPage === 'documents' ? 'active' : '' ?>" href="/?page=documents"><span>▧</span><span>المستندات</span></a><?php endif; ?>
            <a class="nav-item <?= $currentPage === 'notifications' ? 'active' : '' ?>" href="/?page=notifications"><span>◔</span><span>الإشعارات</span></a>
            <?php if (can('academic.manage')): ?><a class="nav-item <?= $currentPage === 'academic' ? 'active' : '' ?>" href="/?page=academic"><span>◫</span><span>العام والفصول</span></a><?php endif; ?>
            <?php if (can('users.view')): ?><a class="nav-item <?= $currentPage === 'admin-users' ? 'active' : '' ?>" href="/?page=admin-users"><span>◌</span><span>المستخدمون والصلاحيات</span></a><?php endif; ?>
            <?php if (can('audit.view')): ?><a class="nav-item <?= $currentPage === 'audit' ? 'active' : '' ?>" href="/?page=audit"><span>≋</span><span>سجل التدقيق</span></a><?php endif; ?>
            <a class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>" href="/?page=reports">
                <span>◫</span>
<span>التقارير والتحليلات</span>
            </a>
        </nav>

        <div class="sidebar-foot">
            <div class="security-chip">
                <span class="pulse">
</span>
                النظام متصل
            </div>
            <form method="post" action="/?page=logout" class="logout-form"><?php echo csrf_field(); ?><button class="logout" type="submit">تسجيل الخروج</button></form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <button class="icon-btn" type="button" onclick="toggleSidebar()">☰</button>
            <div class="top-title">
                <span>منصة الدراسات العليا</span>
                <small>إدارة الدورة الأكاديمية من القبول إلى التخرج</small>
            </div>
            <div class="top-actions">
                <button class="icon-btn" type="button" onclick="toggleTheme()" title="تبديل المظهر">◐</button>
                <div class="user-pill">
                    <div class="avatar">
                        <?= e(initial_letter(current_user()['full_name'] ?? 'U')) ?>
                    </div>
                    <div>
                        <strong>
<?= e(current_user()['full_name'] ?? '') ?>
</strong>
                        <small>
<?= e(status_label(current_user()['role'] ?? '')) ?>
</small>
                    </div>
                </div>
            </div>
        </header>

        <section class="content">
            <?php foreach ($flashMessages as $message): ?>
                <div class="toast <?= e($message['type']) ?>">
                    <?= e($message['message']) ?>
                </div>
            <?php endforeach; ?>
