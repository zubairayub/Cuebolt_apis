@extends('layouts.app')

@section('content')

    <!-- Login Form -->
    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-dark rounded-lg border border-gray-800 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-primary">Welcome Back</h1>
                    <p class="text-gray-400 mt-2">Sign in to access your account</p>
                </div>

                <form class="space-y-6" id="loginForm" action="/login" method="POST" >
    <!-- CSRF Token for security -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    @if (session('success'))
    <div class="bg-green-600 text-white px-4 py-3 rounded-lg text-sm font-medium mb-4">
        {{ session('success') }}
    </div>
@elseif (session('error'))
    <div class="bg-red-600 text-white px-4 py-3 rounded-lg text-sm font-medium mb-4">
        {{ session('error') }}
    </div>
@endif


    <!-- Identifier (Email/Phone) -->
    <div class="relative">
        <label for="identifier" class="block text-sm font-medium text-gray-300 mb-2">
            Email or Phone Number
        </label>
        <input type="text" id="identifier" name="login" required
            class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary pr-10"
            placeholder="Enter email or phone number">
        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary hidden" 
            id="clearIdentifier">✖</button>
        <p class="mt-1 text-sm text-red-500 hidden" id="identifierError"></p>
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
            Password
        </label>
        <div class="relative">
            <input type="password" id="password" name="password" required
                class="w-full bg-secondary border border-gray-800 rounded-lg px-4 py-3 text-white focus:border-primary focus:ring-1 focus:ring-primary pr-10"
                placeholder="Enter password">
            <button type="button" id="togglePassword" 
                class="absolute right-10 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary">👁️</button>
            <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-primary hidden" 
                id="clearPassword">✖</button>
        </div>
        <p class="mt-1 text-sm text-gray-400">
            Password must be at least 8 characters, including uppercase, lowercase, number, and special character.
        </p>
        <p class="mt-1 text-sm text-red-500 hidden" id="passwordError"></p>
    </div>

    <!-- Remember Me & Forgot Password -->
    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input type="checkbox" id="remember" name="remember"
                class="h-4 w-4 rounded border-gray-800 bg-secondary text-primary focus:ring-primary">
            <label for="remember" class="ml-2 block text-sm text-gray-300"> Remember me </label>
        </div>
        <a href="#" class="text-sm text-primary hover:text-yellow-400">Forgot password?</a>
    </div>

    <!-- Submit Button -->
    <button type="submit"
        class="w-full bg-primary text-dark py-3 rounded-lg hover:bg-yellow-400 font-semibold transition-colors">
        Sign In
    </button>

    <!-- Signup Link -->
    <p class="text-center text-sm text-gray-400">
        Don't have an account?
        <a href="{{ route('register.user') }}" class="text-primary hover:text-yellow-400 font-medium"> Sign up now </a>
    </p>
</form>

            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const identifier = document.getElementById('identifier');
        const password = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const identifierError = document.getElementById('identifierError');
        const passwordError = document.getElementById('passwordError');

        // Toggle password visibility
        togglePassword.addEventListener('click', () => {
            const type = password.type === 'password' ? 'text' : 'password';
            password.type = type;
            togglePassword.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });

        // Validation functions
        function validateIdentifier(value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phoneRegex = /^\+?[\d\s-]{10,}$/;
            return emailRegex.test(value) || phoneRegex.test(value);
        }

        function validatePassword(value) {
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
            return passwordRegex.test(value);
        }

        // Real-time validation
        identifier.addEventListener('input', () => {
            if (!validateIdentifier(identifier.value)) {
                identifierError.textContent = 'Please enter a valid email or phone number';
                identifierError.classList.remove('hidden');
            } else {
                identifierError.classList.add('hidden');
            }
        });

        password.addEventListener('input', () => {
            if (!validatePassword(password.value)) {
                passwordError.textContent = 'Password must meet all requirements';
                passwordError.classList.remove('hidden');
            } else {
                passwordError.classList.add('hidden');
            }
        });

      
    </script>
@endsection