@extends('admin.layouts.app')

@section('title', 'Adjust Wallet Balance - ' . $wallet->agent->name)

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Adjust Wallet Balance</h1>
                        <p class="mt-2 text-sm text-gray-700">{{ $wallet->agent->name }} ({{ $wallet->agent->agent_code }})</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.wallets.show', $wallet) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Wallet
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Balance -->
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.1s;">
        <div class="px-4 py-5 sm:p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900">Current Wallet Balance</h3>
                <p class="mt-2 text-3xl font-bold text-blue-600">RM {{ number_format($wallet->balance, 2) }}</p>
                <p class="mt-1 text-sm text-gray-500">Last updated: {{ $wallet->last_updated_at ? $wallet->last_updated_at->format('M d, Y H:i') : 'Never' }}</p>
            </div>
        </div>
    </div>

    <!-- Adjustment Form -->
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.2s;">
        <form method="POST" action="{{ route('admin.wallets.update', $wallet) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Adjustment Type -->
                    <div>
                        <label for="adjustment_type" class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                        <select name="adjustment_type" id="adjustment_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Type</option>
                            <option value="add">Add Funds</option>
                            <option value="subtract">Subtract Funds</option>
                            <option value="set">Set Balance</option>
                        </select>
                        @error('adjustment_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Amount (RM)</label>
                        <input type="number" name="amount" id="amount" step="0.01" min="0" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" id="description" required
                           placeholder="Reason for this adjustment..."
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Admin Notes -->
                <div class="mt-6">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700">Admin Notes (Optional)</label>
                    <textarea name="admin_notes" id="admin_notes" rows="3"
                              placeholder="Additional notes for this adjustment..."
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    @error('admin_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.wallets.show', $wallet) }}" 
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Process Adjustment
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.3s;">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <button onclick="setQuickAdjustment('add', 100)" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add RM 100
                </button>
                <button onclick="setQuickAdjustment('add', 500)" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add RM 500
                </button>
                <button onclick="setQuickAdjustment('subtract', 100)" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m16 0l-4-4m4 4l-4 4"></path>
                    </svg>
                    Subtract RM 100
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function setQuickAdjustment(type, amount) {
    document.getElementById('adjustment_type').value = type;
    document.getElementById('amount').value = amount;
    
    // Set appropriate description based on type and amount
    let description = '';
    if (type === 'add') {
        description = `Quick add of RM ${amount} to wallet balance`;
    } else if (type === 'subtract') {
        description = `Quick deduction of RM ${amount} from wallet balance`;
    }
    
    document.getElementById('description').value = description;
}
</script>
@endsection
