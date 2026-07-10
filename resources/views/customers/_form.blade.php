<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $customer->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Company / Institution</label>
        <input type="text" name="company" class="form-control"
               value="{{ old('company', $customer->company ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Department</label>
        <input type="text" name="department" class="form-control"
               value="{{ old('department', $customer->department ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">VAT Number</label>
        <input type="text" name="vat_number" class="form-control"
               value="{{ old('vat_number', $customer->vat_number ?? '') }}">
    </div>
    <div class="col-md-12">
        <label class="form-label fw-medium">Street / Address</label>
        <input type="text" name="street" class="form-control"
               value="{{ old('street', $customer->street ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-medium">City</label>
        <input type="text" name="city" class="form-control"
               value="{{ old('city', $customer->city ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-medium">ZIP</label>
        <input type="text" name="zip" class="form-control"
               value="{{ old('zip', $customer->zip ?? '') }}">
    </div>
    <div class="col-md-5">
        <label class="form-label fw-medium">Country</label>
        <input type="text" name="country" class="form-control"
               value="{{ old('country', $customer->country ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Phone</label>
        <input type="tel" name="phone" class="form-control"
               value="{{ old('phone', $customer->phone ?? '') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', $customer->email ?? '') }}">
    </div>
</div>
