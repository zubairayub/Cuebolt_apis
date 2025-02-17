@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <div class="bg-dark border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">Trading Made <span class="text-primary">Simple</span></h1>
                <p class="text-xl mb-8 text-gray-400">Access top trading signals and packages from expert traders</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('package.addform') }}"
                        class="bg-primary text-dark px-6 py-3 rounded-lg font-semibold hover:bg-yellow-400">
                        Create Package
                    </a>

                    <a href="{{ route('signal.addform') }}"
                        class="bg-transparent border-2 border-primary text-primary px-6 py-3 rounded-lg font-semibold hover:bg-primary hover:text-dark">Add
                        Signal</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Packages Section -->
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
                            alt="{{ $package->userProfilelink->user->name ?? 'Trader' }}" class="w-12 h-12 rounded-full mr-4" />
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

    <!-- Top Traders Section -->
    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Top Traders</h2>
                <a href="{{ route('packages.list') }}"
                    class="flex items-center px-6 py-2 bg-primary text-dark py-2 rounded-lg hover:bg-yellow-400 font-semibold transition-colors duration-300">
                    <span class="mr-2">View All Traders</span>
                    <i class="lucide-chevron-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Trader Card 1 -->

                @foreach($topTraders as $trader)
                    <div class="bg-dark rounded-lg border border-gray-800 p-6 hover:border-primary transition-colors">
                        <img src="{{ $trader->profile_picture }}" alt="{{ $trader->username }}"
                            class="w-24 h-24 rounded-full mx-auto mb-4 border-2 border-primary">
                        <h3 class="text-xl font-bold text-center mb-2">{{ $trader->user->username }}</h3>
                        <div class="flex justify-between text-gray-400 mb-2">
                            <span>Signals:</span>
                            <span>{{ number_format($trader->user->trades->count()) }}</span>

                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Success Rate:</span>
                            <span class="text-primary">{{ $trader->success_rate }}%</span>
                        </div>
                    </div>
                @endforeach


            </div>
        </div>
    </div>

    <!-- Top Signals Section -->

    <!-- Signals Section -->
    <div class="border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Top Signals</h2>
                <a href="{{ route('packages.list') }}"
                    class="flex items-center px-6 py-2 bg-primary text-dark py-2 rounded-lg hover:bg-yellow-400 font-semibold transition-colors duration-300">
                    <span class="mr-2">View All Signals</span>
                    <i class="lucide-chevron-right"></i>
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Signal Card 1 -->
                @foreach($topSignals as $signal)
                    <div class="bg-dark rounded-lg border border-gray-800 p-6 hover:border-primary transition-colors">
                        <!-- Trader & Package Info -->
                        <div class="flex items-center justify-between mb-3">
              <div class="flex items-center">
                <img 
                  src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format"
                  alt="John Smith"
                  class="w-8 h-8 rounded-full mr-2"
                />
                <div>
                  <h3 class="font-bold text-sm">{{ $signal->package->user->username }}</h3>
                  <p class="text-xs text-gray-400">{{ $signal->package->userProfilelink->short_info }}</p>
                </div>
              </div>
              <button class="flex items-center gap-1 px-2 py-1 bg-gray-700 hover:bg-gray-600 rounded text-xs font-medium transition-colors duration-300">
                
                <span>Follow</span>
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

                <!-- Additional signal cards would follow the same pattern -->
            </div>
        </div>

@endsection