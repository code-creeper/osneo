<x-layout>
    @php($activeTab = ['damages' => 'damages'][request('tab')] ?? 'vehicle_info')

    <x-tab :card="true">
        <x-slot name="tabs">
            <x-tab.item label="Vehicle Information" id="vehicle_info" :active="$activeTab == 'vehicle_info'"/>
            <x-tab.item label="Driver History" id="driver_history" permission="view vehicle driver history"/>
            <x-tab.item
                label="Vehicle History" id="vehicle_history"
                permission="view all vehicle histories|view own vehicle histories"
            />
            <x-tab.item label="Damages" id="damages" permission="view all damages|view own damages" :active="$activeTab == 'damages'"/>
        </x-slot>
        <x-tab.content id="vehicle_info" :active="$activeTab == 'vehicle_info'">
            @include('vehicle.maintenance.show', ['current_condition' => true])
        </x-tab.content>
        <x-tab.content id="driver_history" permission="view vehicle driver history">
            <div class="list-group">
                @foreach($vehicle->driverHistories as $history)
                    <li class="list-group-item {{ $loop->first ? 'active' : '' }}">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">{{ ($history->driver ?? \App\Models\User::forceFind($history->driver_id))->name }}</h5>
                            <small>
                                {{ $history->taken_at->format(config('dates.default')) }}
                                - {{ $history->handed_over_at ? $history->handed_over_at->format(config('dates.default')) : __('Present') }}
                            </small>
                        </div>
                    </li>
                @endforeach
            </div>
        </x-tab.content>
        <x-tab.content id="vehicle_history" permission="view all vehicle histories|view own vehicle histories">
            @forelse($vehicle->maintenanceHistories as $history)
                @include('vehicle.maintenance.show', ['condition' => $history])
            @empty
                <p class="m-0 p-3">{{ __('No Record Found') }}</p>
            @endforelse
        </x-tab.content>
        <x-tab.content id="damages" permission="view all damages|view own damages" :active="$activeTab == 'damages'">
            <livewire:datatables.damage-datatable :vehicle="$vehicle"/>
        </x-tab.content>
    </x-tab>
</x-layout>
