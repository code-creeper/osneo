<div class="{{$wrapperClass}}" x-data="{locale: @js($currentLocale)}">
    @foreach(getLocales() as $locale => $localeName)
        <template x-if="locale === @js($locale)">
            <x-form-input :$name :$label :$type :$floating :language="$locale" {{ $attributes->merge() }}>
                <x-input-language-selector :$locale/>
            </x-form-input>
        </template>
    @endforeach
</div>
