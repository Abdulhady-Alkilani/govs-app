# 🚀 خطة تنفيذ مشروع: أتمتة الاستعلامات والشكاوى والفواتير الحكومية

## تحليل الوضع الحالي للمشروع

### ✅ ما تم إنجازه (Backend Foundation)

| المكون | الحالة | ملاحظات |
|--------|--------|---------|
| **Laravel 12 + Filament v3** | ✅ مُثبّت | `composer.json` يحتوي على `filament/filament: 3.3` |
| **Tailwind CSS v4 + Vite** | ✅ مُثبّت | `@tailwindcss/vite` في `package.json` |
| **10 Models** | ✅ موجودة | `User`, `Role`, `Complaint`, `ComplaintType`, `ComplaintAttachment`, `Inquiry`, `InquiryType`, `Bill`, `Notification`, `SystemLog` |
| **12 Migrations** | ✅ موجودة | جميع الجداول المطلوبة حسب DBML |
| **User Model ← FilamentUser** | ✅ مُنفّذ | `canAccessPanel()` مُعرّف لكلا اللوحتين |
| **Panel Providers** | ✅ موجودان | `AdminPanelProvider` و `EmployeePanelProvider` |
| **Web Routes** | ✅ مكتملة | مسارات Auth, Home, Complaints, Inquiries, Bills, Notifications |
| **6 Controllers** | ✅ موجودة | `AuthController`, `HomeController`, `ComplaintController`, `InquiryController`, `BillController`, `NotificationController` |
| **Auth System** | ✅ يعمل | تسجيل الدخول/الخروج/إنشاء حساب بالرقم الوطني |

### ❌ ما لم يتم إنجازه (الأجزاء المفقودة)

| المكون | الحالة | الأولوية |
|--------|--------|----------|
| **Filament Resources (Admin)** | ❌ غير موجود | 🔴 عالية |
| **Filament Resources (Employee)** | ❌ غير موجود | 🔴 عالية |
| **Filament Widgets (Dashboard)** | ❌ غير موجود | 🔴 عالية |
| **Relation Managers (Attachments)** | ❌ غير موجود | 🔴 عالية |
| **Blade Views (واجهة المواطن)** | ❌ فقط `welcome.blade.php` | 🔴 عالية |
| **Layout Template** | ❌ غير موجود | 🔴 عالية |
| **Laravel Observers** | ❌ غير موجود | 🟡 متوسطة |
| **Database Seeders** | ❌ فقط seeder فارغ | 🟡 متوسطة |
| **Alpine.js** | ❌ غير مُضاف | 🟡 متوسطة |

### ⚠️ مشاكل تحتاج إصلاح

- **⚠️ مشكلة ترتيب الهجرات (Migration Order):** ملف `roles` migration بتاريخ `2026_04_03...` يعمل **بعد** ملف `users` migration بتاريخ `0001_01_01...`، لكن `users` يحتوي على `foreignId('role_id')->constrained('roles')`. هذا سيسبب خطأ عند التنفيذ لأن جدول `roles` لم يُنشأ بعد.

- **⚠️ لون لوحة الموظف خاطئ:** المواصفات تطلب `Color::Blue` لكن الكود الحالي يستخدم `Color::Amber`.

- **ℹ️ مجلد `app/Filament` فارغ تماماً:** لا يوجد أي Resources أو Widgets أو Pages أو Relation Managers.

---

## المرحلة 1: إصلاح البنية الأساسية (Database & Core Fixes)

> **⚠️ مهم:** يجب تنفيذ هذه المرحلة أولاً لضمان عمل قاعدة البيانات بشكل صحيح قبل أي شيء آخر.

### إصلاح ترتيب الهجرات

#### [MODIFY] `database/migrations/0001_01_01_000000_create_users_table.php`
- نقل إنشاء جدول `roles` إلى داخل هذا الملف ليكون **قبل** جدول `users`
- أو إعادة تسمية ملف `roles` migration ليعمل قبل `users`

#### [DELETE] `database/migrations/2026_04_03_140148_create_roles_table.php`
- حذف هذا الملف بعد نقل محتواه إلى ملف users migration

