@extends('layouts.app')

@section('content')


    <!-- Create Package Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-dark rounded-lg border border-gray-800 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2">Create New Package</h1>
                <p class="text-gray-400">Create your trading package and start selling your signals</p>
            </div>

            <form class="space-y-6" method="POST" action="{{ route('packages.store') }}">
                @csrf

                <!-- Basic Information -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-primary">Basic Information</h2>
                    @if (session('success'))
                        <div class="bg-green-600 text-white px-4 py-3 rounded-lg text-sm font-medium mb-4">
                            {{ session('success') }}
                        </div>
                    @elseif (session('error'))
                        <div class="bg-red-600 text-white px-4 py-3 rounded-lg text-sm font-medium mb-4">
                            {{ session('error') }}
                        </div>
                    @endif


                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Package Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}"
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter package name">
                            <x-input-error :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea id="description" name="description" rows="4"
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Describe your package">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" />
                        </div>
                    </div>
                </div>

                <!-- Package Details -->
                <div class="space-y-6">
                    <h2 class="text-xl font-semibold text-primary">Package Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="package_type" class="block text-sm font-medium text-gray-300 mb-2">Package
                                Type</label>
                            <select id="package_type" name="package_type"
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                                <option value="daily" {{ old('package_type') == 'daily' ? 'selected' : '' }}>Daily
                                </option>
                                <option value="weekly" {{ old('package_type') == 'weekly' ? 'selected' : '' }}>Weekly
                                </option>
                                <option value="monthly" {{ old('package_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('package_type') == 'yearly' ? 'selected' : '' }}>Yearly</option>

                            </select>
                            <x-input-error :messages="$errors->get('package_type')" />
                        </div>

                        <div>
                            <label for="signals_count" class="block text-sm font-medium text-gray-300 mb-2">Number of
                                Signals</label>
                            <input type="number" id="signals_count" name="signals_count" value="{{ old('signals_count') }}"
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter number of signals">
                            <x-input-error :messages="$errors->get('signals_count')" />
                        </div>

                        
                <div>
                            <label for="risk_reward_ratio" class="block text-sm font-medium text-gray-300 mb-2">Risk/Reward Ratio</label>
                            <input type="number" step="0.1" id="risk_reward_ratio" name="risk_reward_ratio" 
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter risk/reward ratio">
                        </div>

                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-300 mb-2">Price ($)</label>
                            <input type="number" id="price" name="price" 
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter package price">
                        </div>

                <!-- Dynamic Market Type -->
                <div>
                    <label for="market_type" class="block text-sm font-medium text-gray-300 mb-2">Market Type</label>
                    <select id="market_type" name="market_type_id"
                        class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        @foreach ($marketTypes as $marketType)
                            <option value="{{ $marketType->id }}" {{ old('market_type_id') == $marketType->id ? 'selected' : '' }}>{{ $marketType->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('market_type_id')" />
                </div>

                <!-- Dynamic Duration -->
                <div>
                    <label for="duration_id" class="block text-sm font-medium text-gray-300 mb-2">Duration</label>
                    <select id="duration_id" name="duration_id"
                        class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary">
                        @foreach ($subscriptionTypes as $subscription)
                            <option value="{{ $subscription->id }}" {{ old('duration_id') == $subscription->id ? 'selected' : '' }}>{{ $subscription->duration_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('duration_id')" />
                </div>

                    </div>
                </div>
 <!-- Challenge Settings -->
 <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-primary">Challenge Settings</h2>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="is_challenge" name="is_challenge" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                    
                    <div class="challenge-fields grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="from_amount" class="block text-sm font-medium text-gray-300 mb-2">Starting Amount ($)</label>
                            <input type="number" id="from_amount" name="from_amount" 
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter starting amount">
                        </div>

                        <div>
                            <label for="to_amount" class="block text-sm font-medium text-gray-300 mb-2">Target Amount ($)</label>
                            <input type="number" id="to_amount" name="to_amount" 
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter target amount">
                        </div>

                        <div>
                            <label for="challenge_days" class="block text-sm font-medium text-gray-300 mb-2">Challenge Duration (Days)</label>
                            <input type="number" id="challenge_days" name="challenge_days" 
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter challenge duration">
                        </div>

                        <div>
                            <label for="achieved_rrr" class="block text-sm font-medium text-gray-300 mb-2">Achieved RRR</label>
                            <input type="number" step="0.1" id="achieved_rrr" name="achieved_rrr" 
                                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-2.5 text-white focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Enter achieved RRR">
                        </div>
                    </div>
                </div>

              

                <div class="flex justify-end space-x-4 pt-6">
                    <a href="{{ route('packages.index') }}"
                        class="px-6 py-3 rounded-lg border-2 border-gray-800 text-gray-300 hover:border-gray-700 hover:text-white font-semibold">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-primary text-dark rounded-lg hover:bg-yellow-400 font-semibold">
                        Create Package
                    </button>
                </div>
            </form>

        </div>
    </div>

    <script>
        // Toggle challenge fields visibility
        const challengeToggle = document.getElementById('is_challenge');
        const challengeFields = document.querySelector('.challenge-fields');

        function toggleChallengeFields() {
            if (challengeToggle.checked) {
                challengeFields.style.display = 'grid';
            } else {
                challengeFields.style.display = 'none';
            }
        }

        // Initial state
        challengeFields.style.display = 'none';
        challengeToggle.addEventListener('change', toggleChallengeFields);
    </script>



@endsection