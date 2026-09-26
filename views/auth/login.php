<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UniFlow — تسجيل الدخول</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="login-page">
    <div class="login-orbit orbit-one">
</div>
    <div class="login-orbit orbit-two">
</div>
    <main class="login-card">
        <div class="brand large">
            <div class="brand-mark">U</div>
            <div>
                <strong>UniFlow</strong>
                <small>Graduate Studies OS</small>
            </div>
        </div>
        <div class="login-copy">
            <span class="eyebrow">UNIVERSITY GRADUATE STUDIES</span>
            <h1>كل رحلة أكاديمية<br>
<span>لها مسار واضح.</span>
</h1>
            <p>من القبول والخطة الدراسية إلى البحث والمناقشة واعتماد التخرج.</p>
        </div>
        <?php if ($error): ?>
            <div class="toast error">
<?= e($error) ?>
</div>
        <?php endif; ?>
        <form method="post" class="login-form">
            <?= csrf_field() ?>
            <label>
                <span>البريد الإلكتروني</span>
                <input name="email" type="email" required>
            </label>
            <label>
                <span>كلمة المرور</span>
                <input name="password" type="password" required>
            </label>
            <button class="btn primary wide" type="submit">دخول إلى المنصة</button>
        </form>
        <div class="login-foot">الحساب التجريبي: admin@uniflow.local / password</div>
    </main>
</body>
</html>
