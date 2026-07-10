@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div style="max-width:400px; margin:100px auto;">

    <div class="text-center mb-4">
        <h4 class="fw-semibold">{{ config('app.name') }}</h4>
        <p class="text-muted small">Sign in to continue</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
                </button>
            </form>

        </div>
    </div>

    <p class="text-center text-muted small mt-3">{{ config('app.name') }}</p>
</div>
@endsection