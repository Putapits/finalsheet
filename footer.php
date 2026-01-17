<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$isCitizenDir = basename(dirname($_SERVER['PHP_SELF'])) === 'citizen';
$prefix = ($isCitizenDir) ? '../' : '';

$isCitizenPortal = ($currentPage === 'citizen.php');
$homeUrl = $isCitizenPortal ? '?page=home' : $prefix . 'website.php#home';
$aboutUrl = $isCitizenPortal ? '?page=about' : $prefix . 'about.php';
$servicesUrl = $isCitizenPortal ? '?page=services' : $prefix . 'services.php';
$contactUrl = $isCitizenPortal ? '?page=contact' : $prefix . 'contact.php';
?>
<!-- Footer -->
<footer class="bg-gray-800 dark:bg-gray-900 text-white py-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-lg font-semibold mb-4 text-white">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="<?php echo $homeUrl; ?>"
                            class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Home</a></li>
                    <li><a href="<?php echo $aboutUrl; ?>"
                            class="text-gray-300 hover:text-blue-400 transition-colors duration-200">About</a></li>
                    <li><a href="<?php echo $servicesUrl; ?>"
                            class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Services</a></li>
                    <li><a href="<?php echo $contactUrl; ?>"
                            class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Contact</a></li>
                    <li><a href="<?php echo $prefix; ?>admin-login.html"
                            class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Admin Login</a>
                    </li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4 text-white">Follow Us</h3>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-300 hover:text-blue-400 transition-colors duration-200"
                        aria-label="Facebook">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-300 hover:text-pink-400 transition-colors duration-200"
                        aria-label="Instagram">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.347-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z" />
                        </svg>
                    </a>
                    <a href="#" class="text-gray-300 hover:text-red-400 transition-colors duration-200"
                        aria-label="TikTok">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.89 2.89 0 01.88.13V9.4a6.84 6.84 0 00-.88-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4 text-white">Contact Info</h3>
                <div class="space-y-1 text-gray-300">
                    <p>8th Ave, Grace Park East, Caloocan</p>
                    <p>Metro Manila, Philippines</p>
                    <p>Phone: 09234662520</p>
                    <p>Email: info@healthsanitation.com</p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold mb-4 text-white">Office Hours</h3>
                <div class="space-y-1 text-gray-300">
                    <p>Monday - Friday: 8:00 AM - 5:00 PM (Open)</p>
                    <p>Saturday: Closed</p>
                    <p>Sunday: Closed</p>
                </div>
            </div>
        </div>
        <div class="border-t border-gray-700 dark:border-gray-600 mt-8 pt-8 text-center">
            <p class="text-gray-300">&copy;
                <?php echo date('Y'); ?> Health & Sanitation Services. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scroll-to-top"
    class="fixed bottom-20 right-4 bg-primary dark:bg-blue-700 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-300 opacity-0 invisible">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
    </svg>
</button>

<!-- Chatbot -->
<div id="chatbot-container" class="fixed bottom-4 right-4 z-50">
    <!-- Chatbot Toggle Button -->
    <button id="chatbot-toggle"
        class="bg-primary dark:bg-blue-700 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-300">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
            </path>
        </svg>
    </button>

    <!-- Chatbot Window -->
    <div id="chatbot-window"
        class="hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl w-80 h-[28rem] flex flex-col transition-colors duration-300">
        <!-- Chatbot Header -->
        <div class="bg-primary dark:bg-blue-700 text-white p-4 rounded-t-lg flex justify-between items-center">
            <h3 class="font-semibold">Health Assistant</h3>
            <div class="flex space-x-2">
                <button id="chatbot-minimize"
                    class="hover:bg-blue-700 dark:hover:bg-blue-800 p-1 rounded transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <button id="chatbot-close"
                    class="hover:bg-blue-700 dark:hover:bg-blue-800 p-1 rounded transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chat-messages"
            class="flex-1 p-4 pb-2 overflow-y-auto space-y-4 bg-gray-50 dark:bg-gray-900 transition-colors duration-300 relative">
            <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg transition-colors duration-300">
                <p class="text-sm text-gray-800 dark:text-gray-200" data-translate="chatWelcome">Hello! I'm your Health
                    Assistant. How can I help you today?</p>
            </div>


            <!-- Quick Reply Buttons -->
            <div id="quick-replies"
                class="space-y-2 sticky bottom-0 bg-gray-50 dark:bg-gray-900 p-2 rounded-lg border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-600 dark:text-gray-400 font-medium text-center">Quick Options:</p>
                <div class="grid grid-cols-2 gap-1">
                    <button
                        class="quick-reply-btn bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-200 p-1.5 rounded text-left text-xs transition-colors duration-200"
                        data-reply="services" data-message="Tell me about your services">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z">
                                </path>
                            </svg>
                            Services
                        </div>
                    </button>

                    <button
                        class="quick-reply-btn bg-green-50 dark:bg-green-900 hover:bg-accent hover:text-white dark:hover:bg-green-800 text-green-primary dark:text-green-200 p-1.5 rounded text-left text-xs transition-colors duration-200 border border-accent"
                        data-reply="contact" data-message="How can I contact you?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-accent dark:text-green-light" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            Contact
                        </div>
                    </button>

                    <button
                        class="quick-reply-btn bg-purple-100 dark:bg-purple-900 hover:bg-purple-200 dark:hover:bg-purple-800 text-purple-800 dark:text-purple-200 p-1.5 rounded text-left text-xs transition-colors duration-200"
                        data-reply="location" data-message="Where are you located?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Location
                        </div>
                    </button>

                    <button
                        class="quick-reply-btn bg-orange-100 dark:bg-orange-900 hover:bg-orange-200 dark:hover:bg-orange-800 text-orange-800 dark:text-orange-200 p-1.5 rounded text-left text-xs transition-colors duration-200"
                        data-reply="hours" data-message="What are your operating hours?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-orange-600 dark:text-orange-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Hours
                        </div>
                    </button>

                    <button
                        class="quick-reply-btn bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-800 dark:text-red-200 p-1.5 rounded text-left text-xs transition-colors duration-200"
                        data-reply="emergency" data-message="Do you have emergency services?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                            Emergency
                        </div>
                    </button>

                    <button
                        class="quick-reply-btn bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 text-indigo-800 dark:text-indigo-200 p-1.5 rounded text-left text-xs transition-colors duration-200"
                        data-reply="appointment" data-message="How do I schedule an appointment?">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-indigo-600 dark:text-indigo-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            Appointment
                        </div>
                    </button>

                    <button
                        class="quick-reply-btn bg-teal-100 dark:bg-teal-900 hover:bg-teal-200 dark:hover:bg-teal-800 text-teal-800 dark:text-teal-200 p-1.5 rounded text-left text-xs transition-colors duration-200"
                        data-reply="permits" data-message="Tell me about permits and inspections">
                        <div class="flex items-center">
                            <svg class="w-3 h-3 mr-1 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Permits
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div
            class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-lg transition-colors duration-300">
            <!-- Scroll Down Button -->
            <div class="flex justify-center mb-2">
                <button id="chat-scroll-down"
                    class="hidden bg-primary dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 text-white p-2 rounded-full shadow-lg transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
            </div>

            <div class="flex space-x-2">
                <input type="text" id="chat-input" placeholder="Type your message..."
                    class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200"
                    data-translate-placeholder="chatPlaceholder">
                <button id="chat-send"
                    class="bg-primary dark:bg-blue-700 text-white px-4 py-2 rounded-md hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200"
                    data-translate="send">
                    Send
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $prefix; ?>script.js"></script>
</body>

</html>