@extends('layouts.auth')

@section('title', 'Templates')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Templates</h4>
        <form method="POST" action="{{ route('templates.install') }}">
            @csrf
            <button class="btn btn-primary">
                <i class="bi bi-download me-1"></i> Scan &amp; Install
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('errors_list') && count(session('errors_list')))
        <div class="alert alert-warning">
            <strong>Some templates could not be installed:</strong>
            <ul class="mb-0 mt-1">
                @foreach(session('errors_list') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($templates->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No templates installed yet.
                Click <strong>Scan &amp; Install</strong> to load templates from storage.
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach($templates as $template)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-semibold mb-1">{{ $template->name }}</h6>
                            @if($template->active)
                                <span class="badge text-bg-success">Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="text-muted small mb-2">v{{ $template->version }} · {{ $template->slug }}</div>
                        <p class="small mb-0">{{ $template->description ?? '—' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
