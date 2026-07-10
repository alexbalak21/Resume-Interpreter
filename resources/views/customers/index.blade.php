@extends('layouts.auth')

@section('title', 'Customers')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h4 class="fw-semibold mb-0">Customers</h4>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
            <i class="bi bi-person-plus me-1"></i>New Customer
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    @if($customers->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center text-muted py-5">
                No customers yet. Create your first one above.
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Name</th>
                            <th>Company</th>
                            <th>City</th>
                            <th>Email</th>
                            <th>VAT</th>
                            <th class="pe-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $c)
                        <tr>
                            <td class="ps-3 fw-medium">{{ $c->name }}</td>
                            <td class="text-muted">{{ $c->company ?? '—' }}</td>
                            <td class="text-muted">{{ $c->city ?? '—' }}</td>
                            <td class="text-muted">{{ $c->email ?? '—' }}</td>
                            <td class="text-muted small">{{ $c->vat_number ?? '—' }}</td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('customers.edit', $c) }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($customers->hasPages())
            <div class="mt-3">{{ $customers->links() }}</div>
        @endif
    @endif
</div>

{{-- New Customer Modal --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('customers._form')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
