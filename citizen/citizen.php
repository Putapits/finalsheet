<?php
// Secure session and authentication
require_once '../include/database.php';
startSecureSession();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Redirect to logout to clear any stale session data and break loops
    header('Location: ../logout.php');
    exit();
}

// Verify user role is 'citizen'
if ($_SESSION['role'] !== 'citizen') {
    // Redirect to appropriate dashboard based on role
    header('Location: ../' . Database::getRoleRedirect($_SESSION['role']));
    exit();
}

// Additional security: Verify session user still exists and is active
$user = $database->getUserById($_SESSION['user_id']);
if (!$user || $user['status'] !== 'active') {
    session_destroy();
    header('Location: ../index.php?error=account_inactive');
    exit();
}

// Get the current page from URL parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Define allowed pages
$allowed_pages = ['home', 'about', 'services', 'contact', 'profile'];

// Validate page parameter
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>
<?php include '../header.php'; ?>
<style>
    :root {
        --portal-primary: #4f46e5;
        --portal-secondary: #0ea5e9;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .dark .glass-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .floating-animation {
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .premium-gradient {
        background: linear-gradient(135deg, var(--portal-primary), var(--portal-secondary));
    }

    /* Citizen portal content styling */
    .citizen-portal-content {
        padding-top: 0 !important;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    }

    .portal-logo {
        height: 50px;
        width: auto;
    }

    @media (min-width: 768px) {
        .portal-logo {
            height: 58px;
        }
    }
</style>

<!-- Main Content -->
<main class="flex-1">
    <?php
    switch ($page) {
        case 'home':
            ?>
            <div
                class="citizen-portal-content min-h-[calc(100vh-64px)] bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
                <!-- Hero Header -->
                <div class="premium-gradient pt-24 pb-32 px-4 sm:px-6 lg:px-8">
                    <div class="max-w-7xl mx-auto">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                            <div class="text-white space-y-4 max-w-2xl">
                                <span
                                    class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold tracking-widest uppercase">Welcome
                                    Back</span>
                                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">
                                    Hello, <?php echo htmlspecialchars($user['first_name']); ?>!
                                </h1>
                                <p class="text-lg text-white/80">
                                    Access your health records, book appointments, and manage sanitation permits all in one
                                    place.
                                </p>
                                <div class="flex flex-wrap gap-4 pt-4">
                                    <a href="?page=services"
                                        class="px-6 py-3 bg-white text-indigo-600 font-bold rounded-2xl shadow-lg hover:bg-gray-100 transition-all flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        New Request
                                    </a>
                                    <a href="?page=profile"
                                        class="px-6 py-3 bg-indigo-500 text-white font-bold rounded-2xl shadow-lg hover:bg-indigo-400 transition-all border border-white/20">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                            <div class="hidden md:block floating-animation">
                                <div class="w-64 h-64 glass-card rounded-3xl p-6 flex items-center justify-center">
                                    <img src="../img/HS.png" alt="Portal Logo" class="w-full h-auto drop-shadow-2xl">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20">
                    <div class="dashboard-grid">
                        <!-- Quick Stats / Status -->
                        <div class="glass-card stat-card rounded-3xl p-8 shadow-xl">
                            <div class="flex items-center gap-4 mb-4">
                                <div
                                    class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-2xl text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Active Appointments</h3>
                            </div>
                            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">
                                <?php
                                $stmt = $database->getConnection()->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND status != 'cancelled' AND status != 'completed'");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                            <a href="?page=profile"
                                class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                View History &rarr;
                            </a>
                        </div>

                        <div class="glass-card stat-card rounded-3xl p-8 shadow-xl">
                            <div class="flex items-center gap-4 mb-4">
                                <div
                                    class="p-3 bg-green-100 dark:bg-green-900/30 rounded-2xl text-green-600 dark:text-green-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Service Requests</h3>
                            </div>
                            <p class="text-3xl font-extrabold text-gray-900 dark:text-white">
                                <?php
                                $stmt = $database->getConnection()->prepare("SELECT COUNT(*) FROM service_requests WHERE user_id = ? AND status != 'cancelled'");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </p>
                            <a href="?page=profile"
                                class="mt-4 inline-flex items-center text-sm font-semibold text-green-600 dark:text-green-400 hover:underline">
                                Track Requests &rarr;
                            </a>
                        </div>

                        <div class="glass-card stat-card rounded-3xl p-8 shadow-xl">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Verification Status</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    <?php
                                    $vStatus = $database->isUserVerified($_SESSION['user_id']) ? 'verified' : ($user['verification_status'] ?? 'unverified');
                                    echo ($vStatus === 'verified') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                                    ?>">
                                    <?php echo ucfirst($vStatus); ?>
                                </span>
                            </div>
                            <a href="?page=profile"
                                class="mt-4 inline-flex items-center text-sm font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                Update ID Document &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Services Quick Links -->
                    <div class="mt-12 py-12">
                        <div class="text-center mb-10">
                            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Services</h2>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">All available modules for citizens</p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                            <?php
                            $quicklinks = [
                                ['Health Center', 'health_center_services.php', 'bg-rose-50 text-rose-600', 'H', 'border-rose-200', 'shadow-rose-500/10', 'rose'],
                                ['Sanitation Permit', 'sanitation_permit.php', 'bg-emerald-50 text-emerald-600', 'S', 'border-emerald-200', 'shadow-emerald-500/10', 'emerald'],
                                ['Waste Management', 'wastewater_septic.php', 'bg-sky-50 text-sky-600', 'W', 'border-sky-200', 'shadow-sky-500/10', 'sky'],
                                ['Immunization & Nutrition', 'immunization_tracker.php', 'bg-purple-50 text-purple-600', 'I', 'border-purple-200', 'shadow-purple-500/10', 'purple']
                            ];
                            foreach ($quicklinks as $link):
                                $href = (strpos($link[1], '../') === 0) ? $link[1] : '../' . $link[1];
                                ?>
                                <a href="<?php echo $href; ?>"
                                    class="group p-8 rounded-[2rem] bg-white dark:bg-gray-800 border <?php echo $link[4]; ?> dark:border-opacity-20 hover:border-<?php echo $link[6]; ?>-500 transition-all shadow-xl <?php echo $link[5]; ?> hover:shadow-2xl hover:-translate-y-2">
                                    <div class="flex flex-col gap-6">
                                        <div
                                            class="w-14 h-14 <?php echo $link[2]; ?> rounded-[1.25rem] flex items-center justify-center font-black text-2xl shadow-inner">
                                            <?php echo $link[3]; ?>
                                        </div>
                                        <div>
                                            <h4
                                                class="font-black text-gray-900 dark:text-white group-hover:text-<?php echo $link[6]; ?>-600 transition-colors tracking-tight text-lg">
                                                <?php echo $link[0]; ?>
                                            </h4>
                                            <p
                                                class="text-xs font-bold text-gray-400 mt-2 flex items-center gap-1.5 uppercase tracking-widest">
                                                Start <svg class="w-4 h-4 transition-transform group-hover:translate-x-1"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            break;
        case 'about':
            echo '<div class="citizen-portal-content">';
            ob_start();
            include '../about.php';
            $content = ob_get_clean();
            // Extract only the main content section
            if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                echo $matches[1];
            } else {
                // Fallback: remove everything before first content section
                $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                echo $content;
            }
            echo '</div>';
            break;
        case 'services':
            echo '<div class="citizen-portal-content min-h-[calc(100vh-64px)] bg-gray-50 dark:bg-gray-900 pt-12 pb-20 transition-colors duration-300">';
            // Add service category dropdown
            echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">';
            echo '<div class="glass-card rounded-3xl p-6 md:p-8 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">';
            echo '<div>';
            echo '<h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Browse Services</h2>';
            echo '<p class="text-gray-500 dark:text-gray-400 mt-1">Filter our catalog by health or sanitation categories.</p>';
            echo '</div>';
            echo '<div class="flex items-center gap-4 w-full md:w-auto">';
            echo '<select id="service-category" class="bg-gray-100 dark:bg-gray-800 border-0 text-gray-900 dark:text-white text-sm font-bold rounded-2xl focus:ring-2 focus:ring-primary block w-full md:w-80 p-4 transition-all">';
            echo '<option value="all" class="dark:bg-gray-800">📁 All Categories</option>';
            echo '<option value="health-center-details" class="dark:bg-gray-800">🏥 Health Center Services</option>';
            echo '<option value="sanitation-permit" class="dark:bg-gray-800">📋 Sanitation Permit & Inspection</option>';
            echo '<option value="immunization" class="dark:bg-gray-800">💉 Immunization & Nutrition</option>';
            echo '<option value="wastewater" class="dark:bg-gray-800">💧 Wastewater & Septic Services</option>';
            echo '</select>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            ob_start();
            include '../services.php';
            $content = ob_get_clean();
            // Adjust "Learn More" links to target sections within citizen portal
            $content = str_replace(
                [
                    'href="citizen.php?page=services#health-center"',
                    'href="citizen.php?page=services#sanitation-permit"',
                    'href="citizen.php?page=services#immunization"',
                    'href="citizen.php?page=services#wastewater"'
                ],
                [
                    'href="?page=services#health-center-details" data-service-target="health-center-details" onclick="if(typeof openHealthCenterModal === \'function\'){ openHealthCenterModal(); return false; }"',
                    'href="?page=services#sanitation-permit" data-service-target="sanitation-permit"',
                    'href="?page=services#immunization" data-service-target="immunization"',
                    'href="?page=services#wastewater" data-service-target="wastewater"'
                ],
                $content
            );
            // Extract only the main content section
            if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                echo $matches[1];
            } else {
                // Fallback: remove everything before first content section
                $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                echo $content;
            }
            echo '</div>';
            break;
        case 'contact':
            echo '<div class="citizen-portal-content">';
            ob_start();
            include '../contact.php';
            $content = ob_get_clean();
            // Extract only the main content section
            if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                echo $matches[1];
            } else {
                // Fallback: remove everything before first content section
                $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                echo $content;
            }
            echo '</div>';
            break;
        case 'profile':
            include 'profile.php';
            break;
        default:
            echo '<div class="citizen-portal-content">';
            ob_start();
            include '../website.php';
            $content = ob_get_clean();
            // Extract only the main content section
            if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                echo $matches[1];
            } else {
                // Fallback: remove everything before first content section
                $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                echo $content;
            }
            echo '</div>';
            break;
    }
    ?>
