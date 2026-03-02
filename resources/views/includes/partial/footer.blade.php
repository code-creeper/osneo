<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10">
                Version: {{ getGitBranch() }}.{{ getGitCommit() }} - 
                Environment: {{ ucfirst(env('APP_ENV', 'production')) }}. - 
				Wärme Wimmer GmbH & Co. KG - 
				@if(Auth::check())
					@if (user()->can('manage updates'))
						- {{ __('The last cron job was ') . Cache::get('last_cron_execution', now()->subDays(30))->diffForHumans()}}
						@if(Cache::get('last_cron_execution', now()->subDays(30))->diffInMinutes() > 5)
							<i class="mdi mdi-circle text-danger"></i> {{ __('caution') }}
						@else
							<i class="mdi mdi-circle text-success"></i> {{ __('success') }}
						@endif
					@endif
				@endif
            </div>
        </div>
    </div>
</footer>