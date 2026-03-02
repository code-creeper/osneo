<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Navbar extends Component
{
    protected $listeners = ['refreshPage' => '$refresh'];

    public string $selectedLocale;

    public function mount(): void
    {
        $this->selectedLocale = session()->get('locale', app()->getLocale());
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.navbar', $data);
    }

    public function switchLocale($language): void
    {
        if ( ! Arr::has(getLocales(), $language)) {
            return;
        }

        session()->put('locale', $language);
        $this->selectedLocale = $language;

        $this->dispatch('flashNotification', message: __('Language changed'));
        $this->dispatch('refreshPage');

        $this->js('window.location.reload()');
    }

    public function toggleSidebar(): void
    {
        //TODO:: should be handled by alpine JS. annoying to do a full reload only to toggle sidebar
        $cacheKey = 'leftSidebarCondensed' . Auth::id();

        $value = Cache::remember($cacheKey, now()->addHours(8), fn() => true);
        Cache::put($cacheKey, !$value, now()->addHours(8));

        $this->js('window.location.reload()');
    }

    public function backToAdmin(): void
    {
        Auth::loginUsingId(session()->pull('admin_id'));
        session()->forget('admin_id');

        $username = auth()->user()->name;
        $this->dispatch('flashNotification', message: __('Welcome back :user', ['user' => $username]));

        $this->dispatch('refresh');
        $this->dispatch('refreshPage');
    }
}
