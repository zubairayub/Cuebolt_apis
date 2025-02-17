<body class="bg-secondary text-white"> 
 <!-- Navbar -->
 <nav class="bg-dark border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="flex items-center space-x-2">
                        <span class="text-2xl font-bold text-primary">CueBolt</span>
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-gray-300 hover:text-primary">Home</a>
                    <a href="{{ route('packages.list') }}" class="text-gray-300 hover:text-primary">Packages</a>
                    <a href="#" class="text-gray-300 hover:text-primary">Signals</a>
                    <a href="#" class="text-gray-300 hover:text-primary">Traders</a>
                    @if(Auth::check())
    <a href="{{ route('logout') }}"
       class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700 font-semibold"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>

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
    </nav>