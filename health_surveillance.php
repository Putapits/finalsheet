<?php
/**
 * Health Surveillance System Page - Premium Redesign
 */
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);

// RBAC: Restrict to Health Workers and Admins only
$allowedRoles = ['health_worker', 'admin'];
if (!$isLoggedIn || !in_array($_SESSION['role'], $allowedRoles)) {
    // If user is a citizen or not logged in, redirect them
    header("Location: dashboard.php");
    exit;
}

// Determine verification status
$isVerified = false;
if ($isLoggedIn) {
    $__u = $database->getUserById($_SESSION['user_id']);
    $isVerified = $__u && (($__u['verification_status'] ?? '') === 'verified');
    $my_reports = $database->getUserDiseaseReports($_SESSION['user_id']);
} else {
    $my_reports = [];
}

// SEO Meta Tags
$pageTitle = "Health Surveillance & Epidemiological System | GoServePH";
$metaDescription = "Advanced disease surveillance and health monitoring following PIDSR standards and RA 11332 guidelines.";

include 'header.php';
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

    .surveillance-banner {
        background: linear-gradient(135deg, #be123c 0%, #fb7185 100%);
    }

    .flow-card-premium {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .flow-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px -12px rgba(225, 29, 72, 0.2);
    }
</style>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Hero Section -->
    <section class="surveillance-banner pt-32 pb-40 relative overflow-hidden">
        <div
            class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20">
        </div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center space-y-8">
            <span
                class="inline-block px-5 py-2 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.3em] text-white">Public
                Health Intelligence</span>
            <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter">
                Health <span class="text-rose-200">Surveillance</span>
            </h1>
            <p class="text-xl text-rose-50 max-w-3xl mx-auto font-medium leading-relaxed">
                RA 11332 Compliant System for detecting, tracking, and controlling public health threats through
                high-precision data analysis.
            </p>
            <div class="flex flex-wrap justify-center gap-4 pt-4">
                <button onclick="openServiceForm('disease-monitoring')"
                    class="bg-white text-rose-700 px-10 py-5 rounded-2xl font-black shadow-2xl transition-transform hover:-translate-y-1">
                    Report Incident
                </button>
            </div>
        </div>
    </section>

    <!-- Content Sections -->
    <section class="max-w-7xl mx-auto px-4 -mt-20 pb-20 relative z-20">
        <!-- Quick Indicators -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <div
                class="glass-card p-10 rounded-[2.5rem] shadow-xl border-rose-500/10 hover:-translate-y-2 transition-transform">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-10 h-10 bg-rose-500/10 text-rose-600 rounded-xl flex items-center justify-center font-black">
                        !</div>
                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Case Definition</h4>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between text-sm font-bold"><span
                            class="text-gray-500">Suspected</span><span class="text-gray-900 dark:text-white">Clinical
                            Criteria</span></div>
                    <div class="flex justify-between text-sm font-bold"><span class="text-gray-500">Probable</span><span
                            class="text-gray-900 dark:text-white">Epi-Linked</span></div>
                    <div class="flex justify-between text-sm font-bold"><span
                            class="text-gray-500">Confirmed</span><span class="text-rose-600">Lab Positive</span></div>
                </div>
            </div>
            <div
                class="glass-card p-10 rounded-[2.5rem] shadow-xl border-amber-500/10 hover:-translate-y-2 transition-transform">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-10 h-10 bg-amber-500/10 text-amber-600 rounded-xl flex items-center justify-center font-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">Timeline</h4>
                </div>
                <p class="text-2xl font-black text-gray-900 dark:text-white">24 Hours</p>
                <p class="text-[10px] font-bold text-amber-600 uppercase mt-2 tracking-widest leading-relaxed">Immediate
                    notification required for critical category disease events.</p>
            </div>
            <div
                class="glass-card p-10 rounded-[2.5rem] shadow-xl border-blue-500/10 hover:-translate-y-2 transition-transform">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-10 h-10 bg-blue-500/10 text-blue-600 rounded-xl flex items-center justify-center font-black">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest">RA 11332</h4>
                </div>
                <p class="text-[11px] font-bold text-gray-500 dark:text-gray-400 leading-relaxed italic">
                    "Mandatory reporting of notifiable diseases is required for all health facilities and clinics in
                    accordance with national safety protocols."
                </p>
            </div>
        </div>

        <!-- Flow Analysis -->
        <h3 class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.4em] text-center mb-12">
            Surveillance Lifecycle</h3>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $flow = [
                ['01', 'Detection', 'Initial clinical reporting and data entry.', 'rose'],
                ['02', 'Validation', 'Automated data classification & check.', 'indigo'],
                ['03', 'Investigation', 'DSO field study and exposure tracking.', 'amber'],
                ['04', 'Confirmation', 'Lab-verified final classification.', 'emerald'],
                ['05', 'Analytics', 'AI-driven trend & cluster detection.', 'blue'],
                ['06', 'Response', 'Targeted intervention & containment.', 'red'],
            ];
            foreach ($flow as $f): ?>
                <div
                    class="flow-card-premium glass-card p-10 rounded-[3rem] shadow-xl border-<?php echo $f[3]; ?>-500/10 relative overflow-hidden group">
                    <div class="absolute -top-10 -right-10 w-24 h-24 bg-<?php echo $f[3]; ?>-500/5 blur-3xl rounded-full">
                    </div>
                    <span
                        class="text-4xl font-black text-<?php echo $f[3]; ?>-500/20 mb-6 block"><?php echo $f[0]; ?></span>
                    <h4 class="text-xl font-black text-gray-900 dark:text-white mb-4"><?php echo $f[1]; ?></h4>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 leading-relaxed"><?php echo $f[2]; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Dashboard Insight Box -->
        <!-- My Submitted Reports History -->
        <?php if ($isLoggedIn): ?>
            <div class="mt-16 glass-card rounded-[4rem] p-12 shadow-2xl relative overflow-hidden border-rose-500/10">
                 <div class="absolute inset-0 bg-rose-500/5 pointer-events-none"></div>
                 <div class="relative z-10">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight mb-8">
                        My Submitted Reports
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-white/10 text-xs font-black text-gray-400 uppercase tracking-widest">
                                    <th class="py-4">Report ID</th>
                                    <th class="py-4">Category</th>
                                    <th class="py-4">Date Reported</th>
                                    <th class="py-4">Status</th>
                                    <th class="py-4">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                <?php if (empty($my_reports)): ?>
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-gray-400 font-bold">
                                            No reports submitted yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($my_reports as $rpt): ?>
                                    <tr class="border-b border-gray-100 dark:border-white/5 hover:bg-rose-50/50 dark:hover:bg-rose-900/10 transition-colors">
                                        <td class="py-6 font-bold text-gray-900 dark:text-white">
                                            #<?php echo str_pad($rpt['id'], 6, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="py-6 text-gray-600 dark:text-gray-300">
                                            <?php echo htmlspecialchars($rpt['diagnosis_category'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="py-6 text-gray-600 dark:text-gray-300">
                                            <?php echo date('M d, Y', strtotime($rpt['created_at'])); ?>
                                        </td>
                                        <td class="py-6">
                                            <?php 
                                            // Status mapping
                                            $st = $rpt['status'] ?? 'pending';
                                            $badgeClass = 'bg-gray-100 text-gray-600';
                                            if ($st === 'validated') $badgeClass = 'bg-emerald-100 text-emerald-700';
                                            elseif ($st === 'needs_clarification') $badgeClass = 'bg-amber-100 text-amber-700';
                                            elseif ($st === 'closed') $badgeClass = 'bg-blue-100 text-blue-700';
                                            ?>
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo $badgeClass; ?>">
                                                <?php echo str_replace('_', ' ', $st); ?>
                                            </span>
                                        </td>
                                        <td class="py-6 text-gray-500 italic">
                                            <?php echo htmlspecialchars($rpt['remarks'] ?? '---'); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
    // Logic for reporting case forms can be added here
</script>

<?php
include 'include/service_modals.php';
include 'footer.php';
?>