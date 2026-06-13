# دليل تشغيل مشروع الحكومة الإلكترونية (gov-app)

## المتطلبات الأساسية

| المتطلب | الإصدار المطلوب |
|---------|-----------------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| MySQL | 5.7+ / 8.0+ |
| امتداد PHP | pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo |

---

## التثبيت من الصفر (بعد الاستنساخ من GitHub)

### 1. استنساخ المشروع

```bash
git clone <repository-url> gov-app
cd gov-app
```

### 2. تثبيت حزم PHP

```bash
composer install
```

### 3. إعداد ملف البيئة

```bash
cp .env.example .env
php artisan key:generate
```

### 4. إعداد قاعدة البيانات (MySQL)

أنشئ قاعدة بيانات في MySQL:

```sql
CREATE DATABASE gov_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

ثم عدّل ملف `.env` بإعدادات قاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gov_app
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. إعداد الذكاء الاصطناعي

أضف متغيرات الذكاء الاصطناعي إلى ملف `.env`:

```env
AI_API_URL=https://your-api-endpoint.com/chat/completions
AI_API_KEY=your-api-key
AI_MODEL=your-model-name
AI_MAX_TOKENS=4096
AI_TEMPERATURE=0.7
```

> **ملاحظة:** مفتاح API حساس — لا ترفعه إلى Git.

### 6. تشغيل الهجرات والبذور

```bash
php artisan migrate --seed
```

### 7. إنشاء الرمز التخزيني

```bash
php artisan storage:link
```

### 8. تثبيت حزم الواجهة وبناؤها

```bash
npm install
npm run build
```

### 9. تشغيل معالج الطابور (مهم للإشعارات)

```bash
php artisan queue:work
```

> **ملاحظة:** الإشعارات في لوحات Filament تعمل بشكل متزامن مباشرة عبر `NotificationService`. لكن وظائف الطابور الأخرى قد تحتاج هذا المعالج. إذا لم ترد تشغيله، تأكد من `QUEUE_CONNECTION=sync` في `.env`.

---

## التشغيل السريع (أمر واحد)

```bash
composer setup
```

هذا الأمر ينفّذ: `composer install` → نسخ `.env.example` → `key:generate` → `migrate --force` → `npm install` → `npm run build`

---

## أوامر التشغيل

### تشغيل بيئة التطوير

```bash
composer dev
```

يشغّل ثلاث عمليات متوازية:
- خادم Laravel على `http://127.0.0.1:8000`
- معالج الطابور `queue:listen`
- خادم Vite للواجهة (hot reload)

### أو تشغيل يدوي منفصل

```bash
# المحطة 1: خادم Laravel
php artisan serve

# المحطة 2: معالج الطابور (اختياري)
php artisan queue:work

# المحطة 3: بناء الواجهة (للإنتاج)
npm run build

# أو للتطوير مع hot reload
npm run dev
```

---

## الاختبارات

```bash
# تشغيل جميع الاختبارات
php artisan test

# أو عبر Composer
composer test

# تشغيل اختبار محدد
php artisan test --filter=NotificationTest
php artisan test --filter=FilamentAiReplyActionTest

# فحص السينتاكس لملف محدد
php -l app/Services/NotificationService.php
```

---

## حسابات الدخول الافتراضية

بعد تشغيل `migrate --seed` تتوفر الحسابات التالية:

| الدور | البريد الإلكتروني | كلمة المرور | لوحة التحكم |
|------|-------------------|-------------|------------|
| مدير النظام | `admin@gov.sy` | `password` | `/admin` |
| موظف 1 | `emp1@gov.sy` | `password` | `/employee` |
| موظف 2 | `emp2@gov.sy` | `password` | `/employee` |
| مواطن 1-5 | `citizen1@example.com` ... `citizen5@example.com` | `password` | واجهة المواطن |

---

## هيكل المشروع

### لوحات التحكم

| المسار | الوصف | الدور المطلوب |
|--------|-------|---------------|
| `/admin` | لوحة تحكم المدير | admin |
| `/employee` | لوحة تحكم الموظف | employee |
| `/` (واجهة المواطن) | الواجهة الأمامية للمواطنين | citizen (بعد تسجيل الدخول) |

