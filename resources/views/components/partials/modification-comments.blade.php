@props([
    'permission',
    'edit' => false
])

@if($edit)
    @cannot($permission)
        <div class="col-12">
            <label class="form-label" for="comments">{{ __('Comments') }}</label>
            <textarea class="form-control" name="comments" id="comments"></textarea>
        </div>
    @endcannot
@endif
