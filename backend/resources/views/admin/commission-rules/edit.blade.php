@extends('admin.layouts.app')

@section('title', 'Edit Commission Rule')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Edit Commission Rule</h1>
                        <p class="mt-2 text-sm text-gray-700">Update commission rule for agents.</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.commission-rules.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Rules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.1s;">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('admin.commission-rules.update', $commissionRule) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Plan Name -->
                    <div>
                        <label for="plan_name" class="block text-sm font-medium text-gray-700">Plan Name <span class="text-red-500">*</span></label>
                        <input type="text" name="plan_name" id="plan_name" value="{{ old('plan_name', $commissionRule->plan_name) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('plan_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Plan Type -->
                    <div>
                        <label for="plan_type" class="block text-sm font-medium text-gray-700">Plan Type <span class="text-red-500">*</span></label>
                        <select name="plan_type" id="plan_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Plan Type</option>
                            <option value="senior_care" {{ old('plan_type', $commissionRule->plan_type) == 'senior_care' ? 'selected' : '' }}>Senior Care</option>
                            <option value="medical_card" {{ old('plan_type', $commissionRule->plan_type) == 'medical_card' ? 'selected' : '' }}>Medical Card</option>
                        </select>
                        @error('plan_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Frequency -->
                    <div>
                        <label for="payment_frequency" class="block text-sm font-medium text-gray-700">Payment Frequency</label>
                        <select name="payment_frequency" id="payment_frequency"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Frequency</option>
                            <option value="monthly" {{ old('payment_frequency', $commissionRule->payment_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="quarterly" {{ old('payment_frequency', $commissionRule->payment_frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                            <option value="semi_annually" {{ old('payment_frequency', $commissionRule->payment_frequency) == 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                            <option value="annually" {{ old('payment_frequency', $commissionRule->payment_frequency) == 'annually' ? 'selected' : '' }}>Annually</option>
                        </select>
                        @error('payment_frequency')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Base Amount -->
                    <div>
                        <label for="base_amount" class="block text-sm font-medium text-gray-700">Base Amount (RM) <span class="text-red-500">*</span></label>
                        <input type="number" name="base_amount" id="base_amount" value="{{ old('base_amount', $commissionRule->base_amount) }}" step="0.01" min="0" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('base_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tier Level -->
                    <div>
                        <label for="tier_level" class="block text-sm font-medium text-gray-700">Tier Level <span class="text-red-500">*</span></label>
                        <select name="tier_level" id="tier_level" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select Tier</option>
                            <option value="1" {{ old('tier_level', $commissionRule->tier_level) == '1' ? 'selected' : '' }}>Tier 1</option>
                            <option value="2" {{ old('tier_level', $commissionRule->tier_level) == '2' ? 'selected' : '' }}>Tier 2</option>
                            <option value="3" {{ old('tier_level', $commissionRule->tier_level) == '3' ? 'selected' : '' }}>Tier 3</option>
                            <option value="4" {{ old('tier_level', $commissionRule->tier_level) == '4' ? 'selected' : '' }}>Tier 4</option>
                            <option value="5" {{ old('tier_level', $commissionRule->tier_level) == '5' ? 'selected' : '' }}>Tier 5</option>
                        </select>
                        @error('tier_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Commission Type - Hidden field, always percentage -->
                    <input type="hidden" name="commission_type" value="percentage">

                    <!-- Commission Percentage -->
                    <div>
                        <label for="commission_percentage" class="block text-sm font-medium text-gray-700">Commission Percentage (%) <span class="text-red-500">*</span></label>
                        <input type="number" name="commission_percentage" id="commission_percentage" value="{{ old('commission_percentage', $commissionRule->commission_percentage) }}" step="0.01" min="0" max="100" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('commission_percentage')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Active -->
                    <div class="sm:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $commissionRule->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active (this rule will be applied to new commissions)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.commission-rules.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Rule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
