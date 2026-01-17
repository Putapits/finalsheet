<?php
/**
 * Services Overview Page - Premium Redesign
 */
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);

// Determine verification status
$isVerified = false;
if ($isLoggedIn) {
    $__u = $database->getUserById($_SESSION['user_id']);
    $isVerified = $__u && (($__u['verification_status'] ?? '') === 'verified');
}

// SEO Meta Tags
$pageTitle = "Explore Our Services | Health & Sanitation Ecosystem";
$metaDescription = "Discover our comprehensive suite of health and sanitation services, from medical care to environmental compliance.";

include 'header.php';

$services = [
    [
        'id' => 'health-center-details',
        'title' => 'Health Center Services',
        'description' => 'Comprehensive medical care, emergency response, and community wellness programs.',
        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>',
        'color' => 'emerald',
        'link' => 'health_center_services.php'
    ],
    [
        'id' => 'sanitation-permit',
        'title' => 'Sanitation & Inspection',
        'description' => 'Unified digital processing for commercial permits and health safety compliance.',
        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
        'color' => 'green',
        'link' => 'sanitation_permit.php'
    ],
    [
        'id' => 'immunization',
        'title' => 'Immunization & Nutrition',
        'description' => 'Digital tracking for vaccinations and nutritional development for your family.',
        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path></svg>',
        'color' => 'purple',
        'link' => 'immunization_tracker.php'
    ],
    [
        'id' => 'wastewater',
        'title' => 'Wastewater & Septic',
        'description' => 'Environmental management solutions and automated desludging compliance tracking.',
        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>',
        'color' => 'sky',
    ]
];

if (!(isset($_SESSION['role']) && $_SESSION['role'] === 'citizen')) {
    $services[] = [
        'id' => 'surveillance',
        'title' => 'Health Surveillance',
        'description' => 'RA 11332 incident reporting and real-time community disease monitoring.',
        'icon' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>',
        'color' => 'rose',
        'link' => 'health_surveillance.php'
    ];
}
?>

<style>
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

    .service-card {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .service-card:hover {
        transform: translateY(-10px);
    }
</style>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <section class="pt-40 pb-24 relative overflow-hidden">
        <!-- Abstract Background -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-sky-500/5 blur-[120px] rounded-full"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-20 space-y-6">
                <span
                    class="inline-block px-4 py-1.5 bg-primary/10 text-primary rounded-full text-[10px] font-black uppercase tracking-[0.3em]">Institutional
                    Ecosystem</span>
                <h1 class="text-5xl md:text-7xl font-black text-gray-900 dark:text-white tracking-tighter">
                    Our <span class="text-primary italic">Services</span>.
                </h1>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto font-medium">
                    State-of-the-art public health management systems designed for a safer, healthier community.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($services as $service): ?>
                    <section id="<?php echo $service['id']; ?>" class="service-card h-full">
                        <a href="<?php echo $service['link']; ?>"
                            class="block h-full glass-card rounded-[3rem] p-10 shadow-xl border-<?php echo $service['color']; ?>-500/10 hover:shadow-2xl hover:shadow-<?php echo $service['color']; ?>-500/20 group relative overflow-hidden">

                            <!-- Accent Shadow -->
                            <div
                                class="absolute -bottom-10 -right-10 w-32 h-32 bg-<?php echo $service['color']; ?>-500/5 blur-3xl rounded-full">
                            </div>

                            <div
                                class="mb-8 w-16 h-16 rounded-2xl bg-<?php echo $service['color']; ?>-600/10 text-<?php echo $service['color']; ?>-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                                <?php echo $service['icon']; ?>
                            </div>

                            <h3
                                class="text-2xl font-black text-gray-900 dark:text-white mb-4 tracking-tight group-hover:text-<?php echo $service['color']; ?>-600 transition-colors">
                                <?php echo $service['title']; ?>
                            </h3>

                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-relaxed mb-10">
                                <?php echo $service['description']; ?>
                            </p>

                            <div
                                class="pt-6 border-t border-gray-100 dark:border-white/5 flex justify-between items-center mt-auto">
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-<?php echo $service['color']; ?>-600">Secure
                                    Access</span>
                                <div
                                    class="w-10 h-10 rounded-full bg-gray-50 dark:bg-white/5 flex items-center justify-center group-hover:translate-x-2 transition-transform">
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-<?php echo $service['color']; ?>-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Support CTA -->
    <section class="max-w-7xl mx-auto px-4 pb-24">
        <div class="glass-card rounded-[3rem] p-12 text-center shadow-2xl relative overflow-hidden">
            <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-6">Need Personalized Assistance?</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-10 font-medium max-w-xl mx-auto">Our support team is available
                24/7 to help you navigate our digital services and health programs.</p>
            <a href="contact.php"
                class="inline-block bg-primary text-white font-black py-4 px-12 rounded-2xl shadow-xl shadow-primary/20 transition-transform hover:-translate-y-1">Connect
                with Expert</a>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>