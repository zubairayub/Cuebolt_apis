@extends('layouts.app')

@section('content')

    <!-- Register Form -->

    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
        <div class="bg-dark rounded-lg border border-gray-800 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-yellow-500">Create Account</h1>
                    <p class="text-gray-400 mt-2">Join our trading community today</p>
                </div>

                <form class="space-y-6" id="registrationForm" action="{{ route('register.submit') }}" method="POST">
    @csrf <!-- Add CSRF protection for the form -->

    <!-- Username/Phone -->
    <div class="relative">
        <label for="identifier" class="block text-sm font-medium text-gray-300 mb-2">
            Username or Phone Number
        </label>
        <div class="relative">
            <input type="text" id="identifier" name="email" required
                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 pr-10 @error('identifier') border-red-500 @enderror"
                placeholder="Enter username or phone number">
            <x-input-error :messages="$errors->get('email')" />
        </div>
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
            Password
        </label>
        <div class="relative">
            <input type="password" id="password" name="password" required
                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 pr-10 @error('password') border-red-500 @enderror"
                placeholder="Enter password">
            <x-input-error :messages="$errors->get('password')" />
            <p class="mt-1 text-sm text-gray-400">
                Password must be at least 8 characters, including uppercase, lowercase, number, and special
                character.
            </p>
        </div>
    </div>

    <!-- Trading Capital -->
    <div>
        <label class="block text-sm font-medium text-gray-300 mb-3">
            Your Trading Capital
        </label>
        <div class="grid grid-cols-2 gap-4">
            <label
                class="relative flex items-center p-3 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-700">
                <input type="radio" name="capital" value="0-1000" checked
                    class="h-4 w-4 text-yellow-500 focus:ring-yellow-500 border-gray-600 bg-gray-700">
                <span class="ml-3 text-gray-300">$0 - $1,000</span>
            </label>
            <label
                class="relative flex items-center p-3 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-700">
                <input type="radio" name="capital" value="1000-10000"
                    class="h-4 w-4 text-yellow-500 focus:ring-yellow-500 border-gray-600 bg-gray-700">
                <span class="ml-3 text-gray-300">$1,000 - $10,000</span>
            </label>
            <label
                class="relative flex items-center p-3 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-700">
                <input type="radio" name="capital" value="10000-50000"
                    class="h-4 w-4 text-yellow-500 focus:ring-yellow-500 border-gray-600 bg-gray-700">
                <span class="ml-3 text-gray-300">$10,000 - $50,000</span>
            </label>
            <label
                class="relative flex items-center p-3 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-700">
                <input type="radio" name="capital" value="50000+"
                    class="h-4 w-4 text-yellow-500 focus:ring-yellow-500 border-gray-600 bg-gray-700">
                <span class="ml-3 text-gray-300">$50,000+</span>
            </label>
        </div>
        <x-input-error :messages="$errors->get('capital')" />
    </div>

    <!-- Submit Button -->
    <button type="submit"
        class="w-full bg-yellow-500 text-gray-900 py-3 rounded-lg hover:bg-yellow-400 font-semibold transition-colors">
        Create Account
    </button>

    <!-- Login Link -->
    <p class="text-center text-sm text-gray-400">
        Already have an account?
        <a href="{{ route('login.user') }}" class="text-yellow-500 hover:text-yellow-400 font-medium">
            Sign in
        </a>
    </p>
</form>

            </div>
        </div>
    </div>

    <script>
        // Show/hide clear button for identifier input
        document.getElementById('identifier').addEventListener('input', function (e) {
            const clearButton = e.target.nextElementSibling;
            clearButton.classList.toggle('hidden', !e.target.value);
        });

        // Clear input field
        function clearInput(inputId) {
            const input = document.getElementById(inputId);
            input.value = '';
            input.focus();
            input.nextElementSibling.classList.add('hidden');
        }

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('passwordToggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('lucide-eye');
                icon.classList.add('lucide-eye-off');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('lucide-eye-off');
                icon.classList.add('lucide-eye');
            }
        }

        // Form submission
     
    </script>


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