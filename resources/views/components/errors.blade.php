@props([
    'errors',
    'dismissible' => true,
    'keys' => []
])

@php($keys = is_array($keys) ? $keys : array($keys))

@if ($errors->any())
    <div class="alert alert-danger {{ $dismissible ? 'alert-dismissible' : '' }}">
        @if($dismissible)
            <button type="button" class="btn-close btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif

        <ul class="mb-0">
            @if(count($keys))
                @foreach ($keys as $key)
                    @error($key)
                    <li>{{ $message }}</li>@enderror
                @endforeach
            @else
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            @endif
        </ul>
    </div>
@endif
