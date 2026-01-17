<?php
/**
 * Wastewater & Septic Services Page - Premium Redesign
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
$pageTitle = "Wastewater & Septic Management | Health & Sanitation";
$metaDescription = "Unified digital workflow for property owners and desludging providers to ensure environmental sanitation.";

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

    .water-banner {
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
    }

    .step-indicator {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .step-indicator:hover {
        transform: translateX(10px);
    }
</style>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Hero Section -->
    <section class="water-banner pt-32 pb-32 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10">
        </div>
        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="max-w-3xl space-y-6">
                <span
                    class="inline-block px-4 py-1.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-[0.2em] text-white">Environmental
                    Safety</span>
                <h1 class="text-5xl md:text-7xl font-black text-white tracking-tighter leading-none">
                    Wastewater <br> <span class="text-sky-300">& Septic</span>
                </h1>
                <p class="text-xl text-sky-50 font-medium leading-relaxed">
                    A unified digital workflow for property compliance, ensuring environmental sanitation and public
                    health protection through smart monitoring.
                </p>
                <div class="flex flex-wrap gap-4 pt-6">
                    <button onclick="openServiceForm('septic-registration')"
                        class="bg-white text-sky-700 px-8 py-4 rounded-2xl font-black shadow-xl transition-transform hover:-translate-y-1">
                        Register Septic Tank
                    </button>
                    <a href="#compliance"
                        class="bg-sky-600/30 backdrop-blur-md text-white border border-white/20 px-8 py-4 rounded-2xl font-black transition-all hover:bg-sky-600/50">
                        View Compliance
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Sections -->
    <section class="max-w-7xl mx-auto px-4 -mt-16 pb-20 relative z-20">
        <!-- Key Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
            <?php
            $metrics = [
                ['Cycle', '3-5 Years', 'blue', 'Desludging Frequency'],
                ['Clearance', 'ESC Ready', 'emerald', 'Sanitation Approved'],
                ['Fees', 'FREE', 'amber', 'For Residential Users'],
                ['Mandate', 'Active', 'rose', 'Ordinance No. 8491']
            ];
            foreach ($metrics as $m): ?>
                <div
                    class="glass-card p-8 rounded-[2rem] shadow-xl text-center border-b-4 border-<?php echo $m[2]; ?>-500 hover:-translate-y-2 transition-transform">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2"><?php echo $m[0]; ?></p>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white"><?php echo $m[1]; ?></h3>
                    <p class="text-[9px] font-bold text-<?php echo $m[2]; ?>-600 uppercase mt-2"><?php echo $m[3]; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="grid lg:grid-cols-3 gap-12">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1 space-y-8">
                <div class="glass-card p-10 rounded-[3rem] shadow-2xl">
                    <h4 class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-10">
                        Service Roadmap</h4>
                    <div class="space-y-6">
                        <?php
                        $roadmap = [
                            ['Registration', 'Property Owner', 'blue'],
                            ['Scheduling', 'System Logic', 'sky'],
                            ['Operation', 'Service Provider', 'emerald'],
                            ['Verification', 'Sanitary Inspector', 'rose'],
                            ['Monitoring', 'Health Office', 'indigo']
                        ];
                        foreach ($roadmap as $step): ?>
                            <div class="step-indicator group flex items-center gap-5 cursor-default">
                                <div
                                    class="w-10 h-10 rounded-xl bg-<?php echo $step[2]; ?>-500/10 border border-<?php echo $step[2]; ?>-500/20 flex items-center justify-center text-<?php echo $step[2]; ?>-600 font-black">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-white"><?php echo $step[0]; ?></p>
                                    <p
                                        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest group-hover:text-<?php echo $step[2]; ?>-500 transition-colors">
                                        <?php echo $step[1]; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Support Card -->
                <div class="bg-sky-900 rounded-[3rem] p-10 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-sky-400/20 blur-3xl rounded-full"></div>
                    <h4 class="text-2xl font-black mb-4 tracking-tight leading-tight">Need Technical <br> Support?</h4>
                    <p class="text-sm text-sky-200 font-medium mb-8">Direct access to sanitary inspectors and technical
                        policy guidance.</p>
                    <a href="contact.php"
                        class="inline-block w-full text-center bg-white text-sky-900 font-black py-4 rounded-2xl text-xs uppercase tracking-widest hover:scale-105 transition-transform">Contact
                        Team</a>
                </div>
            </div>

            <!-- Detailed Process -->
            <div class="lg:col-span-2 space-y-10">
                <?php if ($isLoggedIn): 
                    $stmt = $database->getConnection()->prepare("SELECT * FROM service_requests WHERE user_id = ? AND service_type IN ('septic-registration','maintenance-service','system-inspection','wastewater-clearance') ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute([$_SESSION['user_id']]);
                    $myReqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($myReqs)):
                ?>
                <div class="glass-card rounded-[2rem] p-8 shadow-xl border-blue-500/10">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        My Activity
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                             <thead>
                                 <tr class="text-xs font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700">
                                     <th class="pb-3 pl-2">Service</th>
                                     <th class="pb-3">Date</th>
                                     <th class="pb-3">Status</th>
                                     <th class="pb-3">Payment</th>
                                     <th class="pb-3">Result / Remarks</th>
                                     <th class="pb-3 text-right">Action</th>
                                 </tr>
                             </thead>
                             <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                 <?php foreach ($myReqs as $req): 
                                     $sType = ucwords(str_replace('-', ' ', $req['service_type']));
                                     $status = strtolower($req['status'] ?? 'pending');
                                     $sClass = match($status) {
                                         'completed' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10',
                                         'pending' => 'text-amber-600 bg-amber-50 dark:bg-amber-500/10',
                                         'cancelled' => 'text-rose-600 bg-rose-50 dark:bg-rose-500/10',
                                         'in_progress' => 'text-blue-600 bg-blue-50 dark:bg-blue-500/10',
                                         default => 'text-gray-600 bg-gray-50 dark:bg-gray-500/10'
                                     };
                                     $pStatus = strtolower($req['payment_status'] ?? 'unpaid');
                                     $pClass = match($pStatus) {
                                         'paid' => 'text-emerald-600',
                                         'for_verification' => 'text-blue-600 animate-pulse',
                                         default => 'text-gray-400'
                                     };
                                 ?>
                                 <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                                     <td class="py-4 pl-2 font-bold text-gray-700 dark:text-gray-300"><?php echo htmlspecialchars($sType); ?></td>
                                     <td class="py-4 text-gray-500 text-xs"><?php echo date('M j, Y', strtotime($req['created_at'])); ?></td>
                                     <td class="py-4">
                                         <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider <?php echo $sClass; ?>">
                                             <?php echo htmlspecialchars(str_replace('_', ' ', $status)); ?>
                                         </span>
                                     </td>
                                     <td class="py-4">
                                         <span class="text-[10px] font-black uppercase tracking-widest <?php echo $pClass; ?>">
                                            <?php 
                                            // Handling display for FREE vs PAID
                                            if ($pStatus === 'paid') {
                                                if (stripos($req['service_details'], '(Free') !== false || stripos($req['service_details'], 'Residential (Free') !== false) {
                                                    echo '<span class="text-emerald-500">FREE</span>';
                                                } else {
                                                    echo 'PAID';
                                                }
                                            } else {
                                                echo htmlspecialchars(str_replace('_', ' ', $pStatus)); 
                                            }
                                            ?>
                                         </span>
                                         <?php if ($pStatus === 'unpaid' && !in_array($status, ['cancelled', 'completed'])): ?>
                                             <button onclick="openServiceForm('wss-payment', { request_id: '<?php echo $req['id']; ?>', amount: 500 })" 
                                                 class="ml-2 px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[9px] font-bold rounded uppercase tracking-wider transition-colors">
                                                 Pay Now
                                             </button>
                                         <?php endif; ?>
                                     </td>
                                     <td class="py-4 text-xs font-medium text-gray-500 dark:text-gray-400 italic">
                                         <?php echo !empty($req['status_remarks']) ? htmlspecialchars($req['status_remarks']) : 'Processing...'; ?>
                                     </td>
                                     <td class="py-4 text-right">
                                         <button onclick='viewRequestDetails(<?php echo json_encode($req); ?>)' 
                                             class="text-gray-400 hover:text-blue-600 transition-colors p-1 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                         </button>
                                     </td>
                                 </tr>
                                 <?php endforeach; ?>
                                 </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; endif; ?>

                <!-- Registration Card -->
                <div class="glass-card rounded-[3rem] p-12 shadow-2xl border-sky-500/10 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-sky-500/5 blur-[100px] rounded-full"></div>

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
                        <div class="flex items-center gap-5">
                            <div
                                class="w-14 h-14 bg-sky-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-sky-500/20 group-hover:rotate-6 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3
                                    class="text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-none">
                                    Septic Registration</h3>
                                <p class="text-xs font-black text-sky-600 uppercase tracking-widest mt-2">Required for
                                    all property owners</p>
                            </div>
                        </div>
                        <span class="text-5xl font-black text-gray-100 dark:text-white/5 tracking-tighter">01</span>
                    </div>

                    <p class="text-lg text-gray-500 dark:text-gray-400 font-medium mb-10 leading-relaxed">
                        Property owners must document their septic system to establish baseline environmental data. This
                        ensures accurate desludging cycles are maintained according to city health mandates.
                    </p>

                    <div class="grid md:grid-cols-2 gap-8 mb-10">
                        <div
                            class="p-8 bg-gray-50 dark:bg-white/5 rounded-[2rem] border border-gray-100 dark:border-gray-800">
                            <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Required
                                Details</h5>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span> Property Type & Usage
                                </li>
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span> Tank Capacity (m³)
                                </li>
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span> Last Service Date
                                </li>
                            </ul>
                        </div>
                        <div
                            class="p-8 bg-gray-50 dark:bg-white/5 rounded-[2rem] border border-gray-100 dark:border-gray-800">
                            <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Technical
                                Specs</h5>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span> Construction Material
                                </li>
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span> Precise GPS Location
                                </li>
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                    <span class="w-1.5 h-1.5 bg-sky-500 rounded-full"></span> Access Point Status
                                </li>
                            </ul>
                        </div>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <button onclick="openServiceForm('septic-registration')"
                            class="w-full bg-sky-600 hover:bg-sky-700 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-sky-500/30 transition-all transform active:scale-95 group">
                            REGISTER NOW
                            <svg class="w-5 h-5 inline-block ml-2 group-hover:translate-x-1 transition-transform"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    <?php else: ?>
                        <a href="index.php"
                            class="block w-full text-center bg-gray-800 text-white font-black py-5 rounded-[1.5rem] shadow-lg">Login
                            to Register System</a>
                    <?php endif; ?>
                </div>

                <!-- Online Services Hub -->
                <div class="grid md:grid-cols-3 gap-5">
                    <button onclick="openServiceForm('maintenance-service')" class="glass-card p-6 rounded-[2rem] text-left hover:-translate-y-1 transition-transform group border border-blue-100 dark:border-blue-900/30 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-bl-[2rem]"></div>
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white leading-tight">Request<br>Desludging</h4>
                        <p class="text-[10px] uppercase font-bold text-gray-400 mt-3 tracking-wider group-hover:text-blue-600 transition-colors">Schedule Service</p>
                    </button>

                    <button onclick="openServiceForm('system-inspection')" class="glass-card p-6 rounded-[2rem] text-left hover:-translate-y-1 transition-transform group border border-emerald-100 dark:border-emerald-900/30 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-emerald-500/10 rounded-bl-[2rem]"></div>
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white leading-tight">Request<br>Inspection</h4>
                        <p class="text-[10px] uppercase font-bold text-gray-400 mt-3 tracking-wider group-hover:text-emerald-600 transition-colors">Check Compliance</p>
                    </button>

                    <button onclick="openServiceForm('wastewater-clearance')" class="glass-card p-6 rounded-[2rem] text-left hover:-translate-y-1 transition-transform group border border-amber-100 dark:border-amber-900/30 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-amber-500/10 rounded-bl-[2rem]"></div>
                        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/30 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white leading-tight">Get<br>Clearance</h4>
                        <p class="text-[10px] uppercase font-bold text-gray-400 mt-3 tracking-wider group-hover:text-amber-600 transition-colors">Process Permit</p>
                    </button>
                </div>

                <!-- Compliance Status Overview -->
                <div id="compliance" class="glass-card rounded-[3rem] p-12 shadow-2xl border-sky-500/10">
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-8 tracking-tight leading-none">
                        System Analytics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 bg-emerald-500/5 border border-emerald-500/10 rounded-3xl text-center">
                            <div
                                class="w-10 h-10 bg-emerald-500/10 text-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4 font-black">
                                ✔</div>
                            <h5
                                class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-widest">
                                Compliant</h5>
                            <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase">Cycle Maintained</p>
                        </div>
                        <div class="p-6 bg-amber-500/5 border border-amber-500/10 rounded-3xl text-center">
                            <div
                                class="w-10 h-10 bg-amber-500/10 text-amber-600 rounded-xl flex items-center justify-center mx-auto mb-4 font-black">
                                !</div>
                            <h5 class="text-xs font-black text-amber-700 dark:text-amber-400 uppercase tracking-widest">
                                Action Required</h5>
                            <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase">Correction Deadline</p>
                        </div>
                        <div class="p-6 bg-rose-500/5 border border-rose-500/10 rounded-3xl text-center">
                            <div
                                class="w-10 h-10 bg-rose-500/10 text-rose-600 rounded-xl flex items-center justify-center mx-auto mb-4 font-black">
                                ✖</div>
                            <h5 class="text-xs font-black text-rose-700 dark:text-rose-400 uppercase tracking-widest">
                                Violation</h5>
                            <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase">Legal Escalation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- View Details Modal -->
<div id="wss-view-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeViewModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 bg-blue-100 text-blue-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </span>
                    Request Details
                </h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500 bg-white dark:bg-gray-700 p-1 rounded-full shadow-sm hover:shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto space-y-6">
                
                <!-- Main Status Card -->
                <div class="flex items-center justify-between p-5 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-100 dark:border-blue-800/30">
                    <div>
                        <p class="text-[10px] font-bold text-blue-500 dark:text-blue-300 uppercase tracking-widest mb-1">Service Type</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white capitalize tracking-tight" id="view-service-type">Service Name</p>
                    </div>
                    <div class="text-right">
                        <span id="view-status-badge" class="px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-white shadow-sm ring-1 ring-gray-200">Pending</span>
                    </div>
                </div>

                <!-- Inspector info if assigned -->
                <div id="view-inspector-section" class="hidden bg-white dark:bg-gray-700 border border-gray-100 dark:border-gray-600 p-4 rounded-xl shadow-sm">
                     <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Assigned Inspector</p>
                     <div class="flex items-center gap-4">
                         <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                         </div>
                         <div>
                             <p class="font-bold text-gray-900 dark:text-white text-base" id="view-inspector-name">Inspector Name</p>
                             <p class="text-xs text-gray-500 mt-0.5" id="view-inspection-date">Status: Active</p>
                         </div>
                     </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Payment Info -->
                    <div class="p-5 bg-gray-50 dark:bg-gray-700/30 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                             Payment Status
                        </h4>
                        <div class="flex justify-between items-end">
                            <span id="view-payment-status" class="text-xl font-black uppercase">Unpaid</span>
                             <!-- Icon placeholder -->
                             <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                             </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                     <div class="p-5 bg-orange-50 dark:bg-orange-900/10 rounded-2xl border border-orange-100 dark:border-orange-800/20">
                        <h4 class="text-xs font-bold text-orange-400 uppercase tracking-widest mb-4">Latest Updates</h4>
                        <p class="text-sm text-orange-800 dark:text-orange-200 font-medium italic" id="view-latest-remarks">No updates yet.</p>
                    </div>
                </div>

                <!-- Full Details -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 ml-1">Submission Details</h4>
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl text-sm text-gray-600 dark:text-gray-300 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="whitespace-pre-wrap font-sans leading-relaxed" id="view-details-text"></div>
                    </div>
                </div>

            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-100 dark:border-gray-700">
                <button type="button" onclick="closeViewModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewRequestDetails(req) {
    if(!req) return;

    document.getElementById('view-service-type').textContent = req.service_type.replace(/-/g, ' ');
    
    // Status Badge
    const status = req.status || 'pending';
    const badge = document.getElementById('view-status-badge');
    badge.textContent = status.replace(/_/g, ' ');
    // Reset classes
    badge.className = 'px-4 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-white shadow-sm ring-1 ring-inset';
    
    // Status Color Map
    if(status === 'completed') {
        badge.classList.add('text-emerald-600', 'ring-emerald-200', 'bg-emerald-50');
    } else if(status === 'cancelled') {
        badge.classList.add('text-rose-600', 'ring-rose-200', 'bg-rose-50');
    } else if(status === 'in_progress') {
        badge.classList.add('text-blue-600', 'ring-blue-200', 'bg-blue-50');
    } else {
        badge.classList.add('text-amber-600', 'ring-amber-200', 'bg-amber-50');
    }

    // Inspector
    const inspSection = document.getElementById('view-inspector-section');
    if(req.assigned_inspector_id) {
        inspSection.classList.remove('hidden');
        document.getElementById('view-inspector-name').textContent = 'Inspector Assigned (ID: ' + req.assigned_inspector_id + ')'; 
    } else {
        inspSection.classList.add('hidden');
    }

    // Payment Status Logic
    const pStatus = req.payment_status || 'unpaid';
    const pEl = document.getElementById('view-payment-status');
    
    let displayStatus = pStatus;
    let colorClass = 'text-gray-500';

    if(pStatus === 'paid') {
        // Double check for Free status from details
        if (req.service_details && (req.service_details.toLowerCase().includes('(free') || req.service_details.toLowerCase().includes('residential (free'))) {
            displayStatus = 'FREE';
            colorClass = 'text-emerald-500';
        } else {
            displayStatus = 'PAID';
            colorClass = 'text-emerald-600';
        }
    } else if (pStatus === 'for_verification') {
        displayStatus = 'VERIFYING';
        colorClass = 'text-blue-600 animate-pulse';
    }

    pEl.textContent = displayStatus;
    pEl.className = 'text-xl font-black uppercase ' + colorClass;

    // Details Formatting
    // Basic cleanup: remove braces or JSON chars if it somehow got stored typically it is plain text.
    let details = req.service_details || 'No details provided.';
    details = details.replace(/[{}"]/g, '').replace(/,/g, '\n'); // Simple heuristic cleanup if JSON-like
    document.getElementById('view-details-text').textContent = details;

    // Remarks
    document.getElementById('view-latest-remarks').textContent = req.status_remarks || 'No specific updates available.';

    document.getElementById('wss-view-modal').classList.remove('hidden');
}

function closeViewModal() {
    document.getElementById('wss-view-modal').classList.add('hidden');
}
</script>

<?php
include 'include/service_modals.php';
include 'footer.php';
?>