<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Profile')"
            :subtitle="__('Manage your account details and security.')"
        />
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card class="p-4 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <x-avatar :user="auth()->user()" size="lg" />

                    <div class="min-w-0">
                        <p class="text-base font-semibold text-text">{{ auth()->user()->name }}</p>
                        <p class="mt-1 text-sm text-text-muted">
                            <span class="font-medium">{{ __('Role') }}:</span>
                            {{ auth()->user()->role?->name?->label() ?? __('Unassigned') }}
                        </p>
                        <p class="mt-1 text-xs text-text-muted">
                            {{ __('Avatar colour') }}:
                            <span class="font-mono">{{ \App\Services\AvatarPalette::colorFor(auth()->user()) }}</span>
                        </p>
                    </div>
                </div>
            </x-card>

            <x-card class="p-4 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </x-card>

            <x-card class="p-4 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </x-card>

            <x-card class="p-4 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
