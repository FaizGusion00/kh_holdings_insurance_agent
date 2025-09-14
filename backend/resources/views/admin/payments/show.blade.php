@extends('admin.layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Payment Details</h1>
                        <p class="mt-2 text-sm text-gray-700">Transaction #{{ $payment->reference_number }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.payments.edit', $payment) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Payment
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

    <!-- Payment Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Transaction Information</h3>
                </div>
                <div class="px-6 py-4 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Reference Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $payment->reference_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->status_badge_class }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Amount</label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $payment->formatted_amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Payment Type</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $payment->payment_type)) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Payment Method</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucwords($payment->payment_method) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Transaction Date</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $payment->transaction_date->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    @if($payment->gateway_reference)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Gateway Reference</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->gateway_reference }}</p>
                    </div>
                    @endif

                    @if($payment->description)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->description }}</p>
                    </div>
                    @endif

                    @if($payment->processed_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Processed At</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->processed_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif

                    @if($payment->failed_at)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Failed At</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->failed_at->format('M d, Y H:i') }}</p>
                    </div>
                    @endif

                    @if($payment->failure_reason)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Failure Reason</label>
                        <p class="mt-1 text-sm text-red-600">{{ $payment->failure_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="space-y-6">
            <!-- Member Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Member Information</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Member Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->member ? $payment->member->name : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">NRIC</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->member ? $payment->member->nric : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->member ? $payment->member->email : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->member ? $payment->member->phone : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Policy Information -->
            @if($payment->policy)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Policy Information</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Policy Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->policy->policy_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Product</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $payment->policy->product ? $payment->policy->product->name : 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Monthly Premium</label>
                        <p class="mt-1 text-sm text-gray-900">RM {{ number_format($payment->policy->monthly_premium, 2) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->policy->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($payment->policy->status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <a href="{{ route('admin.payments.edit', $payment) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Payment
                    </a>
                    
                    @if($payment->status === 'pending')
                    <form action="{{ route('admin.payments.approve', $payment) }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Approve Payment
                        </button>
                    </form>
                    @endif
                    
                    @if($payment->status !== 'completed')
                    <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST" class="w-full" 
                          onsubmit="return confirm('Are you sure you want to delete this payment?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete Payment
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
