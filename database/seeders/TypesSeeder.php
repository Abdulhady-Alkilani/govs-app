<?php

namespace Database\Seeders;

use App\Models\ComplaintType;
use App\Models\InquiryType;
use Illuminate\Database\Seeder;

class TypesSeeder extends Seeder
{
    public function run(): void
    {
        $complaintTypes = [
            ['name' => 'مياه', 'description' => 'شكاوى متعلقة بخدمات المياه والصرف الصحي', 'is_active' => true],
            ['name' => 'كهرباء', 'description' => 'شكاوى متعلقة بخدمات الكهرباء والتوصيلات', 'is_active' => true],
            ['name' => 'نظافة', 'description' => 'شكاوى متعلقة بخدمات النظافة وجمع النفايات', 'is_active' => true],
            ['name' => 'طرق', 'description' => 'شكاوى متعلقة بالطرق والبنية التحتية', 'is_active' => true],
            ['name' => 'صرف صحي', 'description' => 'شكاوى متعلقة بشبكة الصرف الصحي', 'is_active' => true],
            ['name' => 'اتصالات وإنترنت', 'description' => null, 'is_active' => true],
            ['name' => 'بيئة وتلوث', 'description' => null, 'is_active' => true],
            ['name' => 'أمن وسلامة', 'description' => null, 'is_active' => true],
            ['name' => 'تموين وأسعار', 'description' => null, 'is_active' => true],
            ['name' => 'خدمات صحية', 'description' => null, 'is_active' => true],
        ];

        foreach ($complaintTypes as $type) {
            ComplaintType::updateOrCreate(['name' => $type['name']], $type);
        }

        $inquiryTypes = [
            ['name' => 'بيان عائلي', 'description' => 'طلب استخراج بيان عائلي', 'is_active' => true],
            ['name' => 'لا حكم عليه', 'description' => 'طلب شهادة عدم المحكومية', 'is_active' => true],
            ['name' => 'وثيقة ملكية', 'description' => 'طلب استخراج وثيقة ملكية عقار', 'is_active' => true],
            ['name' => 'استخراج جواز سفر', 'description' => null, 'is_active' => true],
            ['name' => 'تجديد بطاقة شخصية', 'description' => null, 'is_active' => true],
            ['name' => 'استعلام عن مخالفات مرورية', 'description' => null, 'is_active' => true],
            ['name' => 'تسجيل مركبة', 'description' => null, 'is_active' => true],
            ['name' => 'استخراج شهادة ميلاد', 'description' => null, 'is_active' => true],
        ];

        foreach ($inquiryTypes as $type) {
            InquiryType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
