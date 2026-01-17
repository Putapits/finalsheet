<?php
// Check if session functions are available
if (function_exists('startSecureSession')) {
    startSecureSession();
}
$headerIsLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);

// Determine path prefix for subdirectories (like /citizen/ or /health_worker/)
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$isCitizenDir = $currentDir === 'citizen';
$isWorkerDir = $currentDir === 'health_worker';
$prefix = ($isCitizenDir || $isWorkerDir) ? '../' : '';

// Handle navigation links contextually
$isCitizenPortal = ($currentPage === 'citizen.php');
$isWorkerPortal = ($currentPage === 'worker.php');
$isPortal = $isCitizenPortal || $isWorkerPortal;

$homeUrl = $isPortal ? '?page=home' : $prefix . 'website.php#home';
$aboutUrl = $isPortal ? '?page=about' : $prefix . 'about.php';
$servicesUrl = $isPortal ? '?page=services' : $prefix . 'services.php';
$contactUrl = $isPortal ? '?page=contact' : $prefix . 'contact.php';
?>
<!DOCTYPE html>
<html lang="en" id="html-root" class="no-transition">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health & Sanitation Services</title>
    <script>
        window.userRole = '<?php echo $_SESSION['role'] ?? 'guest'; ?>';
        window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        // 1. Initial Theme Application (Prevents White Flash)
        (function () {
            try {
                const root = document.documentElement;
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = savedTheme || (prefersDark ? 'dark' : 'light');

                root.classList.remove('light', 'dark');
                root.classList.add(theme);
                root.classList.add('no-transition');
            } catch (e) {
                console.error('Theme early-init error:', e);
            }
        })();
    </script>

    <style>
        html,
        body {
            background-color: #fbfbfb;
            color: #111827;
        }

        html.dark,
        html.dark body {
            background-color: #111827;
            color: #f3f4f6;
        }

        /* Fix Select Dropdown visibility in Dark Mode */
        html.dark select option {
            background-color: #111827;
            color: #f3f4f6;
        }

        html.dark select {
            color-scheme: dark;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // 2. Tailwind Configuration (Must match strategy used above)
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primary': '#4a90e2',
                        'secondary': '#9aa5b1',
                        'accent': '#4caf50',
                        'custom-bg': '#fbfbfb',
                        'green-primary': '#2e7d32',
                        'green-light': '#81c784',
                        'green-dark': '#1b5e20'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="<?php echo $prefix; ?>styles.css">

    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Additional CSS for theme toggle -->
    <style>
        /* Ensure theme toggle icons work properly */
        #theme-toggle {
            position: relative;
            z-index: 100;
        }

        #theme-toggle svg {
            transition: all 0.2s ease-in-out;
        }

        #theme-toggle-dark-icon.hidden,
        #theme-toggle-light-icon.hidden {
            display: none !important;
            opacity: 0;
        }

        #theme-toggle-dark-icon:not(.hidden),
        #theme-toggle-light-icon:not(.hidden) {
            display: block !important;
            opacity: 1;
        }

        /* Smooth transitions for all theme changes */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }

        /* Prevent transition on page load */
        .no-transition * {
            transition: none !important;
        }

        /* Navbar z-index fix - ensure it stays above everything */
        nav {
            z-index: 9999 !important;
        }

        /* Map container z-index fix */
        #map {
            z-index: 10 !important;
            height: 400px !important;
            min-height: 400px !important;
            width: 100% !important;
            position: relative !important;
            display: block !important;
            background: #f0f0f0 !important;
        }

        @media (min-width: 1024px) {
            #map {
                height: 500px !important;
                min-height: 500px !important;
            }
        }

        /* Ensure Leaflet map elements work properly */
        #map .leaflet-container {
            height: 100% !important;
            width: 100% !important;
        }

        /* Override any conflicting Tailwind styles */
        #map * {
            box-sizing: border-box !important;
        }

        /* Leaflet map controls z-index fix */
        .leaflet-control-container {
            z-index: 100 !important;
        }

        .leaflet-popup {
            z-index: 200 !important;
        }

        /* Ensure map doesn't interfere with navbar */
        .leaflet-container {
            z-index: 10 !important;
        }


        main {
            padding-top: 4rem;
            /* match nav height to avoid white gap */
        }

        .nav-logo {
            height: 50px;
            width: auto;
        }

        @media (min-width: 768px) {
            .nav-logo {
                height: 60px;
            }
        }
    </style>
