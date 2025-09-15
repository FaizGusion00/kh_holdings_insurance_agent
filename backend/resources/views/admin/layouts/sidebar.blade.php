<!-- Sidebar component -->
<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4 border-r border-gray-200">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">KH Holdings</h1>
                <p class="text-xs text-gray-500">Insurance Admin</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="{{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Users Management -->
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="{{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Agents
                        </a>
                    </li>

                    <!-- Members Management -->
                    <li>
                        <a href="{{ route('admin.members.index') }}" 
                           class="{{ request()->routeIs('admin.members.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            Members
                        </a>
                    </li>

                    <!-- Commissions Management -->
                    <li>
                        <a href="{{ route('admin.commissions.index') }}" 
                           class="{{ request()->routeIs('admin.commissions.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Commissions
                        </a>
                    </li>

                    <!-- Commission Rules Management -->
                    <li>
                        <a href="{{ route('admin.commission-rules.index') }}" 
                           class="{{ request()->routeIs('admin.commission-rules.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Commission Rules
                        </a>
                    </li>

                    <!-- Wallets Management -->
                    <li>
                        <a href="{{ route('admin.wallets.index') }}" 
                           class="{{ request()->routeIs('admin.wallets.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9v3m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3m18 0V3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 3v3" />
                            </svg>
                            Agent Wallets
                        </a>
                    </li>

                    <!-- Withdrawal Requests Management -->
                    <li>
                        <a href="{{ route('admin.withdrawals.index') }}" 
                           class="{{ request()->routeIs('admin.withdrawals.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Withdrawal Requests
                        </a>
                    </li>

                    <!-- Products Management - Hidden for now -->
                    {{-- <li>
                        <a href="{{ route('admin.products.index') }}" 
                           class="{{ request()->routeIs('admin.products.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            Products
                        </a>
                    </li> --}}

                    <!-- Hospitals Management -->
                    <li>
                        <a href="{{ route('admin.hospitals.index') }}" 
                           class="{{ request()->routeIs('admin.hospitals.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                            Hospitals
                        </a>
                    </li>

                    <!-- Clinics Management -->
                    <li>
                        <a href="{{ route('admin.clinics.index') }}" 
                           class="{{ request()->routeIs('admin.clinics.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0015 0m-15 0a7.5 7.5 0 1115 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077l1.41-.513m14.095-5.13l1.41-.513M5.106 17.785l1.15-.964m11.49-9.642l1.149-.964M7.501 19.795l.75-1.3m7.5-12.99l.75-1.3m-6.063 16.658l.26-1.477m2.605-14.772l.26-1.477m0 17.726l-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205L12 12m6.894 5.785l-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864l-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495" />
                            </svg>
                            Clinics
                        </a>
                    </li>

                    <!-- Payments Management -->
                    <li>
                        <a href="{{ route('admin.payments.index') }}" 
                           class="{{ request()->routeIs('admin.payments.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                            </svg>
                            Payments
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Reports Section -->
            <li>
                <div class="text-xs font-semibold leading-6 text-gray-400 uppercase tracking-wider">Reports</div>
                <ul role="list" class="-mx-2 mt-2 space-y-1">
                    <li>
                        <a href="{{ route('admin.reports.sales') }}" 
                           class="{{ request()->routeIs('admin.reports.sales') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            Sales Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.commissions') }}" 
                           class="{{ request()->routeIs('admin.reports.commissions') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                            </svg>
                            Commission Report
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.reports.members') }}" 
                           class="{{ request()->routeIs('admin.reports.members') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold transition-all duration-200">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            Members Report
                        </a>
                    </li>
                </ul>
            </li>

            <!-- User Profile -->
            <li class="mt-auto">
                @if(auth()->guard('admin')->check())
                <div class="flex items-center gap-x-4 px-6 py-3 text-sm font-semibold leading-6 text-gray-900 bg-gray-50 rounded-lg mx-2">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">{{ substr(auth()->guard('admin')->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate">{{ auth()->guard('admin')->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->guard('admin')->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                        </button>
                    </form>
                </div>
                @endif
            </li>
        </ul>
    </nav>
</div>
