<x-layout>
    @php($env = isset($env) ? $env : null)
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Settings') }}</h5>
        </div>
        <div class="card-body">
            <form class="row g-3" action="{{ route('system.index') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method($method ?? 'POST')

				@foreach($env as $key => $value)
				<div class="col-6">
					<label class="form-label" for="{{ $key }}">{{ __($key) }}</label>
					<input class="form-control @error('{{ $key }}') is-invalid @enderror" name="{{ $key }}"
						   id="{{ $key }}" type="text" value="{{ old($key, $value ? $value : '') }}">
					<x-error field="{{ $key }}"/>
				</div>
				@endforeach

                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary" type="submit">
                        {{ __('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>
