@extends('layouts.auth')

@section('title', 'Document History')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-semibold mb-0">Document History</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger py-2">{{ session('error') }}</div>
    @endif

    {{-- Filter tabs --}}
    <div class="d-flex gap-2 flex-wrap mb-4">
        <a href="{{ route('documents.history') }}"
           class="btn btn-sm {{ is_null($selectedSlug) ? 'btn-primary' : 'btn-outline-secondary' }}">
            All
        </a>
        @foreach($types as $t)
            <a href="{{ route('documents.history', ['type' => $t->slug]) }}"
               class="btn btn-sm {{ $selectedSlug === $t->slug ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $t->name }}
            </a>
        @endforeach
    </div>

    @if($documents->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No documents yet. <a href="{{ route('dashboard') }}">Create one</a>.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Title</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $doc->title }}</td>
                            <td>
                                <span class="badge text-bg-primary">
                                    {{ $doc->documentType->name }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $doc->reference ?? '—' }}</td>
                            <td>
                                @php
                                    $color = \App\Models\Document::$statusColors[$doc->status] ?? 'secondary';
                                @endphp
                                <span class="badge text-bg-{{ $color }}">
                                    {{ ucfirst($doc->status) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-3 text-end">
                                <div class="d-flex gap-1 justify-content-end">

                                    {{-- View --}}
                                    <a href="{{ route('documents.show', $doc) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    {{-- Update status dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @php
                                                $quoteStatuses   = ['draft','sent','accepted','rejected'];
                                                $invoiceStatuses = ['draft','sent','paid','cancelled'];
                                                $statuses = $doc->isQuote() ? $quoteStatuses : $invoiceStatuses;
                                            @endphp
                                            @foreach($statuses as $s)
                                            <li>
                                                <form method="POST"
                                                    action="{{ route('documents.status', $doc) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="{{ $s }}">
                                                    <button type="submit"
                                                        class="dropdown-item {{ $doc->status === $s ? 'fw-bold' : '' }}">
                                                        {{ ucfirst($s) }}
                                                    </button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    {{-- Convert to Invoice (quotes only, when accepted) --}}
                                    @if($doc->isQuote() && $doc->status === 'accepted' && !$doc->convertedInvoice)
                                    <form method="POST"
                                        action="{{ route('documents.convert', $doc) }}">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm btn-success"
                                            title="Convert to Invoice">
                                            <i class="bi bi-arrow-right-circle me-1"></i>Invoice
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Already converted badge --}}
                                    @if($doc->isQuote() && $doc->convertedInvoice)
                                    <a href="{{ route('documents.show', $doc->convertedInvoice) }}"
                                       class="btn btn-sm btn-outline-info" target="_blank"
                                       title="View generated invoice">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($documents->hasPages())
            <div class="mt-3">{{ $documents->links() }}</div>
        @endif
    @endif

</div>
@endsection
