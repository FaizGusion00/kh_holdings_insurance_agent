@extends('admin.layouts.app')

@section('title', 'Edit Payment')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Edit Payment</h1>
                        <p class="mt-2 text-sm text-gray-700">Update payment transaction details.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.payments.show', $payment) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Payment
                        </a>
                        <a href="{{ route('admin.payments.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Payments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="max-w-5xl mx-auto">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form action="{{ route('admin.payments.update', $payment) }}" method="POST" class="space-y-0">
                @csrf
                @method('PUT')
                
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-8 py-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-white">Edit Payment</h3>
                            <p class="text-indigo-100 text-sm mt-1">Update payment transaction details for {{ $payment->reference_number }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Form Content -->
                <div class="px-8 py-8 space-y-8">
                    <!-- Member Selection -->
                    <div>
                        <label for="member_id" class="block text-sm font-medium text-gray-700">Member *</label>
                        <select name="member_id" id="member_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Member</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ old('member_id', $payment->member_id) == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ $member->nric }})
                                </option>
                            @endforeach
                        </select>
                        @error('member_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Policy Selection -->
                    <div>
                        <label for="policy_id" class="block text-sm font-medium text-gray-700">Policy *</label>
                        <select name="policy_id" id="policy_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Policy</option>
                            @foreach($policies as $policy)
                                <option value="{{ $policy->id }}" {{ old('policy_id', $payment->policy_id) == $policy->id ? 'selected' : '' }}>
                                    {{ $policy->policy_number }} - {{ $policy->product ? $policy->product->name : 'Unknown Product' }}
                                </option>
                            @endforeach
                        </select>
                        @error('policy_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (RM) *</label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                                   value="{{ old('amount', $payment->amount) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Type -->
                        <div>
                            <label for="payment_type" class="block text-sm font-medium text-gray-700">Payment Type *</label>
                            <select name="payment_type" id="payment_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Payment Type</option>
                                <option value="membership_fee" {{ old('payment_type', $payment->payment_type) == 'membership_fee' ? 'selected' : '' }}>Membership Fee</option>
                                <option value="sharing_account" {{ old('payment_type', $payment->payment_type) == 'sharing_account' ? 'selected' : '' }}>Sharing Account</option>
                                <option value="policy_premium" {{ old('payment_type', $payment->payment_type) == 'policy_premium' ? 'selected' : '' }}>Policy Premium</option>
                            </select>
                            @error('payment_type')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                            <select name="payment_method" id="payment_method" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Payment Method</option>
                                <option value="mandate" {{ old('payment_method', $payment->payment_method) == 'mandate' ? 'selected' : '' }}>Mandate</option>
                                <option value="manual" {{ old('payment_method', $payment->payment_method) == 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="card" {{ old('payment_method', $payment->payment_method) == 'card' ? 'selected' : '' }}>Card</option>
                            </select>
                            @error('payment_method')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Status</option>
                                <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ old('status', $payment->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ old('status', $payment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="cancelled" {{ old('status', $payment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Transaction Date -->
                        <div>
                            <label for="transaction_date" class="block text-sm font-medium text-gray-700">Transaction Date *</label>
                            <input type="datetime-local" name="transaction_date" id="transaction_date" required
                                   value="{{ old('transaction_date', $payment->transaction_date ? $payment->transaction_date->format('Y-m-d\TH:i') : '') }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('transaction_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reference Number -->
                        <div>
                            <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number"
                                   value="{{ old('reference_number', $payment->reference_number) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('reference_number')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Gateway Reference -->
                    <div>
                        <label for="gateway_reference" class="block text-sm font-medium text-gray-700">Gateway Reference</label>
                        <input type="text" name="gateway_reference" id="gateway_reference"
                               value="{{ old('gateway_reference', $payment->gateway_reference) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('gateway_reference')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('description', $payment->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('admin.payments.show', $payment) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
