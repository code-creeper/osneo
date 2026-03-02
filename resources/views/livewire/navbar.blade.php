<div class="navbar-custom" style="z-index: 10!important;">
    <ul class="list-unstyled topbar-menu float-end mb-0">
        <x-loader :fullscreen="true"/>

        @if(session()->has('admin_id'))
            <li class="dropdown d-none d-lg-block mt-2">
                <button class="btn btn-sm mt-1 btn-outline-primary" wire:click="backToAdmin">
                    <i class="fas fa-arrow-left"></i> {{ __('Back To Admin') }}
                </button>
            </li>
        @endif

        <li class="dropdown notification-list topbar-dropdown">
            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <img src="{{ asset("assets/images/flags/$selectedLocale.jpg") }}" alt="user-image" class="me-0 me-sm-1" height="12">
                <span class="align-middle d-none d-sm-inline-block">{{ getLocales()[$selectedLocale] ?? '' }}</span>
                <i class="mdi mdi-chevron-down d-none d-sm-inline-block align-middle"></i>
            </a>

            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu" style="">
                @foreach(getLocales() as $locale => $name)
                    @continue($locale == $selectedLocale)
                    <a href="#" wire:click="switchLocale(@js($locale))" class="dropdown-item notify-item">
                        <img src="{{ asset("assets/images/flags/$locale.jpg") }}"
                             alt="user-image" class="me-1" height="12">
                        <span class="align-middle">{{ $name }}</span>
                    </a>
                @endforeach
            </div>
        </li>

        @can('upload documents')
            <li class="notification-list">
                <a class="nav-link" href="javascript: void(0);"
                   wire:modal="modals.documents-uploader">
                    <i class="fal fa-cloud-upload noti-icon"></i>
                </a>
            </li>
        @endcan

        @can('view vehicles')
            <li class="notification-list">
                <a class="nav-link" href="javascript: void(0);" wire:modal="modals.select-vehicle">
                    <i class="far fa-car noti-icon"></i>
                </a>
            </li>
        @endcan

        <livewire:attendance-button/>

        <livewire:notifications location="navbar"/>

        <li class="dropdown notification-list" >
            <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" href="#"
               role="button" aria-haspopup="false"
               aria-expanded="false">
                <span class="account-user-avatar">
                    <img src="{{ user()->avatar_url }}" alt="user-image" class="rounded-circle">
                </span>
                <span>
                    <span class="account-user-name">{{ user()->name }}</span>
                    <span class="account-position">{{ user()->role?->display_name }}</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                <div class=" dropdown-header noti-title">
                    <h6 class="text-overflow m-0">{{ __('Welcome') }} {{ user()->first_name }}!</h6>
                </div>

                <a href="#"
                   wire:modal="forms.profile-form"
                   class="dropdown-item notify-item">
                    <i class="mdi mdi-account-circle me-1"></i>
                    <span>{{ __('My Account') }}</span>
                </a>

                <a href="https://account.activedirectory.windowsazure.com/ChangePassword.aspx" class="dropdown-item notify-item">
                    <i class="mdi mdi-key-chain me-1"></i>
                    <span>{{ __('Update Password') }}</span>
                </a>

                @can('access cloud')
                    <a href="https://myapps.microsoft.com/waerme-wimmer.de" class="dropdown-item notify-item" target="_blank" rel="noopener">
                        <i class="mdi dripicons-view-apps me-1"></i>
                        <span>{{ __('Apps') }}</span>
                    </a>
                    <a href="https://cloud.waerme-wimmer.osneo.de/" class="dropdown-item notify-item" target="_blank" rel="noopener">
                        <i class="mdi dripicons-cloud me-1"></i>
                        <span>{{ __('Cloud') }}</span>
                    </a>
                @endcan

                @can('view all attendance|view own attendance')
                    <a href="{{ route('attendances.index') }}" class="dropdown-item notify-item">
                        <i class="far fa-calendar-check me-1"></i>
                        <span>{{ __('My Attendance') }}</span>
                    </a>
                @endcan

                <a href="https://intern.osneo.de/" class="dropdown-item notify-item" target="_blank" rel="noopener">
                    <i class="mdi dripicons-information me-1"></i>
                    <span>{{ __('Help') }}</span>
                </a>

                <a href="javascript:void(0);"
                   @click="$refs.logoutForm.submit()"
                   class="dropdown-item notify-item">
                    <i class="mdi mdi-logout me-1"></i>
                    <span>{{ __('Logout') }}</span>
                </a>
            </div>

            <form x-ref="logoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>

    </ul>

    <a href="#" wire:click="toggleSidebar" class="d-none d-xl-block" style="
	border: none;
    color: #313a46;
    height: 70px;
    line-height: 70px;
    width: 60px;
    background-color: transparent;
    font-size: 24px;
    cursor: pointer;
    float: left;
    z-index: 1;
    position: relative;
    margin-left: -24px;
	padding: 1px 6px;
    text-align: center;
">
        <i class="mdi mdi-menu"></i>
    </a>
    <button class="button-menu-mobile open-left d-xl-none">
        <i class="mdi mdi-menu"></i>
    </button>
</div>
