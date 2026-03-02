<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Notifications extends Component
{
    public $location = 'notifications';

    public function mount()
    {
    }

    public function getListeners(): array
    {
        $userId = auth()->id();
        return [
            "echo-private:App.Models.User.{$userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'notificationsListener',
            'refresh' => '$refresh'
        ];
    }

    public function render(): View
    {
        $data = array();

        $data['notifications'] = user()->notifications;
        $data['unreadNotificationsCount'] = user()->unreadNotifications->count();
        $data['notificationsCount'] = user()->notifications->count();

        return view('livewire.notifications', $data);
    }

    public function toggleRead(Notification $notification): void
    {
        if ($notification->read_at) {
            $notification->markAsUnread();
        } else {
            $notification->markAsRead();
        }
        $this->dispatch('refresh');
    }

    public function readAll(): void
    {
        user()->unreadNotifications->markAsRead();
        $this->dispatch('refresh');
    }

    public function notificationsListener($event): void
    {
        $this->dispatch('refresh');
        $this->dispatch('flashNotification', message: $event['message'] ?? __('You have received a new notification'));
    }
}