### إصلاح لون لوحة الموظف

#### [MODIFY] `app/Providers/Filament/EmployeePanelProvider.php`
- تغيير `Color::Amber` → `Color::Blue` حسب المواصفات

---

## المرحلة 2: Filament Resources — لوحة المدير (Admin Panel)

> المسار الأساسي: `app/Filament/Resources/`

### Resources المطلوبة (7 ملفات رئيسية + Pages)

#### [NEW] `app/Filament/Resources/RoleResource.php`
- عرض الأدوار (admin, employee, citizen)
- جدول بالأعمدة: `id`, `name`, `users_count`
- Form بسيط: حقل `name` — أو للقراءة فقط (View-Only)

#### [NEW] `app/Filament/Resources/UserResource.php`
- إدارة كاملة (CRUD) للمستخدمين
- **Table Columns:** `national_id`, `name`, `email`, `phone`, `role.name`, `created_at`
- **Form Fields:** `national_id`, `name`, `email`, `password`, `phone`, `role_id` (Select)
- **Filters:** فلتر حسب الدور (citizen/employee/admin)

#### [NEW] `app/Filament/Resources/ComplaintTypeResource.php`
- إدارة أنواع الشكاوى (CRUD)
- **Table:** `name`, `description`, `is_active` (Toggle), `complaints_count`
- **Form:** `name`, `description`, `is_active` (Toggle)

#### [NEW] `app/Filament/Resources/InquiryTypeResource.php`
- إدارة أنواع الاستعلامات (CRUD)
- **Table:** `name`, `description`, `is_active` (Toggle), `inquiries_count`
- **Form:** `name`, `description`, `is_active` (Toggle)

#### [NEW] `app/Filament/Resources/ComplaintResource.php`
- عرض جميع الشكاوى مع إمكانية الإسناد
- **Table:** `id`, `citizen.name`, `type.name`, `status` (Badge), `assignee.name`, `created_at`
- **Form:** `citizen_id` (مخفي/للقراءة)، `type_id`، `assigned_to` (Select للموظفين)، `status`، `description`، `internal_notes`
- **Filters:** حسب الحالة، حسب النوع، حسب الموظف المسند

#### [NEW] `app/Filament/Resources/ComplaintResource/RelationManagers/AttachmentsRelationManager.php`
- عرض مرفقات الشكوى (صور/PDF)
- **Table:** `file_path`, `file_type`, `created_at`
- إمكانية العرض والتنزيل

#### [NEW] `app/Filament/Resources/InquiryResource.php`
- عرض جميع الاستعلامات مع إمكانية الإسناد
- **Table:** `id`, `citizen.name`, `type.name`, `status` (Badge), `assignee.name`, `created_at`
- **Form:** `citizen_id`، `type_id`، `assigned_to`، `status`، `result_text`، `result_file_path` (FileUpload)

#### [NEW] `app/Filament/Resources/BillResource.php`
- إدارة كاملة للفواتير
- **Table:** `id`, `citizen.name`, `bill_type`, `amount`, `status` (Badge)، `due_date`, `paid_at`, `transaction_id`
- **Form:** `citizen_id` (Select)، `bill_type`، `amount`، `status`، `due_date`
- إمكانية إنشاء فواتير جديدة للمواطنين

#### [NEW] `app/Filament/Resources/SystemLogResource.php`
- **للقراءة فقط (View Only)** — بدون Create/Edit/Delete
- **Table:** `user.name`, `action`, `model_type`, `model_id`, `created_at`
- **ViewPage:** عرض `old_value` و `new_value` بصيغة JSON

### Filament Pages لكل Resource

لكل Resource أعلاه، سيتم إنشاء المجلد `Pages/`:
```
ComplaintResource/Pages/ListComplaints.php
ComplaintResource/Pages/CreateComplaint.php
ComplaintResource/Pages/EditComplaint.php
...
```

---

## المرحلة 3: Filament Resources — لوحة الموظف (Employee Panel)

