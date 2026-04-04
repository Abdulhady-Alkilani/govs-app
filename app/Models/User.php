<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'national_id', 'phone', 'role_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     function canAccessPanel(\Filament\Panel $panel): bool
    {
        // التأكد من أن المستخدم يمتلك صلاحية قبل الفحص
        if (!$this->role) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->role->name === 'admin';
        }

        if ($panel->getId() === 'employee') {
            return $this->role->name === 'employee' || $this->role->name === 'admin'; 
        }

        return false;
    }


    // شكاوى المواطن
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'citizen_id');
    }

    // الشكاوى المسندة للموظف لمعالجتها
    public function assignedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_to');
    }

    // استعلامات المواطن
    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'citizen_id');
    }

    // الاستعلامات المسندة للموظف
    public function assignedInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'assigned_to');
    }

    // فواتير المواطن
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class, 'citizen_id');
    }

    // إشعارات المستخدم
    public function customNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // سجل حركات الموظف/المدير
    public function systemLogs(): HasMany
    {
        return $this->hasMany(SystemLog::class);
    }

    // إضافة علاقة الصلاحية
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}