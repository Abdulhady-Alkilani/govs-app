# 🚦 Traffic Violations Module — Implementation Plan

> **Source:** [prompt2.md](file:///d:/Tecjno-Injaz/traffic_app/prompt2.md)
> **Project:** Traffic Reports & Road Safety System (Laravel 12 + FilamentPHP)

---

## Summary

This plan implements the **Traffic Violations (المخالفات المرورية)** module — allowing Police officers to issue fines to citizens/vehicles, with full admin oversight and a citizen-facing violations dashboard. The module integrates with the existing Report, Citizen, Vehicle, and Police data models.

---

## Proposed Changes

### Phase 1: Enum — Violation Status

#### [NEW] [ViolationStatus.php](file:///d:/Tecjno-Injaz/traffic_app/app/Enums/ViolationStatus.php)

Create a `ViolationStatus` enum following the existing `ReportStatus` pattern:

```php
enum ViolationStatus: string {
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Canceled = 'canceled';
}
```

- Include `label()` method (EN labels)
- Include `color()` method → `unpaid` = `danger`, `paid` = `success`, `canceled` = `gray`

---

### Phase 2: Database — Migration

#### [NEW] [create_traffic_violations_table.php](file:///d:/Tecjno-Injaz/traffic_app/database/migrations/0001_01_01_000011_create_traffic_violations_table.php)

Migration matching the DBML schema:

| Column | Type | Constraints |
|---|---|---|
| `id` | `increments` | PK |
| `citizen_id` | `foreignId` | → `citizens_data.id`, NOT NULL, `cascadeOnDelete` |
| `vehicle_id` | `foreignId` | → `vehicles.id`, NULLABLE |
| `police_id` | `foreignId` | → `police_data.id`, NOT NULL |
| `report_id` | `foreignId` | → `reports.id`, NULLABLE |
| `violation_type` | `string` | NOT NULL |
| `description` | `text` | NULLABLE |
| `fine_amount` | `decimal(8,2)` | NOT NULL |
| `status` | `string` | DEFAULT `'unpaid'` (cast to enum in model) |
| `issued_at` | `timestamp` | DEFAULT `now()` |
| `due_date` | `date` | NOT NULL |
| `timestamps` | | `created_at`, `updated_at` |

---

### Phase 3: Model — TrafficViolation

#### [NEW] [TrafficViolation.php](file:///d:/Tecjno-Injaz/traffic_app/app/Models/TrafficViolation.php)

Eloquent model with:

- **Fillable:** all columns except `id` and timestamps
- **Casts:** `status` → `ViolationStatus`, `fine_amount` → `decimal:2`, `issued_at` → `datetime`, `due_date` → `date`
- **Relationships:**
  - `citizen()` → `belongsTo(CitizenData::class, 'citizen_id')`
  - `vehicle()` → `belongsTo(Vehicle::class)`
  - `police()` → `belongsTo(PoliceData::class, 'police_id')`
  - `report()` → `belongsTo(Report::class)`

#### [MODIFY] [CitizenData.php](file:///d:/Tecjno-Injaz/traffic_app/app/Models/CitizenData.php)

- Add `violations()` → `hasMany(TrafficViolation::class, 'citizen_id')`

#### [MODIFY] [PoliceData.php](file:///d:/Tecjno-Injaz/traffic_app/app/Models/PoliceData.php)

- Add `violations()` → `hasMany(TrafficViolation::class, 'police_id')`

#### [MODIFY] [Vehicle.php](file:///d:/Tecjno-Injaz/traffic_app/app/Models/Vehicle.php)

- Add `violations()` → `hasMany(TrafficViolation::class)`

#### [MODIFY] [Report.php](file:///d:/Tecjno-Injaz/traffic_app/app/Models/Report.php)

- Add `violations()` → `hasMany(TrafficViolation::class)`

---

### Phase 4: Police Panel (`/police`)

#### [NEW] [TrafficViolationResource.php](file:///d:/Tecjno-Injaz/traffic_app/app/Filament/Police/Resources/TrafficViolationResource.php)

Resource for police officers to manage violations **they issued**:

- **Global Scope:** Filter by `auth()->user()->policeData->id` via `modifyQueryUsing`
- **Create Form:**
  - `citizen_id` — Searchable Select from `citizens_data` (display `full_name + national_id`)
  - `vehicle_id` — Dependent Select, filtered by selected `citizen_id` (shows plate_number)
  - `violation_type` — Select with options: `Speeding`, `Reckless Driving`, `Red Light`, `Illegal Parking`, `No Seatbelt`, `Using Phone`
  - `description` — Textarea
  - `fine_amount` — TextInput (numeric, min:0.01)
  - `due_date` — DatePicker (min: today)
- **Auto-set:** `police_id` from auth, `issued_at` = now
- **List Table:** ID, Citizen Name, Vehicle Plate, Type, Amount, Status (badge with color), Issued At
- **Validation:** `fine_amount` > 0, `due_date` must be future

#### [NEW] Pages (List / Create / Edit)

- `app/Filament/Police/Resources/TrafficViolationResource/Pages/ListTrafficViolations.php`
- `app/Filament/Police/Resources/TrafficViolationResource/Pages/CreateTrafficViolation.php`
- `app/Filament/Police/Resources/TrafficViolationResource/Pages/EditTrafficViolation.php`

#### [MODIFY] [ViewAssignedReport.php](file:///d:/Tecjno-Injaz/traffic_app/app/Filament/Police/Resources/AssignedReportResource/Pages/ViewAssignedReport.php)

Add **"Issue Violation" (إصدار مخالفة)** header action:

- Opens a modal form with fields: `violation_type`, `description`, `fine_amount`, `due_date`
- **Auto-fills** from current report: `report_id`, `citizen_id`, `vehicle_id`
- **Auto-sets** `police_id` from `auth()->user()->policeData->id`
- On submit: creates `TrafficViolation` record and shows success notification

---

### Phase 5: Admin Panel (`/admin`)

#### [NEW] [TrafficViolationResource.php](file:///d:/Tecjno-Injaz/traffic_app/app/Filament/Admin/Resources/TrafficViolationResource.php)

Full admin resource — **no scope filtering** (sees ALL violations):

- **List Table:** ID, Citizen (searchable), Vehicle Plate, Officer Name, Type, Amount, Status (badge), Issued At, Due Date
- **Filters:** Status filter, date range
- **Search:** By citizen national_id or name
- **Admin Action:** Can change status to `canceled` (Edit form)
- **Read-only fields** for most data; only `status` is editable

#### [NEW] Pages (List / Edit / View)

- `app/Filament/Admin/Resources/TrafficViolationResource/Pages/ListTrafficViolations.php`
- `app/Filament/Admin/Resources/TrafficViolationResource/Pages/EditTrafficViolation.php`
- `app/Filament/Admin/Resources/TrafficViolationResource/Pages/ViewTrafficViolation.php`

#### [NEW] [ViolationsRelationManager.php](file:///d:/Tecjno-Injaz/traffic_app/app/Filament/Admin/Resources/UserResource/RelationManagers/ViolationsRelationManager.php)

- Displays all violations for a citizen user via `citizenData.violations`
- Columns: ID, Type, Amount, Status (badge), Due Date, Officer Name
- Read-only — admin views violations within User context

#### [MODIFY] [UserResource.php](file:///d:/Tecjno-Injaz/traffic_app/app/Filament/Admin/Resources/UserResource.php)

- Register `ViolationsRelationManager` in `getRelations()` array

#### [MODIFY] [StatsOverview.php](file:///d:/Tecjno-Injaz/traffic_app/app/Filament/Admin/Widgets/StatsOverview.php)

Add 2 new Stat widgets:

1. **"Total Unpaid Fines (SAR)"** — `TrafficViolation::where('status', 'unpaid')->sum('fine_amount')` with `money` formatting
2. **"Violations This Week"** — `TrafficViolation::where('created_at', '>=', now()->startOfWeek())->count()`

---

### Phase 6: Citizen Portal (Frontend)

#### [MODIFY] [DashboardController.php](file:///d:/Tecjno-Injaz/traffic_app/app/Http/Controllers/Citizen/DashboardController.php)

- Query `TrafficViolation::where('citizen_id', $citizenData->id)` with vehicle relation
- Paginate with `violations_page` param
- Pass `$violations` to the view

#### [MODIFY] [dashboard.blade.php](file:///d:/Tecjno-Injaz/traffic_app/resources/views/citizen/dashboard.blade.php)

Add **"My Violations" (مخالفاتي)** tab alongside existing tabs:

- New tab button with violations count
- **Data Table Columns:** ID, Date (`issued_at`), Type, Amount (formatted SAR), Vehicle Plate Number, Status
- **Badges:** Red (`bg-red-100 text-red-800`) for Unpaid, Green (`bg-green-100 text-green-800`) for Paid, Gray for Canceled
- **"Pay Now" (دفع الآن)** button: Visible only for `unpaid` violations → mock JS alert + AJAX POST to change status to `paid`
- Pagination using `$violations->withQueryString()->links()`

#### [NEW] [ViolationController.php](file:///d:/Tecjno-Injaz/traffic_app/app/Http/Controllers/Citizen/ViolationController.php)

- `mockPay(TrafficViolation $violation)` — POST route that:
  - Validates the violation belongs to the current citizen
  - Changes status from `unpaid` → `paid`
  - Returns JSON success response

#### [MODIFY] [web.php](file:///d:/Tecjno-Injaz/traffic_app/routes/web.php)

Add route inside citizen middleware group:

```php
Route::post('/violations/{violation}/pay', [ViolationController::class, 'mockPay'])->name('violations.pay');
```

---

### Phase 7: i18n & Seeding

#### [MODIFY] [lang/en/messages.php](file:///d:/Tecjno-Injaz/traffic_app/lang/en/messages.php)

Add translations:

```php
'my_violations' => 'My Violations',
'violation' => 'Violation',
'violations' => 'Violations',
'violation_type' => 'Violation Type',
'fine_amount' => 'Fine Amount',
'due_date' => 'Due Date',
'issued_at' => 'Issue Date',
'status' => 'Status',
'unpaid' => 'Unpaid',
'paid' => 'Paid',
'canceled' => 'Canceled',
'pay_now' => 'Pay Now',
'payment_success' => 'Payment processed successfully!',
'no_violations' => 'No violations found.',
'issue_violation' => 'Issue Violation',
'speeding' => 'Speeding',
'reckless_driving' => 'Reckless Driving',
'red_light' => 'Red Light',
'illegal_parking' => 'Illegal Parking',
'no_seatbelt' => 'No Seatbelt',
'using_phone' => 'Using Phone While Driving',
'officer' => 'Officer',
'total_unpaid_fines' => 'Total Unpaid Fines (SAR)',
'violations_this_week' => 'Violations Issued This Week',
```

#### [MODIFY] [lang/ar/messages.php](file:///d:/Tecjno-Injaz/traffic_app/lang/ar/messages.php)

Add Arabic translations:

```php
'my_violations' => 'مخالفاتي',
'violation' => 'مخالفة',
'violations' => 'المخالفات',
'violation_type' => 'نوع المخالفة',
'fine_amount' => 'مبلغ الغرامة',
'due_date' => 'تاريخ الاستحقاق',
'issued_at' => 'تاريخ الإصدار',
'status' => 'الحالة',
'unpaid' => 'غير مدفوعة',
'paid' => 'مدفوعة',
'canceled' => 'ملغاة',
'pay_now' => 'دفع الآن',
'payment_success' => 'تمت عملية الدفع بنجاح!',
'no_violations' => 'لا توجد مخالفات.',
'issue_violation' => 'إصدار مخالفة',
'speeding' => 'تجاوز السرعة',
'reckless_driving' => 'قيادة متهورة',
'red_light' => 'قطع إشارة حمراء',
'illegal_parking' => 'وقوف غير نظامي',
'no_seatbelt' => 'عدم ربط حزام الأمان',
'using_phone' => 'استخدام الهاتف أثناء القيادة',
'officer' => 'الضابط',
'total_unpaid_fines' => 'إجمالي الغرامات غير المدفوعة (ر.س)',
'violations_this_week' => 'المخالفات الصادرة هذا الأسبوع',
```

#### [MODIFY] [DatabaseSeeder.php](file:///d:/Tecjno-Injaz/traffic_app/database/seeders/DatabaseSeeder.php)

Add seeding loop after reports creation — generate **20+ random violations**:

```php
$policeOfficers = PoliceData::all();
$violationTypes = ['speeding', 'reckless_driving', 'red_light', 'illegal_parking', 'no_seatbelt', 'using_phone'];

for ($i = 0; $i < 25; $i++) {
    $citizen = fake()->randomElement($citizens);
    $vehicle = fake()->optional(0.7)->randomElement($citizen->vehicles->toArray());
    TrafficViolation::create([
        'citizen_id'     => $citizen->id,
        'vehicle_id'     => $vehicle?->id ?? null,
        'police_id'      => fake()->randomElement($policeOfficers)->id,
        'report_id'      => fake()->optional(0.3)->randomElement(Report::pluck('id')->toArray()),
        'violation_type'  => fake()->randomElement($violationTypes),
        'description'     => fake()->optional()->sentence(),
        'fine_amount'     => fake()->randomElement([100, 150, 300, 500, 1000, 2000]),
        'due_date'        => fake()->dateTimeBetween('now', '+90 days')->format('Y-m-d'),
        'status'          => fake()->randomElement(['unpaid', 'unpaid', 'unpaid', 'paid', 'canceled']),
        'issued_at'       => fake()->dateTimeBetween('-60 days', 'now'),
    ]);
}
```

---

## Files Summary

| # | Action | File | Phase |
|---|--------|------|-------|
| 1 | **NEW** | `app/Enums/ViolationStatus.php` | 1 |
| 2 | **NEW** | `database/migrations/0001_01_01_000011_create_traffic_violations_table.php` | 2 |
| 3 | **NEW** | `app/Models/TrafficViolation.php` | 3 |
| 4 | MODIFY | `app/Models/CitizenData.php` | 3 |
| 5 | MODIFY | `app/Models/PoliceData.php` | 3 |
| 6 | MODIFY | `app/Models/Vehicle.php` | 3 |
| 7 | MODIFY | `app/Models/Report.php` | 3 |
| 8 | **NEW** | `app/Filament/Police/Resources/TrafficViolationResource.php` | 4 |
| 9 | **NEW** | `app/Filament/Police/Resources/TrafficViolationResource/Pages/*.php` (×3) | 4 |
| 10 | MODIFY | `app/Filament/Police/Resources/AssignedReportResource/Pages/ViewAssignedReport.php` | 4 |
| 11 | **NEW** | `app/Filament/Admin/Resources/TrafficViolationResource.php` | 5 |
| 12 | **NEW** | `app/Filament/Admin/Resources/TrafficViolationResource/Pages/*.php` (×3) | 5 |
| 13 | **NEW** | `app/Filament/Admin/Resources/UserResource/RelationManagers/ViolationsRelationManager.php` | 5 |
| 14 | MODIFY | `app/Filament/Admin/Resources/UserResource.php` | 5 |
| 15 | MODIFY | `app/Filament/Admin/Widgets/StatsOverview.php` | 5 |
| 16 | MODIFY | `app/Http/Controllers/Citizen/DashboardController.php` | 6 |
| 17 | MODIFY | `resources/views/citizen/dashboard.blade.php` | 6 |
| 18 | **NEW** | `app/Http/Controllers/Citizen/ViolationController.php` | 6 |
| 19 | MODIFY | `routes/web.php` | 6 |
| 20 | MODIFY | `lang/en/messages.php` | 7 |
| 21 | MODIFY | `lang/ar/messages.php` | 7 |
| 22 | MODIFY | `database/seeders/DatabaseSeeder.php` | 7 |

> **Total: 13 new files, 9 modified files**

---

## Verification Plan

### Automated Tests

```bash
php artisan migrate --seed          # Verify migration + seeding
php artisan route:list              # Verify new route registered
```

### Manual Verification

1. **Police Panel** (`/police`):
   - Login as officer → see only own violations in TrafficViolationResource
   - Open an Assigned Report → click "Issue Violation" → verify modal auto-fills report/citizen/vehicle
   - Create a standalone violation → verify it appears in list with correct status badge

2. **Admin Panel** (`/admin`):
   - Login as admin → see ALL violations system-wide
   - Open a User (citizen) → verify ViolationsRelationManager tab shows their fines
   - Dashboard → verify 2 new stat widgets show correct data
   - Change violation status to `canceled`

3. **Citizen Portal** (`/citizen/dashboard`):
   - Login as citizen → see "My Violations" tab with correct count
   - Verify table columns, badges, and pagination
   - Click "Pay Now" → confirm mock alert → status changes to `paid`

4. **i18n** — Switch to Arabic → verify all new strings display correctly in both panels and portal
