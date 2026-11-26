<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="__('Settings')"
            :subtitle="__('Manage your appearance, notifications and account.')"
        />
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card class="p-4 sm:p-8">
                <section>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Appearance') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Choose a light, dark or system theme. Your choice is stored on this device only.') }}
                    </p>

                    <div class="mt-4">
                        <x-theme-toggle variant="settings" />
                    </div>
                </section>
            </x-card>

            <x-card class="p-4 sm:p-8">
                <livewire:settings.update-notifications />
            </x-card>

            <x-card class="p-4 sm:p-8">
                <section>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Account') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('Your account details are managed from your profile page.') }}
                    </p>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-700">{{ __('Name') }}</dt>
                            <dd class="text-gray-600">{{ auth()->user()->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">{{ __('Email') }}</dt>
                            <dd class="text-gray-600">{{ auth()->user()->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-700">{{ __('Role') }}</dt>
                            <dd class="text-gray-600">{{ auth()->user()->role?->name?->label() ?? __('Unassigned') }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <x-button :href="route('profile')" variant="secondary" wire:navigate>
                            {{ __('Edit profile') }}
                        </x-button>
                    </div>
                </section>
            </x-card>
        </div>
    </div>
</x-app-layout>
