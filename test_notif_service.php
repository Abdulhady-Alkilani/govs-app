<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

echo "=== Testing NotificationService ===\n";

$admin = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first();
echo "Admin: " . ($admin ? "#{$admin->id} {$admin->name}" : 'NONE') . "\n";

$notifBefore = DB::table('notifications')->count();
$customBefore = DB::table('custom_notifications')->count();
echo "Filament notifications BEFORE: {$notifBefore}\n";
echo "Custom notifications BEFORE: {$customBefore}\n";

NotificationService::sendToUser(
    user: $admin,
    title: 'Test Notification',
    body: 'This is a test notification body',
    icon: 'heroicon-o-bell',
    color: 'info',
    actionUrl: '/admin/complaints',
);

$notifAfter = DB::table('notifications')->count();
$customAfter = DB::table('custom_notifications')->count();
echo "Filament notifications AFTER: {$notifAfter}\n";
echo "Custom notifications AFTER: {$customAfter}\n";

if ($notifAfter > $notifBefore) {
    $notif = DB::table('notifications')->latest()->first();
    echo "\nLatest Filament notification:\n";
    echo "  type: {$notif->type}\n";
    echo "  notifiable_type: {$notif->notifiable_type}\n";
    echo "  notifiable_id: {$notif->notifiable_id}\n";
    $data = json_decode($notif->data, true);
    echo "  title: " . ($data['title'] ?? 'N/A') . "\n";
    echo "  body: " . ($data['body'] ?? 'N/A') . "\n";
    echo "  format: " . ($data['format'] ?? 'N/A') . "\n";
    echo "\n=== SUCCESS! ===\n";
} else {
    echo "\n=== FAILURE! ===\n";
}
