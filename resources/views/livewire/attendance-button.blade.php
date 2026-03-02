<span>
    <x-loader :fullscreen="true"/>
    @can('create attendance')
        <li class="notification-list">
            <a class="nav-link" href="javascript: void(0);" wire:click="toggleAttendance" wire:loading.class="disabled-link">
                <i class="far fa-{{ user()->attendanceHasStarted() ? 'pause' : 'play' }} noti-icon" wire:loading.class="text-light"></i>
            </a>
        </li>
    @endcan
</span>
