@extends('admin.layouts.app')

@section('title', 'Add New Payment')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Add New Payment</h1>
            <p class="mt-2 text-sm text-gray-700">Record a new payment transaction for a member.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a href="{{ route('admin.payments.index') }}" class="block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Payments
            </a>
        </div>
    </div>

    <div class="mt-8">
        <form action="{{ route('admin.payments.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Member Selection -->
                        <div class="sm:col-span-2">
                            <label for="member_id" class="block text-sm font-medium text-gray-700">Member</label>
                            <select name="member_id" id="member_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Member</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->member_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Policy Selection -->
                        <div class="sm:col-span-2">
                            <label for="policy_id" class="block text-sm font-medium text-gray-700">Insurance Policy</label>
                            <select name="policy_id" id="policy_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Policy</option>
                                @foreach($policies as $policy)
                                    <option value="{{ $policy->id }}" {{ old('policy_id') == $policy->id ? 'selected' : '' }}>
                                        {{ $policy->policy_number }} - {{ $policy->insuranceProduct->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('policy_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Details -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (RM)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">RM</span>
                                </div>
                                <input type="number" name="amount" id="amount" step="0.01" min="0" value="{{ old('amount') }}" required 
                                       class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <select name="payment_method" id="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Method</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="mobile_payment" {{ old('payment_method') == 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('payment_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('due_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="transaction_id" class="block text-sm font-medium text-gray-700">Transaction ID</label>
                            <input type="text" name="transaction_id" id="transaction_id" value="{{ old('transaction_id') }}" 
                                   placeholder="Auto-generated if empty" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('transaction_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Details -->
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3" placeholder="Payment description or notes..." 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Additional Fields -->
                        <div>
                            <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number" value="{{ old('reference_number') }}" 
                                   placeholder="Bank reference, receipt number..." 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('reference_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="processing_fee" class="block text-sm font-medium text-gray-700">Processing Fee (RM)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">RM</span>
                                </div>
                                <input type="number" name="processing_fee" id="processing_fee" step="0.01" min="0" value="{{ old('processing_fee', '0.00') }}" 
                                       class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            @error('processing_fee')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="late_fee" class="block text-sm font-medium text-gray-700">Late Fee (RM)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">RM</span>
                                </div>
                                <input type="number" name="late_fee" id="late_fee" step="0.01" min="0" value="{{ old('late_fee', '0.00') }}" 
                                       class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            @error('late_fee')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="discount_amount" class="block text-sm font-medium text-gray-700">Discount Amount (RM)</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">RM</span>
                                </div>
                                <input type="number" name="discount_amount" id="discount_amount" step="0.01" min="0" value="{{ old('discount_amount', '0.00') }}" 
                                       class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            @error('discount_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Total Calculation Display -->
                        <div class="sm:col-span-2">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Payment Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Base Amount:</span>
                                        <span class="font-medium" id="base-amount">RM 0.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Processing Fee:</span>
                                        <span class="font-medium" id="processing-fee-display">RM 0.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Late Fee:</span>
                                        <span class="font-medium" id="late-fee-display">RM 0.00</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-medium text-green-600" id="discount-display">-RM 0.00</span>
                                    </div>
                                    <div class="border-t pt-2">
                                        <div class="flex justify-between font-semibold">
                                            <span class="text-gray-900">Total Amount:</span>
                                            <span class="text-blue-600" id="total-amount">RM 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.payments.index') }}" class="rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                    Create Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const processingFeeInput = document.getElementById('processing_fee');
    const lateFeeInput = document.getElementById('late_fee');
    const discountInput = document.getElementById('discount_amount');
    
    function updateTotal() {
        const baseAmount = parseFloat(amountInput.value) || 0;
        const processingFee = parseFloat(processingFeeInput.value) || 0;
        const lateFee = parseFloat(lateFeeInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        
        const total = baseAmount + processingFee + lateFee - discount;
        
        document.getElementById('base-amount').textContent = `RM ${baseAmount.toFixed(2)}`;
        document.getElementById('processing-fee-display').textContent = `RM ${processingFee.toFixed(2)}`;
        document.getElementById('late-fee-display').textContent = `RM ${lateFee.toFixed(2)}`;
        document.getElementById('discount-display').textContent = `-RM ${discount.toFixed(2)}`;
        document.getElementById('total-amount').textContent = `RM ${total.toFixed(2)}`;
    }
    
    [amountInput, processingFeeInput, lateFeeInput, discountInput].forEach(input => {
        input.addEventListener('input', updateTotal);
    });
    
    // Initial calculation
    updateTotal();
});
</script>
@endsection
