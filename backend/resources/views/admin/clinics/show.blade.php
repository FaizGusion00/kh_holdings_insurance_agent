@extends('admin.layouts.app')

@section('title', 'Clinic Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Clinic Details</h1>
            <p class="mt-2 text-sm text-gray-700">Complete information about {{ $clinic->name }}.</p>
        </div>
        <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-3">
            <a href="{{ route('admin.clinics.edit', $clinic->id) }}" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500">
                Edit Clinic
            </a>
            <a href="{{ route('admin.clinics.index') }}" class="inline-flex items-center rounded-md bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
                Back to Clinics
            </a>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Clinic Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="text-center">
                        <div class="mx-auto h-24 w-24 rounded-full bg-gradient-to-r from-emerald-600 to-blue-600 flex items-center justify-center">
                            <svg class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $clinic->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $clinic->description }}</p>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $clinic->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $clinic->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">License Number</dt>
                                <dd class="text-sm text-gray-900">{{ $clinic->license_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">License Expiry</dt>
                                <dd class="text-sm text-gray-900">{{ $clinic->license_expiry ? $clinic->license_expiry->format('M j, Y') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="text-sm text-gray-900">{{ $clinic->created_at->format('M j, Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Contact Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">City</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->city }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">State</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->state }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Postal Code</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->postal_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Country</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->country }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->phone }}</dd>
                        </div>
                        @if($clinic->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->email }}</dd>
                        </div>
                        @endif
                        @if($clinic->website)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="{{ $clinic->website }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                                    {{ $clinic->website }}
                                </a>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Specialties -->
            @if($clinic->specialties)
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Specialties</h3>
                    <div class="flex flex-wrap gap-2">
                        @php
                            $specialties = is_string($clinic->specialties) ? 
                                json_decode($clinic->specialties, true) : 
                                $clinic->specialties;
                        @endphp
                        @if(is_array($specialties))
                            @foreach($specialties as $specialty)
                                @if(is_string($specialty))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ $specialty }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ json_encode($specialty) }}
                                    </span>
                                @endif
                            @endforeach
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $clinic->specialties }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Operating Hours -->
            @if($clinic->operating_hours)
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Operating Hours</h3>
                    <div class="space-y-2">
                        @php
                            $operatingHours = is_string($clinic->operating_hours) ? 
                                json_decode($clinic->operating_hours, true) : 
                                $clinic->operating_hours;
                        @endphp
                        @if(is_array($operatingHours))
                            @foreach($operatingHours as $schedule)
                                @if(is_array($schedule) && isset($schedule['day']) && isset($schedule['open_time']) && isset($schedule['close_time']))
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="font-medium text-gray-700">{{ $schedule['day'] }}</span>
                                        <span class="text-gray-600">
                                            @if(isset($schedule['closed']) && $schedule['closed'])
                                                <span class="text-red-600 font-medium">Closed</span>
                                            @else
                                                {{ $schedule['open_time'] }} - {{ $schedule['close_time'] }}
                                            @endif
                                        </span>
                                    </div>
                                @elseif(is_string($schedule))
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="font-medium text-gray-700">Schedule</span>
                                        <span class="text-gray-600">{{ $schedule }}</span>
                                    </div>
                                @else
                                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                        <span class="font-medium text-gray-700">Schedule</span>
                                        <span class="text-gray-600">{{ json_encode($schedule) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @elseif(is_string($operatingHours))
                            <div class="text-gray-600">{{ $operatingHours }}</div>
                        @else
                            <div class="text-gray-600">{{ json_encode($operatingHours) }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- License Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">License Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">License Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->license_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">License Expiry Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($clinic->license_expiry)
                                    <span class="{{ $clinic->license_expiry->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $clinic->license_expiry->format('M j, Y') }}
                                        @if($clinic->license_expiry->isPast())
                                            <span class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">Expired</span>
                                        @endif
                                    </span>
                                @else
                                    N/A
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Audit Information -->
            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Audit Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $clinic->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Clinic ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">#{{ $clinic->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $clinic->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $clinic->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
