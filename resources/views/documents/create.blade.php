@extends('layouts.auth')

@section('title', 'New ' . $type->name)

@section('content')
<div class="container py-4" style="max-width:760px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-0">New {{ $type->name }}</h4>
    </div>

    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @php
        $prefill           = session('convert_data', []);
        $prefillCustomerId = session('convert_customer_id');
    @endphp

    <form method="POST" action="{{ route('documents.store', $type->slug) }}">
        @csrf

        {{-- ================================================================ --}}
        {{-- CUSTOMER PICKER                                                  --}}
        {{-- ================================================================ --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span>Customer</span>
                <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                    <i class="bi bi-person-plus me-1"></i>New Customer
                </button>
            </div>
            <div class="card-body">
                <label class="form-label fw-medium">Select existing customer</label>
                <select class="form-select mb-3" id="customer-picker" name="customer_id">
                    <option value="">— Type a new customer below —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}"
                        data-name="{{ $c->name }}"
                        data-company="{{ $c->company }}"
                        data-department="{{ $c->department }}"
                        data-street="{{ $c->street }}"
                        data-city="{{ $c->city }}"
                        data-zip="{{ $c->zip }}"
                        data-country="{{ $c->country }}"
                        data-phone="{{ $c->phone }}"
                        data-email="{{ $c->email }}"
                        data-vat="{{ $c->vat_number }}"
                        {{ $prefillCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->company ? ' — ' . $c->company : '' }}
                    </option>
                    @endforeach
                </select>
                <p class="text-muted small mb-0">
                    Or fill the fields below manually — the customer will be saved automatically.
                </p>
            </div>
        </div>

        {{-- ================================================================ --}}
        {{-- DYNAMIC FORM SECTIONS                                            --}}
        {{-- ================================================================ --}}
        @foreach($form as $section)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">
                {{ $section['section'] }}
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($section['fields'] as $field)
                    <div class="col-md-6">
                        <label class="form-label fw-medium">
                            {{ $field['label'] ?? ucfirst(str_replace('_', ' ', $field['name'])) }}
                            @if(!empty($field['required']))<span class="text-danger">*</span>@endif
                        </label>

                        @php
                            $prefillValue = $prefill[$field['name']] ?? old($field['name'], '');
                        @endphp

                        @if($field['type'] === 'textarea')
                            <textarea name="{{ $field['name'] }}"
                                class="form-control" rows="3"
                                {{ !empty($field['required']) ? 'required' : '' }}>{{ $prefillValue }}</textarea>

                        @elseif($field['type'] === 'currency' || $field['type'] === 'number')
                            <input type="number" name="{{ $field['name'] }}"
                                class="form-control"
                                step="{{ $field['type'] === 'currency' ? '0.01' : '1' }}"
                                min="0"
                                value="{{ $prefillValue }}"
                                {{ !empty($field['required']) ? 'required' : '' }}>

                        @elseif($field['type'] === 'date')
                            <input type="date" name="{{ $field['name'] }}"
                                class="form-control"
                                value="{{ $prefillValue ?: date('Y-m-d') }}"
                                {{ !empty($field['required']) ? 'required' : '' }}>

                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}"
                                class="form-control"
                                value="{{ $prefillValue }}"
                                {{ !empty($field['required']) ? 'required' : '' }}>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach

        {{-- Product picker --}}
        @if($products->isNotEmpty())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold">Quick-fill from Products</div>
            <div class="card-body">
                <select class="form-select" id="product-picker">
                    <option value="">— Pick a product to auto-fill —</option>
                    @foreach($products as $p)
                    <option value="{{ $p->reference }}"
                        data-name="{{ $p->name }}"
                        data-price="{{ number_format($p->price / 100, 2, '.', '') }}">
                        {{ $p->reference }} — {{ $p->name }} (€{{ number_format($p->price / 100, 2) }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-floppy me-1"></i>Save {{ $type->name }}
            </button>
            <button type="submit"
                formaction="{{ route('documents.preview', $type->slug) }}"
                formtarget="_blank"
                class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Preview
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>

    </form>
</div>

{{-- New Customer Modal (inline save via AJAX) --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold">New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modal-errors" class="alert alert-danger d-none"></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="m_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Company</label>
                        <input type="text" id="m_company" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Department</label>
                        <input type="text" id="m_department" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">VAT Number</label>
                        <input type="text" id="m_vat_number" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium">Street</label>
                        <input type="text" id="m_street" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">City</label>
                        <input type="text" id="m_city" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium">ZIP</label>
                        <input type="text" id="m_zip" class="form-control">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-medium">Country</label>
                        <input type="text" id="m_country" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Phone</label>
                        <input type="tel" id="m_phone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" id="m_email" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewCustomer">
                    <i class="bi bi-floppy me-1"></i>Save & Select
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Customer picker: auto-fill form fields ──────────────────────────────────
document.getElementById('customer-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;

    const map = {
        'customer_name':       opt.dataset.name,
        'customer_company':    opt.dataset.company,
        'customer_department': opt.dataset.department,
        'customer_street':     opt.dataset.street,
        'customer_city':       opt.dataset.city,
        'customer_zip':        opt.dataset.zip,
        'customer_country':    opt.dataset.country,
        'customer_phone':      opt.dataset.phone,
        'customer_email':      opt.dataset.email,
        'customer_vat_number': opt.dataset.vat,
    };

    for (const [name, value] of Object.entries(map)) {
        const el = document.querySelector(`[name="${name}"]`);
        if (el) el.value = value ?? '';
    }
});

// ── Product picker: auto-fill product fields ────────────────────────────────
document.getElementById('product-picker')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    const ref   = document.querySelector('[name="product_reference"]');
    const name  = document.querySelector('[name="product_name"]');
    const price = document.querySelector('[name="product_unit_price"]');
    if (ref)   ref.value   = opt.value;
    if (name)  name.value  = opt.dataset.name;
    if (price) price.value = opt.dataset.price;
});

// ── New customer modal: save via AJAX, then select ─────────────────────────
document.getElementById('saveNewCustomer')?.addEventListener('click', async function () {
    const errBox = document.getElementById('modal-errors');
    errBox.classList.add('d-none');

    const payload = {
        name:       document.getElementById('m_name').value,
        company:    document.getElementById('m_company').value,
        department: document.getElementById('m_department').value,
        street:     document.getElementById('m_street').value,
        city:       document.getElementById('m_city').value,
        zip:        document.getElementById('m_zip').value,
        country:    document.getElementById('m_country').value,
        phone:      document.getElementById('m_phone').value,
        email:      document.getElementById('m_email').value,
        vat_number: document.getElementById('m_vat_number').value,
    };

    if (!payload.name) {
        errBox.textContent = 'Name is required.';
        errBox.classList.remove('d-none');
        return;
    }

    const res = await fetch('{{ route("customers.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(payload),
    });

    if (!res.ok) {
        const err = await res.json();
        errBox.textContent = Object.values(err.errors ?? {}).flat().join(' ');
        errBox.classList.remove('d-none');
        return;
    }

    const customer = await res.json();

    // Add to picker and select it
    const picker = document.getElementById('customer-picker');
    const option = new Option(
        customer.name + (customer.company ? ' — ' + customer.company : ''),
        customer.id,
        true, true
    );
    Object.assign(option.dataset, {
        name:       customer.name,
        company:    customer.company ?? '',
        department: customer.department ?? '',
        street:     customer.street ?? '',
        city:       customer.city ?? '',
        zip:        customer.zip ?? '',
        country:    customer.country ?? '',
        phone:      customer.phone ?? '',
        email:      customer.email ?? '',
        vat:        customer.vat_number ?? '',
    });
    picker.add(option);
    picker.dispatchEvent(new Event('change'));

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
});
</script>
@endsection
