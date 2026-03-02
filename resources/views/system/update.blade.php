<x-layout>
    @php($settings = isset($settings) ? $settings : null)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Update') }}</h5>
        </div>
		<div class="card-body">
			<div class="row">
				<div class="col-xxl-3 col-lg-6">
					<div class="card widget-flat bg-success text-white">
						<div class="card-body">
							<div class="float-end">
								<i class="mdi mdi-account-multiple widget-icon bg-white text-success"></i>
							</div>
							<h6 class="header-title mt-0" title="Customers">{{ __('Current version') }}</h6>
							<h3 class="mt-3 mb-3">{{ config('app.version') }}</h3>
							<p class="mb-0">
								<span class="text-nowrap">&nbsp;</span>
							</p>
						</div>
					</div>
				</div> <!-- end col-->
				<div class="col-xxl-3 col-lg-6">
					<div class="card widget-flat @if(App\Helpers\System::diffChannel()) bg-danger @else bg-success @endif text-white">
						<div class="card-body">
							<div class="float-end">
								<i class="mdi mdi-account-multiple widget-icon bg-white @if(App\Helpers\System::diffChannel()) text-danger @else text-success @endif"></i>
							</div>
							<h6 class="header-title mt-0" title="Customers">{{ __('Current channel') }}</h6>
							<h3 class="mt-3 mb-3">{{ config('app.channel') }}</h3>
							<p class="mb-0">
								<span class="text-nowrap">@if(App\Helpers\System::diffChannel()) {{ __('An update is still pending.') }} @endif &nbsp;</span>
							</p>
						</div>
					</div>
				</div> <!-- end col-->
				<div class="col-xxl-3 col-lg-6">
					<div class="card widget-flat @if(App\Helpers\System::checkMaintenance()) bg-warning @else bg-success @endif text-white">
						<div class="card-body">
							<div class="float-end">
								<i class="mdi mdi-account-multiple widget-icon bg-white @if(App\Helpers\System::checkMaintenance()) text-warning  @else text-success @endif"></i>
							</div>
							<h6 class="header-title mt-0" title="Customers">{{ __('Current environment') }}</h6>
							<h3 class="mt-3 mb-3">{{ config('app.env') }}</h3>
							<p class="mb-0">
								<span class="text-nowrap">@if(App\Helpers\System::checkMaintenance()) {{ __('Maintenance mode activ') }} @endif &nbsp;</span>
							</p>
						</div>
					</div>
				</div> <!-- end col-->
				<div class="col-xxl-3 col-lg-6">
					<div class="card widget-flat bg-success text-white">
						<div class="card-body">
							<div class="float-end">
								<i class="mdi mdi-account-multiple widget-icon bg-white text-success"></i>
							</div>
							<h6 class="header-title mt-2" title="Customers">{{ __('Action') }}</h6>
							<div class="d-grid">
								@if(!App\Helpers\System::checkMaintenance())
									<a href="{{ route('system.systemdown') }}" type="button" class="btn btn-info mt-1">{{ __('System down') }}</a>
								@endif
								@if(App\Helpers\System::checkMaintenance())
									<a href="{{ route('system.systemup') }}" type="button" class="btn btn-info mt-3">{{ __('System up') }}</a>
									<a href="{{ route('system.systemcache') }}" type="button" class="btn btn-info mt-1">{{ __('Clear Cache') }}</a>
									<a href="{{ route('system.systemupdate') }}" type="button" class="btn btn-info mt-1">{{ __('Update') }}</a>
								@endif
							</div>
							<p class="mb-0">
								<span class="text-nowrap">&nbsp;</span>
							</p>
						</div>
					</div>
				</div> <!-- end col-->
			</div>
        </div>
    </div>
</x-layout>

