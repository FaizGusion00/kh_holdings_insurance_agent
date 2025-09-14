@extends('admin.layouts.app')

@section('title', 'Pending Payments')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Pending Payments</h1>
                        <p class="mt-2 text-sm text-gray-700">Review and approve pending payment transactions.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.payments.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            All Payments
                        </a>
                        <a href="{{ route('admin.payments.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Payment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($pendingPayments->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Bulk Actions</h3>
                <span class="text-sm text-gray-500">{{ $pendingPayments->count() }} pending payments</span>
            </div>
        </div>
        <div class="px-6 py-4">
            <form id="bulkApproveForm" action="{{ route('admin.payments.bulk-approve') }}" method="POST">
                @csrf
                <div class="flex items-center space-x-4">
                    <button type="button" id="selectAllBtn" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Select All
                    </button>
                    <button type="button" id="selectNoneBtn" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Select None
                    </button>
                    <button type="submit" id="bulkApproveBtn" disabled
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Approve Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Pending Payments List -->
    <div class="bg-white shadow rounded-lg">
        @if($pendingPayments->count() > 0)
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAllCheckbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Reference
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Member
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Amount
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Method
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pendingPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" 
                                       class="payment-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payment->reference_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payment->member ? $payment->member->name : 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $payment->member ? $payment->member->nric : 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payment->formatted_amount }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucwords(str_replace('_', ' ', $payment->payment_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ ucwords($payment->payment_method) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $payment->transaction_date->format('M d, Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $payment->transaction_date->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('admin.payments.show', $payment) }}" 
                                   class="text-blue-600 hover:text-blue-900">View</a>
                                <a href="{{ route('admin.payments.edit', $payment) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('admin.payments.approve', $payment) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="text-green-600 hover:text-green-900"
                                            onclick="return confirm('Are you sure you want to approve this payment?')">
                                        Approve
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $pendingPayments->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No pending payments</h3>
                <p class="mt-1 text-sm text-gray-500">All payments have been processed.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.payments.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Payment
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const paymentCheckboxes = document.querySelectorAll('.payment-checkbox');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const selectNoneBtn = document.getElementById('selectNoneBtn');

    // Select All checkbox functionality
    selectAllCheckbox.addEventListener('change', function() {
        paymentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkApproveButton();
    });

    // Individual checkbox functionality
    paymentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkApproveButton();
            updateSelectAllCheckbox();
        });
    });

    // Select All button
    selectAllBtn.addEventListener('click', function() {
        paymentCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
        selectAllCheckbox.checked = true;
        updateBulkApproveButton();
    });

    // Select None button
    selectNoneBtn.addEventListener('click', function() {
        paymentCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        selectAllCheckbox.checked = false;
        updateBulkApproveButton();
    });

    // Bulk approve form submission
    document.getElementById('bulkApproveForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.payment-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one payment to approve.');
            return;
        }
        
        if (!confirm(`Are you sure you want to approve ${checkedBoxes.length} payment(s)?`)) {
            e.preventDefault();
        }
    });

    function updateBulkApproveButton() {
        const checkedBoxes = document.querySelectorAll('.payment-checkbox:checked');
        bulkApproveBtn.disabled = checkedBoxes.length === 0;
    }

    function updateSelectAllCheckbox() {
        const checkedBoxes = document.querySelectorAll('.payment-checkbox:checked');
        const totalBoxes = paymentCheckboxes.length;
        
        if (checkedBoxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedBoxes.length === totalBoxes) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Initialize
    updateBulkApproveButton();
    updateSelectAllCheckbox();
});
</script>
@endsection
