@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div>
        <div class="mx-auto max-w-7xl">
            <div class="px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Dashboard</h1>
                <p class="mt-2 text-sm text-gray-700">Welcome back! Here's what's happening with your insurance business today.</p>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Agents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Agents</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($metrics['total_agents']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-green-700">{{ $metrics['active_agents'] }}</span>
                    <span class="text-gray-500">active</span>
                </div>
            </div>
        </div>

        <!-- Total Members -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-green-600 to-green-700 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3.375 3.375 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Members</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($metrics['total_members']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-green-700">{{ $metrics['active_members'] }}</span>
                    <span class="text-gray-500">active</span>
                </div>
            </div>
        </div>

        <!-- Monthly Commissions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-yellow-600 to-yellow-700 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Monthly Commissions</dt>
                            <dd class="text-lg font-medium text-gray-900">RM {{ number_format($metrics['monthly_commissions'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-blue-700">{{ $metrics['total_products'] }}</span>
                    <span class="text-gray-500">products</span>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg bounce-in" style="animation-delay: 0.4s;">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-gradient-to-r from-purple-600 to-purple-700 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Monthly Revenue</dt>
                            <dd class="text-lg font-medium text-gray-900">RM {{ number_format($metrics['total_revenue'], 2) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-orange-700">{{ $metrics['pending_payments'] }}</span>
                    <span class="text-gray-500">pending</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Commission Trends Chart -->
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Commission Trends</h3>
                <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
            </div>
            <div class="h-72">
                <canvas id="commissionChart"></canvas>
            </div>
        </div>

        <!-- Revenue Analytics Chart -->
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Revenue Analytics</h3>
                <div class="w-3 h-3 bg-gradient-to-r from-green-500 to-green-600 rounded-full animate-pulse"></div>
            </div>
            <div class="h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Member Growth Chart -->
        <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Member Growth</h3>
                <div class="w-3 h-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full animate-pulse"></div>
            </div>
            <div class="h-72">
                <canvas id="memberGrowthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Performing Agents -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Top Performing Agents</h3>
        </div>
        <div class="overflow-hidden">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($topAgents as $index => $agent)
                <li class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">{{ $index + 1 }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $agent->name }}</div>
                                <div class="text-sm text-gray-500">{{ $agent->agent_code }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">{{ $agent->total_members }} members</div>
                            <div class="text-sm text-gray-500">RM {{ number_format($agent->total_commission ?? 0, 2) }}</div>
                        </div>
                    </div>
                </li>
                @empty
                <li class="px-6 py-4 text-center text-gray-500">
                    No agents found
                </li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Activities</h3>
        </div>
        <div class="overflow-hidden">
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($recentActivities as $activity)
                <li class="px-6 py-4 hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                @if($activity['icon'] === 'user-plus')
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                @elseif($activity['icon'] === 'dollar-sign')
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                @elseif($activity['icon'] === 'credit-card')
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">{{ $activity['message'] }}</p>
                            <p class="text-sm text-gray-500">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                </li>
                @empty
                <li class="px-6 py-4 text-center text-gray-500">
                    No recent activities
                </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Chart.js configuration for modern animations
Chart.defaults.animation = {
    duration: 2000,
    easing: 'easeInOutQuart'
};

Chart.defaults.plugins.legend = {
    display: false
};

// Commission Trends Chart - Modern Line Chart
const commissionCtx = document.getElementById('commissionChart').getContext('2d');
new Chart(commissionCtx, {
    type: 'line',
    data: {
        labels: @json($commissionTrends->pluck('month')),
        datasets: [{
            label: 'Commission Amount (RM)',
            data: @json($commissionTrends->pluck('commission')),
            borderColor: 'rgb(99, 102, 241)',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(99, 102, 241)',
            pointBorderColor: '#ffffff',
            pointBorderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: 'rgb(99, 102, 241)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                borderColor: 'rgb(99, 102, 241)',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Commission: RM ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6b7280',
                    font: {
                        size: 12
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    color: '#6b7280',
                    font: {
                        size: 12
                    },
                    callback: function(value) {
                        return 'RM ' + value.toLocaleString();
                    }
                }
            }
        },
        elements: {
            point: {
                hoverBackgroundColor: 'rgb(99, 102, 241)'
            }
        }
    }
});

// Revenue Analytics Chart - Modern Doughnut Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Pending', 'Failed'],
        datasets: [{
            data: [
                @json($metrics['completed_revenue'] ?? 0),
                @json($metrics['pending_revenue'] ?? 0),
                @json($metrics['failed_revenue'] ?? 0)
            ],
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(239, 68, 68, 0.8)'
            ],
            borderColor: [
                'rgb(34, 197, 94)',
                'rgb(251, 191, 36)',
                'rgb(239, 68, 68)'
            ],
            borderWidth: 2,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': RM ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Member Growth Chart - Modern Bar Chart
const memberGrowthCtx = document.getElementById('memberGrowthChart').getContext('2d');
new Chart(memberGrowthCtx, {
    type: 'bar',
    data: {
        labels: @json($memberGrowth->pluck('date')),
        datasets: [{
            label: 'New Members',
            data: @json($memberGrowth->pluck('count')),
            backgroundColor: [
                'rgba(168, 85, 247, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)',
                'rgba(251, 191, 36, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(99, 102, 241, 0.8)'
            ],
            borderColor: [
                'rgb(168, 85, 247)',
                'rgb(236, 72, 153)',
                'rgb(59, 130, 246)',
                'rgb(34, 197, 94)',
                'rgb(251, 191, 36)',
                'rgb(239, 68, 68)',
                'rgb(99, 102, 241)'
            ],
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#ffffff',
                bodyColor: '#ffffff',
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        return 'New Members: ' + context.parsed.y;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#6b7280',
                    font: {
                        size: 12
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    color: '#6b7280',
                    font: {
                        size: 12
                    },
                    stepSize: 1
                }
            }
        }
    }
});

// Add smooth animations on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Apply animations to chart containers
document.querySelectorAll('.bg-white.shadow-lg').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});
</script>
@endpush
@endsection
