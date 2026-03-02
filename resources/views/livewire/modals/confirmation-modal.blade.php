<x-wire-elements-pro::bootstrap.modal onSubmit="confirm" :contentPadding="false" class="borderless">
    <x-slot name="title">{{ $prompt['title'] }}</x-slot>

    <div class="px-1">

        @if($prompt['message'])
        <p class="px-2 pt-3">{{ $prompt['message'] }}</p>
        @endif

        @if($tableData)
            <table class="table border-top mb-0">
                <thead>
                <tr>
                    @foreach($tableHeaders as $header)
                        <th scope="col" class="px-2 bg-light">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($tableData as $columns)
                    <tr>
                        @foreach($columns as $column)
                            <td class="px-2">
                                {{ $column }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        @if($confirmPhrase)
            <div class="px-2 py-2">
                <input class="form-control" type="text" wire:model="confirmPhraseInput"
                       placeholder="{{ __("wire-elements-pro::modal.confirmation.please_enter_phrase_to_continue", ['phrase' => $confirmPhrase]) }}"
                       required
                >

                @error('confirmPhraseInput')
                <div class="mt-2 text-danger">{{ $message }}</div>
                @enderror
            </div>
        @endif

        @if($confirmWithComments)
            <div class="px-2 py-2">
                <textarea class="form-control" wire:model="comments"
                          placeholder="{{ __("wire-elements-pro::modal.confirmation.comments") }}">
                </textarea>

                @error('comments')
                <div class="mt-2 text-danger">{{ $message }}</div>
                @enderror
            </div>
        @endif
    </div>

    <x-slot name="buttons">
        <button type="button" wire:modal="close" class="btn btn-sm btn-secondary"
                data-bs-dismiss="modal">{{ $prompt['cancel'] }}</button>
        <button type="submit" class="btn btn-sm btn-primary">{{ $prompt['confirm'] }}</button>
    </x-slot>
</x-wire-elements-pro::bootstrap.modal>