> المسار الأساسي: `app/Filament/Employee/Resources/`

### Resources المطلوبة (2 ملفات رئيسية + Pages)

#### [NEW] `app/Filament/Employee/Resources/ComplaintResource.php`
- **Scope:** الشكاوى المسندة للموظف الحالي فقط (`assigned_to == auth()->id()`)
- **لا حذف** — `canDelete: false`
- **التعديل مسموح فقط على:** `status`, `internal_notes`
- **Relation Manager:** `AttachmentsRelationManager` (عرض وتنزيل فقط — بدون إضافة/حذف)

#### [NEW] `app/Filament/Employee/Resources/ComplaintResource/RelationManagers/AttachmentsRelationManager.php`
- عرض فقط — بدون أزرار Create/Delete

#### [NEW] `app/Filament/Employee/Resources/InquiryResource.php`
- **Scope:** الاستعلامات المسندة للموظف الحالي فقط
- **لا حذف** — `canDelete: false`
- **التعديل مسموح فقط على:** `status`, `result_text`, `result_file_path` (FileUpload لرفع ملف النتيجة)

### Filament Pages (Employee)
```
Employee/Resources/ComplaintResource/Pages/ListComplaints.php
Employee/Resources/ComplaintResource/Pages/EditComplaint.php
Employee/Resources/InquiryResource/Pages/ListInquiries.php
Employee/Resources/InquiryResource/Pages/EditInquiry.php
```

---

## المرحلة 4: Filament Widgets (لوحات المعلومات)

### Admin Dashboard Widgets

#### [NEW] `app/Filament/Widgets/StatsOverview.php`
- **إجمالي المستخدمين** — `User::count()`
- **إجمالي الشكاوى** — `Complaint::count()`
- **إجمالي الفواتير المدفوعة** — `Bill::where('status', 'paid')->count()`
- مع أيقونات وألوان مميزة

#### [NEW] `app/Filament/Widgets/ComplaintsChart.php`
- **رسم بياني خطي** — الشكاوى الواردة حسب الشهر (آخر 12 شهر)
- استخدام `Filament\Widgets\ChartWidget`

#### [NEW] `app/Filament/Widgets/LatestComplaints.php`
- **جدول بآخر 5 شكاوى واردة**
- الأعمدة: رقم الشكوى، اسم المواطن، النوع، الحالة، التاريخ

### Employee Dashboard Widgets

#### [NEW] `app/Filament/Employee/Widgets/EmployeeStatsOverview.php`
- **عدد الطلبات المعلقة** المسندة للموظف الحالي
- **عدد الطلبات المنجزة** من قبل الموظف الحالي

---

## المرحلة 5: واجهة المواطن الأمامية (Frontend — Blade + Tailwind + Alpine.js)

> **⚠️ مهم:** يجب إضافة `Alpine.js` إلى المشروع أولاً عبر CDN أو npm، ثم بناء جميع الصفحات.

### إعداد البنية التحتية

#### [MODIFY] `resources/js/app.js`
- إضافة `import Alpine from 'alpinejs'`

#### أو إضافة Alpine.js عبر CDN في Layout

### Templates و Layouts

#### [NEW] `resources/views/layouts/app.blade.php`
- **Navbar:** شعار التطبيق، اسم المستخدم، أيقونة الإشعارات (مع عداد)، زر تسجيل الخروج
- **Sidebar (اختياري):** روابط سريعة للأقسام
- **Content Area:** `@yield('content')`
- **Footer:** حقوق الملكية
- **تصميم RTL** متجاوب (Mobile-first) باستخدام Tailwind CSS
- استخدام Alpine.js للقوائم المنسدلة وإظهار/إخفاء الـ Sidebar

#### [NEW] `resources/views/components/alert.blade.php`
- مكون تنبيه متعدد الأنواع (success, error, warning, info)

#### [NEW] `resources/views/components/status-badge.blade.php`
- Badge ملون حسب حالة الطلب (pending=أصفر, processing=أزرق, completed=أخضر, rejected=أحمر)

### صفحات المصادقة (Auth)

