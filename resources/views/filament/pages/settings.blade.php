<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="flex justify-end mt-6">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                <x-filament::loading-indicator wire:loading wire:target="save" class="h-5 w-5" />
                ذخیره تنظیمات
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
