<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Customer list page.
     */
    public function index()
    {
        $customers = Customer::orderBy('name')->paginate(20);
        return view('customers.index', compact('customers'));
    }

    /**
     * Return all customers as JSON (used by the form picker).
     */
    public function list()
    {
        $customers = Customer::orderBy('name')->get([
            'id', 'name', 'company', 'department',
            'street', 'city', 'zip', 'country',
            'phone', 'email', 'vat_number',
        ]);
        return response()->json($customers);
    }

    /**
     * Save a new customer (called from document form or customer list).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'company'    => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'street'     => ['nullable', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:255'],
            'zip'        => ['nullable', 'string', 'max:20'],
            'country'    => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:100'],
        ]);

        $customer = Customer::create($validated);

        // If called via AJAX (from the document form), return JSON
        if ($request->expectsJson()) {
            return response()->json($customer, 201);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer saved.');
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'company'    => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'street'     => ['nullable', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:255'],
            'zip'        => ['nullable', 'string', 'max:20'],
            'country'    => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:100'],
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated.');
    }
}
