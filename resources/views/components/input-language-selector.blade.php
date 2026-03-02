@props(['locale'])
<x-slot:append class="p-0">
    <div class="dropdown btn-group">
        <button class="btn btn-sm btn-light dropdown-toggle py-1" type="button" data-bs-toggle="dropdown">
            <img  src="{{ asset("assets/images/flags/$locale.jpg") }}" alt="user-image" class="me-1" height="12">
        </button>

        <div class="dropdown-menu dropdown-menu-animated dropdown-left">
            @foreach(getLocales() as $locale => $name)
                <a x-show="locale !== @js($locale)" href="#" @click="locale = @js($locale)" class="dropdown-item notify-item">
                    <img src="{{ asset("assets/images/flags/$locale.jpg") }}"
                         alt="user-image" class="me-1" height="12">
                    <span class="align-middle">{{ $name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</x-slot:append>
