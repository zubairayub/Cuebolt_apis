 <!-- Footer -->
 <footer class="bg-dark border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 text-primary">CueBolt</h3>
                    <p class="text-gray-400">Your trusted trading marketplace for signals and packages.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-primary">Home</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary">Packages</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary">Signals</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary">Traders</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-primary">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-primary">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Newsletter</h4>
                    <p class="text-gray-400 mb-4">Subscribe to get the latest updates</p>
                    <div class="flex">
                        <input type="email" placeholder="Enter your email" class="bg-secondary text-white px-4 py-2 rounded-l-lg w-full border border-gray-800">
                        <button class="bg-primary text-dark px-4 py-2 rounded-r-lg hover:bg-yellow-400 font-semibold">Subscribe</button>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 CueBolt. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0" defer></script>
    <script>
      // Mobile menu functions
      function toggleMobileMenu() {
        const mobileMenu = document.querySelector('.mobile-menu');
        const overlay = document.getElementById('mobileMenuOverlay');
        
        mobileMenu.classList.toggle('show');
        
        if (mobileMenu.classList.contains('show')) {
          overlay.classList.remove('hidden');
          document.body.style.overflow = 'hidden';
        } else {
          overlay.classList.add('hidden');
          document.body.style.overflow = '';
        }
      }
      </script>
</html>