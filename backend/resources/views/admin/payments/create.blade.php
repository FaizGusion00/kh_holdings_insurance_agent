@extends('admin.layouts.app')

@section('title', 'Add New Payment')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Add New Payment</h1>
                        <p class="mt-2 text-sm text-gray-700">Record a new payment transaction for a member.</p>
                    </div>
                    <div class="flex space-x-3">
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

    <!-- Create Form -->
    <div class="max-w-5xl mx-auto">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <form action="{{ route('admin.payments.store') }}" method="POST" class="space-y-0">
                @csrf
                
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-white">Create New Payment</h3>
                            <p class="text-blue-100 text-sm mt-1">Fill in the details below to record a new payment transaction</p>
                        </div>
                    </div>
                </div>
                
                <!-- Form Content -->
                <div class="px-8 py-8 space-y-8">
                    <!-- Member and Policy Selection -->
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Member Selection -->
                        <div class="space-y-3">
                            <label for="member_id" class="block text-sm font-semibold text-gray-800 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Select Member *
                            </label>
                            <select name="member_id" id="member_id" required
                                    class="mt-2 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 hover:border-gray-300">
                                <option value="">Choose a member...</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->nric }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Policy Selection -->
                        <div class="space-y-3">
                            <label for="policy_id" class="block text-sm font-semibold text-gray-800 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Select Policy *
                            </label>
                            <select name="policy_id" id="policy_id" required
                                    class="mt-2 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 hover:border-gray-300">
                                <option value="">Choose a policy...</option>
                                @foreach($policies as $policy)
                                    <option value="{{ $policy->id }}" {{ old('policy_id') == $policy->id ? 'selected' : '' }}>
                                        {{ $policy->policy_number }} - {{ $policy->product ? $policy->product->name : 'Unknown Product' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('policy_id')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Payment Details Section -->
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Payment Details
                        </h4>
                        
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <!-- Amount -->
                            <div class="space-y-3">
                                <label for="amount" class="block text-sm font-semibold text-gray-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Amount (RM) *
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-medium">RM</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" required
                                           value="{{ old('amount') }}"
                                           placeholder="0.00"
                                           class="pl-12 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm transition-all duration-200 hover:border-gray-300">
                                </div>
                                @error('amount')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Payment Type -->
                            <div class="space-y-3">
                                <label for="payment_type" class="block text-sm font-semibold text-gray-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Payment Type *
                                </label>
                                <select name="payment_type" id="payment_type" required
                                        class="mt-2 block w-full px-4 py-3 border-2 border-gray-200 rounded-xl shadow-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm transition-all duration-200 hover:border-gray-300">
                                    <option value="">Choose payment type...</option>
                                    <option value="membership_fee" {{ old('payment_type') == 'membership_fee' ? 'selected' : '' }}>Membership Fee</option>
                                    <option value="sharing_account" {{ old('payment_type') == 'sharing_account' ? 'selected' : '' }}>Sharing Account</option>
                                    <option value="policy_premium" {{ old('payment_type') == 'policy_premium' ? 'selected' : '' }}>Policy Premium</option>
                                </select>
                                @error('payment_type')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                            <select name="payment_method" id="payment_method" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select Payment Method</option>
                                <option value="mandate" {{ old('payment_method') == 'mandate' ? 'selected' : '' }}>Mandate</option>
                                <option value="manual" {{ old('payment_method') == 'manual' ? 'selected' : '' }}>Manual</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
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
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                   value="{{ old('transaction_date', now()->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('transaction_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Reference Number -->
                        <div>
                            <label for="reference_number" class="block text-sm font-medium text-gray-700">Reference Number</label>
                            <input type="text" name="reference_number" id="reference_number"
                                   value="{{ old('reference_number') }}"
                                   placeholder="Auto-generated if empty"
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
                               value="{{ old('gateway_reference') }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('gateway_reference')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  placeholder="Payment description or notes..."
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-8 py-6 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200 flex justify-end space-x-4">
                    <a href="{{ route('admin.payments.index') }}" 
                       class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-sm font-semibold rounded-xl shadow-sm text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 border-2 border-transparent text-sm font-semibold rounded-xl shadow-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform hover:scale-105 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection