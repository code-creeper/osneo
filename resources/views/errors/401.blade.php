@extends('layouts.error')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-4">
            <div class="text-center">
                {{--<img src="{{ asset('assets/images/file-searching.svg') }}" height="90" alt="File not found Image">--}}

                <h1 class="text-error mt-4">401</h1>
                <h4 class="text-uppercase text-danger mt-3">{{ __('Unauthorized') }}</h4>
                <p class="text-muted mt-3">{{ __('You do not have enough permissions to view this page') }}</p>

                <a class="btn btn-info mt-3" href="{{ route('dashboard') }}"><i class="mdi mdi-reply"></i>{{ __('Go To Dashboard') }}</a>
            </div>
        </div>
    </div>
@endsection