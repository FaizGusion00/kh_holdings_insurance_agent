@extends('admin.layouts.app')

@section('title', 'Edit Commission')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Commission</h1>
            <p class="mt-2 text-sm text-gray-700">Update commission details and status.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.commissions.index') }}" class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Commissions
            </a>
        </div>
    </div>

    <div class="mt-8">
        <form action="{{ route('admin.commissions.update', $commission->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Agent Selection -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Agent</label>
                            <select id="user_id" name="user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select an agent</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ old('user_id', $commission->user_id) == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }} ({{ $agent->agent_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Product Selection -->
                        <div>
                            <label for="product_id" class="block text-sm font-medium text-gray-700">Product</label>
                            <select id="product_id" name="product_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select a product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id', $commission->product_id) == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>


                        <!-- Base Amount -->
                        <div>
                            <label for="base_amount" class="block text-sm font-medium text-gray-700">Base Amount (RM)</label>
                            <input type="number" name="base_amount" id="base_amount" value="{{ old('base_amount', $commission->base_amount) }}" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Enter base amount">
                            @error('base_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Commission Percentage -->
                        <div>
                            <label for="commission_percentage" class="block text-sm font-medium text-gray-700">Commission Percentage (%)</label>
                            <input type="number" name="commission_percentage" id="commission_percentage" value="{{ old('commission_percentage', $commission->commission_percentage) }}" step="0.01" min="0" max="100" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Enter percentage">
                            @error('commission_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Commission Amount (Auto-calculated) -->
                        <div>
                            <label for="commission_amount" class="block text-sm font-medium text-gray-700">Commission Amount (RM)</label>
                            <input type="number" name="commission_amount" id="commission_amount" value="{{ old('commission_amount', $commission->commission_amount) }}" step="0.01" min="0" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50 sm:text-sm" placeholder="Auto-calculated">
                            <p class="mt-1 text-xs text-gray-500">This will be calculated automatically based on base amount and percentage</p>
                            @error('commission_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Frequency -->
                        <div>
                            <label for="payment_frequency" class="block text-sm font-medium text-gray-700">Payment Frequency</label>
                            <select id="payment_frequency" name="payment_frequency" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select payment frequency</option>
                                <option value="monthly" {{ old('payment_frequency', $commission->payment_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly" {{ old('payment_frequency', $commission->payment_frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                <option value="semi_annually" {{ old('payment_frequency', $commission->payment_frequency) == 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                                <option value="annually" {{ old('payment_frequency', $commission->payment_frequency) == 'annually' ? 'selected' : '' }}>Annually</option>
                            </select>
                            @error('payment_frequency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Commission Type - Hidden field, always direct -->
                        <input type="hidden" name="commission_type" value="direct">

                        <div>
                            <label for="tier_level" class="block text-sm font-medium text-gray-700">Tier Level</label>
                            <select id="tier_level" name="tier_level" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select tier level</option>
                                <option value="1" {{ old('tier_level', $commission->tier_level) == '1' ? 'selected' : '' }}>Tier 1</option>
                                <option value="2" {{ old('tier_level', $commission->tier_level) == '2' ? 'selected' : '' }}>Tier 2</option>
                                <option value="3" {{ old('tier_level', $commission->tier_level) == '3' ? 'selected' : '' }}>Tier 3</option>
                                <option value="4" {{ old('tier_level', $commission->tier_level) == '4' ? 'selected' : '' }}>Tier 4</option>
                                <option value="5" {{ old('tier_level', $commission->tier_level) == '5' ? 'selected' : '' }}>Tier 5</option>
                            </select>
                            @error('tier_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                            <select id="month" name="month" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select month</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('month', $commission->month) == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                            @error('month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                            <select id="year" name="year" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select year</option>
                                @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ old('year', $commission->year) == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            @error('year')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select status</option>
                                <option value="pending" {{ old('status', $commission->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status', $commission->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="paid" {{ old('status', $commission->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="rejected" {{ old('status', $commission->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('notes', $commission->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Audit Information -->
                        <div class="sm:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Audit Information</h4>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Created</dt>
                                    <dd class="text-sm text-gray-900">{{ $commission->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Last Updated</dt>
                                    <dd class="text-sm text-gray-900">{{ $commission->updated_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500">Commission ID</dt>
                                    <dd class="text-sm text-gray-900">#{{ $commission->id }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.commissions.index') }}" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Update Commission
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseAmountInput = document.getElementById('base_amount');
    const percentageInput = document.getElementById('commission_percentage');
    const commissionAmountInput = document.getElementById('commission_amount');
    
    function calculateCommission() {
        const baseAmount = parseFloat(baseAmountInput.value) || 0;
        const percentage = parseFloat(percentageInput.value) || 0;
        const commissionAmount = (baseAmount * percentage) / 100;
        
        commissionAmountInput.value = commissionAmount.toFixed(2);
    }
    
    baseAmountInput.addEventListener('input', calculateCommission);
    percentageInput.addEventListener('input', calculateCommission);
});
</script>
@endsection
