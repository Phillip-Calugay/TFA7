<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.index', ['title' => 'Customers', 'customers' => Customer::orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('customers.form', ['title' => 'Create Customer', 'customer' => new Customer]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validated($request));

        return to_route('customers.index')->with('status', 'Customer created successfully.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', ['title' => 'Edit Customer', 'customer' => $customer]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request, $customer));

        return to_route('customers.index')->with('status', 'Customer updated successfully.');
    }

    public function show(Customer $customer): View
    {
        return view('customers.show', ['title' => 'Customer details', 'customer' => $customer]);
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return to_route('customers.index')->with('status', 'Customer deleted successfully.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('customers')->ignore($customer)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        $data['phone'] = $data['phone'] ?? '';

        return $data;
    }
}
