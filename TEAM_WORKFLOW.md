# Team Workflow

## تقسيم مقترح للفريق

### عضو 1

- Students
- Admissions
- Programs
- Courses
- Plans
- Enrollments

### عضو 2

- Supervisors
- Research
- Theses
- Committees
- Graduation

### عضو 3

- Auth
- Dashboard
- Reports
- Lookup
- Notifications
- UI / CSS / JavaScript

## قواعد الدمج

- لا تعدل ملفات Module آخر بدون تنسيق.
- منطق الأعمال يذهب إلى Service.
- SQL يذهب إلى Repository.
- Controller يستقبل الطلب ويستدعي Service فقط.
- View لا تحتوي SQL.
- لا تضع HTML داخل Repository أو Service.
- أي عملية متعددة الخطوات تستخدم Transaction.
