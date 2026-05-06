


هذا الملف هو **"وثيقة التوصيف الشاملة (Master Blueprint)"** المخصصة لتوجيه أي AI Agent (أو مبرمج) لإكمال تطوير المشروع بدقة واحترافية وبدون أي تشتت.

يمكنك نسخ هذا المحتوى بالكامل وإعطائه للـ AI Agent ليقوم ببناء النظام خطوة بخطوة.

---

# 🚀 وثيقة توصيف مشروع: أتمتة الاستعلامات والشكاوى والفواتير
**Project Master Blueprint for AI Agent**

## 1. نظرة عامة على المشروع (Project Overview)
تطبيق ويب حكومي خدمي يهدف إلى أتمتة المعاملات الورقية (تقديم الشكاوى، طلب الاستعلامات وإصدار الوثائق، سداد الفواتير). يعتمد النظام على بنية أدوار متعددة (مواطن، موظف، مدير)، بحيث يمتلك المواطن واجهة أمامية (Frontend) خاصة به، بينما يدار النظام من قبل الإدارة والموظفين عبر لوحات تحكم منفصلة (Filament Multi-Panel Backend).

## 2. التقنيات المستخدمة (Tech Stack)
*   **البيئة الخلفية (Backend):** Laravel 12 (PHP 8.2+).
*   **لوحات الإدارة (Admin/Employee Panels):** Filament v3 (Multi-Panel).
*   **الواجهة الأمامية للمواطن (Frontend):** Blade Templates, HTML5, CSS3, Tailwind CSS, Alpine.js, Vanilla JavaScript.
*   **قاعدة البيانات (Database):** MySQL / MariaDB.

---

## 3. مخطط قاعدة البيانات النهائي (DBML)
*(أيها الـ AI Agent، يجب الالتزام الصارم بهذا المخطط والعلاقات عند إنشاء النماذج وملفات التهجير).*

```dbml
Enum status_types { pending, processing, completed, rejected }
Enum bill_status { unpaid, paid }

Table roles {
  id bigint [primary key, increment]
  name varchar[unique, note: 'citizen, employee, admin']
  created_at timestamp
  updated_at timestamp
}

Table users {
  id bigint[primary key, increment]
  national_id varchar[unique, note: 'الرقم الوطني للمواطن']
  role_id bigint [ref: > roles.id]
  name varchar
  email varchar[unique]
  password varchar
  phone varchar [null]
  created_at timestamp
  updated_at timestamp
}

Table complaint_types {
  id bigint [primary key, increment]
  name varchar
  description text [null]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table complaints {
  id bigint [primary key, increment]
  citizen_id bigint [ref: > users.id]
  type_id bigint [ref: > complaint_types.id]
  assigned_to bigint [null, ref: > users.id]
  description text
  status status_types [default: 'pending']
  internal_notes text [null]
  created_at timestamp
  updated_at timestamp
}

Table complaint_attachments {
  id bigint [primary key, increment]
  complaint_id bigint [ref: > complaints.id]
  file_path varchar
  file_type varchar
  created_at timestamp
}

Table inquiry_types {
  id bigint[primary key, increment]
  name varchar
  description text [null]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table inquiries {
  id bigint[primary key, increment]
  citizen_id bigint [ref: > users.id]
  type_id bigint [ref: > inquiry_types.id]
  assigned_to bigint[null, ref: > users.id]
  status status_types [default: 'pending']
  result_text text [null]
  result_file_path varchar [null]
  created_at timestamp
  updated_at timestamp
}

Table bills {
  id bigint [primary key, increment]
  citizen_id bigint[ref: > users.id]
  bill_type varchar
  amount decimal(10, 2)
  status bill_status [default: 'unpaid']
  due_date date
  paid_at timestamp[null]
  transaction_id varchar [null]
  created_at timestamp
  updated_at timestamp
}

Table notifications {
  id bigint [primary key, increment]
  user_id bigint[ref: > users.id]
  title varchar
  message text
  is_read boolean [default: false]
  created_at timestamp
}

Table system_logs {
  id bigint [primary key, increment]
  user_id bigint [ref: > users.id]
  action varchar
  model_type varchar
  model_id bigint
  old_value json [null]
  new_value json [null]
  created_at timestamp
}
```

---

## 4. مواصفات لوحات التحكم (Filament Multi-Panel Specs)

### أ. لوحة مدير النظام (Admin Panel) - المسار: `/admin`
*   **الصلاحية:** للمستخدمين الذين يمتلكون `role_id` يطابق الـ `admin`.
*   **اللون الأساسي:** `Color::Amber` (أو أي لون مميز).
*   **الـ Resources المطلوبة:**
    1.  `RoleResource`: لإدارة الصلاحيات (للقراءة فقط، أو تحكم كامل).
    2.  `UserResource`: إدارة (المواطنين، الموظفين، المدراء). تعيين الصلاحيات.
    3.  `ComplaintTypeResource` & `InquiryTypeResource`: إضافة وتفعيل/إلغاء تفعيل أنواع الخدمات.
    4.  `ComplaintResource`: عرض جميع الشكاوى، إمكانية إسناد الشكوى لموظف معين (`assigned_to`).
        *   **Relation Managers:** `AttachmentsRelationManager` لرؤية مرفقات الشكوى.
    5.  `InquiryResource`: عرض جميع الاستعلامات وإسنادها.
    6.  `BillResource`: إدارة الفواتير، رفع فواتير جديدة للمواطنين، مراقبة حالات الدفع.
    7.  `SystemLogResource`: (للقراءة فقط Read-Only) لمراقبة تصرفات الموظفين.