### المسارات الرئيسية للمواطن

| المسار | الوصف |
|--------|-------|
| `/complaints` | قائمة الشكاوى |
| `/complaints/create` | تقديم شكوى جديدة |
| `/complaints/{id}` | عرض تفاصيل الشكوى |
| `/inquiries` | قائمة الاستفسارات |
| `/inquiries/create` | تقديم استفسار جديد |
| `/inquiries/{id}` | عرض تفاصيل الاستفسار |
| `/bills` | قائمة الفواتير |
| `/notifications` | قائمة الإشعارات |

---

## الميزات الرئيسية

### 1. الذكاء الاصطناعي
- **تصنيف الشكاوى تلقائياً** — عند تقديم شكوى يصنّفها AI حسب الأولوية (عالية/متوسطة/منخفضة) ويُلخّصها
- **تحسين نص الشكوى** — المواطن يمكنه تحسين صياغة شكواه عبر AI
- **توليد رد رسمي** — الموظف/المدير يمكنه توليد رد رسمي من ملاحظة قصيرة عبر زر "توليد رد رسمي" في لوحة التحكم
- **التحقق من المرفقات** — التحقق بالذكاء الاصطناعي من صلاحية المرفقات المُرفقة

### 2. نظام الإشعارات
- إشعارات فورية في لوحات Filament (المدير والموظف) عند تقديم شكوى/استفسار جديد
- إشعار للموظف المسند إليه عند إسناد شكوى/استفسار له
- إشعار للمواطن عند تحديث حالة المعاملة
- الإشعارات تُحفظ في جدولين: `notifications` (Filament) و `custom_notifications` (مخصص)

### 3. لوحة تحكم المدير (`/admin`)
- إدارة الشكاوى والاستفسارات والفواتير والمستخدمين والأدوار
- عرض إحصائيات عبر ويدجت Dashboard
- تغيير حالة المعاملات والرد عليها

### 4. لوحة تحكم الموظف (`/employee`)
- عرض الشكاوى والاستفسارات المسندة إليه
- إحصائيات شخصية (طلبات قيد الانتظار، مكتملة) + مخطط بياني + جدول إحصائي
- تغيير حالة المعاملة وتوليد رد رسمي بالذكاء الاصطناعي

---

## إعدادات مهمة في `.env`

```env
# قاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gov_app
DB_USERNAME=root
DB_PASSWORD=

# التخزين
FILESYSTEM_DISK=public

# الطابور (استخدم sync للإشعارات الفورية، أو database مع queue:work)
QUEUE_CONNECTION=database

# الذكاء الاصطناعي
AI_API_URL=https://your-api-endpoint.com/chat/completions
AI_API_KEY=your-api-key
AI_MODEL=your-model-name
AI_MAX_TOKENS=4096
AI_TEMPERATURE=0.7

# اللغة الافتراضية
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
```

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| الإشعارات لا تظهر في لوحة Filament | تأكد من تشغيل `php artisan queue:work` أو ضع `QUEUE_CONNECTION=sync` في `.env` |
| خطأ "Class not found" بعد pull | شغّل `composer install` و `php artisan filament:upgrade` |
| الواجهة لا تظهر بشكل صحيح | شغّل `npm run build` أو `npm run dev` |
| خطأ "No application encryption key" | شغّل `php artisan key:generate` |
| الصور/المرفقات لا تظهر | شغّل `php artisan storage:link` |
| إشعارات Filament لا تصل | الإشعارات تُحفظ مباشرة عبر `NotificationService` بدون طابور، تحقق من جدول `notifications` في قاعدة البيانات |

---

## أوامر مفيدة

```bash
# مسح الكاش
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# إعادة الهجرات
php artisan migrate:fresh --seed

# نشر ملفات Filament
php artisan filament:upgrade

# فحص السينتاكس لجميع ملفات PHP المعدّلة
find app/ -name "*.php" -exec php -l {} \; 2>&1 | grep -v "No syntax errors"

# تشغيل اختبار محدد
php artisan test --filter=NotificationTest
```