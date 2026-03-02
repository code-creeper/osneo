<span>
    @if($location == 'navbar')
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none " data-bs-toggle="dropdown" href="#" role="button"
               aria-haspopup="false" aria-expanded="false">
                <i class="fal fa-bell noti-icon"></i>
                @if($unreadNotificationsCount)
                    <span class="translate-middle badge rounded-pill bg-danger position-absolute"
                          style="top: 40%;left: 80%">
                        {{ $unreadNotificationsCount }}
                        <span class="visually-hidden">{{ __('unread messages') }}</span>
                    </span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg " style=" width: 500px">

                <div class="dropdown-item noti-title">
                    <h5 class="m-0">
                        @if($unreadNotificationsCount)
                            <span class="float-end">
                                <a href="javascript:void(0)" wire:click="readAll" class="text-dark">
                                    <small>{{ __('Clear All') }}</small>
                                </a>
                            </span>
                        @endif
                        {{ __('Notification') }}
                    </h5>
                </div>

                <div style="max-height: 600px;" data-simplebar>

                    @forelse($notifications as $notification)
                        <div class="dropdown-item notify-item {{ $notification->read_at ? '' : 'bg-light' }}">
                            <div class="notify-icon bg-primary">
                                <i class="{{ $notification->icon }}"></i>
                            </div>
                            <p class="notify-details">
                                <span class="d-flex justify-content-between">
                                    <span>{!! $notification->message !!}</span>
                                    <i wire:click="toggleRead('{{$notification->id}}')"
                                       class="cursor-pointer fal fa-envelope{{$notification->read_at ? '-open' : ''}}"></i>
                                </span>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    @empty
                        <a href="javascript:void(0);" class="dropdown-item notify-item text-center">
                            {{ __("You're All Caught Up!") }}
                        </a>
                    @endforelse
                </div>


                @if($notificationsCount)
                    <a href="{{ route('notifications') }}" class="dropdown-item text-center text-primary notify-item notify-all">
                        {{ __('View All') }}
                    </a>
                @endif

            </div>
        </li>
    @else
        <div class="card">
            <div class="card-header">
                <h5 class="my-0">
                    @if($unreadNotificationsCount)
                        <span class="float-end">
                            <a href="javascript:void(0)" wire:click="readAll" class="text-primary">
                                <span >{{ __('Mark all as read') }}</span>
                            </a>
                        </span>
                    @endif
                    {{ __('Notifications') }}
                </h5>
            </div>
            <div class="card-body px-0">
                <div class="notification-list">
                    <div>
                        @forelse($notifications as $notification)
                            <div class="notify-item {{ $notification->read_at ? '' : 'bg-light' }}">
                                <div class="notify-icon bg-primary">
                                    <i class="{{ $notification->icon }}"></i>
                                </div>
                                <p class="notify-details">
                                    <span class="d-flex justify-content-between">
                                        <span>{!! $notification->message !!}</span>
                                        <i wire:click="toggleRead('{{$notification->id}}')"
                                           class="cursor-pointer fal fa-envelope{{$notification->read_at ? '-open' : ''}}"></i>
                                    </span>
                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                </p>
                            </div>
                        @empty
                            <a href="javascript:void(0);" class="dropdown-item notify-item text-center">
                                {{ __("You're All Caught Up!") }}
                            </a>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</span>
