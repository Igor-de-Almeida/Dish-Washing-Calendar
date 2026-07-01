<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
    <div class="flex items-center gap-2">
        <select wire:change="setLocale($event.target.value)" class="bg-transparent border-0 text-sm focus:ring-0 cursor-pointer">
            <option value="pt" {{ app()->getLocale() === 'pt' ? 'selected' : '' }}>🇵🇹 Português</option>
            <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
        </select>
    </div>
</div>
