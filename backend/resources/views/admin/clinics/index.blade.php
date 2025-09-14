@extends('admin.layouts.app')

@section('title', 'Clinics Management')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Clinics Management</h1>
                        <p class="mt-2 text-sm text-gray-700">Manage clinic information and partnerships.</p>
                    </div>
                    <a href="{{ route('admin.clinics.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Clinic
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white shadow rounded-lg p-6 fade-in" style="animation-delay: 0.1s;">
        <form method="GET" action="{{ route('admin.clinics.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Clinic name, city..." 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="state" class="block text-sm font-medium text-gray-700">State</label>
                <select name="state" id="state" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All States</option>
                    <option value="Johor" {{ request('state') == 'Johor' ? 'selected' : '' }}>Johor</option>
                    <option value="Kedah" {{ request('state') == 'Kedah' ? 'selected' : '' }}>Kedah</option>
                    <option value="Kelantan" {{ request('state') == 'Kelantan' ? 'selected' : '' }}>Kelantan</option>
                    <option value="Melaka" {{ request('state') == 'Melaka' ? 'selected' : '' }}>Melaka</option>
                    <option value="Negeri Sembilan" {{ request('state') == 'Negeri Sembilan' ? 'selected' : '' }}>Negeri Sembilan</option>
                    <option value="Pahang" {{ request('state') == 'Pahang' ? 'selected' : '' }}>Pahang</option>
                    <option value="Perak" {{ request('state') == 'Perak' ? 'selected' : '' }}>Perak</option>
                    <option value="Perlis" {{ request('state') == 'Perlis' ? 'selected' : '' }}>Perlis</option>
                    <option value="Pulau Pinang" {{ request('state') == 'Pulau Pinang' ? 'selected' : '' }}>Pulau Pinang</option>
                    <option value="Sabah" {{ request('state') == 'Sabah' ? 'selected' : '' }}>Sabah</option>
                    <option value="Sarawak" {{ request('state') == 'Sarawak' ? 'selected' : '' }}>Sarawak</option>
                    <option value="Selangor" {{ request('state') == 'Selangor' ? 'selected' : '' }}>Selangor</option>
                    <option value="Terengganu" {{ request('state') == 'Terengganu' ? 'selected' : '' }}>Terengganu</option>
                    <option value="Kuala Lumpur" {{ request('state') == 'Kuala Lumpur' ? 'selected' : '' }}>Kuala Lumpur</option>
                    <option value="Labuan" {{ request('state') == 'Labuan' ? 'selected' : '' }}>Labuan</option>
                    <option value="Putrajaya" {{ request('state') == 'Putrajaya' ? 'selected' : '' }}>Putrajaya</option>
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Filter
                </button>
                <a href="{{ route('admin.clinics.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Clinics Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 fade-in" style="animation-delay: 0.2s;">
        @forelse($clinics as $clinic)
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-600 to-green-700 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.795l.75-1.3m7.5-12.99l.75-1.3m-6.063 16.658l.26-1.477m2.605-14.772l.26-1.477m0 17.726l-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205L12 12m6.894 5.785l-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864l-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($clinic->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $clinic->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $clinic->description }}</p>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $clinic->city }}, {{ $clinic->state }}
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            {{ $clinic->phone }}
                        </div>
                        @if($clinic->email)
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            {{ $clinic->email }}
                        </div>
                        @endif
                        @if($clinic->specialties)
                        <div class="mt-2">
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $specialties = is_string($clinic->specialties) ? 
                                        json_decode($clinic->specialties, true) : 
                                        $clinic->specialties;
                                @endphp
                                @if(is_array($specialties))
                                    @foreach($specialties as $specialty)
                                        @if(is_string($specialty))
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $specialty }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ json_encode($specialty) }}
                                            </span>
                                        @endif
                                    @endforeach
                                @elseif(is_string($specialties))
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $specialties }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ json_encode($specialties) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($clinic->operating_hours)
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">
                                <strong>Operating Hours:</strong>
                                @php
                                    $operatingHours = is_string($clinic->operating_hours) ? 
                                        json_decode($clinic->operating_hours, true) : 
                                        $clinic->operating_hours;
                                @endphp
                                @if(is_array($operatingHours))
                                    @foreach($operatingHours as $schedule)
                                        @if(is_array($schedule) && isset($schedule['day']) && isset($schedule['open_time']) && isset($schedule['close_time']))
                                            <div>{{ $schedule['day'] }}: {{ $schedule['open_time'] }} - {{ $schedule['close_time'] }}</div>
                                        @elseif(is_string($schedule))
                                            <div>{{ $schedule }}</div>
                                        @else
                                            <div>{{ json_encode($schedule) }}</div>
                                        @endif
                                    @endforeach
                                @elseif(is_string($operatingHours))
                                    <div>{{ $operatingHours }}</div>
                                @else
                                    <div>{{ json_encode($operatingHours) }}</div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.clinics.show', $clinic) }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">View</a>
                        <a href="{{ route('admin.clinics.edit', $clinic) }}" 
                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                    </div>
                    <div class="text-sm text-gray-500">
                        Created {{ $clinic->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.795l.75-1.3m7.5-12.99l.75-1.3m-6.063 16.658l.26-1.477m2.605-14.772l.26-1.477m0 17.726l-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205L12 12m6.894 5.785l-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864l-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No clinics</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by adding a new clinic.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.clinics.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Add Clinic
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($clinics->hasPages())
    <div class="mt-6">
        {{ $clinics->links() }}
    </div>
    @endif
</div>
@endsection
