# تقرير شامل عن مشروع البوابة الحكومية الإلكترونية (gov-app)

> تاريخ التقرير: 2026-06-13
> لغة التقرير: العربية
> نوع المشروع: تطبيق ويب حكومي متكامل (Government e-Services Portal)

---

## جدول المحتويات

1. [نظرة عامة على المشروع](#1-نظرة-عامة-على-المشروع)
2. [التقنيات المستخدمة (Tech Stack)](#2-التقنيات-المستخدمة-tech-stack)
3. [بنية المشروع (Directory Structure)](#3-بنية-المشروع-directory-structure)
4. [شرح مجلد `app` بالتفصيل](#4-شرح-مجلد-app-بالتفصيل)
   - 4.1 [النماذج (Models)](#41-النماذج-models)
   - 4.2 [وحدات التحكم (Controllers)](#42-وحدات-التحكم-controllers)
   - 4.3 [الخدمات (Services)](#43-الخدمات-services)
   - 4.4 [المراقبون (Observers)](#44-المراقبون-observers)
   - 4.5 [المزودون (Providers)](#45-المزودون-providers)
   - 4.6 [الوسائط (Middleware)](#46-الوسائط-middleware)
   - 4.7 [Filament — لوحة الإدارة والموظفين](#47-filament--لوحة-الإدارة-والموظفين)
5. [قاعدة البيانات (Database & Migrations)](#5-قاعدة-البيانات-database--migrations)
6. [البيانات الأولية (Seeders)](#6-البيانات-الأولية-seeders)
7. [المسارات (Routes)](#7-المسارات-routes)
8. [التهيئة (Configuration)](#8-التهيئة-configuration)
9. [واجهات العرض (Views & Frontend)](#9-واجهات-العرض-views--frontend)
10. [ميزات الذكاء الاصطناعي (AI Features)](#10-ميزات-الذكاء-الاصطناعي-ai-features)
11. [نظام الإشعارات](#11-نظام-الإشعارات)
12. [نظام الصلاحيات والأدوار](#12-نظام-الصلاحيات-والأدوار)
13. [مخطط تدفق العمل (Workflow)](#13-مخطط-تدفق-العمل-workflow)

---

## 1. نظرة عامة على المشروع

### وصف المشروع

المشروع هو **بوابة خدمات حكومية إلكترونية** تتيح للمواطنين التفاعل مع الجهات الحكومية رقمياً، وتشمل الخدمات الرئيسية:

| الخدمة | الوصف |
|--------|-------|
| **الشكاوى** | تقديم ومتابعة الشكاوى مع تصنيف تلقائي بالذكاء الاصطناعي |
| **الاستعلامات** | تقديم طلبات استعلامات حكومية والحصول على النتائج إلكترونياً |
| **الفواتير** | عرض ودفع الفواتير والغرامات الحكومية وإرفاق إشعارات الدفع |
| **الإشعارات** | نظام إشعارات فورية لكل التحديثات |
| **المعالجة الإدارية** | لوحات تحكم منفصلة للمديرين والموظفين عبر Filament |

### الأدوار (Roles) الثلاثة في النظام

```
┌─────────────┐     ┌──────────────┐     ┌─────────────────┐
│   المواطن    │     │    الموظف     │     │     المدير       │
│   (Citizen) │     │  (Employee)  │     │    (Admin)      │
├─────────────┤     ├──────────────┤     ├─────────────────┤
│ - تسجيل/دخول │     │ - معالجة      │     │ - إدارة كاملة    │
│ - تقديم شكوى │     │   الطلبات     │     │   للنظام         │
│ - تقديم استعلام│    │   المسندة     │     │ - إدارة المستخدمين│
│ - دفع الفواتير│    │ - الرد بالـAI  │     │ - الإحصائيات     │
│ - استلام     │     │ - رؤية        │     │ - سجلات النظام   │
│   الإشعارات  │     │   المرفقات    │     │ - لوحات رسمية    │
└─────────────┘     └──────────────┘     └─────────────────┘
      الواجهة الأمامية        لوحة /employee             لوحة /admin
     (Blade + Tailwind)    (Filament Panel)         (Filament Panel)
```

---

## 2. التقنيات المستخدمة (Tech Stack)

### الخلفية (Backend)

| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| **PHP** | ^8.2 | لغة البرمجة الأساسية |
| **Laravel Framework** | ^12.0 | إطار العمل الخلفي |
| **Filament** | 3.3 | لوحات التحكم الإدارية (Admin + Employee) |
| **Filament Notifications** | 3.3 | نظام الإشعارات الداخلية لـ Filament |
| **MySQL** | - | قاعدة البيانات |

### الواجهة الأمامية (Frontend)

| التقنية | الاستخدام |
|---------|-----------|
| **Tailwind CSS** 4.0 | إطار التصميم |
| **Alpine.js** 3.x | التفاعلية في الواجهة (إدارة الحالة، النوافذ المنبثقة) |
| **Vite** 7.x | أداة بناء الأصول (Asset Bundler) |
| **Blade** | محرك القوالب |

### المكتبات الإضافية

| المكتبة | الاستخدام |
|---------|-----------|
| `bezhansalleh/filament-language-switch` | مبدّل اللغة داخل لوحات Filament |
| `laravel/tinker` | واجهة تفاعلية مع التطبيق |
| `fakerphp/faker` | توليد بيانات تجريبية (في وضع التطوير) |
| `pestphp/pest` | إطار الاختبارات |

### الذكاء الاصطناعي

يتصل المشروع بخدمة **LiteLLM Proxy** التي توّلِج الطلبات إلى مزودي نماذج الذكاء الاصطناعي (مثل Gemini)، ويُستخدم النموذج الافتراضي `gemini-3-flash-preview`.

---

## 3. بنية المشروع (Directory Structure)

```
gov-app/
│
├── app/                          ★ الكود الأساسي للتطبيق (مفصل في القسم 4)
│   ├── Filament/                 → لوحات التحكم (Admin + Employee)
│   ├── Http/                     → Controllers, Middleware, Responses
│   ├── Models/                   → نماذج قاعدة البيانات (11 نموذج)
│   ├── Observers/                → مراقبو الأحداث (3 مراقبين)
│   ├── Providers/                → مزودو الخدمة
│   └── Services/                 → الخدمات (AI + Notifications)
│
├── bootstrap/
│   └── app.php                   → إعداد التطبيق: التوجيه + الوسائط + إعادة التوجيه
│
├── config/                       → ملفات التهيئة
│   ├── ai.php                    → إعدادات الذكاء الاصطناعي
│   ├── app.php                   → إعدادات التطبيق العامة
│   ├── auth.php                  → إعدادات المصادقة
│   ├── database.php              → إعدادات قاعدة البيانات
│   ├── filesystems.php           → إعدادات نظام الملفات
│   ├── logging.php               → إعدادات التسجيل (Logging)
│   ├── mail.php                  → إعدادات البريد
│   ├── queue.php                 → إعدادات الطوابير
│   ├── services.php              → إعدادات الخدمات الخارجية
│   ├── session.php               → إعدادات الجلسات
│   └── cache.php                 → إعدادات التخزين المؤقت
│
├── database/
│   ├── migrations/               → 19 ملف هجرة (إنشاء وتعديل الجداول)
│   ├── seeders/                  → 5 ملفات بيانات أولية
│   └── factories/                → UserFactory (توليد بيانات)
│
├── lang/                         → ملفات الترجمة
│   ├── ar.json                   → الترجمة العربية (277 سطر)
│   └── en.json                   → الترجمة الإنجليزية
│
├── resources/
│   ├── css/app.css               → نقطة دخول Tailwind CSS
│   ├── js/
│   │   ├── app.js                → نقطة الدخول الرئيسية
│   │   └── bootstrap.js          → إعداد Axios
│   └── views/                    → قوالب Blade
│       ├── auth/                 → تسجيل الدخول والتسجيل
│       ├── bills/                → صفحات الفواتير
│       ├── complaints/           → صفحات الشكاوى
│       ├── inquiries/            → صفحات الاستعلامات
│       ├── components/           → مكوّنات Blade قابلة لإعادة الاستخدام
│       ├── filament/             → عروض مخصصة لودجات Filament
│       ├── home/                 → الصفحة الرئيسية للمواطن
│       ├── landing/              → الصفحة الترحيبية
│       ├── layouts/              → القوالب الرئيسية (app.blade.php)
│       ├── notifications/        → صفحة الإشعارات
│       ├── profile/              → الملف الشخصي
│       ├── vendor/               → عروض موفّرة من الحزم (pagination)
│       └── welcome.blade.php     → صفحة الترحيب الافتراضية
│
├── routes/
│   ├── web.php                   → مسارات الويب (74 سطر)
│   └── console.php               → أوامر الطرفية (Artisan)
│
├── public/                       → الملفات العامة الموجَّهة للويب
├── storage/                      → التخزين (الملفات المرفوعة، السجلات، الكاش)
├── tests/                        → الاختبارات (Pest)
├── vendor/                       → حزم Composer
├── node_modules/                 → حزم npm
│
├── artisan                       → أداة سطر أوامر Laravel
├── composer.json                 → إدارة حزم PHP
├── package.json                  → إدارة حزم JavaScript
├── vite.config.js                → إعداد Vite
├── phpunit.xml                   → إعداد الاختبارات
├── .env / .env.example           → متغيرات البيئة
└── README.md                     → ملف القراءة
```

---

## 4. شرح مجلد `app` بالتفصيل

مجلد `app/` هو قلب التطبيق ويحتوي على كل الكود البرمجي. ينقسم إلى 6 مجلدات فرعية:

```
app/
├── Filament/        ← لوحات التحكم
├── Http/            ← طبقة HTTP
├── Models/          ← نماذج البيانات
├── Observers/       ← مراقبو الأحداث
├── Providers/       ← مزودو الخدمة
└── Services/        ← الخدمات المنطقية
```

---

### 4.1 النماذج (Models)

يحتوي مجلد `app/Models/` على **11 نموذج** تمثل جداول قاعدة البيانات والعلاقات بينها.

#### 4.1.1 `User.php` — نموذج المستخدم (97 سطر)

يمثل جميع أنواع المستخدمين في النظام (مواطن، موظف، مدير).

**الحقول القابلة للتعبئة:**
```php
'name', 'email', 'password', 'national_id', 'phone', 'role_id'
```

**الميزات الرئيسية:**

- **تنفيذ واجهة `FilamentUser`**: للسماح بالوصول إلى لوحات Filament.
- **دالة `canAccessPanel()`** (السطر 31): تتحكم في وصول المستخدم للوحات بناءً على دوره:
  ```php
  if ($panel->getId() === 'admin') {
      return $this->role->name === 'admin';
  }
  if ($panel->getId() === 'employee') {
      return $this->role->name === 'employee';
  }
  ```
- **العلاقات (Relationships)**: يحتوي على 8 علاقات:
  - `complaints()` — شكاوى المواطن (citizen_id)
  - `assignedComplaints()` — الشكاوى المسندة للموظف (assigned_to)
  - `inquiries()` — استعلامات المواطن
  - `assignedInquiries()` — الاستعلامات المسندة للموظف
  - `bills()` — فواتير المواطن
  - `customNotifications()` — إشعارات المستخدم
  - `systemLogs()` — سجلات النظام للمستخدم
  - `role()` — علاقة الانتماء للدور (BelongsTo)

#### 4.1.2 `Role.php` — نموذج الدور (16 سطر)

```php
protected $fillable = ['name'];
public function users(): HasMany
```
نموذج بسيط يحمل اسم الدور ويرتبط بعدة مستخدمين. الأدوار: `admin`, `employee`, `citizen`.

#### 4.1.3 `Complaint.php` — نموذج الشكوى (36 سطر)

```php
protected $fillable = [
    'citizen_id', 'type_id', 'assigned_to',
    'description', 'status', 'internal_notes',
    'ai_priority', 'ai_summary'
];
```

**العلاقات:**
- `citizen()` → `User` (من قدّم الشكوى)
- `type()` → `ComplaintType` (نوع الشكوى)
- `assignee()` → `User` (الموظف المسند للمعالجة)
- `attachments()` → `ComplaintAttachment` (المرفقات)

> **ملاحظة:** حقل `ai_priority` و `ai_summary` يُملآن تلقائياً بواسطة الذكاء الاصطناعي عند إنشاء الشكوى.

#### 4.1.4 `Inquiry.php` — نموذج الاستعلام (34 سطر)

```php
protected $fillable = [
    'citizen_id', 'type_id', 'assigned_to',
    'status', 'details', 'result_text', 'result_file_path'
];
```

**العلاقات:** نفس بنية الشكوى (`citizen`, `type`, `assignee`, `attachments`).

#### 4.1.5 `Bill.php` — نموذج الفاتورة (50 سطر)

```php
protected $fillable = [
    'citizen_id', 'bill_type', 'amount', 'paid_amount',
    'payment_receipt_path', 'payment_details',
    'status', 'due_date', 'paid_at', 'transaction_id'
];
```

**Casts:** `amount` كعشري، `due_date` كتاريخ، `paid_at` كتاريخ ووقت.

**ميزة مهمة — حدث `booted()`:** عند إنشاء فاتورة جديدة، يتم تلقائياً إنشاء إشعار للمواطن:
```php
static::created(function ($bill) {
    Notification::create([
        'user_id' => $bill->citizen_id,
        'title' => 'لديك فاتورة / غرامة جديدة',
        'message' => 'تم إصدار مطالبة مالية جديدة...',
        'action_url' => route('bills.pay', $bill->id),
    ]);
});
```

#### 4.1.6 `Notification.php` — نموذج الإشعار المخصص (23 سطر)

```php
protected $table = 'custom_notifications';  // جدول مخصص (وليس notifications الافتراضي)
```
الحقول: `user_id`, `title`, `message`, `action_url`, `is_read`.

#### 4.1.7 `SystemLog.php` — نموذج سجل النظام (34 سطر)

يسجّل جميع العمليات (إنشاء، تعديل، حذف) على النماذج.

```php
protected $fillable = [
    'user_id', 'action', 'model_type',
    'model_id', 'old_value', 'new_value'
];
```

**ميزة:** يحتوي على علاقة بوليمورفيك `model()` (MorphTo) لربط السجل بأي نموذج. قيم `old_value` و `new_value` تُحفظ بصيغة JSON.

#### 4.1.8 `ComplaintType.php` و `InquiryType.php` (21 سطر لكل منهما)

نماذج إدارة الأنواع:
```php
protected $fillable = ['name', 'description', 'is_active'];
// is_active → boolean
```

#### 4.1.9 `ComplaintAttachment.php` و `InquiryAttachment.php` (20 سطر لكل منهما)

نماذج المرفقات مع حقول الذكاء الاصطناعي:
```php
protected $fillable = ['...', 'is_ai_verified', 'ai_ocr_text'];
// is_ai_verified → boolean (نتيجة فحص AI للوثيقة)
// ai_ocr_text → النص المستخرج من الصورة (OCR)
```

---

### 4.2 وحدات التحكم (Controllers)

مجلد `app/Http/Controllers/` يحتوي على **11 متحكّماً**:

| المتحكّم | الوظيفة |
|---------|---------|
| `AuthController` | تسجيل الدخول/الخروج/التسجيل |
| `ComplaintController` | إدارة شكاوى المواطن |
| `InquiryController` | إدارة استعلامات المواطن |
| `BillController` | إدارة الفواتير والدفع |
| `HomeController` | الصفحة الرئيسية للمواطن |
| `LandingController` | الصفحة الترحيبية |
| `ProfileController` | الملف الشخصي |
| `NotificationController` | الإشعارات |
| `LanguageController` | تبديل اللغة |
| `AiController` | نقاط نهاية AI (JSON API) |
| `Controller` | المتحكّم الأساسي (abstract) |

---

#### 4.2.1 `AuthController.php` — المصادقة (84 سطر)

**تسجيل الدخول `login()`:**
```php
$credentials = $request->validate([
    'national_id' => 'required|string',   // ← الدخول بالرقم الوطني
    'password' => 'required|string',
]);

if (Auth::attempt($credentials)) {
    // إعادة التوجيه حسب الدور:
    // admin   → /admin
    // employee → /employee
    // citizen → /home
}
```

> **ميزة بارزة:** تسجيل الدخول يتم عبر **الرقم الوطني** (`national_id`) وليس البريد الإلكتروني، بما يتناسب مع الطبيعة الحكومية.

**التسجيل `register()`:**
- التحقق: `national_id` (11 رقم ومُتفرّد)، `name`، `email` (مُتفرّد)، `password` (8 أحرف مؤكّد)، `phone`.
- يُسند دور `citizen` تلقائياً للمستخدم الجديد.
- يسجّل الدخول مباشرة بعد التسجيل.

**تسجيل الخروج `logout()`:** يُبطل الجلسة ويُعيد توليد التوكن.

---

#### 4.2.2 `ComplaintController.php` — الشكاوى (132 سطر)

يدير دورة حياة الشكاوى من منظور المواطن:

**`index()` — عرض قائمة الشكاوى:**
- يجلب شكاوى المواطن الحالي فقط (`Auth::user()->complaints()`).
- يدعم البحث (بالرقم أو النوع)، الفلترة (بالحالة)، والترتيب (الأحدث/الأقدم).
- ترقيم الصفحات (10 لكل صفحة) مع الاحتفاظ بمعاملات البحث (`appends`).

**`store()` — تقديم شكوى جديدة — يحتوي على 3 ميزات AI:**

```php
// 1️⃣ إنشاء الشكوى
$complaint = Complaint::create([...]);

// 2️⃣ الميزة 1: التصنيف التلقائي بالذكاء الاصطناعي
$classification = AiService::classifyComplaint($request->description, $typeName);
// → يملأ ai_summary و ai_priority تلقائياً

// 3️⃣ معالجة المرفقات
foreach ($request->file('attachments') as $file) {
    $path = $file->store('complaints_attachments', 'public');
    $attachment = ComplaintAttachment::create([...]);

    // 4️⃣ الميزة 6: التحقق من المرفقات بالرؤية (Vision AI)
    if ($isImage) {
        $verification = AiService::verifyAttachment($base64Image, $mimeType);
        $attachment->update([
            'is_ai_verified' => $verification['is_valid'],
            'ai_ocr_text' => $verification['extracted_text'],
        ]);
    }
}
```

> **ميزة أمان:** جميع استدعاءات AI ملفوفة بـ `try/catch` بحيث لا توقف فشل الـ AI عملية حفظ الشكوى الأساسية.

**`show()`:** عرض تفاصيل شكوى محددة (مع التحقق من الملكية عبر `findOrFail`).

---

#### 4.2.3 `InquiryController.php` — الاستعلامات (123 سطر)

مشابه لـ ComplaintController مع اختلافات:
- يدعم **حقول مخصصة ديناميكية** (`custom_fields`) تُجمّع في حقل `details`.
- التحقق من المرفقات بالـ Vision AI نفس آلية الشكاوى.
- حد أقصى للمرفقات: 10 ميغابايت.

---

#### 4.2.4 `BillController.php` — الفواتير والدفع (104 سطر)

| الدالة | الوظيفة |
|--------|---------|
| `index()` | قائمة الفواتير مع بحث وفلترة وترتيب (بالأحدث/الأقدم/الاستحقاق) |
| `store()` | إنشاء فاتورة جديدة (حالة: غير مدفوعة، استحقاق: +30 يوم) |
| `showPaymentForm()` | نموذج الدفع (للفواتير غير المدفوعة فقط) |
| `processPayment()` | معالجة الدفع: توليد رقم معاملة، حفظ الإشعار، تحديث الحالة |
| `show()` | عرض تفاصيل الفاتورة |

**آلية الدفع:**
```php
$receiptPath = $request->file('payment_receipt')->store('receipts', 'public');
$transactionId = 'PAY-' . strtoupper(Str::random(10));

$bill->update([
    'status' => 'paid',
    'paid_at' => now(),
    'transaction_id' => $transactionId,
    'paid_amount' => $request->paid_amount,
    'payment_receipt_path' => $receiptPath,
    'payment_details' => $request->payment_details,
]);
```

> **ملاحظة:** الدفع يتم بإرفاق صورة إشعار التحويل (وليس بوابة دفع إلكترونية مباشرة) — يخضع للتحقق اليدوي لاحقاً.

---

#### 4.2.5 `AiController.php` — واجهة برمجة الذكاء الاصطناعي (60 سطر)

نقطتا نهاية ترجع JSON:

| المسار | الدالة | الوظيفة |
|--------|--------|---------|
| `POST /ai/generate-reply` | `generateReply()` | توليد رد رسمي من ملاحظة الموظف |
| `POST /ai/enhance-text` | `enhanceText()` | تحسين صياغة نص الشكوى |

```php
public function enhanceText(Request $request)
{
    $request->validate(['text' => 'required|string|min:10']);
    $enhanced = AiService::enhanceText($request->text);
    return response()->json([
        'success' => true,
        'enhanced_text' => $enhanced,
    ]);
}
```

---

#### 4.2.6 المتحكّمات المساندة

**`HomeController.php` (28 سطر):** يجلب إحصائيات المواطن (عدد الشكاوى، الاستعلامات، الفواتير غير المدفوعة) وآخر الأنشطة (3 شكاوى و3 فواتير).

**`ProfileController.php` (40 سطر):** تعديل الاسم، البريد، وكلمة المرور (اختيارياً).

**`NotificationController.php` (40 سطر):** عرض الإشعارات (15 لكل صفحة)، تحديد كمقروء (فردي/كلي)، وإعادة التوجيه لـ `action_url`.

**`LanguageController.php` (26 سطر):** تبديل اللغة بين العربية والإنجليزية، مع حفظها في الجلسة والكوكي.

**`LandingController.php` (11 سطر):** عرض الصفحة الترحيبية العامة.

---

### 4.3 الخدمات (Services)

مجلد `app/Services/` يحتوي على خدمتين رئيسيتين للمنطق القابل لإعادة الاستخدام.

#### 4.3.1 `AiService.php` — خدمة الذكاء الاصطناعي (249 سطر)

خدمة ثابتة (static) تتصل بـ **LiteLLM Proxy API**. تحتوي على 6 دوال:

| الدالة | نوع | الوظيفة |
|--------|------|---------|
| `chat($prompt)` | نص | إرسال نص واستقبال رد من الـ AI |
| `chatWithImage($prompt, $base64, $mime)` | نص+صورة | إرسال نص وصورة (Vision AI) |
| `classifyComplaint($desc, $type)` | مصفوفة | تصنيف الشكوى (أولوية + ملخص) |
| `generateOfficialReply($note)` | نص | توليد رد رسمي حكومي |
| `enhanceText($text)` | نص | تحسين صياغة نص |
| `verifyAttachment($base64, $mime)` | مصفوفة | فحص الوثيقة بالرؤية (Vision) |

**تفصيل `chat()`:**
```php
$response = Http::withHeaders([
    'x-litellm-api-key' => config('ai.api_key'),
])->timeout(30)->post(config('ai.api_url'), [
    'model' => config('ai.model'),           // gemini-3-flash-preview
    'messages' => [['role' => 'user', 'content' => $prompt]],
    'max_tokens' => (int) config('ai.max_tokens'),
    'temperature' => (float) config('ai.temperature'),
]);
```

**تفصيل `classifyComplaint()`:** يستخدم Prompt هندسي يحث النموذج على:
- تصنيف الأولوية بدقة (high/medium/low) وفق معايير محددة (خطر على الحياة = high).
- إرجاع JSON فقط يحتوي على `summary` و `priority`.
- تنظيف الرد من أكواد Markdown (` ```json `) قبل فك تشفير JSON.

**تفصيل `verifyAttachment()`:** Prompt يطلب من النموذج:
- تحديد إن كانت الصورة "وثيقة رسمية مقبولة" (هوية، جواز، فاتورة، إلخ).
- رفض الصور غير الرسمية (سيلفي، مناظر، لقطات شاشة).
- استخراج النص (OCR) من الوثيقة.

#### 4.3.2 `NotificationService.php` — خدمة الإشعارات (73 سطر)

ترسل الإشعارات بشكل مزدوج:

```php
// 1️⃣ إشعار Filament في قاعدة البيانات (يظهر في لوحات التحكم)
$user->notifications()->create([
    'id' => Str::uuid(),
    'type' => 'Filament\\Notifications\\DatabaseNotification',
    'data' => $data,
]);

// 2️⃣ إشعار مخصص في جدول custom_notifications (يظهر في واجهة المواطن)
CustomNotification::create([
    'user_id' => $user->id,
    'title' => $title,
    'message' => $body,
    'action_url' => $actionUrl,
]);
```

دالتان: `sendToUser()` (لمستخدم واحد) و `sendToUsers()` (لعدة مستخدمين).

---

### 4.4 المراقبون (Observers)

مجلد `app/Observers/` يحتوي على **3 مراقبين** يتفاعلون مع أحداث النماذج تلقائياً.

#### 4.4.1 `ComplaintObserver.php` (69 سطر)

| الحدث | الإجراء |
|-------|---------|
| `created` | إشعار جميع المديرين بوجود شكوى جديدة + إشعار الموظف المسند (إن وُجد) |
| `updated` | عند تغيير الحالة → إشعار المواطن بتحديث الحالة |

مثال على إشعار المديرين:
```php
$admins = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->get();
NotificationService::sendToUsers(
    $admins,
    __('شكوى جديدة #') . $complaint->id,
    __('تم تقديم شكوى جديدة من المواطن :name', ['name' => $complaint->citizen?->name]),
    'heroicon-o-exclamation-triangle',
    'warning',
    '/admin/complaints/' . $complaint->id . '/edit',
);
```

#### 4.4.2 `InquiryObserver.php` (69 سطر)

نفس بنية ComplaintObserver لكن للاستعلامات.

#### 4.4.3 `SystemLogObserver.php` (44 سطر)

**مراقب عام (Universal Observer)** يُسجّل كل العمليات على النماذج:

| الحدث | الإجراء |
|-------|---------|
| `created` | تسجيل القيم الجديدة في `system_logs` |
| `updated` | تسجيل القيم القديمة والجديدة |
| `deleted` | تسجيل القيم قبل الحذف |

```php
public function updated($model): void
{
    SystemLog::create([
        'user_id' => auth()->id(),
        'action' => 'update',
        'model_type' => get_class($model),
        'model_id' => $model->id,
        'old_value' => collect($model->getOriginal())->except(['created_at', 'updated_at'])->toArray(),
        'new_value' => collect($model->getAttributes())->except(['created_at', 'updated_at'])->toArray(),
    ]);
}
```

> يُسجَّل هذا المراقب لثلاثة نماذج: `Complaint`, `Inquiry`, `Bill` في `AppServiceProvider`.

---

### 4.5 المزودون (Providers)

#### 4.5.1 `AppServiceProvider.php` (44 سطر)

في دالة `boot()`:

1. **تخصيص عرض الترقيم**: `Paginator::defaultView('vendor.pagination.tailwind')`.
2. **إعداد مبدّل اللغة** لـ Filament (عربي/إنجليزي، دائري الشكل، يظهر داخل اللوحات فقط).
3. **تسجيل المراقبين**:
   ```php
   Complaint::observe(ComplaintObserver::class);
   Inquiry::observe(InquiryObserver::class);
   Complaint::observe(SystemLogObserver::class);
   Inquiry::observe(SystemLogObserver::class);
   Bill::observe(SystemLogObserver::class);
   ```

#### 4.5.2 `AdminPanelProvider.php` (71 سطر)

إعداد لوحة تحكم المدير (`/admin`):

```php
$panel
    ->default()                    // اللوحة الافتراضية
    ->id('admin')
    ->path('admin')
    ->login(RedirectToCentralLogin::class)  // إعادة توجيه لتسجيل الدخول المركزي
    ->colors(['primary' => Color::Blue])
    ->discoverResources(in: app_path('Filament/Resources'), ...)
    ->databaseNotifications()       // تفعيل الإشعارات في قاعدة البيانات
    ->databaseNotificationsPolling('30s')  // فحص كل 30 ثانية
    ->discoverWidgets(in: app_path('Filament/Widgets'), ...)
```

> **ربط `LogoutResponse`:** يعيد توجيه تسجيل الخروج إلى `/` بدلاً من صفحة دخول Filament.

#### 4.5.3 `EmployeePanelProvider.php` (70 سطر)

نفس إعداد لوحة المدير لكن لـ `/employee`، مع اكتشاف الموارد من `app/Filament/Employee/`.

---

### 4.6 الوسائط (Middleware)

#### `RedirectToLogin.php` (19 سطر)
يعيد توجيه المستخدمين غير المسجلين إلى `/login` عند محاولة الوصول للوحات Filament:
```php
if (!Auth::check()) {
    return redirect()->guest('/login');
}
```

#### `SetLocale.php` (32 سطر)
يضبط لغة التطبيق في كل طلب بناءً على (بالأولوية):
1. الجلسة (`Session::get('locale')`)
2. الكوكي (`filament_language_switch_locale`)
3. الإعداد الافتراضي (`ar`)

كما يضيف رؤوس منع التخزين المؤقت (`no-store, no-cache`).

#### `Responses/LogoutResponse.php` (14 سطر)
يعيد توجيه تسجيل الخروج من لوحات Filament إلى الصفحة الرئيسية `/`.

---

### 4.7 Filament — لوحة الإدارة والموظفين

يستخدم المشروع **Filament 3.3** بـ** لوحتين منفصلتين**:

```
app/Filament/
├── Resources/              ← موارد لوحة المدير (8 موارد)
│   ├── ComplaintResource.php
│   ├── InquiryResource.php
│   ├── BillResource.php
│   ├── UserResource.php
│   ├── RoleResource.php
│   ├── ComplaintTypeResource.php
│   ├── InquiryTypeResource.php
│   └── SystemLogResource.php
│
├── Employee/               ← موارد لوحة الموظف (مُقيّدة)
│   └── Resources/
│       ├── ComplaintResource.php
│       └── InquiryResource.php
│
├── Pages/Auth/             ← صفحة إعادة التوجيه لتسجيل الدخول
│   └── RedirectToCentralLogin.php
│
└── Widgets/                ← ودجات لوحة المدير (3 ودجات)
    ├── StatsOverview.php
    ├── ComplaintsChart.php
    └── LatestComplaints.php
```

---

#### 4.7.1 موارد لوحة المدير (Admin Resources)

##### `ComplaintResource.php` (300 سطر) — الأكثر تعقيداً

**الأعمدة (Table Columns):**
| العمود | الميزات |
|--------|---------|
| ID | قابل للترتيب |
| اسم المواطن | قابل للبحث والترتيب |
| النوع | قابل للبحث والترتيب |
| الحالة | Badge ملوّن (pending=أصفر، processing=أزرق، completed=أخضر، rejected=أحمر) |
| **أولوية AI** | Badge ملوّن برموز تعبيرية (🔴 عالية، 🟡 متوسطة، ⚪ منخفضة) |
| ملخص AI | مقطوع بـ 50 حرف مع tooltip |
| المعالج | قابل للبحث والترتيب |
| تاريخ الإنشاء | تاريخ ووقت |

**الفلترات:** الحالة، أولوية AI، النوع، المعالج.

**الإجراءات (Actions):**

1. **`EditAction`** — تعديل عادي.

2. **`changeStatusAndReply`** — إجراء مركّب (الأهم):
   - نافذة منبثقة تحتوي: اختيار الحالة + ملاحظة سريعة + حقل الرد الرسمي.
   - زر إضافي **"🪄 توليد رد رسمي"** يستدعي `AiService::generateOfficialReply()`.
   - عند الحفظ: يحدّث الحالة ويحفظ الرد في `internal_notes`.

```php
->action(function (Complaint $record, array $data): void {
    $reply = !empty($data['official_reply']) ? $data['official_reply'] : null;
    if (empty($reply) && !empty($data['employee_quick_note'])) {
        $reply = AiService::generateOfficialReply($data['employee_quick_note'])
                 ?? $data['employee_quick_note'];
    }
    $record->update([
        'status' => $data['status'],
        'internal_notes' => $reply,
    ]);
})
```

**النموذج (Form):** اختيار المواطن، النوع، المعالج، الحالة، الوصف، الملاحظات الداخلية، وقسم "تحليل الذكاء الاصطناعي" (للقراءة فقط).

##### `InquiryResource.php` (247 سطر)
مشابه لـ ComplaintResource مع حقول الاستعلام (`result_text`, `result_file_path`).

##### `UserResource.php` (129 سطر)
إدارة المستخدمين: الرقم الوطني، الاسم، البريد، كلمة المرور، الهاتف، الدور.

##### `BillResource.php` (175 سطر)
إدارة الفواتير مع عرض نوع الفاتورة المترجم، المبلغ بالليرة السورية (SYP)، الحالة، إشعار الدفع.

##### `ComplaintTypeResource.php` و `InquiryTypeResource.php` (97 سطر لكل)
إدارة أنواع الشكاوى/الاستعلامات مع عدّاد عدد العناصر المرتبطة.

##### `RoleResource.php` (84 سطر)
إدارة الأدوار مع عدّاد المستخدمين لكل دور.

##### `SystemLogResource.php` (107 سطر)
- **للقراءة فقط** (`canCreate()` يرجع false).
- عرض: المستخدم، الإجراء، نوع النموذج، رقم النموذج، التاريخ.
- صفحة عرض تفصيلية تعرض `old_value` و `new_value` بصيغة JSON منسّقة.

---

#### 4.7.2 موارد لوحة الموظف (Employee Resources)

تتميز بـ**قيود أمان صارمة**:

##### `Employee/Resources/ComplaintResource.php` (257 سطر)

```php
// 1️⃣ منع الإنشاء والحذف
public static function canCreate(): bool { return false; }
public static function canDelete($record): bool { return false; }

// 2️⃣ تقييد البيانات المعروضة
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->where(function ($query) {
        $query->where('assigned_to', Auth::id())      // الشكاوى المسندة له
              ->orWhereNull('assigned_to');            // أو غير المسندة لأحد
    });
}
```

**حقول معطّلة في النموذج:** المواطن، النوع، الوصف (للقراءة فقط، `dehydrated(false)` = لا تُحفظ).
**حقول قابلة للتعديل:** الحالة، الملاحظات الداخلية فقط.

نفس ميزة "تغيير الحالة والرد بالـ AI" متوفرة.

##### `Employee/Resources/InquiryResource.php` (211 سطر)
نفس القيود للاستعلامات.

---

#### 4.7.3 الودجات (Widgets)

##### ودجات المدير:

| الودجة | النوع | الوظيفة |
|--------|-------|---------|
| `StatsOverview` | إحصائيات | إجمالي المستخدمين، الشكاوى، الفواتير المدفوعة |
| `ComplaintsChart` | رسم بياني (Line) | الشكاوى الشهرية خلال آخر 12 شهراً |
| `LatestComplaints` | جدول مخصص | آخر 5 شكاوى مع المواطن والنوع |

##### ودجات الموظف:

| الودجة | النوع | الوظيفة |
|--------|-------|---------|
| `EmployeeStatsOverview` | إحصائيات | الطلبات المعلقة والمنجزة للموظف |
| `EmployeeRequestsChart` | رسم بياني (Bar) | الشكاوى والاستفسارات الشهرية |
| `EmployeeRequestsStatsTable` | جدول مخصص | توزيع الطلبات حسب الحالة |

---

#### 4.7.4 RelationManagers (مديرو العلاقات)

##### `AttachmentsRelationManager.php` (للشكاوى والاستعلامات)

يعرض المرفقات داخل صفحة تعديل الشكوى/الاستعلام:
- أعمدة: مسار الملف، نوع الملف، **حالة التحقق بالـ AI** (أيقونة خضراء ✅ أو حمراء ❌)، النص المستخرج (OCR)، تاريخ الرفع.
- إجراءات: عرض، تحميل، حذف.
- نموذج رفع ملف (صور JPEG/PNG أو PDF).

---

#### 4.7.5 صفحة إعادة التوجيه

##### `RedirectToCentralLogin.php` (14 سطر)
تعيد توجيه محاولة الوصول لصفحة دخول Filament إلى `/login` المركزي:
```php
public function mount(): void
{
    redirect('/login')->send();
    exit;
}
```
> هذا يضمن نقطة دخول موحّدة لجميع الأدوار.

---

## 5. قاعدة البيانات (Database & Migrations)

قاعدة البيانات: **MySQL** (الاسم: `gov_app`).

### مخطط العلاقات (ERD)

```
┌──────────┐       ┌──────────────┐
│  roles   │◄──────│    users     │
│──────────│ 1   N │──────────────│
│ id       │       │ id           │
│ name     │       │ national_id  │
└──────────┘       │ role_id (FK) │
                   │ name         │
                   │ email        │
                   │ password     │
                   │ phone        │
                   └──────┬───────┘
                          │
          ┌───────────────┼───────────────┐
          │               │               │
          ▼               ▼               ▼
   ┌─────────────┐ ┌─────────────┐ ┌───────────┐
   │ complaints  │ │  inquiries  │ │   bills   │
   │─────────────│ │─────────────│ │───────────│
   │ id          │ │ id          │ │ id        │
   │ citizen_id  │ │ citizen_id  │ │ citizen_id│
   │ type_id (FK)│ │ type_id (FK)│ │ bill_type │
   │ assigned_to │ │ assigned_to │ │ amount    │
   │ description │ │ status      │ │ status    │
   │ status      │ │ details     │ │ due_date  │
   │ ai_priority │ │ result_text │ │ paid_at   │
   │ ai_summary  │ │ result_file │ │ txn_id    │
   └──────┬──────┘ └──────┬──────┘ └───────────┘
          │               │
          ▼               ▼
   ┌──────────────────┐  ┌────────────────────┐
   │complaint_attach- │  │inquiry_attachments │
   │     ments        │  │────────────────────│
   │──────────────────│  │ id                 │
   │ id               │  │ inquiry_id (FK)    │
   │ complaint_id(FK) │  │ file_path          │
   │ file_path        │  │ file_name          │
   │ file_type        │  │ file_type          │
   │ is_ai_verified   │  │ is_ai_verified     │
   │ ai_ocr_text      │  │ ai_ocr_text        │
   └──────────────────┘  └────────────────────┘

   ┌──────────────┐  ┌──────────────────┐  ┌──────────────┐
   │complaint_types│  │  inquiry_types   │  │ system_logs  │
   │──────────────│  │──────────────────│  │──────────────│
   │ id           │  │ id               │  │ id           │
   │ name         │  │ name             │  │ user_id (FK) │
   │ description  │  │ description      │  │ action       │
   │ is_active    │  │ is_active        │  │ model_type   │
   └──────────────┘  └──────────────────┘  │ model_id     │
                                            │ old_value    │
   ┌────────────────────┐                   │ new_value    │
   │custom_notifications│                   └──────────────┘
   │────────────────────│
   │ id                 │   ┌────────────────────┐
   │ user_id (FK)       │   │  notifications      │ (Filament)
   │ title              │   │────────────────────│
   │ message            │   │ id (uuid)          │
   │ action_url         │   │ type               │
   │ is_read            │   │ notifiable (morph) │
   └────────────────────┘   │ data               │
                            │ read_at           │
                            └────────────────────┘
```

### قائمة الهجرات (19 ملف)

| # | الملف | الوظيفة |
|---|-------|---------|
| 1 | `0001_01_01_000000_create_users_table` | إنشاء `roles`, `users`, `password_reset_tokens`, `sessions` |
| 2 | `0001_01_01_000001_create_cache_table` | جدول الكاش |
| 3 | `0001_01_01_000002_create_jobs_table` | جدول الطوابير |
| 4 | `2026_03_07_120345_create_complaint_types_table` | أنواع الشكاوى |
| 5 | `2026_03_07_120345_create_inquiry_types_table` | أنواع الاستعلامات |
| 6 | `2026_03_07_120418_create_complaints_table` | الشكاوى |
| 7 | `2026_03_07_120419_create_inquiries_table` | الاستعلامات |
| 8 | `2026_03_07_120429_create_complaint_attachments_table` | مرفقات الشكاوى |
| 9 | `2026_03_07_120442_create_bills_table` | الفواتير |
| 10 | `2026_03_07_120458_create_system_logs_table` | سجلات النظام |
| 11 | `2026_03_07_120533_create_notifications_table` | الإشعارات (الأصلية) |
| 12 | `2026_05_10_123714_add_details_to_inquiries_table` | إضافة `details` |
| 13 | `2026_05_10_123717_create_inquiry_attachments_table` | مرفقات الاستعلامات |
| 14 | `2026_05_10_132347_add_payment_fields_to_bills_table` | حقول الدفع (`paid_amount`, `receipt`, `details`) |
| 15 | `2026_05_10_132402_add_action_url_to_notifications_table` | إضافة `action_url` للإشعارات |
| 16 | `2026_05_11_103831_rename_notifications_to_custom_notifications` | إعادة تسمية الجدول |
| 17 | `2026_05_11_104041_create_filament_notifications_table` | جدول إشعارات Filament |
| 18 | `2026_05_23_120000_add_ai_fields_to_tables` | إضافة حقول AI للشكاوى والمرفقات |
| 19 | `2026_06_04_120000_add_ai_fields_to_inquiry_attachments` | حقول AI لمرفقات الاستعلامات |

### الحالات (Status Enums)

**الشكاوى والاستعلامات:** `pending` → `processing` → `completed` / `rejected`

**الفواتير:** `unpaid` → `paid`

---

## 6. البيانات الأولية (Seeders)

```
DatabaseSeeder (المُنسّق الرئيسي)
├── RoleSeeder       → إنشاء الأدوار: admin, employee, citizen
├── UserSeeder       → 1 مدير + 2 موظفين + 5 مواطنين
├── TypesSeeder      → 10 أنواع شكاوى + 8 أنواع استعلامات
└── DummyDataSeeder  → 20 شكوى + 20 استعلام + 20 فاتورة + 10 إشعارات
```

### بيانات تجريبية جاهزة

| المستخدم | الرقم الوطني | كلمة المرور | الدور |
|----------|-------------|-------------|-------|
| مدير النظام | `00000000001` | `password` | admin |
| موظف 1 | `00000000002` | `password` | employee |
| موظف 2 | `00000000003` | `password` | employee |
| مواطن 1-5 | `10000000001` - `10000000005` | `password` | citizen |

### أنواع الشكاوى (10): مياه، كهرباء، نظافة، طرق، صرف صحي، اتصالات وإنترنت، بيئة وتلوث، أمن وسلامة، تموين وأسعار، خدمات صحية.

### أنواع الاستعلامات (8): بيان عائلي، لا حكم عليه، وثيقة ملكية، استخراج جواز سفر، تجديد بطاقة شخصية، استعلام عن مخالفات مرورية، تسجيل مركبة، استخراج شهادة ميلاد.

---

## 7. المسارات (Routes)

جميع المسارات في `routes/web.php` (74 سطر):

### المسارات العامة
| الطريقة | المسار | الوظيفة |
|---------|--------|---------|
| GET | `/` | الصفحة الترحيبية (`landing`) |
| GET | `/lang/{locale}` | تبديل اللغة |

### مسارات الزوار (Guest)
| الطريقة | المسار | الوظيفة |
|---------|--------|---------|
| GET/POST | `/login` | تسجيل الدخول |
| GET/POST | `/register` | إنشاء حساب |

### مسارات المستخدمين المسجّلين (Auth)
| الطريقة | المسار | الوظيفة |
|---------|--------|---------|
| POST/GET | `/logout` | تسجيل الخروج |
| GET | `/home` | الصفحة الرئيسية |
| GET/PUT | `/profile` | الملف الشخصي |
| GET/POST/GET | `/complaints` | الشكاوى (قائمة/إنشاء/عرض) |
| GET/POST/GET | `/inquiries` | الاستعلامات (قائمة/إنشاء/عرض) |
| GET/POST | `/bills`, `/bills/{id}/pay` | الفواتير والدفع |
| GET/POST | `/notifications` | الإشعارات |
| POST | `/ai/generate-reply` | توليد رد بالـ AI |
| POST | `/ai/enhance-text` | تحسين نص بالـ AI |

### لوحات Filament (مُسجّلة تلقائياً)
| المسار | اللوحة | المستهدف |
|--------|--------|---------|
| `/admin` | لوحة المدير | دور admin |
| `/employee` | لوحة الموظف | دور employee |

---

## 8. التهيئة (Configuration)

### `bootstrap/app.php` (37 سطر)

الإعدادات الأهم:

1. **الوسائط:** يضيف `SetLocale` لوسائط الويب.
2. **استثناء CSRF:** مسارات `ai/*` مستثناة من حماية CSRF (للسماح بطلبات AJAX).
3. **إعادة توجيه المستخدمين بعد الدخول:**
   ```php
   $middleware->redirectUsersTo(function () {
       $user = auth()->user();
       if ($user->role->name === 'admin') return '/admin';
       if ($user->role->name === 'employee') return '/employee';
       return '/home';
   });
   ```

### `config/ai.php` (19 سطر)

إعدادات الذكاء الاصطناعي:
```php
'api_url'     => env('AI_API_URL'),           // عنوان LiteLLM Proxy
'api_key'     => env('AI_API_KEY'),            // مفتاح API
'model'       => env('AI_MODEL', 'gemini-3-flash-preview'),
'max_tokens'  => env('AI_MAX_TOKENS', 4096),
'temperature' => env('AI_TEMPERATURE', 0.7),
```

### متغيرات البيئة `.env`

| المتغير | القيمة الافتراضية | الاستخدام |
|---------|-------------------|-----------|
| `DB_CONNECTION` | mysql | نوع قاعدة البيانات |
| `DB_DATABASE` | gov_app | اسم قاعدة البيانات |
| `SESSION_DRIVER` | database | تخزين الجلسات |
| `QUEUE_CONNECTION` | database | نظام الطوابير |
| `CACHE_STORE` | database | التخزين المؤقت |
| `FILESYSTEM_DISK` | local | نظام الملفات |

---

## 9. واجهات العرض (Views & Frontend)

### القالب الرئيسي `layouts/app.blade.php` (130 سطر)

- **اتجاه RTL/LTR** تلقائي حسب اللغة: `dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"`.
- **شريط تنقّل** مع: الشعار، روابط (الرئيسية، الشكاوى، الاستعلامات، الفواتير)، أيقونة الإشعارات (مع عدّاد غير المقروء)، الملف الشخصي، مبدّل اللغة، تسجيل الخروج.
- **قائمة منسدلة** للأجهزة المحمولة (Alpine.js `x-data="{ open: false }"`).
- **عرض التنبيهات** (`session('success')`, `session('error')`, `$errors`).
- **تذييل** بحقوق النشر.

### المكوّنات (Components)

#### `components/alert.blade.php` (31 سطر)
تنبيه قابل للإغلاق (Alpine.js) بأربعة أنواع: success, error, warning, info. كل نوع له لون وأيقونة خاصة.

#### `components/status-badge.blade.php` (17 سطر)
شارة حالة ملوّنة (Badge) ترسم حالات: pending (أصفر)، processing (أزرق)، completed (أخضر)، rejected (أحمر)، unpaid (أصفر)، paid (أخضر).

### الصفحات الرئيسية

| الصفحة | المسار | الوصف |
|--------|--------|-------|
| `landing/index` | `/` | صفحة ترحيبية بخدمات البوابة |
| `home/index` | `/home` | لوحة تحكم المواطن (إحصائيات + إجراءات سريعة) |
| `auth/login` | `/login` | تسجيل الدخول بالرقم الوطني |
| `auth/register` | `/register` | إنشاء حساب مواطن |

### صفحات الشكاوى

| الصفحة | الميزات البارزة |
|--------|-----------------|
| `complaints/index` | قائمة مع بحث/فلترة/ترتيب |
| `complaints/create` | **زر "حسّن الصياغة بالـ AI"** (Alpine.js + fetch API) |
| `complaints/show` | عرض تحليل AI (الأولوية + الملخص) + شارات التحقق على المرفقات |

### نموذج تحسين النص بالـ AI (في `complaints/create.blade.php`)

يستخدم Alpine.js لاستدعاء `/ai/enhance-text` بدون إعادة تحميل الصفحة:
```javascript
async enhanceText() {
    const response = await fetch('{{ route('ai.enhance-text') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ text: this.description }),
    });
    const data = await response.json();
    if (data.success) {
        this.description = data.enhanced_text;
    }
}
```

---

## 10. ميزات الذكاء الاصطناعي (AI Features)

يحتوي المشروع على **6 ميزات ذكاء اصطناعي** متكاملة:

### الميزة 1: التصنيف التلقائي للشكاوى
- **الموقع:** `ComplaintController::store()`
- **الآلية:** عند تقديم شكوى، يحلل الـ AI الوصف ويحدد:
  - **الأولوية** (high/medium/low) وفق معايير صارمة.
  - **الملخص** في سطر واحد.
- **النتيجة:** تُحفظ في `ai_priority` و `ai_summary`.

### الميزة 2: توليد ردود رسمية بالـ AI
- **الموقع:** `changeStatusAndReply` action في Filament.
- **الآلية:** يكتب الموظف/المدير ملاحظة سريعة، ويحوّلها الـ AI لرد رسمي حكومي موجّه للمواطن.
- **Prompt:** يلزم النموذج ببدء الرسالة بالتحية، دون أي مقدمة أو توضيح.

### الميزة 5: تحسين صياغة النصوص
- **الموقع:** زر في صفحة `complaints/create`.
- **الآلية:** يصحح الأخطاء الإملائية ويحسّن الصياغة لشكوى رسمية.

### الميزة 6: التحقق من المرفقات بالرؤية (Vision AI)
- **الموقع:** `ComplaintController::store()` و `InquiryController::store()`.
- **الآلية:** عند رفع صورة، يفحصها الـ AI لتحديد:
  - إن كانت وثيقة رسمية مقبولة (`is_valid`).
  - استخراج النص منها (`extracted_text` / OCR).
  - سبب الرفض إن لم تكن صالحة.
- **العرض:** شارات خضراء/حمراء على المرفقات + عمود في Filament.

### معالجة الأخطاء
جميع استدعاءات AI:
- ملفوفة بـ `try/catch`.
- تسجّل الأخطاء في `Log`.
- **لا توقف** العملية الأساسية عند الفشل (degradation graceful).

---

## 11. نظام الإشعارات

النظام **مزدوج** (يعمل في واجهتين):

```
                    ┌──────────────────────────────┐
                    │    NotificationService       │
                    │      (sendToUser)            │
                    └──────────┬───────────────────┘
                               │
                 ┌─────────────┴─────────────┐
                 ▼                           ▼
    ┌────────────────────────┐   ┌────────────────────────┐
    │  جدول notifications    │   │ جدول custom_notifications│
    │  (Filament)            │   │ (واجهة المواطن)          │
    │────────────────────────│   │────────────────────────│
    │ - يظهر في لوحات        │   │ - يظهر في /notifications │
    │   admin و employee     │   │ - عدّاد في شريط التنقل    │
    │ - polling كل 30 ثانية  │   │ - زر "تحديد كمقروء"       │
    └────────────────────────┘   └────────────────────────┘
```

### محفّزات الإشعارات

| الحدث | المستلم | الإشعار |
|-------|---------|---------|
| شكوى/استعلام جديد | المديرون + الموظف المسند | "شكوى جديدة #" |
| تغيير حالة شكوى/استعلام | المواطن | "تحديث حالة شكواك إلى: ..." |
| إنشاء فاتورة جديدة | المواطن | "لديك فاتورة / غرامة جديدة" |

---

## 12. نظام الصلاحيات والأدوار

### مصفوفة الصلاحيات

| الميزة | المواطن | الموظف | المدير |
|--------|---------|--------|--------|
| تسجيل الدخول | ✅ | ✅ | ✅ |
| تقديم شكوى/استعلام | ✅ | ❌ | ❌ |
| دفع الفواتير | ✅ | ❌ | ❌ |
| رؤية بياناته فقط | ✅ (عبر `Auth::user()`) | - | - |
| الوصول لـ /admin | ❌ | ❌ | ✅ |
| الوصول لـ /employee | ❌ | ✅ | ❌ |
| معالجة الطلبات | ❌ | ✅ (المسندة له فقط) | ✅ (الكل) |
| إدارة المستخدمين | ❌ | ❌ | ✅ |
| إدارة الأنواع | ❌ | ❌ | ✅ |
| حذف السجلات | ❌ | ❌ | ✅ |
| عرض سجلات النظام | ❌ | ❌ | ✅ |

### آلية الحماية متعددة الطبقات

1. **طبقة النموذج:** `User::canAccessPanel()` تتحقق من الدور.
2. **طبقة الوسائط:** `RedirectToLogin` يمنع غير المسجّلين.
3. **طبقة Filament:** `canCreate()`, `canDelete()`, `getEloquentQuery()` تقيّد الموظف.
4. **طبقة المتحكّم:** `Auth::user()->complaints()` تضمن رؤية المواطن لبياناته فقط.

---

## 13. مخطط تدفق العمل (Workflow)

### تدفق الشكوى الكامل

```
[المواطن]                          [النظام + AI]                    [المدير/الموظف]
    │                                    │                                  │
    │  1. يملأ النموذج                    │                                  │
    │     (نوع + وصف)                    │                                  │
    │  ──────────────────────►           │                                  │
    │                                    │  2. تحسين الصياغة (اختياري)        │
    │                                    │     AiService::enhanceText()      │
    │                                    │                                  │
    │  3. يرفع المرفقات                   │                                  │
    │  4. يقدّم الشكوى  ─────────►        │                                  │
    │                                    │  5. حفظ في DB                     │
    │                                    │  6. تصنيف AI: أولوية + ملخص       │
    │                                    │     AiService::classifyComplaint()│
    │                                    │  7. فحص المرفقات بالـ Vision AI   │
    │                                    │     AiService::verifyAttachment() │
    │                                    │  8. إشعار المديرين  ──────────►   │
    │                                    │                        9. يفتح    │
    │                                    │                           لوحة    │
    │                                    │                           التحكم  │
    │                                    │                       10. يحدّد   │
    │                                    │                           المعالج │
    │                                    │  11. إشعار الموظف  ─────────►    │
    │                                    │                        12. يعالج  │
    │                                    │                       13. يكتب    │
    │                                    │                           ملاحظة  │
    │                                    │                       14. يولّد   │
    │                                    │                           رداً AI │
    │                                    │                       15. يغيّر   │
    │                                    │                           الحالة  │
    │                                    │  16. إشعار المواطن ◄─────────   │
    │  17. يستلم الإشعار ◄────────       │     "تحديث حالة شكواك"            │
    │  18. يرى التحليل + الرد            │                                  │
    │      في complaints/show            │                                  │
    │                                    │  19. تسجيل في system_logs         │
    │                                    │     (قيم قديمة + جديدة)           │
```

### تدفق الفاتورة

```
[المدير يُنشئ فاتورة] → [إشعار تلقائي للمواطن] → [المواطن يدفع + يرفق إشعار]
→ [توليد رقم معاملة PAY-xxx] → [تحديث الحالة لـ paid] → [إشعار بالنجاح]
```

---

## ملخص تقني

| المؤشر | القيمة |
|--------|--------|
| إطار العمل | Laravel 12 + Filament 3.3 |
| عدد النماذج (Models) | 11 |
| عدد المتحكّمات (Controllers) | 11 |
| عدد موارد Filament | 10 (8 مدير + 2 موظف) |
| عدد الودجات | 6 (3 مدير + 3 موظف) |
| عدد المراقبين | 3 |
| عدد الخدمات | 2 (AI + Notifications) |
| عدد الهجرات | 19 |
| عدد الـ Seeders | 5 |
| ميزات الذكاء الاصطناعي | 6 ميزات متكاملة |
| اللغات المدعومة | العربية + الإنجليزية |
| الأدوار | 3 (مواطن، موظف، مدير) |

---

*نهاية التقرير*
