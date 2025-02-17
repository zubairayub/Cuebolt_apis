@extends('layouts.app')

@section('content')




    <!-- Add Signal Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-dark rounded-lg border border-gray-800 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Add New Signal</h1>
                <p class="text-gray-400">Create a new trading signal for your package</p>
            </div>

            <form id="signalForm" class="space-y-6" method="POST" action="{{ route('trades.store') }}">
            @csrf
            <!-- Basic Information -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-primary">Signal Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="package_id" class="block text-sm font-medium text-gray-300 mb-2">Package</label>
                            <select id="package_id" name="package_id" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                @foreach($userPackages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('package_id')" />
                        </div>

                        <div>
                            <label for="trade_type_id" class="block text-sm font-medium text-gray-300 mb-2">Trade
                                Type</label>
                            <select id="trade_type_id" name="trade_type_id" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">

                                @foreach ($tradetype as $trade)
                                    <option value="{{ $trade->id }}" {{ old('id') == $trade->id ? 'selected' : '' }}>
                                        {{ $trade->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('trade_type_id')" />
                        </div>

                        <div>
                            <label for="trade_name" class="block text-sm font-medium text-gray-300 mb-2">Trade Name</label>
                            <input type="text" id="trade_name" name="trade_name" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter trade name">
                                <x-input-error :messages="$errors->get('trade_name')" />
                        </div>

                        <div style = "display: none">
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">status</label>
                            <input type="text" id="status" name="status" value="1" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter trade name">
                                <x-input-error :messages="$errors->get('status')" />
                        </div>

                        <div>
                            <label for="signal_name" class="block text-sm font-medium text-gray-300 mb-2">Signal
                                Name</label>
                            <input type="text" id="signal_name" name="signal_name" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="e.g., BTC/USD">
                                <x-input-error :messages="$errors->get('trade_name')" />
                        </div>

                        <div>
                            <label for="market_pair_id" class="block text-sm font-medium text-gray-300 mb-2">Market
                                Pair</label>
                            <select id="market_pair_id" name="market_pair_id" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                @foreach ($marketpair as $pair)
                                    <option value="{{ $pair->id }}" {{ old('id') == $pair->id ? 'selected' : '' }}>
                                        {{ $pair->symbol }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('market_pair_id')" />
                        </div>

                        <div>
                            <label for="trade_date" class="block text-sm font-medium text-gray-300 mb-2">Trade Date</label>
                            <input type="date" id="trade_date" name="trade_date" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                <x-input-error :messages="$errors->get('trade_date')" />
                            </div>
                    </div>
                </div>

                <!-- Price Information -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-primary">Price Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="entry_price" class="block text-sm font-medium text-gray-300 mb-2">Entry
                                Price</label>
                            <input type="number" step="0.01" id="entry_price" name="entry_price" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter entry price">
                                <x-input-error :messages="$errors->get('entry_price')" />
                        </div>

                        <div>
                            <label for="stop_loss" class="block text-sm font-medium text-gray-300 mb-2">Stop Loss</label>
                            <input type="number" step="0.01" id="stop_loss" name="stop_loss" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter stop loss">
                                <x-input-error :messages="$errors->get('stop_loss')" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Take Profit Targets</label>
                            <div class="space-y-4" id="takeProfitContainer">
                                <div class="flex items-center space-x-4">
                                    <input type="number" step="0.01" name="take_profit[]" required
                                        class="flex-1 bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                        placeholder="Enter take profit target">
                                    <button type="button" onclick="addTakeProfit()"
                                        class="px-4 py-2.5 bg-primary text-dark rounded-lg hover:bg-yellow-400">
                                        +
                                    </button>
                                    <x-input-error :messages="$errors->get('take_profit')" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-primary">Additional Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="time_frame" class="block text-sm font-medium text-gray-300 mb-2">Time Frame</label>
                            <select id="time_frame" name="time_frame" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="1m">1 Minute</option>
                                <option value="5m">5 Minutes</option>
                                <option value="15m">15 Minutes</option>
                                <option value="30m">30 Minutes</option>
                                <option value="1h" selected>1 Hour</option>
                                <option value="4h">4 Hours</option>
                                <option value="1d">1 Day</option>
                            </select>
                            <x-input-error :messages="$errors->get('time_frame')" />
                        </div>

                        <div>
                            <label for="validity" class="block text-sm font-medium text-gray-300 mb-2">Validity</label>
                            <input type="text" id="validity" name="validity" required
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="e.g., 30 days">
                                <x-input-error :messages="$errors->get('validity')" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-6">
                    <button type="button" onclick="window.history.back()"
                        class="px-6 py-3 rounded-lg border-2 border-gray-800 text-gray-300 hover:border-gray-700 hover:text-white font-semibold">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-primary text-dark rounded-lg hover:bg-yellow-400 font-semibold">
                        Add Signal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addTakeProfit() {
            const container = document.getElementById('takeProfitContainer');
            const div = document.createElement('div');
            div.className = 'flex items-center space-x-4';
            div.innerHTML = `
                        <input type="number" step="0.01" name="take_profit[]" required
                            class="flex-1 bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Enter take profit target">
                        <button type="button" onclick="this.parentElement.remove()"
                            class="px-4 py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600">
                            -
                        </button>
                    `;
            container.appendChild(div);
        }

    
    </script>



@endsection