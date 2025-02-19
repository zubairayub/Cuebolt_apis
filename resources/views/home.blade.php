@extends('layouts.app')
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 50;
    }

    .modal.show {
        display: flex;
    }

    .toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        transform: translateY(150%);
        transition: transform 0.3s ease-in-out;
    }

    .toast.show {
        transform: translateY(0);
    }
</style>


<div id="positionModal" class="modal items-center justify-center">
    <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Position Calculator</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                <i class="lucide-x">X</i>
            </button>
        </div>

        <div class="space-y-4">
            <!-- Capital & Risk Input -->
            <div class="grid grid-cols-2 gap-4">
                <div style="display:none;">
                    <label class="block text-sm text-gray-400 mb-1">Signal ID</label>
                    <input type="number" id="signal_id" name="signal_id" value="10000"
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white focus:outline-none focus:border-yellow-500">
                </div>
                <div style="display:none;">
                    <label class="block text-sm text-gray-400 mb-1">User ID</label>
                    <input type="number" id="user_id" name="user_id" value="10000"
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white focus:outline-none focus:border-yellow-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Capital (USDT)</label>
                    <input type="number" id="capital" value="10000"
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white focus:outline-none focus:border-yellow-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Risk (%)</label>
                    <input type="number" id="riskPercentage" value="2"
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white focus:outline-none focus:border-yellow-500">
                </div>
            </div>

            <!-- Trade Levels -->
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Entry</label>
                    <input type="number" name="entryPrice" id="entryPrice" readonly
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Stop Loss</label>
                    <input type="number" id="stopLoss" readonly
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Take Profit</label>
                    <input type="number" id="takeProfit" readonly
                        class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-white">
                </div>
            </div>

            <!-- Results -->
            <div class="bg-gray-700 rounded-lg p-4 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-400">Risk Amount</div>
                        <div class="text-lg font-bold text-white-500" id="riskAmount">$200.00</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400">Position Size</div>
                        <div class="text-lg font-bold text-white-500" id="positionSize">0.0047 BTC</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-400">Potential Loss</div>
                        <div class="text-lg font-bold text-red-500" id="potentialLoss">-$200.00</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-400">Potential Profit</div>
                        <div class="text-lg font-bold text-green-500" id="potentialProfit">$500.00</div>
                    </div>
                </div>
            </div>
            <!-- Follow Trade Button -->
            <button id="followTradeBtn" onclick="followTrade()"
                class="w-full bg-yellow-500 text-gray-900 py-3 rounded-lg hover:bg-yellow-400 font-semibold transition-colors duration-300 flex items-center justify-center">
                <i class="lucide-user-plus mr-2"></i>
                Follow Trade
            </button>
            <div class="text-xs text-gray-400">
                By following this trade, orders will be automatically placed with the calculated position size and risk
                management parameters.
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div id="successToast" class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
    <i class="lucide-check-circle mr-2"></i>
    <span>Trade followed successfully! Check your dashboard for updates.</span>
