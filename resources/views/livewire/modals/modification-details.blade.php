<x-wire-elements-pro::bootstrap.modal>
    <x-slot name="title">{{ __('Modifications') }}</x-slot>
    <table class="table table-sm table-borderless">
        @foreach($changes as $change)
            <tr>
                <th>{{ $change['label'] }}:</th>
                <td>
                    {{ $change['source'] }}
                    @if(isset($change['data']))
                        <i class="fal fa-long-arrow-right mx-2"></i>
                        {{ $change['data'] }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</x-wire-elements-pro::bootstrap.modal>