</head>

<body class="bg-custom-bg dark:bg-gray-900 text-gray-900 dark:text-white">
    <!-- Navbar -->
    <nav
        class="bg-white dark:bg-gray-800 shadow-md fixed w-full top-0 z-50 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <img src="<?php echo $prefix; ?>img/HS.png" alt="Health & Sanitation Logo"
                            class="h-10 w-10 rounded shadow-sm">
                        <div>
                            <h1 class="text-gray-900 dark:text-white text-lg font-bold leading-tight">
                                <?php echo ($isWorkerDir || (isset($_SESSION['role']) && $_SESSION['role'] === 'health_worker')) ? 'LGU Officials' : 'Health & Sanitation'; ?>
                            </h1>
                            <p class="text-gray-500 dark:text-gray-400 text-[10px] uppercase font-bold tracking-wider">
                                <?php echo ($isWorkerDir || (isset($_SESSION['role']) && $_SESSION['role'] === 'health_worker')) ? 'Health Worker Portal' : 'Citizen Portal'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="flex items-center space-x-1">
                        <a href="<?php echo $homeUrl; ?>"
                            class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">Home</a>
                        <a href="<?php echo $aboutUrl; ?>"
                            class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">About</a>

                        <!-- Services Dropdown (Adopted Feature) -->
                        <div class="relative group">
                            <button
                                class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-4 py-2 rounded-md text-sm font-medium flex items-center gap-1 transition-colors">
                                Services
                                <svg class="w-4 h-4 transform group-hover:rotate-180 transition-transform duration-200"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <!-- Dropdown Menu -->
                            <div
                                class="absolute left-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 py-2 border border-gray-100 dark:border-gray-700">
                                <a href="<?php echo $prefix; ?>health_center_services.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="bg-blue-600/10 p-2 rounded-lg text-blue-600 dark:text-blue-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="font-medium">Health Center Services</span>
                                </a>
                                <?php if (!(isset($_SESSION['role']) && $_SESSION['role'] === 'health_worker')): ?>
                                    <a href="<?php echo $prefix; ?>sanitation_permit.php"
                                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <div class="bg-green-600/10 p-2 rounded-lg text-green-600 dark:text-green-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <span class="font-medium">Sanitation Permit & Inspection</span>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo $prefix; ?>immunization_tracker.php"
                                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="bg-purple-600/10 p-2 rounded-lg text-purple-600 dark:text-purple-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z">
                                            </path>
                                        </svg>
                                    </div>
                                    <span class="font-medium">Immunization Tracker</span>
                                </a>
                                <?php if (!(isset($_SESSION['role']) && $_SESSION['role'] === 'health_worker')): ?>
                                    <a href="<?php echo $prefix; ?>wastewater_septic.php"
                                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <div class="bg-cyan-600/10 p-2 rounded-lg text-cyan-600 dark:text-cyan-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </div>
                                        <span class="font-medium">Wastewater Services</span>
                                    </a>
                                <?php endif; ?>
                                <?php if (!(isset($_SESSION['role']) && $_SESSION['role'] === 'citizen')): ?>
                                    <a href="<?php echo $prefix; ?>health_surveillance.php"
                                        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <div class="bg-indigo-600/10 p-2 rounded-lg text-indigo-600 dark:text-indigo-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <span class="font-medium">Health Surveillance System</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="<?php echo $contactUrl; ?>"
                            class="text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">Contact</a>
                    </div>
                </div>

                <!-- Right side controls -->
                <div class="flex items-center space-x-4">
                    <!-- Dark/Light Mode Toggle -->
                    <button id="theme-toggle"
                        class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- Language Selector -->
                    <select id="language-selector"
                        class="hidden md:block bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs rounded border border-gray-200 dark:border-gray-600 px-2 py-1 outline-none">
                        <option value="en">English</option>
                        <option value="fil">Filipino</option>
                    </select>

                    <?php if ($headerIsLoggedIn): ?>
                        <!-- User Profile Dropdown -->
                        <div class="relative">
                            <button id="header-user-menu-button"
                                class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none">
                                <div
                                    class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                    <?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?>
                                </div>
                                <span
                                    class="hidden lg:block text-sm font-semibold text-gray-700 dark:text-gray-200"><?php echo $_SESSION['first_name'] ?? 'User'; ?></span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <!-- Dropdown -->
                            <div id="header-user-dropdown"
                                class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl py-1 z-50 border border-gray-100 dark:border-gray-700 transition-all">
                                <a href="<?php echo $prefix; ?>citizen/profile.php"
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Profile</a>
                                <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                                <a href="<?php echo $prefix; ?>logout.php"
                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 font-medium">Sign
                                    out</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $prefix; ?>index.php"
                            class="bg-primary text-white px-5 py-2 rounded-md text-sm font-bold shadow-md hover:bg-blue-600 hover:scale-105 transition-all">Login</a>
                    <?php endif; ?>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-button"
                            class="p-2 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu"
            class="hidden md:hidden bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="<?php echo $homeUrl; ?>"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Home</a>
                <a href="<?php echo $aboutUrl; ?>"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">About</a>
                <a href="<?php echo $prefix; ?>sanitation_permit.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Sanitation
                    Permit</a>
                <a href="<?php echo $prefix; ?>health_center_services.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Health
                    Services</a>
                <a href="<?php echo $prefix; ?>immunization_tracker.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Immunization</a>
                <a href="<?php echo $prefix; ?>wastewater_septic.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Wastewater</a>
                <a href="<?php echo $prefix; ?>health_surveillance.php"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Surveillance</a>
                <a href="<?php echo $contactUrl; ?>"
                    class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Contact</a>
            </div>
        </div>
    </nav>

    <script>
            // 3. Theme Toggle & UI Logic
            (function () {
                const root = document.documentElement;

                const initThemeUI = () => {
                    const themeToggle = document.getElementById('theme-toggle');
                    const moonIcon = document.getElementById('theme-toggle-dark-icon');
                    const sunIcon = document.getElementById('theme-toggle-light-icon');

                    if (!themeToggle || !moonIcon || !sunIcon) return;

                    const updateUI = (isDark) => {
                        if (isDark) {
                            moonIcon.classList.add('hidden');
                            sunIcon.classList.remove('hidden');
                        } else {
                            moonIcon.classList.remove('hidden');
                            sunIcon.classList.add('hidden');
                        }
                    };

                    // Sync UI on load
                    updateUI(root.classList.contains('dark'));

                    // Handle clicks
                    themeToggle.addEventListener('click', () => {
                        const willBeDark = !root.classList.contains('dark');
                        const newTheme = willBeDark ? 'dark' : 'light';

                        root.classList.remove('light', 'dark');
                        root.classList.add(newTheme);
                        localStorage.setItem('theme', newTheme);
                        updateUI(willBeDark);

                        window.dispatchEvent(new CustomEvent('themechanged', { detail: { theme: newTheme } }));
                    });

                    // Smooth transitions enabled after first load
                    setTimeout(() => root.classList.remove('no-transition'), 100);
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initThemeUI);
                } else {
                    initThemeUI();
                }

                // Mobile Menu Toggle
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', () => {
                        mobileMenu.classList.toggle('hidden');
                    });
                }

                // Header User Dropdown Toggle
                const userMenuButton = document.getElementById('header-user-menu-button');
                const userDropdown = document.getElementById('header-user-dropdown');
                if (userMenuButton && userDropdown) {
                    userMenuButton.addEventListener('click', (e) => {
                        e.stopPropagation();
                        userDropdown.classList.toggle('hidden');
                    });

                    document.addEventListener('click', (e) => {
                        if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                            userDropdown.classList.add('hidden');
                        }
                    });
                }
            })();
    </script>