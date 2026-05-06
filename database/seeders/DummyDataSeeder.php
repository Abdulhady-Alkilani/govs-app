<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Complaint;
use App\Models\Inquiry;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $citizenRole = Role::where('name', 'citizen')->first();
        $employeeRole = Role::where('name', 'employee')->first();
        $citizens = User::where('role_id', $citizenRole->id)->pluck('id')->toArray();
        $employees = User::where('role_id', $employeeRole->id)->pluck('id')->toArray();
        $statuses = ['pending', 'processing', 'completed', 'rejected'];
        $billTypes = ['water', 'electricity', 'other'];

        for ($i = 0; $i < 20; $i++) {
            $status = $statuses[array_rand($statuses)];

            Complaint::create([
                'citizen_id' => $citizens[array_rand($citizens)],
                'type_id' => rand(1, 5),
                'assigned_to' => $employees[array_rand($employees)],
                'description' => 'وصف الشكوى رقم '.($i + 1).' - مثال على نص الشكوى',
                'status' => $status,
                'internal_notes' => rand(0, 1) ? 'ملاحظات داخلية للشكوى رقم '.($i + 1) : null,
            ]);
        }

        for ($i = 0; $i < 20; $i++) {
            $status = $statuses[array_rand($statuses)];

            Inquiry::create([
                'citizen_id' => $citizens[array_rand($citizens)],
                'type_id' => rand(1, 3),
                'assigned_to' => $employees[array_rand($employees)],
                'status' => $status,
                'result_text' => $status === 'completed' ? 'نتيجة الاستعلام رقم '.($i + 1).': تمت المعالجة بنجاح' : null,
                'result_file_path' => null,
            ]);
        }

        for ($i = 0; $i < 20; $i++) {
            $status = rand(0, 1) ? 'paid' : 'unpaid';
            $paidAt = null;
            $transactionId = null;

            if ($status === 'paid') {
                $paidAt = now()->subDays(rand(1, 60));
                $transactionId = 'TXN-'.strtoupper(uniqid());
            }

            Bill::create([
                'citizen_id' => $citizens[array_rand($citizens)],
                'bill_type' => $billTypes[array_rand($billTypes)],
                'amount' => rand(1000, 50000),
                'status' => $status,
                'due_date' => now()->addDays(rand(-30, 90))->toDateString(),
                'paid_at' => $paidAt,
                'transaction_id' => $transactionId,
            ]);
        }

        for ($i = 0; $i < 10; $i++) {
            Notification::create([
                'user_id' => $citizens[array_rand($citizens)],
                'title' => 'إشعار رقم '.($i + 1),
                'message' => 'هذا نص الإشعار رقم '.($i + 1).' للتجربة',
                'is_read' => (bool) rand(0, 1),
            ]);
        }
    }
}
