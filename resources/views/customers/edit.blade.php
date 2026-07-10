@extends('layouts.auth')

@section('title', 'Edit Customer')

@section('content')
<div class="container py-4" style="max-width:700px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-0">Edit Customer</h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('customers.update', $customer) }}">
                @csrf
                @method('PUT')
                @include('customers._form')
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
