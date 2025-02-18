@extends('layouts.app')
<style>
    /* Hide the dropdowns when not in use */
    #timeframeDropdown,
    #dateDropdown {
        display: none;
    }

    /* Style the selected elements */
    .selected {
        font-weight: bold;
    }
</style>
@section('content')
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-gray-900 to-gray-800 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-16">
            <div class="flex flex-col md:flex-row items-center gap-8">
                <div class="relative">
                    <img src="{{ $profile_picture ?? 'https://via.placeholder.com/150' }}" alt="{{ $name }}"
                        class="w-32 h-32 rounded-full border-4 border-yellow-500" />
                    <span
                        class="absolute bottom-0 right-0 w-4 h-4 rounded-full {{ $profile->is_online ? 'bg-green-500' : 'bg-gray-500' }} border-2 border-gray-900"></span>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold">{{ $name }}</h1>
                        <span class="bg-yellow-500 text-gray-900 px-2 py-1 rounded text-sm font-semibold">
                            {{ $profile->trader == 1 ? 'Trader' : 'User' }}

                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-gray-400 mb-4">
                        <div class="flex items-center">
                            <i data-lucide="globe" class="w-4 h-4 mr-1"></i>
                            {{ $profile->location ?? 'Unknown' }}
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                            Member since {{ $profile->created_at->format('M d, Y') }}
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="star" class="w-4 h-4 mr-1 text-yellow-500"></i>
                            {{ $profile->rating ?? '0.00' }} Rating
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="clock" class="w-4 h-4 mr-1"></i>
                            Response time: {{ $profile->average_response_time ?? 'N/A' }}
                        </div>
                    </div>

                    <p class="text-gray-400 mb-6">{{ $profile->short_info ?? 'No bio available' }}</p>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        @foreach ([['icon' => 'activity', 'label' => 'Total Signals', 'value' => $totalSignals['total_signals'] ?? 0, 'sub' => ($totalSignals['active_signals'] ?? 0) . ' Active'], ['icon' => 'trending-up', 'label' => 'Success Rate', 'value' => ($successRate['success_rate'] ?? 0) . '%', 'sub' => 'Win Percentage'], ['icon' => 'package', 'label' => 'Packages', 'value' => $profile->packages->count() ?? 0, 'sub' => 'Available Plans'], ['icon' => 'users', 'label' => 'Followers', 'value' => $signalFollowers ?? 0, 'sub' => 'Active Subscribers']] as $stat)
                            <div class="bg-gray-800 p-4 rounded-lg">
                                <div class="flex items-center text-gray-400 mb-1">
                                    <i data-lucide="{{ $stat['icon'] }}" class="w-4 h-4 mr-2"></i>
                                    {{ $stat['label'] }}
                                </div>
                                <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
                                <div class="text-sm text-gray-400">{{ $stat['sub'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ([['icon' => 'dollar-sign', 'label' => 'Monthly Earnings', 'value' => $earnings['month'] ?? 0], ['icon' => 'award', 'label' => 'Yearly Earnings', 'value' => $earnings['year'] ?? 0], ['icon' => 'zap', 'label' => 'Overall Earnings', 'value' => $earnings['overall'] ?? 0]] as $earning)
                            <div class="bg-gray-800 p-4 rounded-lg">
                                <div class="flex items-center text-gray-400 mb-1">
                                    <i data-lucide="{{ $earning['icon'] }}" class="w-4 h-4 mr-2"></i>
                                    {{ $earning['label'] }}
                                </div>
                                <div class="text-xl font-bold">${{ number_format($earning['value'], 2) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Recent Signals Section -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Recent Signals</h2>
            <button class="flex items-center text-yellow-500 hover:text-yellow-400">
                View All <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Signal Card 1 -->
            @foreach($topSignals as $signal)
                <div class="bg-dark rounded-lg border border-gray-800 p-6 hover:border-primary transition-colors">
                    <!-- Trader & Package Info -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format"
                                alt="John Smith" class="w-8 h-8 rounded-full mr-2" />
                            <div>
                                <h3 class="font-bold text-sm">{{ $signal->package->user->username }}</h3>
                                <p class="text-xs text-gray-400">{{ $signal->package->userProfilelink->short_info }}</p>
                            </div>
                        </div>
                        <!-- <button
                                                                                                                                                                        class="flex items-center gap-1 px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded text-xs font-medium transition-colors duration-300">

                                                                                                                                                                        <span>Follow</span>
                                                                                                                                                                    </button> -->

                        <button
                            onclick="openPositionCalculator({{ $signal->entry_price }}, {{ $signal->stop_loss }}, {{ $signal->take_profit }}, {{ $signal->id }}, {{ Auth::id() }})"
                            class="flex items-center gap-1 px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded text-xs font-medium transition-colors duration-300">

                            <span>Follow Trade</span>
                        </button>
                    </div>

                    <!-- Trade Details -->
                    <div class="space-y-3">
                        <!-- Basic Info -->
                        <div class="flex justify-between items-center">
                            <span class="text-yellow-500 font-semibold text-sm">{{ $signal->marketPair->symbol }}</span>
                            <span class="bg-green-500 text-xs px-2 py-0.5 rounded">{{ $signal->tradeType->name }}</span>
                        </div>

                        <!-- Entry/TP/SL Grid -->
                        <div class="grid grid-cols-3 gap-2 bg-gray-700 p-2 rounded-lg text-sm">
                            <div>
                                <div class="text-xs text-gray-400">Entry</div>
                                <div class="font-semibold">
                                    {{ number_format($signal->entry_price, 2) }}

                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">TP</div>
                                <div class="font-semibold text-green-500">
                                    {{ number_format($signal->take_profit, 2) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">SL</div>
                                <div class="font-semibold text-red-500">{{ number_format($signal->stop_loss, 2) }}</div>
                            </div>
                        </div>

                        <!-- Trade Images -->
                        <div class="grid grid-cols-2 gap-2">
                            @forelse ($signal->images as $image)
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="Trade Chart"
                                    class="rounded-lg w-full h-20 object-cover" />
                            @empty

                            @endforelse
                        </div>


                        <!-- Additional Details -->
                        <div class="grid grid-cols-4 gap-2 text-xs">
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">TF</div>
                                <div class="font-semibold">{{ $signal->time_frame }}</div>
                            </div>
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">RRR</div>
                                <div class="font-semibold">{{ number_format($signal->rrr, 2) }}</div>
                            </div>
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">Valid</div>
                                <div class="font-semibold">{{ $signal->validity }}</div>
                            </div>
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">Profit</div>
                                <div class="font-semibold text-green-500">{{ number_format($signal->profit_loss, 2) }}</div>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>Posted:
                                {{ \Carbon\Carbon::parse($signal->created_at)->utc()->format('Y-m-d H:i:s') }}</span>
                            <span>{{ \Carbon\Carbon::parse($signal->created_at)->utc()->format('H:i') }} UTC</span>
                        </div>


                        <!-- Notes -->
                        <div class="bg-gray-700 p-2 rounded-lg">
                            <div class="text-xs text-gray-400">Notes</div>
                            <p class="text-xs">{{ $signal->notes }}</p>
                        </div>
                    </div>

                </div>
            @endforeach

            <!-- Additional signal cards would go here -->
        </div>
    </div>

    <!-- Top Performer Section -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Top Performing Signals</h2>
            <button class="flex items-center text-yellow-500 hover:text-yellow-400">
                View All <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($topPerformingSignals as $signal)
                <div class="bg-dark rounded-lg border border-gray-800 p-6 hover:border-primary transition-colors">
                    <!-- Trader & Package Info -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format"
                                alt="John Smith" class="w-8 h-8 rounded-full mr-2" />
                            <div>
                                <h3 class="font-bold text-sm">{{ $signal->package->user->username }}</h3>
                                <p class="text-xs text-gray-400">{{ $signal->package->userProfilelink->short_info }}</p>
                            </div>
                        </div>
                        <!-- <button
                                                                                                                                                                        class="flex items-center gap-1 px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded text-xs font-medium transition-colors duration-300">

                                                                                                                                                                        <span>Follow</span>
                                                                                                                                                                    </button> -->

                        <button
                            onclick="openPositionCalculator({{ $signal->entry_price }}, {{ $signal->stop_loss }}, {{ $signal->take_profit }}, {{ $signal->id }}, {{ Auth::id() }})"
                            class="flex items-center gap-1 px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded text-xs font-medium transition-colors duration-300">

                            <span>Follow Trade</span>
                        </button>
                    </div>

                    <!-- Trade Details -->
                    <div class="space-y-3">
                        <!-- Basic Info -->
                        <div class="flex justify-between items-center">
                            <span class="text-yellow-500 font-semibold text-sm">{{ $signal->marketPair->symbol }}</span>
                            <span class="bg-green-500 text-xs px-2 py-0.5 rounded">{{ $signal->tradeType->name }}</span>
                        </div>

                        <!-- Entry/TP/SL Grid -->
                        <div class="grid grid-cols-3 gap-2 bg-gray-700 p-2 rounded-lg text-sm">
                            <div>
                                <div class="text-xs text-gray-400">Entry</div>
                                <div class="font-semibold">
                                    {{ number_format($signal->entry_price, 2) }}

                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">TP</div>
                                <div class="font-semibold text-green-500">
                                    {{ number_format($signal->take_profit, 2) }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-400">SL</div>
                                <div class="font-semibold text-red-500">{{ number_format($signal->stop_loss, 2) }}</div>
                            </div>
                        </div>

                        <!-- Trade Images -->
                        <div class="grid grid-cols-2 gap-2">
                            @forelse ($signal->images as $image)
                                <img src="{{ asset('storage/' . $image->image_path) }}" alt="Trade Chart"
                                    class="rounded-lg w-full h-20 object-cover" />
                            @empty

                            @endforelse
                        </div>


                        <!-- Additional Details -->
                        <div class="grid grid-cols-4 gap-2 text-xs">
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">TF</div>
                                <div class="font-semibold">{{ $signal->time_frame }}</div>
                            </div>
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">RRR</div>
                                <div class="font-semibold">{{ number_format($signal->rrr, 2) }}</div>
                            </div>
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">Valid</div>
                                <div class="font-semibold">{{ $signal->validity }}</div>
                            </div>
                            <div class="bg-gray-700 p-1.5 rounded">
                                <div class="text-gray-400">Profit</div>
                                <div class="font-semibold text-green-500">{{ number_format($signal->profit_loss, 2) }}</div>
                            </div>
                        </div>

                        <!-- Date & Time -->
                        <div class="flex justify-between text-xs text-gray-400">
                            <span>Posted:
                                {{ \Carbon\Carbon::parse($signal->created_at)->utc()->format('Y-m-d H:i:s') }}</span>
                            <span>{{ \Carbon\Carbon::parse($signal->created_at)->utc()->format('H:i') }} UTC</span>
                        </div>


                        <!-- Notes -->
                        <div class="bg-gray-700 p-2 rounded-lg">
                            <div class="text-xs text-gray-400">Notes</div>
                            <p class="text-xs">{{ $signal->notes }}</p>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Packages Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Top Trading Packages</h2>
                <a href="{{ route('packages.list') }}"
                    class="flex items-center px-6 py-2 bg-primary text-dark py-2 rounded-lg hover:bg-yellow-400 font-semibold transition-colors duration-300">
                    <span class="mr-2">View All Packages</span>
                    <i class="lucide-chevron-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($topPackages as $package)
                        <div class="bg-dark rounded-lg border border-gray-800 p-6 hover:border-primary transition-colors">
                            <!-- Trader Profile -->
                            <div class="flex items-center mb-4">
                                <img src="{{ $package->userProfilelink->profile_picture
                    ? asset('storage/' . $package->userProfilelink->profile_picture)
                    : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format' }}"
                                    alt="{{ $package->userProfilelink->user->name ?? 'Trader' }}"
                                    class="w-12 h-12 rounded-full mr-4" />
                                <div>
                                    <h3 class="text-xl font-bold">{{ $package->name }}</h3>
                                    <div class="flex items-center">
                                        <span class="text-gray-400 mr-2">{{ $package->user->username }}</span>
                                        <div class="flex items-center text-yellow-500">
                                            <i class="lucide-star mr-1"></i>
                                            @if ($package->userProfilelink->rating > 0)
                                                <span>{{ number_format($package->userProfilelink->rating, 1) }}</span>
                                            @else
                                                <span class="text-gray-400 text-sm"></span>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <p class="text-gray-400 mb-6">{{ $package->description }}</p>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-700 p-3 rounded-lg">
                                    <div class="flex items-center text-sm text-gray-400 mb-1">
                                        <i class="lucide-activity mr-1"></i>
                                        Signals
                                    </div>
                                    <div class="text-lg font-bold">{{ number_format($package->user->trades->count()) }}</div>
                                </div>
                                <div class="bg-gray-700 p-3 rounded-lg">
                                    <div class="flex items-center text-sm text-gray-400 mb-1">
                                        <i class="lucide-trending-up mr-1"></i>
                                        Win Rate
                                    </div>
                                    <div class="text-lg font-bold">{{ number_format($package->win_percentage, 2) }}%
                                    </div>
                                </div>
                                <div class="bg-gray-700 p-3 rounded-lg">
                                    <div class="flex items-center text-sm text-gray-400 mb-1">
                                        <i class="lucide-bar-chart-3 mr-1"></i>
                                        RRR
                                    </div>
                                    <div class="text-lg font-bold">{{ number_format($package->risk_reward_ratio, 2) }}

                                    </div>
                                </div>
                                <div class="bg-gray-700 p-3 rounded-lg">
                                    <div class="flex items-center text-sm text-gray-400 mb-1">
                                        <i class="lucide-timer mr-1"></i>
                                        RRR Achieved
                                    </div>
                                    <div class="text-lg font-bold">{{ number_format($package->achieved_rrr, 2) }}</div>
                                </div>
                            </div>

                            <!-- Active Hours -->
                            <!-- <div class="text-sm text-gray-400 mb-6">
                                                                                                                                                                                                                                                                                                    <span class="font-semibold">Active Hours:</span> 8:00 AM - 4:00 PM EST
                                                                                                                                                                                                                                                                                                  </div> -->

                            <!-- Price and Subscribers -->
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xl font-bold text-yellow-500">
                                    ${{ number_format($package->price, 2) }}
                                    <span class="text-sm text-gray-400 font-medium">/ {{ ucfirst($package->package_type) }}</span>
                                </span>

                                <div class="flex items-center text-gray-400">
                                    <i class="lucide-users mr-1"></i>
                                    <span>{{ number_format($package->activeOrders->count()) }} subscribers</span>
                                </div>
                            </div>

                            <button
                                class="w-full bg-yellow-500 text-gray-900 py-3 rounded-lg hover:bg-yellow-400 font-semibold transition-colors duration-300">
                                Subscribe Now
                            </button>
                        </div>
                @endforeach
            </div>
        </div>

        <!-- Performance Report Section -->
        <div class="max-w-7xl mx-auto px-4 py-12 border-t border-gray-800">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold">Performance Report</h2>
                <div class="flex gap-4">
                    <div class="relative">
                        <button onclick="toggleDropdown('fromDateDropdown')"
                            class="bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 flex items-center justify-between min-w-[140px]">
                            <span id="selectedFromDate">From Date</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                        </button>
                        <div id="fromDateDropdown"
                            class="absolute hidden mt-2 w-full bg-gray-800 border border-gray-700 rounded-lg shadow-lg z-10">
                            <div class="p-2">
                                <input type="date" id="fromDate"
                                    class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <button onclick="toggleDropdown('toDateDropdown')"
                            class="bg-gray-800 text-white px-4 py-2 rounded-lg border border-gray-700 flex items-center justify-between min-w-[140px]">
                            <span id="selectedToDate">To Date</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                        </button>
                        <div id="toDateDropdown"
                            class="absolute hidden mt-2 w-full bg-gray-800 border border-gray-700 rounded-lg shadow-lg z-10">
                            <div class="p-2">
                                <input type="date" id="toDate"
                                    class="w-full px-4 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                            </div>
                        </div>
                    </div>
                    <!-- Apply Filter Button -->
                    <button onclick="applyFilter()"
                        class="bg-yellow-500 text-gray-900 px-4 py-2 rounded-lg font-semibold hover:bg-yellow-400 transition-colors flex items-center">
                        <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                        Apply
                    </button>
                </div>
            </div>


            <!-- Performance Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Win Rate</h3>
                        <i data-lucide="target" class="w-5 h-5 text-yellow-500"></i>
                    </div>
                    <div class="text-3xl font-bold mb-2" id="avgWinRate">85%</div>
                    <div class="text-sm text-gray-400" id="winStats">34 out of 40 trades</div>
                </div>

                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Average RRR</h3>
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-yellow-500"></i>
                    </div>
                    <div class="text-3xl font-bold mb-2" id="avgrrr">1:2.5</div>
                    <div class="text-sm text-gray-400">Risk-Reward Ratio</div>
                </div>

                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Profit Factor</h3>
                        <i data-lucide="trending-up" class="w-5 h-5 text-yellow-500"></i>
                    </div>
                    <div class="text-3xl font-bold mb-2">2.8</div>
                    <div class="text-sm text-gray-400">Gross Profit/Gross Loss</div>
                </div>

                <div class="bg-gray-800 p-6 rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Total Trades</h3>
                        <i data-lucide="activity" class="w-5 h-5 text-yellow-500"></i>
                    </div>
                    <div class="text-3xl font-bold mb-2" id="totaltradespackage">40</div>
                    <!-- <div class="text-sm text-gray-400">This month</div> -->
                </div>
            </div>

            <!-- Monthly Performance Certificate -->
            <div class="bg-gray-800 rounded-lg p-8">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold mb-2">Monthly Performance Certificate</h3>
                    <p class="text-gray-400 fromtodate">February 2025</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Trade Statistics -->
                    <div class="space-y-6">
                        <div class="bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-4">Trade Statistics</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Total Trades</span>
                                    <span class="font-semibold" id="totaltradespackagestats">40</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Winning Trades</span>
                                    <span class="font-semibold text-green-500" id="winningtrades">34</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Losing Trades</span>
                                    <span class="font-semibold text-red-500" id="losingtrades">6</span>
                                </div>
                                <!-- <div class="flex justify-between">
                                    <span class="text-gray-400">Average Win</span>
                                    <span class="font-semibold text-green-500" id="avg win">$450</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Average Loss</span>
                                    <span class="font-semibold text-red-500">$180</span>
                                </div> -->
                            </div>
                        </div>

                        <!-- <div class="bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-4">Risk Management</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Average RRR</span>
                                    <span class="font-semibold">1:2.5</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Max Drawdown</span>
                                    <span class="font-semibold">8.5%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Profit Factor</span>
                                    <span class="font-semibold">2.8</span>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <!-- Performance Metrics -->
                    <div class="space-y-6">
                        <!-- <div class="bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-4">Performance Metrics</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Net Profit</span>
                                    <span class="font-semibold text-green-500">$12,450</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Win Rate</span>
                                    <span class="font-semibold">85%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Average Trade Duration</span>
                                    <span class="font-semibold">2.5 days</span>
                                </div>
                            </div>
                        </div> -->

                        <div class="bg-gray-700 p-4 rounded-lg">
                            <h4 class="text-lg font-semibold mb-4">Market Analysis</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Most Traded Pair</span>
                                    <span class="font-semibold" id="mosttradedpair">BTC/USDT</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Best Performing Pair</span>
                                    <span class="font-semibold" id="bestperformingpair">ETH/USDT</span>
                                </div>
                                <!-- <div class="flex justify-between">
                                    <span class="text-gray-400">Average Position Size</span>
                                    <span class="font-semibold">$5,000</span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Initialize Lucide Icons -->
    <script src="https://unpkg.com/lucide"></script>
    <script>
        lucide.createIcons();


    </script>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Include Flatpickr CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Function to toggle the visibility of the dropdown
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
        }
        // Function to set the selected timeframe
        function selectTimeframe(timeframe) {
            document.getElementById('selectedTimeframe').textContent = timeframe;
            toggleDropdown('timeframeDropdown'); // Close dropdown after selection
        }
        // Select date and update the display text
        function selectDate(date) {
            document.getElementById('selectedDate').textContent = date;
            toggleDropdown('dateDropdown'); // Close dropdown after selection
        }

        // Change year (previous/next)
        function changeYear(direction) {
            const yearDisplay = document.getElementById('yearDisplay');
            let currentYear = parseInt(yearDisplay.textContent, 10);
            currentYear += direction; // Increase or decrease the year
            yearDisplay.textContent = currentYear;
        }

        function applyFilter() {
            // Get the selected 'From Date' and 'To Date'
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            console.log(`From Date: ${fromDate}, To Date: ${toDate}`);

            console.log(`From Date: ${fromDate}, To Date: ${toDate}`);

            // Format the dates to display the month and year
            const fromDateObj = new Date(fromDate);
            const toDateObj = new Date(toDate);

            const options = { year: 'numeric', month: 'long' };
            const fromDateFormatted = fromDateObj.toLocaleDateString('en-US', options);
            const toDateFormatted = toDateObj.toLocaleDateString('en-US', options);

            // Update the text content of the <p> element with the formatted date range
            const dateRangeElement = document.querySelector('.fromtodate');
            dateRangeElement.textContent = `${fromDateFormatted} - ${toDateFormatted}`;

            // Send a GET request with query parameters
            fetch(`/fetch-top-packages?from_date=${encodeURIComponent(fromDate)}&to_date=${encodeURIComponent(toDate)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json', // Add proper headers
                },
            })
                .then(response => response.json())  // Parse the JSON response
                .then(data => {
                    console.log('Filtered Data:', data);
                    // Update the UI with the filtered data and average win rate
                    displayPackages(data);
                })
                .catch(error => {
                    console.error('Error fetching top packages:', error);
                });
        }

        function displayPackages(data) {
            // alert(data.average_win_rate);
            // Check if 'data' has the 'average_win_rate' property
            if (data && data.average_win_rate !== undefined) {
                //  alert('hello');
                const avgWinRateElement = document.getElementById('avgWinRate');
                const winStatsElement = document.getElementById('winStats');
                const avgrrr = document.getElementById('avgrrr');
                const totaltradespackage = document.getElementById('totaltradespackage');
                const totaltradespackagestats = document.getElementById('totaltradespackagestats');
                const winningtrades = document.getElementById('winningtrades');
                const losingtrades = document.getElementById('losingtrades');
                const mosttradedpair = document.getElementById('mosttradedpair');
                const bestperformingpair = document.getElementById('bestperformingpair');
                

                
                // Update the win rate display
                avgWinRateElement.textContent = `${data.average_win_rate}%`;
                avgrrr.textContent = `${data.average_rrr}`;
                totaltradespackage.textContent = `${data.total_trades}`;
                totaltradespackagestats.textContent = `${data.total_trades}`;
                winningtrades.textContent = `${data.win_count}`;
                losingtrades.textContent = `${data.loss_count}`;
                mosttradedpair.textContent = `${data.most_traded_pair}`;
                bestperformingpair.textContent = `${data.best_performing_pair}`;

                // Calculate total trades and successful trades based on win percentage (example logic)
                const totalTrades = data.total_trades;
                const successfulTrades = data.packages.filter(package => package.win_percentage > 50).length;

                // Update stats
                winStatsElement.textContent = `${successfulTrades} out of ${totalTrades} trades`;
            } else {
                console.error('Average win rate not found in the response data');
            }
        }


        // Initialize Flatpickr for date picker
        flatpickr("#selectedDate", {
            dateFormat: "F Y",  // Month Year format (e.g., February 2025)
            onChange: function (selectedDates, dateStr, instance) {
                document.getElementById('selectedDate').textContent = dateStr;
                toggleDropdown('dateDropdown');
            }
        });
    </script>


@endsection