#### [NEW] `resources/views/auth/login.blade.php`
- نموذج تسجيل دخول (الرقم الوطني + كلمة المرور)
- تصميم مركزي أنيق مع خلفية متدرجة

#### [NEW] `resources/views/auth/register.blade.php`
- نموذج إنشاء حساب (الرقم الوطني 11 خانة، الاسم، البريد، الهاتف، كلمة المرور + التأكيد)
- Validation مرئي مع رسائل خطأ عربية

### لوحة تحكم المواطن

#### [NEW] `resources/views/home/index.blade.php`
- 3 بطاقات إحصائية: (عدد الشكاوى، عدد الاستعلامات، الفواتير غير المدفوعة)
- جدول آخر الشكاوى (3 شكاوى)
- جدول آخر الفواتير (3 فواتير)
- روابط سريعة للأقسام

### صفحات الشكاوى

#### [NEW] `resources/views/complaints/index.blade.php`
- جدول الشكاوى مع Pagination
- الأعمدة: الرقم، النوع، الحالة (Badge)، التاريخ، زر عرض التفاصيل
- زر "تقديم شكوى جديدة"

#### [NEW] `resources/views/complaints/create.blade.php`
- نموذج تقديم شكوى:
  - اختيار نوع الشكوى (Select مع أنواع مفعلة فقط)
  - وصف الشكوى (Textarea)
  - رفع مرفقات (Multiple Files — صور/PDF) مع Preview باستخدام Alpine.js
- رسالة نجاح بعد الإرسال

#### [NEW] `resources/views/complaints/show.blade.php`
- عرض تفاصيل الشكوى: النوع، الوصف، الحالة، التاريخ
- عرض المرفقات (صور بـ lightbox / روابط تحميل PDF)
- حالة المعالجة مع Timeline بسيط

### صفحات الاستعلامات

#### [NEW] `resources/views/inquiries/index.blade.php`
- جدول الاستعلامات مع Pagination
- الأعمدة: الرقم، النوع، الحالة، التاريخ، زر عرض

#### [NEW] `resources/views/inquiries/create.blade.php`
- نموذج طلب استعلام: اختيار نوع الاستعلام (Select) + زر إرسال

#### [NEW] `resources/views/inquiries/show.blade.php`
- عرض تفاصيل الاستعلام
- إذا تم الإنجاز: عرض النتيجة النصية `result_text`
- إذا يوجد ملف نتيجة: زر تحميل `result_file_path`

### صفحات الفواتير

#### [NEW] `resources/views/bills/index.blade.php`
- جدول الفواتير مع Pagination
- الأعمدة: النوع، المبلغ، الحالة (Badge)، تاريخ الاستحقاق، تاريخ الدفع، رقم العملية
- زر "دفع" بجانب الفواتير غير المدفوعة

#### [NEW] `resources/views/bills/pay.blade.php`
- **محاكاة صفحة دفع "شام كاش"**
- عرض تفاصيل الفاتورة (النوع + المبلغ)
- Modal بسيط لإدخال بيانات الدفع الوهمية (رقم الهاتف + رمز التحقق)
- استخدام Alpine.js لإظهار/إخفاء الـ Modal
- زر تأكيد الدفع يرسل POST request

### صفحات الإشعارات

#### [NEW] `resources/views/notifications/index.blade.php`
- قائمة الإشعارات مع Pagination
- تمييز الإشعارات غير المقروءة بلون مختلف
- زر "تحديد كمقروء" لكل إشعار
- زر "تحديد الكل كمقروء" في الأعلى

---

## المرحلة 6: Business Logic — Observers

> هذه المرحلة تضيف السلوك التلقائي للنظام عند تغيير حالات الطلبات.

#### [NEW] `app/Observers/ComplaintObserver.php`
- **`updated()`:** عند تغيير `status` → إنشاء `Notification` تلقائي للمواطن صاحب الشكوى
  - مثال: "تم تحديث حالة شكواك رقم #X إلى: قيد المعالجة"

#### [NEW] `app/Observers/InquiryObserver.php`
- **`updated()`:** عند تغيير `status` → إنشاء `Notification` تلقائي للمواطن صاحب الاستعلام

