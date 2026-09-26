USE uniflow_graduate;

INSERT IGNORE INTO users (full_name, email, password, role) VALUES
('مدير النظام', 'admin@uniflow.local', '$2y$12$5RAsz0fzpEJsNCIVe37JneUxzmqzuMmO7GDHtxFSQrqU0Ox20Gv1a', 'ADMIN'),
('موظف الدراسات العليا', 'staff@uniflow.local', '$2y$12$5RAsz0fzpEJsNCIVe37JneUxzmqzuMmO7GDHtxFSQrqU0Ox20Gv1a', 'STAFF'),
('د. أحمد محمد', 'ahmad@uniflow.local', '$2y$12$5RAsz0fzpEJsNCIVe37JneUxzmqzuMmO7GDHtxFSQrqU0Ox20Gv1a', 'SUPERVISOR'),
('د. سارة علي', 'sara@uniflow.local', '$2y$12$5RAsz0fzpEJsNCIVe37JneUxzmqzuMmO7GDHtxFSQrqU0Ox20Gv1a', 'COMMITTEE');

INSERT IGNORE INTO departments (name, code) VALUES
('علوم الحاسوب', 'CS'),
('هندسة البرمجيات', 'SE');

INSERT IGNORE INTO programs (department_id, name, degree, duration_years, required_credits) VALUES
(1, 'ماجستير علوم الحاسوب', 'MASTER', 2.0, 6),
(1, 'دكتوراه علوم الحاسوب', 'PHD', 4.0, 6),
(2, 'ماجستير هندسة البرمجيات', 'MASTER', 2.0, 6);

INSERT IGNORE INTO academic_years (name, is_current) VALUES
('2025/2026', 0),
('2026/2027', 1),
('2027/2028', 0);

INSERT IGNORE INTO applicants
(application_no, full_name, phone, email, national_id, previous_degree, previous_gpa)
VALUES
('APP-2026-001', 'علي أحمد صالح', '967700000001', 'ali@example.local', '10001', 'بكالوريوس علوم حاسوب', 3.55),
('APP-2026-002', 'محمد عبدالله حسن', '967700000002', 'mohammed@example.local', '10002', 'بكالوريوس هندسة برمجيات', 3.30);

INSERT IGNORE INTO students
(student_no, full_name, national_id, phone, email, program_id, admission_date, status)
VALUES
('PG-2026-001', 'نبراس الحذيفي', '20001', '967711111111', 'nbras@example.local', 1, '2026-09-01', 'ACTIVE'),
('PG-2026-002', 'المعتصم الشلح', '20002', '967722222222', 'moatasem@example.local', 1, '2026-09-01', 'ACTIVE'),
('PG-2026-003', 'هيثم البخيتي', '20003', '967733333333', 'haitham@example.local', 3, '2026-09-01', 'ACTIVE'),
('PG-2025-004', 'نزار المصنف', '20004', '967744444444', 'nizar@example.local', 1, '2025-09-01', 'GRADUATED');

INSERT IGNORE INTO supervisors (user_id, academic_rank, specialization) VALUES
(3, 'أستاذ مشارك', 'الذكاء الاصطناعي وقواعد البيانات');

INSERT IGNORE INTO student_supervisors (student_id, supervisor_id, supervisor_role, assigned_at) VALUES
(1, 1, 'PRIMARY', '2026-09-02'),
(2, 1, 'PRIMARY', '2026-09-02');

INSERT IGNORE INTO courses (program_id, code, name, credits, is_required) VALUES
(1, 'CS501', 'مناهج البحث العلمي', 3, 1),
(1, 'CS502', 'قواعد البيانات المتقدمة', 3, 1),
(1, 'CS503', 'هندسة البرمجيات المتقدمة', 3, 0),
(3, 'SE501', 'هندسة البرمجيات الحديثة', 3, 1);

INSERT INTO applications (applicant_id, program_id, application_type, status, notes)
SELECT 1, 1, 'NEW', 'PENDING', 'بانتظار استكمال المستندات'
WHERE NOT EXISTS (SELECT 1 FROM applications WHERE applicant_id = 1 AND program_id = 1 AND application_type = 'NEW');

INSERT INTO applications (applicant_id, program_id, application_type, status, notes)
SELECT 2, 3, 'NEW', 'APPROVED', 'تمت الموافقة الأولية'
WHERE NOT EXISTS (SELECT 1 FROM applications WHERE applicant_id = 2 AND program_id = 3 AND application_type = 'NEW');

INSERT IGNORE INTO study_plans (student_id, academic_year_id, status) VALUES
(1, 2, 'APPROVED'),
(2, 2, 'IN_PROGRESS'),
(3, 2, 'DRAFT');

INSERT IGNORE INTO student_courses (student_id, course_id, study_plan_id, grade, status) VALUES
(1, 1, 1, 88, 'PASSED'),
(1, 2, 1, 91, 'PASSED'),
(2, 1, 2, 82, 'PASSED'),
(2, 2, 2, 79, 'PASSED'),
(3, 4, 3, NULL, 'REGISTERED');

INSERT INTO research_proposals
(student_id, supervisor_id, title, abstract, status, submitted_at)
SELECT 1, 1, 'نظام ذكي لإدارة المعرفة الجامعية', 'دراسة تطبيقية في إدارة المعرفة.', 'APPROVED', '2026-09-05'
WHERE NOT EXISTS (SELECT 1 FROM research_proposals WHERE student_id = 1 AND title = 'نظام ذكي لإدارة المعرفة الجامعية');

INSERT INTO research_proposals
(student_id, supervisor_id, title, abstract, status, submitted_at)
SELECT 2, 1, 'تحسين استرجاع المعلومات في الأنظمة الجامعية', 'بحث في تقنيات الاسترجاع.', 'UNDER_REVIEW', '2026-09-06'
WHERE NOT EXISTS (SELECT 1 FROM research_proposals WHERE student_id = 2 AND title = 'تحسين استرجاع المعلومات في الأنظمة الجامعية');

INSERT INTO theses (student_id, proposal_id, title, start_date, status)
SELECT 1, 1, 'نظام ذكي لإدارة المعرفة الجامعية', '2026-09-10', 'DEFENDED'
WHERE NOT EXISTS (SELECT 1 FROM theses WHERE student_id = 1);

INSERT INTO theses (student_id, proposal_id, title, start_date, status)
SELECT 2, 2, 'تحسين استرجاع المعلومات في الأنظمة الجامعية', '2026-09-11', 'READY_FOR_DEFENSE'
WHERE NOT EXISTS (SELECT 1 FROM theses WHERE student_id = 2);

INSERT IGNORE INTO committees (thesis_id, committee_type, meeting_date, status) VALUES
(1, 'DEFENSE', '2026-09-20', 'COMPLETED'),
(2, 'DEFENSE', '2026-10-15', 'PLANNED');

INSERT IGNORE INTO committee_members (committee_id, user_id, member_role) VALUES
(1, 3, 'مشرف'),
(1, 4, 'ممتحن'),
(2, 3, 'مشرف'),
(2, 4, 'ممتحن');

INSERT IGNORE INTO defenses (thesis_id, defense_date, location, result, notes) VALUES
(1, '2026-09-20', 'قاعة الدراسات العليا', 'PASS', 'مناقشة ناجحة واستيفاء المتطلبات.');
