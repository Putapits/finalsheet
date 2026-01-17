<?php 
// Start session and check login status BEFORE any output
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in and redirect to appropriate dashboard (only if no output yet)
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && !headers_sent()) {
    $redirect_url = Database::getRoleRedirect($_SESSION['role']);
    header('Location: ' . $redirect_url);
    exit();
}

include 'header.php'; 
?>

    <!-- Main Content -->
    <main class="pt-16">
        <!-- About Section -->
        <section id="about" class="py-20 bg-custom-bg dark:bg-gray-800 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-4xl font-bold text-center mb-12 text-gray-900 dark:text-white" data-translate="aboutTitle">About Us</h2>
                
                <!-- Main About Content -->
                <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg transition-colors duration-300">
                            <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white flex items-center">
                                <svg class="w-6 h-6 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Our Mission
                            </h3>
                            <p class="text-lg text-gray-700 dark:text-gray-300" data-translate="aboutText1">
                                We are dedicated to providing comprehensive health and sanitation services to our community. 
                                Our mission is to ensure the well-being of all residents through quality healthcare, 
                                environmental protection, and public health initiatives that promote a safer, healthier living environment for everyone.
                            </p>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg transition-colors duration-300">
                            <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white flex items-center">
                                <svg class="w-6 h-6 mr-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Our Team
                            </h3>
                            <p class="text-lg text-gray-700 dark:text-gray-300" data-translate="aboutText2">
                                With over 15 years of experience and a team of qualified professionals including licensed healthcare workers, 
                                environmental specialists, and certified sanitation experts, we strive to maintain the highest standards 
                                in public health and sanitation services. Our dedicated staff works around the clock to ensure community safety and wellness.
                            </p>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="bg-blue-100 dark:bg-blue-900 p-8 rounded-lg shadow-lg transition-colors duration-300">
                            <h3 class="text-2xl font-semibold mb-4 text-blue-900 dark:text-blue-100 flex items-center" data-translate="vision">
                                <svg class="w-6 h-6 mr-3 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Our Vision
                            </h3>
                            <p class="text-blue-800 dark:text-blue-200 text-lg" data-translate="visionText">
                                To create a healthier and safer community through innovative health and sanitation solutions, 
                                advanced technology integration, and sustainable environmental practices that protect and enhance 
                                the quality of life for current and future generations.
                            </p>
                        </div>
                        
                        <div class="bg-green-50 dark:bg-green-900 p-8 rounded-lg shadow-lg transition-colors duration-300 border-l-4 border-accent">
                            <h3 class="text-2xl font-semibold mb-4 text-green-primary dark:text-green-100 flex items-center">
                                <svg class="w-6 h-6 mr-3 text-accent dark:text-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span data-translate="ourValues">Our Values</span>
                            </h3>
                            <ul class="text-green-primary dark:text-green-200 text-lg space-y-2">
                                <li>• <strong data-translate="excellence">Excellence:</strong> <span data-translate="excellenceDesc">Delivering the highest quality services</span></li>
                                <li>• <strong data-translate="integrity">Integrity:</strong> <span data-translate="integrityDesc">Transparent and ethical practices</span></li>
                                <li>• <strong data-translate="community">Community:</strong> <span data-translate="communityDesc">Serving with compassion and dedication</span></li>
                                <li>• <strong data-translate="innovation">Innovation:</strong> <span data-translate="innovationDesc">Embracing modern solutions</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Section -->
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow-lg p-8 transition-colors duration-300">
                    <h3 class="text-2xl font-semibold text-center mb-8 text-gray-900 dark:text-white" data-translate="ourImpact">Our Impact</h3>
                    <div class="grid md:grid-cols-4 gap-8 text-center">
                        <div class="space-y-2">
                            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">15+</div>
                            <div class="text-gray-700 dark:text-gray-300" data-translate="yearsOfService">Years of Service</div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-bold text-accent dark:text-green-light">50,000+</div>
                            <div class="text-gray-700 dark:text-gray-300" data-translate="peopleServed">People Served</div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">1,200+</div>
                            <div class="text-gray-700 dark:text-gray-300" data-translate="healthInspections">Health Inspections</div>
                        </div>
                        <div class="space-y-2">
                            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">24/7</div>
                            <div class="text-gray-700 dark:text-gray-300" data-translate="emergencyResponses">Emergency Response</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php include 'footer.php'; ?>
