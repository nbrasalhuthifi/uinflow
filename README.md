

# UniFlow — نظام إدارة الدراسات العليا

نظام إلكتروني لإدارة الدراسات العليا في الجامعة، مبني باستخدام PHP 8+ وMySQL وHTML/CSS/JavaScript.

## فكرة المشروع

يهدف UniFlow إلى تنظيم وإدارة بيانات طلاب الدراسات العليا والقبول والبرامج والمقررات والبحث العلمي والرسائل ولجان المناقشة والتخرج والتقارير.

## أهداف النظام

- إدارة الطلاب والقبول.
- إدارة الأقسام والبرامج والمقررات.
- إدارة الخطط والتسجيل.
- إدارة المشرفين والأبحاث والرسائل.
- إدارة لجان المناقشة والتخرج.
- التقارير والإحصائيات.
- إدارة المستخدمين والصلاحيات.
- إدارة المستندات.
- البحث الموحد.
- حماية وسلامة البيانات.

## مستخدمو النظام

### مدير النظام
إدارة المستخدمين والصلاحيات والإعدادات.

### موظف الدراسات العليا
إدارة الطلاب والقبول والبيانات الأكاديمية والتقارير.

### المشرف الأكاديمي
متابعة الطلاب والأبحاث والرسائل الخاصة به.

### الطالب
متابعة بياناته الأكاديمية والبحث والرسالة وإجراءات التخرج.

## الوحدات

- `Auth`
- `Students`
- `Admissions`
- `Departments`
- `Programs`
- `Courses`
- `Plans`
- `Enrollments`
- `Supervisors`
- `Research`
- `Theses`
- `Committees`
- `Graduation`
- `Reports`
- `Dashboard`
- `Documents`
- `Lookup`
- `Admin`

## أهم المميزات

### الأمان والصلاحيات
- تسجيل دخول ومصادقة.
- أدوار وصلاحيات.
- حماية الصفحات والعمليات.
- سجل تدقيق.
- حماية الجلسات ومحاولات الدخول.

### البحث
البحث في:
- الطلاب.
- البرامج.
- المقررات.
- المشرفين.

ويستخدم النظام Prepared Statements متوافقة مع:

```text
ATTR_EMULATE_PREPARES=false

التخرج

يتم التحقق من استحقاق التخرج من الخادم بناءً على:

الساعات المجتازة >= الساعات المطلوبة للبرنامج.

حالة الرسالة DEFENDED.

نتيجة آخر مناقشة PASS أو PASS_WITH_CHANGES.


ولا يعتمد النظام على إخفاء زر التخرج في الواجهة فقط.

الصور والمستندات

صور الطلاب والمشرفين اختيارية.

JPG / PNG / WEBP.

الحد الأقصى للصورة 2MB.

إدارة المستندات المرتبطة بالعمليات الأكاديمية.


الواجهة

عربية RTL.

Responsive.

Dark / Light Mode.

جداول وبطاقات وحالات.

بحث وواجهات متجاوبة.


بنية النظام

Request
  ↓
Route
  ↓
Controller
  ↓
Service
  ↓
Repository
  ↓
PDO / MySQL

التقنيات

PHP 8+

MySQL 8+

HTML5

CSS3

JavaScript

PDO

Git

GitHub


قاعدة البيانات

اسم قاعدة البيانات:

uniflow_graduate

منفذ MySQL:

3308

ملفات قاعدة البيانات:

database/schema.sql
database/seed.sql
database/upgrade_existing.sql
database/upgrade_production.sql

التشغيل

1. تشغيل MySQL

يجب تشغيل MySQL على المنفذ 3308.

2. قاعدة البيانات

استورد:

database/schema.sql
database/seed.sql

3. إعداد البيئة

انسخ:

.env.example

إلى:

.env

وضع بيانات MySQL المناسبة.

4. تشغيل المشروع

من مجلد المشروع:

php -S localhost:8000 -t public

ثم افتح:

http://localhost:8000

الحساب التجريبي

Email: admin@uniflow.local
Password: password

ترقية قاعدة بيانات موجودة

إذا كانت قاعدة البيانات موجودة ولا تريد حذف البيانات:

database/upgrade_existing.sql

يفضل أخذ نسخة احتياطية قبل الترقية.

الاختبارات

php tests/smoke.php

كما توجد فحوصات Syntax داخل:

tests/README.md

Git Workflow

يتبع الفريق:

Issue
 ↓
Branch
 ↓
Commit
 ↓
Push
 ↓
Pull Request
 ↓
Review
 ↓
Merge

توزيع الفريق

Person 1 — Student & Academic

feature/student-nbras

المسؤولية:

Students
Admissions
Departments
Programs
Courses
Plans
Enrollments

Person 2 — Research & Graduation

feature/research-graduation

المسؤولية:

Supervisors
Research
Theses
Committees
Graduation

Person 3 — System & Administration

feature/system-admin

المسؤولية:

Auth
Admin
Dashboard
Reports
Lookup
Documents

GitHub Project Board

مراحل العمل:

Backlog
Todo
In Progress
Review
Done

يتم ربط المهام بـ GitHub Issues ومتابعتها من خلال Project Board.

توثيق المشروع

docs/
├── SRS.md
├── USER_STORIES.md
└── EDGE_CASES.md

SRS.md — متطلبات النظام.

USER_STORIES.md — قصص المستخدم ومعايير القبول.

EDGE_CASES.md — الحالات الاستثنائية.


استخدام الذكاء الاصطناعي

يستخدم الفريق أدوات الذكاء الاصطناعي للمساعدة في:

فهم الأخطاء.

اقتراح الحلول.

تحسين الكود.

كتابة التوثيق.

مراجعة بعض الأجزاء.


ويتم فهم ومراجعة أي كود قبل اعتماده.

يتم توثيق استخدام AI في:

AI_Log.md

حالة المشروع

المشروع قيد التطوير كمشروع تخرج جامعي، ويهدف إلى بناء نظام حقيقي متصل بقاعدة بيانات MySQL وليس مجرد واجهات تجريبية.

الترخيص

المشروع مخصص للاستخدام الأكاديمي ضمن مشروع التخرج

## حالة التوثيق

تم تنظيم وتوثيق متطلبات المشروع ضمن مستودع GitHub.
