<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
};
?>

<button type="button" wire:click="logout" class="block w-full px-3 py-2 text-start text-sm font-medium text-text hover:bg-surface-muted hover:text-text focus:outline-none">
    {{ __('Log Out') }}
</button>
