@php($showType = $showType ?? true)

{{-- Honeypot: hidden from people, catnip for bots. --}}
<div class="hidden" aria-hidden="true">
    <label for="website">{{ __('Website') }}</label>
    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
</div>

@if ($showType)
    <div>
        <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
        <select
            name="type"
            id="type"
            required
            class="mt-1 block w-full min-h-[44px] rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
        >
            <option value="consultation" @selected(old('type', 'consultation') === 'consultation')>
                {{ __('Consultation') }}
            </option>
            <option value="inspection" @selected(old('type') === 'inspection')>
                {{ __('Inspection') }}
            </option>
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>
@endif

<div>
    <x-input
        name="name"
        type="text"
        :label="__('Name')"
        :value="old('name', auth()->user()?->name)"
        required
        autocomplete="name"
    />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input
        name="phone"
        type="tel"
        :label="__('Phone')"
        :value="old('phone')"
        required
        autocomplete="tel"
    />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div>
    <x-input
        name="email"
        type="email"
        :label="__('Email')"
        :value="old('email', auth()->user()?->email)"
        required
        autocomplete="email"
    />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<div>
    <x-input
        name="preferred_date"
        type="date"
        :label="__('Preferred date (optional)')"
        :value="old('preferred_date')"
    />
    <x-input-error :messages="$errors->get('preferred_date')" class="mt-2" />
</div>

<div>
    <label for="message" class="block text-sm font-medium text-gray-700">{{ __('Message') }}</label>
    <textarea
        name="message"
        id="message"
        rows="4"
        required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
    >{{ old('message') }}</textarea>
    <x-input-error :messages="$errors->get('message')" class="mt-2" />
</div>
