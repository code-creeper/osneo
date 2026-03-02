@extends('layouts.auth')

@section('heading', 'Login')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email address') }}</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}"
                   required autocomplete="email" autofocus
                   placeholder="{{ __('Enter your email') }}">
            <x-error field="email"/>
        </div>

        <div class="mb-3">
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-muted float-end" tabindex="3"><small>{{ __('Forgot your password?') }}</small></a>
            @endif

            <label for="password" class="form-label">{{ __('Password') }}</label>
            <div class="input-group input-group-merge">
                <input id="password" type="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('Enter your password') }}" autocomplete="current-password">
                <div class="input-group-text" data-password="false">
                    <span class="password-eye"></span>
                </div>
                <x-error field="password"/>
            </div>
        </div>

        <div class="mb-3 mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember"  name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
            </div>
        </div>

        <div class="mb-3 d-grid">
            <button class="btn btn-primary" type="submit"> {{ __('Login') }} </button>
        </div>
        <hr>
        <div class="text-center">
            <p class="text-muted font-16">
                Or Sign in with
                <a href="{{ route('auth.redirect', 'microsoft365') }}" class=" border-danger text-danger">
                    <i class="mdi mdi-microsoft-office"></i> Microsoft 365
                </a>
            </p>
        </div>

    </form>
@endsection
