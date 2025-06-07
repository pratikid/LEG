<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class UserSettings extends Component
{
    public string $name = '';

    public string $email = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);
        session()->flash('success', 'Settings updated!');
    }

    public function render(): View
    {
        return view('livewire.user-settings');
    }
}
