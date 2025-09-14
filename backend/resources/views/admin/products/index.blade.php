@extends('admin.layouts.app')

@section('title', 'Insurance Products Management')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="bounce-in">
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Insurance Products</h1>
                        <p class="mt-2 text-sm text-gray-700">Manage insurance products and their commission rules.</p>
                    </div>
                    <a href="{{ route('admin.products.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Product
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white shadow rounded-lg p-6 fade-in" style="animation-delay: 0.1s;">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Product name, category..." 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Categories</option>
                    <option value="life" {{ request('category') == 'life' ? 'selected' : '' }}>Life Insurance</option>
                    <option value="health" {{ request('category') == 'health' ? 'selected' : '' }}>Health Insurance</option>
                    <option value="motor" {{ request('category') == 'motor' ? 'selected' : '' }}>Motor Insurance</option>
                    <option value="property" {{ request('category') == 'property' ? 'selected' : '' }}>Property Insurance</option>
                    <option value="travel" {{ request('category') == 'travel' ? 'selected' : '' }}>Travel Insurance</option>
                </select>
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Filter
                </button>
                <a href="{{ route('admin.products.index') }}" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 fade-in" style="animation-delay: 0.2s;">
        @forelse($products as $product)
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($product->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Inactive
                            </span>
                        @endif
                        <form method="POST" action="{{ route('admin.products.toggle-status', $product) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $product->name }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $product->description }}</p>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Category:</span>
                            <span class="font-medium text-gray-900">{{ ucfirst($product->category) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Premium Range:</span>
                            <span class="font-medium text-gray-900">RM {{ number_format($product->min_premium, 2) }} - RM {{ number_format($product->max_premium, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Commission Rules:</span>
                            <span class="font-medium text-gray-900">{{ $product->commission_rules_count }} rules</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Active Policies:</span>
                            <span class="font-medium text-gray-900">{{ $product->policies_count }} policies</span>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between">
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.products.show', $product) }}" 
                           class="text-blue-600 hover:text-blue-900 text-sm font-medium">View</a>
                        <a href="{{ route('admin.products.edit', $product) }}" 
                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">Edit</a>
                    </div>
                    <div class="text-sm text-gray-500">
                        Created {{ $product->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new insurance product.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.products.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Add Product
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($products->hasPages())
    <div class="mt-6">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
