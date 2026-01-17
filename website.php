<?php
// Start session and check login status BEFORE any output
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);

// Redirect logged-in users to their appropriate dashboard (only if no output yet)
if ($isLoggedIn && !headers_sent()) {
    $redirect_url = Database::getRoleRedirect($_SESSION['role']);
    header('Location: ' . $redirect_url);
    exit();
}

// Determine verification status for logged-in users
$isVerified = false;
if ($isLoggedIn) {
    $__u = $database->getUserById($_SESSION['user_id']);
    $isVerified = $__u && (($__u['verification_status'] ?? '') === 'verified');
}

// Handle appointment form submission
$appointment_message = '';
$appointment_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_form']) && $isLoggedIn) {
    // Validate and sanitize input
    $appointmentData = [
        'user_id' => $_SESSION['user_id'],
        'first_name' => trim($_POST['first-name']),
        'middle_name' => trim($_POST['middle-name']),
        'last_name' => trim($_POST['last-name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'birth_date' => $_POST['birth-date'],
        'gender' => $_POST['gender'],
        'civil_status' => $_POST['civil-status'],
        'address' => trim($_POST['address']),
        'appointment_type' => $_POST['appointment-type'],
        'preferred_date' => $_POST['preferred-date'],
        'health_concerns' => trim($_POST['health-concerns']),
        'medical_history' => trim($_POST['medical-history']),
        'current_medications' => trim($_POST['current-medications']),
        'allergies' => trim($_POST['allergies']),
        'emergency_contact_name' => trim($_POST['emergency-contact-name']),
        'emergency_contact_phone' => trim($_POST['emergency-contact-phone'])
    ];

    // Basic validation
    if (
        empty($appointmentData['first_name']) || empty($appointmentData['last_name']) ||
        empty($appointmentData['email']) || empty($appointmentData['phone']) ||
        empty($appointmentData['birth_date']) || empty($appointmentData['gender']) ||
        empty($appointmentData['civil_status']) || empty($appointmentData['address']) ||
        empty($appointmentData['appointment_type']) || empty($appointmentData['preferred_date']) ||
        empty($appointmentData['health_concerns']) || empty($appointmentData['medical_history']) ||
        empty($appointmentData['emergency_contact_name']) || empty($appointmentData['emergency_contact_phone'])
    ) {

        $appointment_message = 'Please fill in all required fields.';
    } elseif (!filter_var($appointmentData['email'], FILTER_VALIDATE_EMAIL)) {
        $appointment_message = 'Please enter a valid email address.';
    } else {
        // Gate: require verified account
        if (!$isVerified) {
            $appointment_success = false;
            $appointment_message = 'Your account is not verified. Please upload a valid ID in your Profile and wait for admin approval before booking an appointment.';
        } else {
            // Create appointment
            if ($database->createAppointment($appointmentData)) {
                $appointment_success = true;
                $appointment_message = 'Appointment booked successfully! We will contact you soon to confirm your appointment.';
            } else {
                $appointment_message = 'Failed to book appointment. Please try again.';
            }
        }
    }
}

// Now include header after session is started
include 'header.php';
?>

<!-- Main Content -->
<main class="pt-16">
    <!-- Hero Section -->
    <section id="home"
        class="bg-gradient-to-r from-white via-blue-100 to-blue-200 dark:from-blue-900 dark:via-blue-800 dark:to-blue-900 text-gray-900 dark:text-white py-20 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl font-bold mb-6 text-gray-900 dark:text-white" data-translate="heroTitle">
                Health & Sanitation Services</h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-700 dark:text-gray-200" data-translate="heroSubtitle">Promoting
                community health and environmental safety</p>
            <button
                class="bg-primary text-white dark:bg-gray-200 dark:text-blue-900 px-8 py-3 rounded-lg font-semibold hover:bg-blue-600 dark:hover:bg-gray-300 transition-colors border-2 border-accent"
                data-translate="getStarted">
                Get Started
            </button>
        </div>
    </section>

    <!-- Our Services (Overview) -->
    <?php
    // Detect base path for assets and links
    $isCitizenPortal = (strpos($_SERVER['PHP_SELF'], '/citizen/') !== false);
    $rootPath = $isCitizenPortal ? '../' : '';
    $assetBasePath = $rootPath;

    // Build a services link that works both on public site and inside the citizen portal
    $servicesLink = ($isCitizenPortal)
        ? 'citizen.php?page=services'
        : 'services.php';
    ?>
    <section id="health-center"
        class="py-20 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-3">Our Services</h2>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">We provide comprehensive health
                    and sanitation services to meet all your needs.</p>
            </div>

            <?php
            $cacheBuster = '?v=' . time();
            $serviceCards = [
                [
                    'image' => $assetBasePath . 'img/hcs.png' . $cacheBuster,
                    'label' => 'Health',
                    'title' => 'Health Center Services',
                    'description' => 'Medical consultations, check-ups, and comprehensive health services for the community.',
                    'url' => 'health_center_services.php'
                ],
                [
                    'image' => $assetBasePath . 'img/spi.png' . $cacheBuster,
                    'label' => 'Permits',
                    'title' => 'Sanitation Permit and Inspection',
                    'description' => 'Business sanitation permits and professional inspections to ensure compliance.',
                    'url' => 'sanitation_permit.php'
                ],
                [
                    'image' => $assetBasePath . 'img/int.png' . $cacheBuster,
                    'label' => 'Care',
                    'title' => 'Immunization and Nutrition Tracker',
                    'description' => 'Child immunization schedules and nutrition monitoring tools for families.',
                    'url' => 'immunization_tracker.php'
                ],
                [
                    'image' => $assetBasePath . 'img/wss.png' . $cacheBuster,
                    'label' => 'Water',
                    'title' => 'Wastewater and Septic Services',
                    'description' => 'Wastewater management, septic system maintenance, and compliance support.',
                    'url' => 'wastewater_septic.php'
                ],
                [
                    'image' => $assetBasePath . 'img/hss.png' . $cacheBuster,
                    'label' => 'Monitoring',
                    'title' => 'Health Surveillance System',
                    'description' => 'Health incident reporting and community surveillance for informed action.',
                    'url' => 'health_surveillance.php'
                ],
            ];
            ?>
            <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
                <?php foreach ($serviceCards as $card): ?>
                    <?php $link = $assetBasePath . $card['url']; ?>
                    <div
                        class="service-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-8 shadow-sm transition-all duration-300 hover:shadow-xl">
                        <div class="flex items-start justify-between mb-6">
                            <div
                                class="h-20 w-20 rounded-3xl border-2 border-dashed border-orange-300 bg-orange-50 flex items-center justify-center p-3">
                                <img src="<?php echo htmlspecialchars($card['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="<?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    class="w-full h-full object-contain">
                            </div>
                            <span
                                class="px-3 py-1 text-xs font-semibold text-orange-600 bg-orange-50 rounded-full"><?php echo htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                            <?php echo htmlspecialchars($card['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-6">
                            <?php echo htmlspecialchars($card['description'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>"
                            class="text-sm font-semibold text-orange-500 hover:underline flex items-center gap-2">Learn More
                            <span>&rarr;</span></a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 dark:bg-gray-900 text-white py-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4 text-white">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#home"
                                class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Home</a></li>
                        <li><a href="#about"
                                class="text-gray-300 hover:text-blue-400 transition-colors duration-200">About</a></li>
                        <li><a href="#health-center"
                                class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Services</a>
                        </li>
                        <li><a href="#contact"
                                class="text-gray-300 hover:text-blue-400 transition-colors duration-200">Contact</a>
                        </li>
                        <li><a href="admin-login.html"
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
                                    d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-.88-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z" />
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
                <p class="text-gray-300">&copy; 2024 Health & Sanitation Services. All rights reserved.</p>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages"
                class="flex-1 p-4 pb-2 overflow-y-auto space-y-4 bg-gray-50 dark:bg-gray-900 transition-colors duration-300 relative">
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg transition-colors duration-300">
                    <p class="text-sm text-gray-800 dark:text-gray-200" data-translate="chatWelcome">Hello! I'm your
                        Health Assistant. How can I help you today?</p>
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
                                <svg class="w-3 h-3 mr-1 text-blue-600 dark:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="w-3 h-3 mr-1 text-red-600 dark:text-red-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
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
                                <svg class="w-3 h-3 mr-1 text-teal-600 dark:text-teal-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
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

    <script src="script.js"></script>

    <!-- Map Initialization Script -->
    <script>
        console.log('=== WEBSITE MAP DEBUG START ===');

        // Function to show status in map container
        function showMapStatus(message, isError = false) {
            const mapContainer = document.getElementById('map');
            if (mapContainer) {
                const bgColor = isError ? '#f8d7da' : '#d4edda';
                const textColor = isError ? '#721c24' : '#155724';
                const borderColor = isError ? '#f5c6cb' : '#c3e6cb';
                mapContainer.innerHTML = `
                    <div style="padding: 20px; background: ${bgColor}; color: ${textColor}; text-align: center; border: 2px solid ${borderColor}; border-radius: 8px; font-family: Arial, sans-serif; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <h3 style="margin: 0 0 10px 0;">${isError ? '❌ Error' : '✅ Status'}</h3>
                        <p style="margin: 0 0 10px 0;">${message}</p>
                        <small>Time: ${new Date().toLocaleTimeString()}</small>
                    </div>
                `;
            }
        }

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM loaded, starting map initialization...');

            // Small delay to ensure everything is ready
            setTimeout(function () {
                const mapContainer = document.getElementById('map');
                console.log('Map container found:', !!mapContainer);

                if (!mapContainer) {
                    console.error('❌ Map container not found!');
                    return;
                }

                // Force container styling to match working test
                mapContainer.style.height = '400px';
                mapContainer.style.width = '100%';
                mapContainer.style.position = 'relative';
                mapContainer.style.display = 'block';
                mapContainer.style.backgroundColor = '#f0f0f0';
                mapContainer.style.border = '2px solid #007cba';

                console.log('Container styled, checking Leaflet...');
                showMapStatus('Checking Leaflet library...', false);

                // Check if Leaflet is available
                if (typeof L === 'undefined') {
                    console.error('❌ Leaflet not available');
                    showMapStatus('Leaflet library not loaded. Check internet connection.', true);
                    return;
                }

                console.log('✅ Leaflet available, version:', L.version);
                showMapStatus('Leaflet loaded! Creating map...', false);

                // Create the map
                setTimeout(function () {
                    try {
                        console.log('Creating map instance...');
                        mapContainer.innerHTML = ''; // Clear status message

                        // Create map exactly like the working test
                        const map = L.map('map').setView([14.5995, 120.9842], 13);
                        console.log('✅ Map instance created');

                        // Add tiles
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        console.log('✅ Tiles added');

                        // Add marker
                        L.marker([14.5995, 120.9842])
                            .addTo(map)
                            .bindPopup('Health & Sanitation Office')
                            .openPopup();
                        console.log('✅ Marker added');

                        // Force map to resize
                        setTimeout(function () {
                            map.invalidateSize();
                            console.log('✅ Map size invalidated');
                        }, 100);

                        console.log('🎉 MAP WORKING IN WEBSITE.PHP!');

                    } catch (error) {
                        console.error('❌ Map creation error:', error);
                        console.error('Error stack:', error.stack);
                        showMapStatus(`Map failed: ${error.message}`, true);
                    }
                }, 500);

            }, 200);
        });
    </script>
    </body>

    </html>