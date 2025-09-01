<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - KH Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/v2/24/outline/esm/index.js"></script>
    
    <!-- Custom CSS -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Ensure content elements with fade-in class remain visible */
        .fade-in:not(.flash-message) {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Ensure bounce-in elements remain visible */
        .bounce-in {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Only allow flash messages to be auto-hidden */
        .flash-message.fade-in {
            transition: opacity 0.5s ease-out;
        }
        
        /* Prevent any accidental hiding of main content */
        main .fade-in,
        main .bounce-in {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        /* Override any potential CSS animations that might hide content */
        .fade-in,
        .bounce-in {
            animation-fill-mode: forwards !important;
        }
        
        /* Ensure all content sections remain visible */
        .bg-white.shadow.rounded-lg,
        .space-y-6 > div {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
    </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: false, darkMode: false }">
    <div class="min-h-full">
        <!-- Sidebar for mobile -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative z-50 lg:hidden" 
             x-cloak>
            <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
            
            <div class="fixed inset-0 z-40 flex">
                <div class="relative flex w-full max-w-xs flex-1 flex-col bg-white pb-4 pt-5">
                    <div class="absolute right-0 top-0 -mr-12 pt-2">
                        <button type="button" 
                                @click="sidebarOpen = false"
                                class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                            <span class="sr-only">Close sidebar</span>
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Mobile sidebar content -->
                    @include('admin.layouts.sidebar')
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
            @include('admin.layouts.sidebar')
        </div>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top navigation -->
            @include('admin.layouts.header')

            <!-- Page content -->
            <main class="py-6">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <!-- Flash messages -->
                    @if(session('success'))
                        <div class="mb-6 rounded-md bg-green-50 p-4 fade-in flash-message">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 rounded-md bg-red-50 p-4 fade-in flash-message">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm text-red-800">
                                        <ul class="list-disc space-y-1 pl-5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-md bg-red-50 p-4 fade-in flash-message">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm text-red-800">
                                        <ul class="list-disc space-y-1 pl-5">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page content -->
                    <div class="fade-in">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                // Only target flash message containers, not content elements
                const flashMessages = document.querySelectorAll('.flash-message.fade-in');
                flashMessages.forEach(function(message) {
                    message.style.transition = 'opacity 0.5s ease-out';
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.remove();
                    }, 500);
                });
            }, 5000);
        });

        // Debug: Monitor for any content hiding
        document.addEventListener('DOMContentLoaded', function() {
            // Test: Ensure all content elements are visible
            setTimeout(function() {
                const contentElements = document.querySelectorAll('.fade-in, .bounce-in');
                contentElements.forEach(function(element) {
                    if (element.style.opacity === '0' || element.style.visibility === 'hidden') {
                        console.log('Content element was hidden, forcing visibility:', element);
                        element.style.opacity = '1';
                        element.style.visibility = 'visible';
                        element.style.display = 'block';
                    }
                });
            }, 1000);
            
            // Monitor for any elements that might be getting hidden
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && 
                        (mutation.attributeName === 'style' || mutation.attributeName === 'class')) {
                        
                        const target = mutation.target;
                        if (target.classList.contains('fade-in') || target.classList.contains('bounce-in')) {
                            // If content elements are being hidden, force them to be visible
                            if (target.style.opacity === '0' || target.style.visibility === 'hidden') {
                                console.log('Content element was hidden, forcing visibility:', target);
                                target.style.opacity = '1';
                                target.style.visibility = 'visible';
                                target.style.display = 'block';
                            }
                        }
                    }
                });
            });
            
            // Observe all content elements
            const contentElements = document.querySelectorAll('.fade-in, .bounce-in');
            contentElements.forEach(function(element) {
                observer.observe(element, {
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });
            });
        });

        // Initialize tooltips and other UI components
        document.addEventListener('alpine:init', () => {
            Alpine.data('tooltip', () => ({
                show: false,
                toggle() {
                    this.show = !this.show;
                }
            }));
        });
    </script>

    @stack('scripts')
</body>
</html>