*   **الـ Widgets المطلوبة:**
    1.  `StatsOverview`: إجمالي المستخدمين، إجمالي الشكاوى، إجمالي الفواتير المدفوعة.
    2.  `ComplaintsChart`: رسم بياني خطي للشكاوى الواردة حسب الشهر.
    3.  `LatestComplaints`: جدول بآخر 5 شكاوى واردة.

### ب. لوحة الموظف (Employee Panel) - المسار: `/employee`
*   **الصلاحية:** للمستخدمين الذين يمتلكون `role_id` يطابق `employee` أو `admin`.
*   **اللون الأساسي:** `Color::Blue`.
*   **الـ Resources المطلوبة (صلاحيات مقيدة):**
    1.  `ComplaintResource`: لا يمكنه الحذف. يمكنه فقط التعديل على (الحالة `status`، إدخال `internal_notes`). يمكنه فلترة الشكاوى المسندة إليه فقط (`assigned_to == auth()->id()`).
        *   **Relation Managers:** `AttachmentsRelationManager` (عرض وتنزيل المرفقات فقط).
    2.  `InquiryResource`: لا يمكنه الحذف. التعديل مسموح فقط على (الحالة `status`، النتيجة النصية `result_text`، رفع ملف النتيجة `result_file_path`).
*   **الـ Widgets المطلوبة:**
    1.  `EmployeeStatsOverview`: عدد الطلبات المعلقة المسندة إليه، عدد الطلبات المنجزة من قبله.

---

## 5. مواصفات واجهة المواطن (Frontend Specs)
*   **التصميم:** متجاوب (Mobile-first) يعتمد على Tailwind CSS.
*   **التفاعلية:** استخدام Alpine.js للقوائم المنسدلة (Dropdowns)، النوافذ المنبثقة (Modals)، التبويبات (Tabs)، وإخفاء/إظهار التنبيهات. (تجنب استخدام jQuery تماماً).
*   **الصفحات والمكونات (Blade Views):**
    1.  `auth.login` / `auth.register`: تسجيل دخول وإنشاء حساب (إدخال الرقم الوطني 11 خانة).
    2.  `layouts.app`: مخطط الصفحة الرئيسية يحتوي على Navbar (أيقونة الإشعارات، زر تسجيل الخروج) و Sidebar إن لزم الأمر.
    3.  `home.index`: لوحة تحكم المواطن (إحصائيات بسيطة وآخر النشاطات).
    4.  `complaints.index` / `create` / `show`: رفع الشكوى مع دعم رفع صور/ملفات.
    5.  `inquiries.index` / `create` / `show`: طلب خدمة واستعراض ملف النتيجة وتحميله.
    6.  `bills.index` / `pay`: جدول الفواتير مع زر "دفع" يفتح Modal بسيط لمحاكاة إدخال بيانات "شام كاش".
    7.  `notifications.index`: عرض الإشعارات مع زر Mark as Read (عبر Ajax أو إعادة تحميل بسيطة).

---

## 6. البيانات الوهمية (Database Seeding)
يجب بناء فئات Seeder متكاملة لتسهيل التجربة:
1.  `RoleSeeder`: إدخال (admin, employee, citizen).
2.  `UserSeeder`: إنشاء 1 Admin، 2 Employees، و 5 Citizens.
3.  `TypesSeeder`: إدخال أنواع شكاوى (مياه، كهرباء، نظافة) وأنواع استعلامات (بيان عائلي، لا حكم عليه).
4.  `DummyDataSeeder`: توليد 20 شكوى، 20 استعلام، و 20 فاتورة موزعة عشوائياً على المواطنين.

---

## 7. خطة التنفيذ (A-to-Z Execution Plan)
*أيها الـ AI، قم بتنفيذ هذا المشروع بالترتيب الصارم التالي:*

### Phase 1: Database & Core Models
1. Create models and migrations perfectly aligned with the DBML provided.
2. Ensure `Role` migration runs before `User` migration.
3. Add relationships and `$fillable` / `casts` properties in Models.
4. Implement polymorphic relation for `SystemLog`.

### Phase 2: User Roles & Filament Setup
1. Install Filament v3 panels.
2. Modify `User` model to implement `FilamentUser`.
3. Create `canAccessPanel()` logic handling roles IDs.
4. Customize `AdminPanelProvider` and `EmployeePanelProvider` (Colors, paths, middleware).

### Phase 3: Filament Resources (Backend Development)
1. Build Admin Resources (`RoleResource`, `UserResource`, `TypeResources`, etc.).
2. Build Employee Resources with Scopes (Employees see only what's assigned to them or general pending).
3. Build Relation Managers for Attachments.
4. Build Dashboard Widgets for both panels.

### Phase 4: Frontend Development (Citizen Interface)
1. Set up Tailwind CSS & Alpine.js via Vite.
2. Build Auth Controllers & Blade views.
3. Build logic and UI for Complaints (with file upload logic).
4. Build logic and UI for Inquiries (downloading generated files).
5. Build logic and UI for Bills (Payment Mockup).

### Phase 5: Business Logic & Observers
1. Implement Laravel Observers: 
   * When a complaint/inquiry status changes, automatically create a `Notification` for the citizen.
   * When any model is updated/deleted in Filament, automatically create a `SystemLog` entry.

### Phase 6: Seeders & Final Polish
1. Write the `DatabaseSeeder` integrating `RoleSeeder`, `UserSeeder`, etc.
2. Test the entire flow (Login as Admin -> Assign task -> Login as Employee -> Resolve task -> Login as Citizen -> View Notification & Result).

---
**End of Blueprint. AI Agent, start executing Phase 1 now!**