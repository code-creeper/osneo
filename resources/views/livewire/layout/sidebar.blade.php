<div class="leftside-menu" wire:key="{{ str()->uuid() }}">
    <x-logo logo="{{ asset('images/logo/logo_light.png') }}" mode="light"/>
    <x-logo logo="{{ asset('images/logo/logo_dark.png') }}" mode="dark"/>

    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav">
            <x-layout.sidebar.title label="{{ __('Navigation') }}"/>

            <x-layout.sidebar.nav-item label="{{ __('Dashboard') }}" icon="fal fa-home" route="{{ route('dashboard') }}"/>

            <x-layout.sidebar.nav-dropdown permission="access admin area|create tickets"
                                           label="Cloud" icon="fal fa-cloud" id="cloud">

                <x-layout.sidebar.nav-dropdown-item
                    label="Forms Management" icon="fal fa-keyboard"
                    permission="access admin area"
                    route="{{ route('document-types.index') }}"
                />

                <x-layout.sidebar.nav-dropdown-item
                    permission="viewAny" :permission-params="\App\Models\Document::class"
                    route="{{ route('documents.index') }}" label="Documents"
                />

                <x-layout.sidebar.nav-dropdown-item
                    label="Tickets" icon="fal fa-ticket"
                    permission="create tickets"
                    route="{{ route('tickets.index') }}"
                />

                <x-layout.sidebar.nav-dropdown-item
                    label="Invoices"
                    permission="view invoices"
                    route="{{ route('invoices.index') }}"
                />
            </x-layout.sidebar.nav-dropdown>

            <x-layout.sidebar.nav-dropdown
                permission="create contacts|create services|create service categories|create contracts"
                label="CRM" icon="fal fa-calculator" id="crm">

                <x-layout.sidebar.nav-dropdown-item
                    label="Contacts"
                    permission="create contacts"
                    route="{{ route('contacts.index') }}"
                />

                <x-layout.sidebar.nav-dropdown-item
                    label="Services"
                    permission="create services"
                    route="{{ route('services.index') }}"
                />

                <x-layout.sidebar.nav-dropdown-item
                    label="Service Categories"
                    permission="create service categories"
                    route="{{ route('serviceCategories.index') }}"
                />

                <x-layout.sidebar.nav-dropdown-item
                    label="Contracts"
                    permission="create contracts"
                    route="{{ route('contracts.index') }}"
                />
            </x-layout.sidebar.nav-dropdown>

            <x-layout.sidebar.nav-dropdown
                permission="view all attendance|view own attendance|create manual entries|view manual entries"
                label="Working Hours" icon="fal fa-user-clock" id="time">
                <x-layout.sidebar.nav-dropdown-item
                    label="Overview" permission="view all attendance|view own attendance"
                    route="{{ route('attendances.index') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Summary" permission="access admin area"
                    route="{{ route('attendances.summary') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Calendar" permission="view all attendance|view own attendance"
                    route="{{ route('calendar.employee') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Manual Entries" permission="create manual entries|view manual entries"
                    route="{{ route('manual-entries.index') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Payroll" permission="view all attendance"
                    route="{{ route('payroll') }}"
                />
            </x-layout.sidebar.nav-dropdown>

            <x-layout.sidebar.nav-dropdown
                permission="view all leaves|view own leaves|view all leave transactions|view own leave transactions|view reasons|access admin area"
                label="Leaves" icon="fal fa-house-leave" id="leaves">
                <x-layout.sidebar.nav-dropdown-item
                    label="Index" permission="view all leaves|view own leaves"
                    route="{{ route('leaves.index') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Planning calendar" permission="view all leaves"
                    route="{{ route('calendar.admin') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Leave Transactions" permission="view all leave transactions|view own leave transactions"
                    route="{{ route('leave-transactions.index') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Leave Balance" permission="access admin area"
                    route="{{ route('leaves.balance') }}"
                />
                <x-layout.sidebar.nav-dropdown-item
                    label="Reasons" permission="view reasons"
                    route="{{ route('leave-reasons.index') }}"
                />
            </x-layout.sidebar.nav-dropdown>

            <x-layout.sidebar.nav-item
                label="Vehicles" permission="view vehicles" permission="view vehicles"
                route="{{ route('vehicles.index') }}" icon="fal fa-cars"
            />

            <x-layout.sidebar.nav-item
                label="Announcements" icon="fal fa-megaphone"
                permission="create announcements"
                route="{{ route('announcements.index') }}"
            />

            <x-layout.sidebar.nav-item
                label="Modifications" icon="fal fa-edit"
                permission="view all modifications|view own modifications" :permissionParams="\App\Models\Modification::class"
                route="{{ route('modifications.index') }}"
            />

            <x-layout.sidebar.nav-item
                label="Users" icon="fal fa-users"
                permission="view users"
                route="{{ route('users.index') }}"
            />

            <x-layout.sidebar.nav-item
                label="Roles" icon="fal fa-user-shield"
                permission="manage permissions"
                route="{{ route('roles.index') }}"
            />

            <x-layout.sidebar.nav-dropdown
                label="Logs" icon="fal fa-bug" id="logs"
                permission="access logs">
                <x-layout.sidebar.nav-dropdown-item label="Activity Log" route="{{ route('logs.activity') }}"/>
                <x-layout.sidebar.nav-dropdown-item label="Error Log" route="{{ url('log-viewer') }}" target="_blank"/>
                <x-layout.sidebar.nav-dropdown-item label="Language Variable Log" route="{{ route('logs.language') }}"/>
            </x-layout.sidebar.nav-dropdown>

            <x-layout.sidebar.nav-dropdown
                label="Settings" icon="fal fa-cog" id="settings"
                permission="manage updates|view tags|manage settings">
                <x-layout.sidebar.nav-dropdown-item label="General" permission="manage settings" route="{{ route('settings.general.edit') }}"/>
                <x-layout.sidebar.nav-dropdown-item label="Tags" permission="view tags" route="{{ route('tags.index') }}"/>
                <x-layout.sidebar.nav-dropdown-item label="Constants" permission="view constants" route="{{ route('constants.index') }}"/>
                <x-layout.sidebar.nav-dropdown-item label="System" permission="manage updates" route="{{ route('system.index') }}"/>
                <x-layout.sidebar.nav-dropdown-item label="Update" permission="manage updates" route="{{ route('system.systemindex') }}"/>
                <x-layout.sidebar.nav-dropdown-item label="Languages" permission="manage settings" route="{{ url('languages') }}" target="_blank"/>
            </x-layout.sidebar.nav-dropdown>


        </ul>
        <div class="clearfix"></div>
    </div>
</div>
