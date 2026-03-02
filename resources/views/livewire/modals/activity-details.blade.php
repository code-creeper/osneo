<x-wire-elements-pro::bootstrap.modal :$title>
    <table class="table table-sm table-borderless">
        @if($activity->isCrud())
            @foreach($activity->changedAttributes() as $attribute => $value)
                <tr>
                    <th>{{ $activity->getAttributeName($attribute) }}:</th>
                    <td>
                        @if(count($activity->oldAttributes()))
                            {!! $activity->oldAttributeValue($attribute) !!}
                            <i class="fal fa-long-arrow-right mx-2"></i>
                        @endif
                        {!! $value !!}
                    </td>
                </tr>
            @endforeach
        @endif
    </table>
</x-wire-elements-pro::bootstrap.modal>
