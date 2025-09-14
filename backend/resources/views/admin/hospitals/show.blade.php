@extends('admin.layouts.app')

@section('title', 'Hospital Details')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $hospital->name }}</h1>
                        <p class="mt-2 text-sm text-gray-700">Hospital information and details</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.hospitals.edit', $hospital) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Hospital
                        </a>
                        <a href="{{ route('admin.hospitals.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Hospitals
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hospital Details -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Hospital Information</h3>
                </div>
                <div class="px-6 py-4 space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Hospital Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hospital->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $hospital->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $hospital->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Description</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $hospital->description ?? 'No description available' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $hospital->full_address }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hospital->phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hospital->email ?? 'Not provided' }}</p>
                        </div>
                    </div>

                    @if($hospital->website)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Website</label>
                        <a href="{{ $hospital->website }}" target="_blank" class="mt-1 text-sm text-blue-600 hover:text-blue-500">
                            {{ $hospital->website }}
                        </a>
                    </div>
                    @endif

                    @if($hospital->license_number)
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">License Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $hospital->license_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">License Expiry</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($hospital->license_expiry)
                                    @if(is_string($hospital->license_expiry))
                                        {{ \Carbon\Carbon::parse($hospital->license_expiry)->format('M d, Y') }}
                                    @else
                                        {{ $hospital->license_expiry->format('M d, Y') }}
                                    @endif
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="space-y-6">
            <!-- Specialties -->
            @if($hospital->specialties && is_array($hospital->specialties) && count($hospital->specialties) > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Specialties</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($hospital->specialties as $specialty)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $specialty }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Stats</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="text-sm font-medium text-gray-900">{{ $hospital->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Created</span>
                        <span class="text-sm font-medium text-gray-900">{{ $hospital->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Last Updated</span>
                        <span class="text-sm font-medium text-gray-900">{{ $hospital->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <a href="{{ route('admin.hospitals.edit', $hospital) }}" 
                       class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Hospital
                    </a>
                    <form action="{{ route('admin.hospitals.destroy', $hospital) }}" method="POST" class="w-full" 
                          onsubmit="return confirm('Are you sure you want to delete this hospital?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Delete Hospital
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