</main>

<?php include '../footer.php'; ?>

<script>
    // Service category dropdown functionality & smooth navigation
    const serviceCategorySelect = document.getElementById('service-category');
    const serviceSections = document.querySelectorAll('.citizen-portal-content section[id]');
    const serviceLinks = document.querySelectorAll('a[data-service-target]');

    function updateServiceUrl(target) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', 'services');
        if (!target || target === 'all') {
            url.searchParams.delete('focus');
        } else {
            url.searchParams.set('focus', target);
        }
        window.history.replaceState({}, '', url.pathname + '?' + url.searchParams.toString());
    }

    function smoothScrollToSection(section) {
        if (!section) return;
        const nav = document.querySelector('nav');
        const offset = nav ? nav.offsetHeight + 16 : 100;
        const elementTop = section.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({
            top: elementTop < 0 ? 0 : elementTop,
            behavior: 'smooth'
        });
    }

    function applyServiceFilter(target, options = {}) {
        const { scroll = true, updateUrl = false } = options;
        let visibleSection = null;

        serviceSections.forEach(section => {
            if (!target || target === 'all') {
                section.style.display = '';
                return;
            }

            if (section.id === target) {
                section.style.display = '';
                visibleSection = section;
            } else {
                section.style.display = 'none';
            }
        });

        if (updateUrl) {
            updateServiceUrl(target);
        }

        if (!scroll) return;

        if (!target || target === 'all') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            smoothScrollToSection(visibleSection || document.getElementById(target));
        }
    }

    if (serviceCategorySelect) {
        serviceCategorySelect.addEventListener('change', function (event) {
            const selectedCategory = event.target.value || 'all';
            applyServiceFilter(selectedCategory, { scroll: true, updateUrl: true });
        });

        serviceLinks.forEach(link => {
            link.addEventListener('click', function (event) {
                const target = this.dataset.serviceTarget;
                if (!target) return;
                event.preventDefault();
                serviceCategorySelect.value = target;
                applyServiceFilter(target, { scroll: true, updateUrl: true });
            });
        });

        const focusParam = new URLSearchParams(window.location.search).get('focus');
        if (focusParam && document.getElementById(focusParam)) {
            serviceCategorySelect.value = focusParam;
            applyServiceFilter(focusParam, { scroll: true, updateUrl: false });
        } else {
            applyServiceFilter('all', { scroll: false, updateUrl: false });
        }
    }
</script>