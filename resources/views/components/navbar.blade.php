<body class="bg-secondary text-white">
    <!-- Navbar -->
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo and Mobile Menu Button -->
                <div class="flex items-center">
                    <button
                        class="md:hidden p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none"
                        onclick="toggleMobileMenu()">
                        <i data-lucide="menu"></i>
                    </button>
                    <a href="{{ route('home') }}" class="flex items-center space-x-2 ml-2 md:ml-0">
                        <i class="lucide-trending-up text-yellow-500" style="width: 28px; height: 28px;"></i>
                        <span class="text-xl font-bold text-yellow-500">CueBolt</span>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}"
                        class="text-gray-300 hover:text-yellow-500 transition-colors duration-200">Home</a>
                    <a href="{{ route('packages.list') }}"
                        class="text-gray-300 hover:text-yellow-500 transition-colors duration-200">Packages</a>
                    <a href="#" class="text-gray-300 hover:text-yellow-500 transition-colors duration-200">Signals</a>
                    <a href="#" class="text-gray-300 hover:text-yellow-500 transition-colors duration-200">Traders</a>
                    @auth
                    <a href="{{ route('trader.dashboard', ['username' => Auth::user()->username]) }}"
                        class="text-gray-300 hover:text-yellow-500 transition-colors duration-200">Dashboard</a>
    @endauth

                    @if(Auth::check()) <a href="{{ route('logout') }}"
                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-semibold"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> Logout </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('login.user') }}"
                            class="bg-primary text-dark px-4 py-2 rounded-lg hover:bg-yellow-400 font-semibold">
                            Sign In / Up
                        </a>
                    @endif

                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu fixed inset-y-0 left-0 w-64 bg-gray-800 shadow-lg z-50 md:hidden">
            <div class="flex justify-between items-center p-4 border-b border-gray-700">
                <span class="text-lg font-bold text-yellow-500">Menu</span>
                <button class="text-gray-400 hover:text-white" onclick="toggleMobileMenu()">
                    <i class="lucide-x" style="width: 24px; height: 24px;"></i>
                </button>
            </div>
            <div class="py-4 px-2 space-y-1">
                <a href="{{ route('home') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-colors duration-200">
                    Home
                </a>
                <a href="{{ route('packages.list') }}"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-colors duration-200">
                    Packages
                </a>
                <a href="#"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-colors duration-200">
                    Signals
                </a>
                <a href="#"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-colors duration-200">
                    Traders
                </a>
                @auth
                    <a href="{{ route('trader.dashboard', ['username' => Auth::user()->username]) }}"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-700 transition-colors duration-200">
                        Dashboard
                    </a>
                @endauth
                <div class="px-3 pt-4">
                    @if(Auth::check()) <a href="{{ route('logout') }}"
                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-semibold"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> Logout </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('login.user') }}"
                            class="bg-primary text-dark px-4 py-2 rounded-lg hover:bg-yellow-400 font-semibold">
                            Sign In / Up
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"
            onclick="toggleMobileMenu()"></div>
    </nav>