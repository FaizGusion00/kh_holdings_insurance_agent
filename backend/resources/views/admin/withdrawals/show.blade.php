@extends('admin.layouts.app')

@section('title', 'Withdrawal Request Details')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Withdrawal Request Details</h1>
                        <p class="mt-2 text-sm text-gray-700">Review and process agent withdrawal request.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.withdrawals.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Withdrawal Details -->
        <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.1s;">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Withdrawal Information</h3>
                
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Request ID</dt>
                        <dd class="mt-1 text-sm text-gray-900">#{{ $withdrawal->id }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Amount</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold">RM {{ number_format($withdrawal->amount, 2) }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$withdrawal->status] }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Requested Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->created_at->format('M d, Y H:i') }}</dd>
                    </div>
                    
                    @if($withdrawal->processed_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Processed Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->processed_at->format('M d, Y H:i') }}</dd>
                    </div>
                    @endif
                    
                    @if($withdrawal->processedByAdmin)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Processed By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $withdrawal->processedByAdmin->name }}</dd>
                    </div>
                    @endif
                </dl>
                
                @if($withdrawal->notes)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-500">Agent Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $withdrawal->notes }}</dd>
                </div>
                @endif
                
                @if($withdrawal->admin_notes)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-500">Admin Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900 bg-blue-50 p-3 rounded-md">{{ $withdrawal->admin_notes }}</dd>
                </div>
                @endif
            </div>
        </div>

        <!-- Agent Information -->
        <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.2s;">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Agent Information</h3>
                
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-lg font-medium text-gray-700">
                                {{ substr($withdrawal->agent->name, 0, 2) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $withdrawal->agent->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ $withdrawal->agent->agent_code }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ $withdrawal->agent->email }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ $withdrawal->agent->phone_number }}</p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Bank Details</h4>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2">
                        <div>
                            <dt class="text-xs text-gray-500">Bank Name</dt>
                            <dd class="text-sm text-gray-900">{{ $withdrawal->agent->bank_name ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Account Number</dt>
                            <dd class="text-sm text-gray-900">{{ $withdrawal->agent->bank_account_number ?: 'Not provided' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Account Owner</dt>
                            <dd class="text-sm text-gray-900">{{ $withdrawal->agent->bank_account_owner ?: 'Not provided' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($withdrawal->status === 'pending')
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.3s;">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Approve Form -->
                <form action="{{ route('admin.withdrawals.approve', $withdrawal) }}" method="POST" class="flex-1">
                    @csrf
                    <div class="mb-4">
                        <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Admin Notes (Optional)
                        </label>
                        <textarea name="admin_notes" id="approve_notes" rows="3" 
                                  placeholder="Add any notes for approval..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve Withdrawal
                    </button>
                </form>

                <!-- Reject Form -->
                <form action="{{ route('admin.withdrawals.reject', $withdrawal) }}" method="POST" class="flex-1">
                    @csrf
                    <div class="mb-4">
                        <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Rejection Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea name="admin_notes" id="reject_notes" rows="3" 
                                  placeholder="Please provide reason for rejection..."
                                  required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject Withdrawal
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Complete Withdrawal (for approved requests) -->
    @if($withdrawal->status === 'approved')
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.3s;">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Complete Withdrawal</h3>
            
            <form action="{{ route('admin.withdrawals.complete', $withdrawal) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="proof_file" class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Transfer Proof <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="proof_file" id="proof_file" 
                               accept="image/*,.pdf" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Upload screenshot or receipt of the transfer (JPG, PNG, PDF)</p>
                    </div>
                    
                    <div>
                        <label for="complete_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Additional Notes (Optional)
                        </label>
                        <textarea name="admin_notes" id="complete_notes" rows="3" 
                                  placeholder="Add any additional notes..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Proof Display (for completed requests) -->
    @if($withdrawal->status === 'completed' && $withdrawal->proof_url)
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.3s;">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Transfer Proof</h3>
            
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <a href="{{ Storage::url($withdrawal->proof_url) }}" 
                   target="_blank" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Transfer Proof
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
