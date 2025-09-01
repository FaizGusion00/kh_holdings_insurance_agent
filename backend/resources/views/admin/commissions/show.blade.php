@extends('admin.layouts.app')

@section('title', 'Commission Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Commission Details</h1>
            <p class="mt-2 text-sm text-gray-700">Commission ID: #{{ $commission->id }}</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-3">
            <a href="{{ route('admin.commissions.edit', $commission->id) }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                Edit Commission
            </a>
            <a href="{{ route('admin.commissions.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Commissions
            </a>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Commission Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center">
                        <div class="mx-auto h-20 w-20 rounded-full bg-gradient-to-r from-green-600 to-blue-600 flex items-center justify-center">
                            <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-2xl font-bold text-gray-900">RM {{ number_format($commission->commission_amount, 2) }}</h3>
                        <p class="text-sm text-gray-500">Commission Amount</p>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $commission->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 
                                   ($commission->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                {{ ucfirst($commission->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Commission Type</dt>
                                <dd class="text-sm text-gray-900">{{ ucfirst($commission->commission_type) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tier Level</dt>
                                <dd class="text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $commission->tier_level <= 2 ? 'bg-green-100 text-green-800' : 
                                           ($commission->tier_level <= 4 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        Tier {{ $commission->tier_level }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Period</dt>
                                <dd class="text-sm text-gray-900">{{ date('F Y', mktime(0, 0, 0, $commission->month, 1, $commission->year)) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $commission->created_at->format('M j, Y g:i A') }}</dd>
                            </div>
                            @if($commission->status === 'paid' && $commission->paid_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Paid Date</dt>
                                <dd class="text-sm text-gray-900">{{ $commission->paid_at->format('M j, Y g:i A') }}</dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Agent Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Agent Information</h3>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12">
                            <div class="h-12 w-12 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">{{ substr($commission->user->name, 0, 1) }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-lg font-medium text-gray-900">{{ $commission->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $commission->user->email }}</div>
                            <div class="text-sm text-gray-500">Agent Code: {{ $commission->user->agent_code }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Product Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Product Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $commission->product->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Product Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($commission->product->product_type) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Premium Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($commission->product->premium_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Coverage Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900">RM {{ number_format($commission->product->coverage_amount, 2) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Policy Information -->
            @if($commission->policy)
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Policy Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Policy Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $commission->policy->policy_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Policy Holder</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $commission->policy->member->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Policy Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $commission->policy->status === 'active' ? 'bg-green-100 text-green-800' : 
                                       ($commission->policy->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($commission->policy->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $commission->policy->start_date ? $commission->policy->start_date->format('M j, Y') : 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($commission->notes)
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Notes</h3>
                    <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                        {{ $commission->notes }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            @if($commission->status === 'pending')
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Actions</h3>
                    <div class="flex space-x-3">
                        <form action="{{ route('admin.commissions.approve', $commission->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700" 
                                    onclick="return confirm('Are you sure you want to approve this commission?')">
                                Approve Commission
                            </button>
                        </form>
                        <form action="{{ route('admin.commissions.reject', $commission->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700" 
                                    onclick="return confirm('Are you sure you want to reject this commission?')">
                                Reject Commission
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @elseif($commission->status === 'approved')
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Actions</h3>
                    <form action="{{ route('admin.commissions.markPaid', $commission->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700" 
                                onclick="return confirm('Are you sure you want to mark this commission as paid?')">
                            Mark as Paid
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
