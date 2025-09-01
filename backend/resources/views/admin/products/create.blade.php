@extends('admin.layouts.app')

@section('title', 'Add New Insurance Product')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Add New Insurance Product</h1>
            <p class="mt-2 text-sm text-gray-700">Create a new insurance product with coverage details and commission rules.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.products.index') }}" class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Products
            </a>
        </div>
    </div>

    <div class="mt-8">
        <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Basic Information -->
                        <div>
                            <label for="product_type" class="block text-sm font-medium text-gray-700">Product Type</label>
                            <select id="product_type" name="product_type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select product type</option>
                                <option value="life_insurance" {{ old('product_type') == 'life_insurance' ? 'selected' : '' }}>Life Insurance</option>
                                <option value="health_insurance" {{ old('product_type') == 'health_insurance' ? 'selected' : '' }}>Health Insurance</option>
                                <option value="motor_insurance" {{ old('product_type') == 'motor_insurance' ? 'selected' : '' }}>Motor Insurance</option>
                                <option value="property_insurance" {{ old('product_type') == 'property_insurance' ? 'selected' : '' }}>Property Insurance</option>
                                <option value="travel_insurance" {{ old('product_type') == 'travel_insurance' ? 'selected' : '' }}>Travel Insurance</option>
                                <option value="personal_accident" {{ old('product_type') == 'personal_accident' ? 'selected' : '' }}>Personal Accident</option>
                            </select>
                            @error('product_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="base_price" class="block text-sm font-medium text-gray-700">Base Price (RM)</label>
                            <input type="number" name="base_price" id="base_price" value="{{ old('base_price') }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('base_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_frequency" class="block text-sm font-medium text-gray-700">Payment Frequency</label>
                            <select id="payment_frequency" name="payment_frequency" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select frequency</option>
                                <option value="monthly" {{ old('payment_frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('payment_frequency') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="semi_annually" {{ old('payment_frequency') == 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                                <option value="annually" {{ old('payment_frequency') == 'annually' ? 'selected' : '' }}>Annually</option>
                            </select>
                            @error('payment_frequency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_multiplier" class="block text-sm font-medium text-gray-700">Price Multiplier</label>
                            <input type="number" name="price_multiplier" id="price_multiplier" value="{{ old('price_multiplier', 1.0) }}" step="0.1" min="0.1" max="10" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('price_multiplier')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="waiting_period_days" class="block text-sm font-medium text-gray-700">Waiting Period (Days)</label>
                            <input type="number" name="waiting_period_days" id="waiting_period_days" value="{{ old('waiting_period_days', 30) }}" min="0" max="365" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('waiting_period_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_coverage_amount" class="block text-sm font-medium text-gray-700">Max Coverage Amount (RM)</label>
                            <input type="number" name="max_coverage_amount" id="max_coverage_amount" value="{{ old('max_coverage_amount') }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('max_coverage_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="is_active" name="is_active" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coverage Details -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Coverage Details</h3>
                    <div id="coverage-container">
                        <div class="coverage-item grid grid-cols-1 gap-4 sm:grid-cols-3 border border-gray-200 rounded-lg p-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Coverage Type</label>
                                <input type="text" name="coverage_details[0][coverage_type]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g., Medical Expenses">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount (RM)</label>
                                <input type="number" name="coverage_details[0][amount]" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <input type="text" name="coverage_details[0][description]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Coverage description">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-coverage" class="mt-3 inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Add Coverage
                    </button>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.products.index') }}" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let coverageIndex = 1;
    
    document.getElementById('add-coverage').addEventListener('click', function() {
        const container = document.getElementById('coverage-container');
        const newItem = document.createElement('div');
        newItem.className = 'coverage-item grid grid-cols-1 gap-4 sm:grid-cols-3 border border-gray-200 rounded-lg p-4 mt-3';
        newItem.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700">Coverage Type</label>
                <input type="text" name="coverage_details[${coverageIndex}][coverage_type]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g., Medical Expenses">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Amount (RM)</label>
                <input type="number" name="coverage_details[${coverageIndex}][amount]" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="0.00">
            </div>
            <div class="flex items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="coverage_details[${coverageIndex}][description]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Coverage description">
                </div>
                <button type="button" class="ml-2 inline-flex items-center rounded-md bg-red-600 px-2 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500" onclick="this.parentElement.parentElement.remove()">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(newItem);
        coverageIndex++;
    });
});
</script>
@endsection
