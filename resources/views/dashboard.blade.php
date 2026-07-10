@extends('layouts.auth')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Dashboard</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('documents.history') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i>History
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle p-3">
                        <i class="bi bi-file-earmark-text text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $templates->count() }}</div>
                        <div class="text-muted small">Templates</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success-subtle p-3">
                        <i class="bi bi-box-seam text-success fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold">{{ $productCount }}</div>
                        <div class="text-muted small">Products</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <a href="{{ route('documents.history') }}" class="text-decoration-none text-dark">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-warning-subtle p-3">
                            <i class="bi bi-clock-history text-warning fs-4"></i>
                        </div>
                        <div>
                            <div class="fs-3 fw-bold">{{ $documentCount }}</div>
                            <div class="text-muted small">Documents</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Available templates -->
    <h6 class="fw-semibold mb-3 text-muted text-uppercase" style="font-size:11px;letter-spacing:1px;">
        Generate a document
    </h6>

    @if($templates->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No templates installed yet.
                <a href="{{ route('templates.index') }}">Go to Templates</a> and click Scan &amp; Install.
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach($templates as $template)
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-semibold mb-0">{{ $template->name }}</h6>
                            <span class="badge text-bg-primary">v{{ $template->version }}</span>
                        </div>
                        <p class="text-muted small mb-3">{{ $template->description ?? '—' }}</p>
                        <a href="{{ route('documents.create', $template->slug) }}"
                           class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-plus-lg me-1"></i>New {{ $template->name }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <!-- Recent documents -->
    @if($recentDocs->isNotEmpty())
    <h6 class="fw-semibold mb-3 text-muted text-uppercase" style="font-size:11px;letter-spacing:1px;">
        Recent documents
    </h6>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Title</th>
                        <th>Type</th>
                        <th>Created</th>
                        <th class="pe-3 text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentDocs as $doc)
                    <tr>
                        <td class="ps-3 fw-medium">{{ $doc->title }}</td>
                        <td><span class="badge text-bg-primary">{{ $doc->documentType->name }}</span></td>
                        <td class="text-muted small">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                        <td class="pe-3 text-end">
                            <a href="{{ route('documents.show', $doc) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($documentCount > 5)
        <div class="card-footer bg-white text-center">
            <a href="{{ route('documents.history') }}" class="text-decoration-none small">
                View all {{ $documentCount }} documents →
            </a>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
