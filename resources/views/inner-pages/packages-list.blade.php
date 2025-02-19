@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-3xl font-bold mb-8">Top Trading Packages</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Elite Trader Pro Package -->
            @foreach($topPackages as $package)
                <div class="bg-dark rounded-lg border border-gray-800 p-6 hover:border-primary transition-colors">
                    <!-- Trader Profile -->
                    <div class="flex items-center mb-4">
                       <a href="{{ route('trader.dashboard', ['username' => $package->user->username]) }}">
                       <img src="{{ $package->userProfilelink->profile_picture
                ? asset('storage/' . $package->userProfilelink->profile_picture)
                : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format' }}"
                            alt="{{ $package->userProfilelink->user->name ?? 'Trader' }}" class="w-12 h-12 rounded-full mr-4" /></a>
                        <div>
                            <h3 class="text-xl font-bold">{{ $package->name }}</h3>
                            <div class="flex items-center">
                                <span class="text-gray-400 mr-2"><a href="{{ route('trader.dashboard', ['username' => $package->user->username]) }}">{{ $package->user->username }}</a></span>
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

@endsection