@extends('admin.layouts.app')

@section('title', 'Calculate Commissions')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Calculate Commissions</h1>
                        <p class="mt-2 text-sm text-gray-700">Calculate agent commissions for a specific period based on active policies and commission rules.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.commissions.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Commissions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calculation Form -->
    <div class="bg-white shadow rounded-lg p-6 fade-in" style="animation-delay: 0.1s;">
        <form method="POST" action="{{ route('admin.commissions.calculate.post') }}" class="space-y-6">
            @csrf
            
            <!-- Period Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                    <select name="month" id="month" required 
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Month</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('month') == $i ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>
                    @error('month')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                    <select name="year" id="year" required 
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Year</option>
                        @for($year = date('Y'); $year >= 2020; $year--)
                            <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                    @error('year')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Agent Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Agents</label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="select_all_agents" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="select_all_agents" class="ml-2 text-sm font-medium text-gray-700">Select All Agents</label>
                    </div>
                    <hr class="my-2">
                    @foreach($agents as $agent)
                        <div class="flex items-center">
                            <input type="checkbox" name="agent_ids[]" value="{{ $agent->id }}" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded agent-checkbox">
                            <label class="ml-2 text-sm text-gray-700">
                                {{ $agent->name }} ({{ $agent->agent_code }})
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="mt-1 text-sm text-gray-500">Leave unchecked to calculate for all agents</p>
            </div>

            <!-- Product Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Products</label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center">
                        <input type="checkbox" id="select_all_products" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="select_all_products" class="ml-2 text-sm font-medium text-gray-700">Select All Products</label>
                    </div>
                    <hr class="my-2">
                    @foreach($products as $product)
                        <div class="flex items-center">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded product-checkbox">
                            <label class="ml-2 text-sm text-gray-700">
                                {{ $product->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="mt-1 text-sm text-gray-500">Leave unchecked to calculate for all products</p>
            </div>

            <!-- Commission Rules Preview -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Commission Rules Preview</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($commissionRules->take(6) as $rule)
                            <div class="bg-white p-3 rounded border">
                                <div class="text-sm font-medium text-gray-900">{{ $rule->product->name }}</div>
                                <div class="text-xs text-gray-600">
                                    Tier {{ $rule->tier_level }}: {{ $rule->commission_percentage }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($commissionRules->count() > 6)
                        <p class="text-sm text-gray-500 mt-2">... and {{ $commissionRules->count() - 6 }} more rules</p>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.commissions.index') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="-ml-1 mr-2 h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Calculate Commissions
                </button>
            </div>
        </form>
    </div>

    <!-- Information Panel -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 fade-in" style="animation-delay: 0.2s;">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">How Commission Calculation Works</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Commissions are calculated based on active policies and commission rules</li>
                        <li>Only policies with 'active' status are considered</li>
                        <li>Commission amount = Policy total × Commission percentage</li>
                        <li>Multiple commission rules per product are supported</li>
                        <li>Calculated commissions will have 'pending' status initially</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all agents functionality
    const selectAllAgents = document.getElementById('select_all_agents');
    const agentCheckboxes = document.querySelectorAll('.agent-checkbox');
    
    selectAllAgents.addEventListener('change', function() {
        agentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Select all products functionality
    const selectAllProducts = document.getElementById('select_all_products');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    selectAllProducts.addEventListener('change', function() {
        productCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    // Update select all checkboxes based on individual selections
    function updateSelectAll(selectAllCheckbox, individualCheckboxes) {
        const checkedCount = Array.from(individualCheckboxes).filter(cb => cb.checked).length;
        const totalCount = individualCheckboxes.length;
        
        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === totalCount) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
    
    agentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => updateSelectAll(selectAllAgents, agentCheckboxes));
    });
    
    productCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => updateSelectAll(selectAllProducts, productCheckboxes));
    });
});
</script>
@endsection
