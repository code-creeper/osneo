<x-wire-elements-pro::bootstrap.modal>
    <x-slot name="title">{{ __('Auto Checkout Entries') }}</x-slot>
    <table class="table table-striped table-centered table-sm">
        <thead>
        <tr>
            <th>{{ __('Attendance Date') }}</th>
            <th>{{ __('Checkin') }}</th>
            <th>{{ __('Checkout') }}</th>
        </tr>
        </thead>
        @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->format(config('dates.attendance.date')) }}</td>
                <td>{{ $attendance->checked_in_at->format(config('dates.attendance.time')) }}</td>
                <td>{{ $attendance->checked_out_at->format(config('dates.attendance.time')) }}</td>
            </tr>
        @endforeach
    </table>
</x-wire-elements-pro::bootstrap.modal>
