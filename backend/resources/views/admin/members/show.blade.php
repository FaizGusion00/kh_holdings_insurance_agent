@extends('admin.layouts.app')

@section('title', 'Member Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Member Details</h1>
            <p class="mt-2 text-sm text-gray-700">View member information and policies.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-3">
            <a href="{{ route('admin.members.edit', $member) }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Edit Member
            </a>
            <a href="{{ route('admin.members.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Members
            </a>
        </div>
    </div>

    <div class="mt-8 space-y-6">
        <!-- Member Information -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">NRIC</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->nric }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->email ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('F j, Y') : 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($member->gender) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Occupation</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->occupation }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Race</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->race }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->status === 'active' ? 'bg-green-100 text-green-800' : ($member->status === 'inactive' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($member->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Relationship with Agent</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->relationship_with_agent }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Plan Information -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Current Plan</h3>
                @php
                    $currentPlan = $member->currentActivePlan();
                @endphp
                @if($currentPlan)
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Plan Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $currentPlan->plan_name ?? $currentPlan->product->name ?? 'Unknown Plan' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Plan Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $currentPlan instanceof \App\Models\MedicalInsurancePolicy ? 'Medical Insurance' : 'Member Policy' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($currentPlan->start_date)->format('F j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">End Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($currentPlan->end_date)->format('F j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($currentPlan->end_date && \Carbon\Carbon::parse($currentPlan->end_date)->isPast())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                @elseif($currentPlan->end_date && \Carbon\Carbon::parse($currentPlan->end_date)->diffInDays(now()) <= 30)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Expires Soon
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Days Remaining</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($currentPlan->end_date)
                                    {{ \Carbon\Carbon::parse($currentPlan->end_date)->diffInDays(now()) }} days
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-500 text-lg font-medium mb-2">No Active Plan</div>
                        <div class="text-gray-400 text-sm">This member is not currently enrolled in any insurance plan.</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Network Hierarchy Information -->
        @if($member->is_agent)
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Network Hierarchy</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Agent Code</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $member->agent_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Network Level</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($member->mlm_level == 1) bg-green-100 text-green-800
                                @elseif($member->mlm_level == 2) bg-yellow-100 text-yellow-800
                                @elseif($member->mlm_level == 3) bg-orange-100 text-orange-800
                                @elseif($member->mlm_level == 4) bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                Level {{ $member->mlm_level }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Referrer Agent</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($member->referrerAgent)
                                {{ $member->referrerAgent->name }} ({{ $member->referrer_agent_code }})
                            @else
                                Top Level Agent
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Wallet Balance</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-semibold text-green-600">RM {{ number_format($member->wallet_balance ?? 0, 2) }}</dd>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Address Information -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Address Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">City</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->city }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">State</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->state ?: 'Not provided' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Postal Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->postal_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Referrer Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->referrer_code ?: 'Not provided' }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Emergency Contact</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Emergency Contact Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->emergency_contact_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Emergency Contact Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->emergency_contact_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Relationship</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->emergency_contact_relationship }}</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent Information -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Agent Information</h3>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Agent Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->agent->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Agent Code</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->agent->agent_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Agent Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->agent->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Agent Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->agent->phone_number }}</dd>
                    </div>
                </div>
            </div>
        </div>


        <!-- Payment History -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Payment History</h3>
                
                @if($member->paymentTransactions->count() > 0)
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($member->paymentTransactions->take(5) as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $payment->transaction_id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->created_at->format('M j, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($member->paymentTransactions->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-500">View all {{ $member->paymentTransactions->count() }} payments</a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No payments</h3>
                        <p class="mt-1 text-sm text-gray-500">This member doesn't have any payment history yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