</div>
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

                        <a href="{{ route('trader.dashboard', ['username' => $trader->user->username]) }}">  <img src="{{ $package->userProfilelink->profile_picture
                    ? asset('storage/' . $package->userProfilelink->profile_picture)
                    : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format' }}"
                                alt="{{ $trader->username }}" class="w-24 h-24 rounded-full mx-auto mb-4 border-2 border-primary">
                            <h3 class="text-xl font-bold text-center mb-2">{{ $trader->user->username }}</a></h3>
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
                                <a href="{{ route('trader.dashboard', ['username' => $trader->user->username]) }}"> <img src="{{ $package->userProfilelink->profile_picture
                    ? asset('storage/' . $package->userProfilelink->profile_picture)
                    : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&auto=format' }}"
                                        alt="John Smith" class="w-8 h-8 rounded-full mr-2" />
                                    <div>
                                        <h3 class="font-bold text-sm">{{ $signal->package->user->username }}</h3></a>
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
                                <!-- <div class="grid grid-cols-3 gap-2 bg-gray-700 p-2 rounded-lg text-sm">
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
                                                                        </div> -->
                                <div class="grid grid-cols-3 gap-2 bg-gray-700 p-2 rounded-lg text-sm">
                                    <!-- Entry Price -->
                                    <div>
                                        <div class="text-xs text-gray-400">Entry</div>
                                        <div class="font-semibold">
                                            {{ number_format($signal->entry_price, 2) }}
                                        </div>
                                    </div>

                                    <!-- Take Profit with Percentage Difference & Tooltip -->
                                    <div class="relative group">
                                        <div class="text-xs text-gray-400">TP</div>
                                        <div class="font-semibold text-green-500">
                                            {{ number_format($signal->take_profit, 2) }}
                                            <span class="text-xs text-green-400 block">
                                                (+{{ number_format($signal->percentageDifferencetp, 2) }}%)
                                            </span>
                                        </div>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute left-1/2 transform -translate-x-1/2 bottom-full mb-1 w-max bg-black text-white text-xs rounded-lg py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            TP is {{ number_format($signal->percentageDifferencetp, 2) }}% above Entry Price
                                        </div>
                                    </div>

                                    <!-- Stop Loss with Percentage Difference & Tooltip -->
                                    <div class="relative group">
                                        <div class="text-xs text-gray-400">SL</div>
                                        <div class="font-semibold text-red-500">
                                            {{ number_format($signal->stop_loss, 2) }}
                                            <span class="text-xs text-red-400 block">
                                                ({{ number_format($signal->percentageDifferencesl, 2) }}%)
                                            </span>
                                        </div>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute left-1/2 transform -translate-x-1/2 bottom-full mb-1 w-max bg-black text-white text-xs rounded-lg py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            SL is {{ number_format($signal->percentageDifferencesl, 2) }}% below Entry Price
                                        </div>
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
                                    <div class="bg-gray-700 p-1.5 rounded relative group">
                                        <div class="text-gray-400">Predicted Risk-Reward</div>
                                        <div class="font-semibold text-blue-500">
                                            {{ number_format($signal->rrr, 2) }}
                                        </div>

                                        <!-- Tooltip for explanation -->
                                        <div
                                            class="absolute left-1/2 transform -translate-x-1/2 bottom-full mb-1 w-max bg-black text-white text-xs rounded-lg py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            RRR shows potential reward vs risk. Higher is better!
                                        </div>
                                    </div>
                                    <div class="bg-gray-700 p-1.5 rounded relative group">
                                        <div class="text-gray-400">Achieved RRR</div>
                                        <div class="font-semibold">
                                            {{ $signal->rrr !== null ? number_format($signal->rrr, 2) : 'Trade is Live' }}
                                        </div>
                                        <div
                                            class="absolute left-1/2 transform -translate-x-1/2 bottom-full mb-1 w-max bg-black text-white text-xs rounded-lg py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            {{ $signal->rrr !== null ? 'Achieved RRR represents the actual risk-to-reward ratio after execution.' : 'Trade is still live, RRR will be calculated after completion.' }}
                                        </div>
                                    </div>

                                    <div class="bg-gray-700 p-1.5 rounded">
                                        <div class="text-gray-400">Valid</div>
                                        <div class="font-semibold">{{ $signal->validity }}</div>
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
        <script>
            // Position Calculator Functions
            function openPositionCalculator(entry, sl, tp, signal_id, user_id) {
                document.getElementById('signal_id').value = signal_id;
                document.getElementById('user_id').value = user_id;
                document.getElementById('entryPrice').value = entry;
                document.getElementById('stopLoss').value = sl;
                document.getElementById('takeProfit').value = tp;
                document.getElementById('positionModal').classList.add('show');
                calculatePosition();
            }

            function closeModal() {
                document.getElementById('positionModal').classList.remove('show');
            }

            function calculatePosition() {
                const capital = parseFloat(document.getElementById('capital').value);
                const riskPercentage = parseFloat(document.getElementById('riskPercentage').value);
                const entry = parseFloat(document.getElementById('entryPrice').value);
                const sl = parseFloat(document.getElementById('stopLoss').value);
                const tp = parseFloat(document.getElementById('takeProfit').value);

                // Calculate risk amount
                const riskAmount = capital * (riskPercentage / 100);

                // Calculate position size
                const riskPerCoin = Math.abs(entry - sl);
                const quantity = riskAmount / riskPerCoin;

                // Calculate potential profit/loss
                const potentialLoss = quantity * (sl - entry);
                const potentialProfit = quantity * (tp - entry);

                // Update UI
                document.getElementById('riskAmount').textContent = `$${riskAmount.toFixed(2)}`;
                document.getElementById('positionSize').textContent = `${quantity.toFixed(4)} BTC`;
                document.getElementById('potentialLoss').textContent = `$${Math.abs(potentialLoss).toFixed(2)}`;
                document.getElementById('potentialProfit').textContent = `$${potentialProfit.toFixed(2)}`;
            }

            // Add event listeners for real-time calculations
            document.getElementById('capital').addEventListener('input', calculatePosition);
            document.getElementById('riskPercentage').addEventListener('input', calculatePosition);

            function followTrade() {

                const followBtn = document.getElementById('followTradeBtn'); // Get button element

                // Disable button & show loader
                if (followBtn) {
                    followBtn.disabled = true;
                    followBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Processing...`;
                }
                // Prepare the data to send to the backend
                const data = {
                    signal_id: document.getElementById('signal_id').value, // Replace with the actual signal id
                    user_id: document.getElementById('user_id').value, // Replace with the actual user id
                    current_price: document.getElementById('entryPrice').value, // Replace with the actual current price
                    entry_price: document.getElementById('entryPrice').value, // Replace with the actual entry price
                    take_profit: document.getElementById('takeProfit').value, // Replace with the actual take profit
                    stop_loss: document.getElementById('stopLoss').value, // Replace with the actual stop loss

                };
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfMeta) {
                    console.error('CSRF token meta tag is missing!');
                    return;
                }
                const csrfToken = csrfMeta.getAttribute('content');
                // Submit the data using a POST request
                fetch('/signal/performance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // Ensure CSRF protection
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(data => {
                        // Close the modal (this part remains the same)
                        closeModal();

                        // Show success toast
                        const toast = document.getElementById('successToast');
                        toast.classList.add('show');

                        // Hide toast after 3 seconds
                        setTimeout(() => {
                            toast.classList.remove('show');
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error submitting trade:', error);
                        // Optionally, you can show an error toast here if desired
                    })
                    .finally(() => {
                        // Re-enable button & reset text after response
                        if (followBtn) {
                            followBtn.disabled = false;
                            followBtn.innerHTML = "Follow Trade";
                        }
                    });
            }

            // Add event listeners for real-time calculations
            document.getElementById('capital').addEventListener('input', calculatePosition);
            document.getElementById('riskPercentage').addEventListener('input', calculatePosition);
        </script>
@endsection