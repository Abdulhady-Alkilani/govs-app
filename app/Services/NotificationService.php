<?php

namespace App\Services;

use App\Models\Notification as CustomNotification;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    /**
     * إرسال إشعار Filament لقاعدة البيانات بشكل متزامن (بدون طابور)
     * كما يحفظ نسخة في جدول الإشعارات المخصص.
     */
    public static function sendToUser(
        User $user,
        string $title,
        string $body,
        string $icon = 'heroicon-o-bell',
        string $color = 'info',
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        $filamentNotif = FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->color($color);

        if ($actionUrl) {
            $filamentNotif->actions([
                Action::make('view')
                    ->label($actionLabel ?? __('عرض'))
                    ->url($actionUrl),
            ]);
        }

        $data = $filamentNotif->getDatabaseMessage();

        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'Filament\\Notifications\\DatabaseNotification',
            'data' => $data,
            'read_at' => null,
        ]);

        CustomNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $body,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);
    }

    /**
     * إرسال إشعار لعدة مستخدمين
     */
    public static function sendToUsers(
        iterable $users,
        string $title,
        string $body,
        string $icon = 'heroicon-o-bell',
        string $color = 'info',
        ?string $actionUrl = null,
        ?string $actionLabel = null,
    ): void {
        foreach ($users as $user) {
            self::sendToUser($user, $title, $body, $icon, $color, $actionUrl, $actionLabel);
        }
    }
}