#### [NEW] `app/Observers/SystemLogObserver.php` (أو Trait)
- تسجيل تلقائي في `system_logs` عند أي **تعديل أو حذف** يتم من لوحة Filament
- حفظ `old_value` و `new_value` كـ JSON

#### [MODIFY] `app/Providers/AppServiceProvider.php`
- تسجيل جميع الـ Observers في `boot()`

---

## المرحلة 7: Database Seeders

#### [NEW] `database/seeders/RoleSeeder.php`
- إنشاء 3 أدوار: `admin`, `employee`, `citizen`

#### [NEW] `database/seeders/UserSeeder.php`
- 1 Admin: `admin@gov.sy` / `password`
- 2 Employees: `emp1@gov.sy`, `emp2@gov.sy` / `password`
- 5 Citizens: بأرقام وطنية وهمية (11 خانة) / `password`

#### [NEW] `database/seeders/TypesSeeder.php`
- أنواع شكاوى: مياه، كهرباء، نظافة، طرق، صرف صحي
- أنواع استعلامات: بيان عائلي، لا حكم عليه، وثيقة ملكية

#### [NEW] `database/seeders/DummyDataSeeder.php`
- 20 شكوى عشوائية موزعة على المواطنين
- 20 استعلام عشوائي
- 20 فاتورة عشوائية (مزيج بين مدفوع وغير مدفوع)
- 10 إشعارات عشوائية

#### [MODIFY] `database/seeders/DatabaseSeeder.php`
- استدعاء جميع الـ Seeders بالترتيب الصحيح:
  1. `RoleSeeder`
  2. `UserSeeder`
  3. `TypesSeeder`
  4. `DummyDataSeeder`

---

## ملخص الملفات المطلوبة

| المرحلة | ملفات جديدة | ملفات معدّلة | ملفات محذوفة |
|---------|-------------|-------------|-------------|
| Phase 1: إصلاحات | 0 | 2 | 1 |
| Phase 2: Admin Resources | ~21 | 0 | 0 |
| Phase 3: Employee Resources | ~8 | 0 | 0 |
| Phase 4: Widgets | 4 | 0 | 0 |
| Phase 5: Frontend Views | ~14 | 1-2 | 0 |
| Phase 6: Observers | 3 | 1 | 0 |
| Phase 7: Seeders | 4 | 1 | 0 |
| **المجموع** | **~54 ملف** | **~6 ملفات** | **1 ملف** |

---

## خطة التحقق (Verification Plan)

### اختبار تلقائي
```bash
# 1. تشغيل الهجرات من الصفر
php artisan migrate:fresh

# 2. تشغيل الـ Seeders
php artisan db:seed

# 3. تشغيل التطبيق
composer dev
```

### اختبار يدوي (سيناريو كامل)
1. **تسجيل دخول كـ Admin** → `/admin` → التحقق من الـ Dashboard والـ Resources
2. **إسناد شكوى لموظف** → التأكد من ظهورها في لوحة الموظف
3. **تسجيل دخول كـ Employee** → `/employee` → معالجة الشكوى (تغيير الحالة)
4. **تسجيل دخول كـ Citizen** → `/home` → التحقق من ظهور الإشعار والحالة المحدثة
5. **اختبار الدفع** → فتح فاتورة غير مدفوعة → محاكاة الدفع → التحقق من `transaction_id`

---

## أسئلة مفتوحة

- **1. هل تريد استخدام Alpine.js عبر npm أم CDN؟**
  - **npm:** `npm install alpinejs` ثم import في `app.js` — أفضل لبيئة الإنتاج
  - **CDN:** إضافة `<script>` في Layout — أسرع للتطوير

- **2. هل تريد تشغيل `php artisan migrate:fresh --seed` الآن لإعادة بناء القاعدة؟**
  - هذا سيحذف جميع البيانات الحالية.

- **3. هل تريد أن تكون واجهة المواطن RTL بالكامل (عربية) أم ثنائية اللغة؟**
