<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow p-6">
            {{ $this->form }}
        </div>
        
        <!-- Reports Table -->
        <div class="bg-white rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
