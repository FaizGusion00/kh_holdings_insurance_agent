@extends('admin.layouts.app')

@section('title', 'Members Management')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Members Management</h1>
                        <p class="mt-2 text-sm text-gray-700">Manage all insurance members and their policies.</p>
                    </div>
                    <a href="{{ route('admin.members.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Member
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Statistics -->
    <div class="bg-white shadow rounded-lg p-6 fade-in" style="animation-delay: 0.1s;">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Network Overview</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $networkStats['total_agents'] }}</div>
                <div class="text-sm text-blue-800">Total Agents</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $networkStats['level_1_agents'] }}</div>
                <div class="text-sm text-green-800">Level 1 Agents</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $networkStats['level_2_agents'] }}</div>
                <div class="text-sm text-yellow-800">Level 2 Agents</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-orange-600">{{ $networkStats['level_3_agents'] }}</div>
                <div class="text-sm text-orange-800">Level 3+ Agents</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">{{ $networkStats['total_customers'] }}</div>
                <div class="text-sm text-purple-800">Customers</div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white shadow rounded-lg p-6 fade-in" style="animation-delay: 0.2s;">
        <form method="GET" action="{{ route('admin.members.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Name, email, agent code..." 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Types</option>
                    <option value="agents" {{ request('type') == 'agents' ? 'selected' : '' }}>Agents Only</option>
                    <option value="customers" {{ request('type') == 'customers' ? 'selected' : '' }}>Customers Only</option>
                </select>
            </div>
            
            <div>
                <label for="level" class="block text-sm font-medium text-gray-700">Agent Level</label>
                <select name="level" id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Levels</option>
                    <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>Level 1</option>
                    <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Level 2</option>
                    <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>Level 3</option>
                    <option value="4" {{ request('level') == '4' ? 'selected' : '' }}>Level 4</option>
                    <option value="5" {{ request('level') == '5' ? 'selected' : '' }}>Level 5</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Filter
                </button>
                <a href="{{ route('admin.members.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Members Table -->
    <div class="bg-white shadow rounded-lg fade-in" style="animation-delay: 0.2s;">
        <div class="px-4 py-5 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member/Agent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Network Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referrer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wallet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($members as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($member->is_agent)
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-green-600 to-blue-600 flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">A</span>
                                            </div>
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-600 to-purple-600 flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">{{ substr($member->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $member->name }}
                                            @if($member->is_agent)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Agent
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                        <div class="text-sm text-gray-500">
                                            @if($member->agent_code)
                                                Code: {{ $member->agent_code }}
                                            @else
                                                {{ $member->phone }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->is_agent)
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($member->mlm_level == 1) bg-green-100 text-green-800
                                            @elseif($member->mlm_level == 2) bg-yellow-100 text-yellow-800
                                            @elseif($member->mlm_level == 3) bg-orange-100 text-orange-800
                                            @elseif($member->mlm_level == 4) bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            Level {{ $member->mlm_level }}
                                        </span>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500">Customer</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->is_agent && $member->referrer_agent_code)
                                    <div class="text-sm text-gray-900">{{ $member->referrerAgent->name ?? 'Unknown' }}</div>
                                    <div class="text-sm text-gray-500">{{ $member->referrer_agent_code }}</div>
                                @elseif(!$member->is_agent)
                                    <div class="text-sm text-gray-900">{{ $member->agent->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $member->agent->email ?? '' }}</div>
                                @else
                                    <div class="text-sm text-gray-500">Top Level</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->is_agent)
                                    <div class="flex flex-col">
                                        <span class="text-blue-600 text-sm font-medium">RM {{ number_format($member->wallet_balance ?? 0, 2) }}</span>
                                        <span class="text-gray-500 text-xs">Agent Wallet</span>
                                    </div>
                                @else
                                    <div class="flex flex-col">
                                        <span class="text-gray-600 text-sm">Customer</span>
                                        <span class="text-gray-500 text-xs">No wallet</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @elseif($member->status === 'inactive')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Inactive
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $member->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.members.show', $member) }}" 
                                       class="text-blue-600 hover:text-blue-900">View</a>
                                    <a href="{{ route('admin.members.edit', $member) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    @if($member->is_agent)
                                        <a href="{{ route('admin.wallets.show', $member->user_id) }}" 
                                           class="text-green-600 hover:text-green-900">Wallet</a>
                                    @else
                                        <a href="/plans" target="_blank" 
                                           class="text-green-600 hover:text-green-900">Plans</a>
                                    @endif
                                    <button onclick="confirmDelete('{{ $member->id }}', '{{ $member->name }}', {{ $member->is_agent ? 'true' : 'false' }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No members found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($members->hasPages())
            <div class="mt-6">
                {{ $members->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4" id="deleteModalTitle">Delete Member</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="deleteModalMessage">
                    Are you sure you want to delete this member? This action cannot be undone.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmDelete" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Delete
                </button>
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
let memberToDelete = null;

function confirmDelete(memberId, memberName, isAgent) {
    memberToDelete = memberId;
    const modal = document.getElementById('deleteModal');
    const title = document.getElementById('deleteModalTitle');
    const message = document.getElementById('deleteModalMessage');
    
    if (isAgent) {
        title.textContent = 'Delete Agent';
        message.textContent = `Are you sure you want to delete agent "${memberName}"? This will also delete ALL their downline agents and customers. This action cannot be undone.`;
    } else {
        title.textContent = 'Delete Member';
        message.textContent = `Are you sure you want to delete member "${memberName}"? This action cannot be undone.`;
    }
    
    modal.classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    memberToDelete = null;
}

document.getElementById('confirmDelete').addEventListener('click', function() {
    if (memberToDelete) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/members/${memberToDelete}`;
        form.submit();
    }
});

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection
