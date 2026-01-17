<?php
/**
 * Health Center Services Page
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
$pageTitle = "Health Center Services | Health & Sanitation";
$metaDescription = "Access medical consultations, emergency care, and preventive health services at your local health center.";

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

    .service-card-premium {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .service-card-premium:hover {
        transform: translateY(-12px) scale(1.02);
    }
</style>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Hero Section with Background Glow -->
    <section class="relative pt-32 pb-20 overflow-hidden">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-4xl h-96 bg-blue-500/10 blur-[120px] rounded-full">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center space-y-6 mb-16">
                <span
                    class="inline-block px-4 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full text-xs font-black uppercase tracking-widest">Medical
                    Care</span>
                <h1 class="text-5xl md:text-6xl font-black text-gray-900 dark:text-white tracking-tighter">
                    Health Center <span class="text-blue-600">Services</span>
                </h1>
                <p class="max-w-2xl mx-auto text-lg text-gray-500 dark:text-gray-400 font-medium">
                    Access expert medical care and emergency response services designed for the
                    community's well-being.
                </p>
                <div class="flex justify-center pt-4">
                    <button onclick="openHealthCenterModal()"
                        class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-8 py-4 rounded-2xl text-sm font-black flex items-center gap-3 transition-all hover:shadow-2xl hover:shadow-blue-500/20 border border-gray-100 dark:border-gray-700">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Find Nearest Health Center
                    </button>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Medical Consultations -->
                <div
                    class="service-card-premium glass-card p-10 rounded-[2.5rem] shadow-2xl border-blue-500/10 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 blur-3xl rounded-full -mr-10 -mt-10">
                    </div>

                    <div
                        class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/40 mb-8 transform group-hover:rotate-6 transition-transform">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-4">Medical Consultations</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 leading-relaxed font-medium">
                        Direct access to qualified medical professionals for diagnosis, prescriptions, and routine
                        check-ups.
                    </p>

                    <ul class="space-y-4 mb-10">
                        <?php foreach (['General Practice', 'Specialist Referrals', 'Health Assessments', 'Medical Certificates'] as $item): ?>
                            <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                <div
                                    class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center text-blue-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <?php echo $item; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('medical-consultation')"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-500/30 transition-all transform active:scale-95">
                            Book Now
                        </button>
                    <?php elseif ($isLoggedIn): ?>
                        <a href="citizen/profile.php"
                            class="block w-full text-center bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 font-bold py-4 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                            Verification Required
                        </a>
                    <?php else: ?>
                        <a href="index.php"
                            class="block w-full text-center bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg">
                            Login to Book
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Emergency Care -->
                <div
                    class="service-card-premium glass-card p-10 rounded-[2.5rem] shadow-2xl border-rose-500/10 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/5 blur-3xl rounded-full -mr-10 -mt-10">
                    </div>

                    <div
                        class="w-16 h-16 bg-rose-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-rose-500/40 mb-8 transform group-hover:rotate-6 transition-transform">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>

                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-4">Emergency Response</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 leading-relaxed font-medium">
                        24/7 critical care and rapid medical response for urgent health situations and trauma care.
                    </p>

                    <ul class="space-y-4 mb-10">
                        <?php foreach (['Rapid EMS Response', 'Trauma Stabilization', 'Ambulance Services', 'First Aid Support'] as $item): ?>
                            <li class="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-300">
                                <div
                                    class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center text-rose-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <?php echo $item; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('emergency-care')"
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-rose-500/30 transition-all transform active:scale-95">
                            Request EMS
                        </button>
                    <?php elseif ($isLoggedIn): ?>
                        <a href="citizen/profile.php"
                            class="block w-full text-center bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 font-bold py-4 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                            Verification Required
                        </a>
                    <?php else: ?>
                        <a href="index.php"
                            class="block w-full text-center bg-rose-600 text-white font-black py-4 rounded-2xl shadow-lg">
                            Login to Request
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php if (!in_array($_SESSION['role'] ?? '', ['health_worker', 'admin', 'nurse', 'doctor'])): ?>
            <!-- Citizen Health History Section -->
            <div class="mt-24 space-y-10">
                <div class="text-center">
                    <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">My Health <span
                            class="text-blue-600">Visits</span></h2>
                    <p class="text-gray-500 font-medium">History of your medical consultations, emergency care, and
                        appointments.</p>
                </div>

                <?php
                $reminders = $database->getFamilyUpcomingReminders($_SESSION['user_id']);
                if (!empty($reminders)): ?>
                    <div
                        class="bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-[2.5rem] p-8 shadow-sm">
                        <h3
                            class="text-sm font-black text-indigo-900 dark:text-indigo-200 uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                            <span class="p-2 bg-indigo-200/50 dark:bg-indigo-900/50 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </span>
                            Upcoming Follow-ups & Reminders
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php foreach ($reminders as $rem): ?>
                                <div
                                    class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-indigo-100 dark:border-indigo-700 shadow-sm hover:shadow-md transition-shadow flex items-start gap-4">
                                    <div
                                        class="mt-1 w-10 h-10 shrink-0 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-black uppercase text-indigo-500 tracking-widest mb-1 truncate">
                                            <?php echo htmlspecialchars($rem['name']); ?>'s
                                            <?php echo htmlspecialchars($rem['type']); ?>
                                        </p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white leading-relaxed">
                                            <?php echo htmlspecialchars($rem['detail']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="glass-card rounded-[3rem] overflow-hidden border-blue-500/10 shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead
                            class="bg-blue-50/50 dark:bg-blue-900/20 text-[11px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-6">Service Type</th>
                                <th class="px-8 py-6">Reference Date</th>
                                <th class="px-8 py-6">Details</th>
                                <th class="px-8 py-6">Status</th>
                                <th class="px-8 py-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php
                            $myAppointments = $database->getUserAppointments($_SESSION['user_id']);
                            $allServiceRequests = $database->getServiceRequestsByUserId($_SESSION['user_id']);

                            // Filter for health related services only
                            $sanitationTypes = ['septic-registration', 'maintenance-service', 'system-inspection', 'wastewater-clearance', 'installation-upgrade', 'maintenance-service'];
                            $myServiceRequests = array_filter($allServiceRequests, function ($req) use ($sanitationTypes) {
                                return !in_array($req['service_type'], $sanitationTypes);
                            });

                            $myRequests = [];
                            // Normalize Appointments
                            foreach ($myAppointments as $app) {
                                $app['service_type'] = $app['appointment_type'];
                                $app['service_details'] = $app['health_concerns'] ?? 'Medical Appointment';
                                $myRequests[] = $app;
                            }
                            // Add Health Service Requests
                            foreach ($myServiceRequests as $req) {
                                $myRequests[] = $req;
                            }

                            // Sort by date descending
                            usort($myRequests, function ($a, $b) {
                                $dateA = $a['preferred_date'] ?? $a['created_at'];
                                $dateB = $b['preferred_date'] ?? $b['created_at'];
                                return strtotime($dateB) - strtotime($dateA);
                            });

                            if (empty($myRequests)):
                                ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center gap-4 text-gray-400">
                                            <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                            <p class="font-black uppercase tracking-widest">No visit history yet</p>
                                            <p class="text-sm font-normal max-w-xs mx-auto">Your medical appointments and
                                                service requests will appear here once booked.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($myRequests as $req):
                                    $statusColors = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'in_progress' => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                        'rejected' => 'bg-rose-100 text-rose-700'
                                    ];
                                    $badgeIds = $statusColors[$req['status']] ?? 'bg-gray-100 text-gray-700';
                                    ?>
                                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-8 py-6">
                                            <span
                                                class="text-sm font-black uppercase text-gray-900 dark:text-white"><?php echo htmlspecialchars(str_replace('-', ' ', $req['service_type'])); ?></span>
                                        </td>
                                        <td class="px-8 py-6 text-sm font-medium text-gray-600 dark:text-gray-400">
                                            <?php echo $req['preferred_date'] ? date('M d, Y', strtotime($req['preferred_date'])) : date('M d, Y', strtotime($req['created_at'])); ?>
                                            <div class="text-[10px] text-gray-400 uppercase tracking-wider font-bold mt-1">
                                                <?php echo $req['preferred_date'] ? 'Preferred Date' : 'Requested On'; ?>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-sm text-gray-500 font-medium max-w-xs truncate">
                                            <?php echo htmlspecialchars($req['service_details']); ?>
                                        </td>
                                        <td class="px-8 py-6">
                                            <span
                                                class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo $badgeIds; ?>">
                                                <?php echo htmlspecialchars(str_replace('_', ' ', $req['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <button onclick='viewRequestDetails(<?php echo json_encode($req); ?>)'
                                                class="px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase rounded-xl hover:bg-blue-600 hover:text-white transition-all">
                                                View
                                            </button>
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


        <?php if (in_array($_SESSION['role'] ?? '', ['health_worker', 'admin', 'nurse', 'doctor'])): ?>
            <!-- Queue Management Section (Staff Only) -->
            <div class="mt-24 space-y-10">
                <!-- Available Doctors Section -->
                <div class="mb-8">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        Available Doctors
                    </h3>
                    <div id="doctorsList" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <!-- Loading State -->
                        <div class="glass-card p-4 rounded-2xl flex items-center gap-3 animate-pulse">
                            <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                                <div class="h-2 bg-gray-200 rounded w-1/3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Today's <span
                                class="text-blue-600">Patient Queue</span></h2>
                        <p class="text-gray-500 font-medium">Manage check-ins, assignments, and visit statuses in
                            real-time.</p>
                    </div>
                    <button onclick="fetchQueue()"
                        class="p-3 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl hover:rotate-180 transition-all duration-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>

                <div class="glass-card rounded-[3rem] overflow-hidden border-blue-500/10 shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead
                                class="bg-blue-50/50 dark:bg-blue-900/20 text-[11px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest">
                                <tr>
                                    <th class="px-8 py-6">Type</th>
                                    <th class="px-8 py-6">Patient Name</th>
                                    <th class="px-8 py-6">Detail</th>
                                    <th class="px-8 py-6">Status</th>
                                    <th class="px-8 py-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="queueTableBody" class="divide-y divide-gray-100 dark:divide-gray-800">
                                <!-- Dynamic Queue -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                async function fetchQueue() {
                    try {
                        const resp = await fetch('api/queue_management.php?action=fetch_queue');
                        const data = await resp.json();
                        if (data.success) {
                            const tbody = document.getElementById('queueTableBody');
                            tbody.innerHTML = data.queue.length ? '' : '<tr><td colspan="5" class="px-8 py-12 text-center text-gray-400 font-bold">No patients in queue today.</td></tr>';

                            data.queue.forEach(item => {
                                const tr = document.createElement('tr');
                                const isEmergency = item.detail === 'emergency-care';
                                tr.className = isEmergency
                                    ? "bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-colors shadow-sm"
                                    : "hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors";

                                const statusColors = {
                                    'pending': 'bg-amber-100 text-amber-700',
                                    'in_progress': 'bg-blue-100 text-blue-700',
                                    'completed': 'bg-emerald-100 text-emerald-700',
                                    'cancelled': 'bg-rose-100 text-rose-700'
                                };

                                const badgeClass = statusColors[item.status] || 'bg-gray-100 text-gray-700';

                                const detailDisplay = isEmergency
                                    ? '<span class="flex items-center gap-2 text-rose-600 font-black animate-pulse"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg> EMERGENCY CARE</span>'
                                    : item.detail;

                                tr.innerHTML = `
                                    <td class="px-8 py-6">
                                        <span class="text-[10px] font-black uppercase text-gray-400">${item.type.replace('_', ' ')}</span>
                                    </td>
                                    <td class="px-8 py-6 font-bold text-gray-900 dark:text-white">
                                        ${item.first_name} ${item.last_name}
                                    </td>
                                    <td class="px-8 py-6 text-sm text-gray-500 font-medium">
                                        ${detailDisplay}
                                    </td>
                                    <td class="px-8 py-6">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${badgeClass}">
                                            ${item.status.replace('_', ' ')}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right space-x-2 flex justify-end items-center">
                                        <button onclick='viewRequestDetails(${JSON.stringify({
                                ...item,
                                service_type: item.detail,
                                service_details: item.description,
                                full_name: item.type === 'service_request' ? item.first_name : `${item.first_name} ${item.last_name}`
                            })})' class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-[10px] font-black uppercase rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">View</button>
                                        
                                        ${item.status === 'pending' ? `
                                            <button onclick="openAssignModal('${item.id}', '${item.type}')" class="px-3 py-2 bg-emerald-600 text-white text-[10px] font-black uppercase rounded-xl shadow-lg shadow-emerald-500/30 hover:scale-105 transition-transform">Accept</button>
                                            <button onclick="declineRequest('${item.id}', '${item.type}')" class="px-3 py-2 bg-rose-600 text-white text-[10px] font-black uppercase rounded-xl shadow-lg shadow-rose-500/30 hover:scale-105 transition-transform">Decline</button>
                                        ` : ''}
                                        ${item.status === 'in_progress' ? `
                                            <button onclick="updateQueue('${item.id}', '${item.type}', 'complete')" class="px-4 py-2 bg-emerald-600 text-white text-[10px] font-black uppercase rounded-xl shadow-lg shadow-emerald-500/30">Complete</button>
                                        ` : ''}
                                    </td>
                                `;
                                tbody.appendChild(tr);
                            });
                        }
                    } catch (e) { console.error(e); }
                }

                async function updateQueue(id, type, action) {
                    const fd = new FormData();
                    fd.append('id', id);
                    fd.append('type', type);

                    try {
                        const resp = await fetch(`api/queue_management.php?action=${action}`, {
                            method: 'POST',
                            body: fd
                        });
                        const data = await resp.json();
                        if (data.success) {
                            fetchQueue();
                        } else {
                            alert(data.message || 'Operation failed');
                        }
                    } catch (e) { console.error(e); }
                }

                document.addEventListener('DOMContentLoaded', () => {
                    fetchQueue();
                    fetchDoctors();
                });

                async function fetchDoctors() {
                    try {
                        const resp = await fetch('api/queue_management.php?action=fetch_doctors');
                        const data = await resp.json();
                        if (data.success) window.availableDoctors = data.doctors;
                        const container = document.getElementById('doctorsList');
                        if (data.success && data.doctors.length) {
                            container.innerHTML = data.doctors.map(doc => `
                                <div class="glass-card p-4 rounded-2xl flex items-center gap-3 border border-blue-50 dark:border-blue-900/30">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 font-bold text-xs overflow-hidden">
                                        ${doc.profile_picture ? `<img src="${doc.profile_picture}" class="w-full h-full object-cover">` : doc.first_name[0] + doc.last_name[0]}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Dr. ${doc.first_name} ${doc.last_name}</h4>
                                        <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider flex items-center gap-1">
                                            Available
                                        </p>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            container.innerHTML = '<p class="text-gray-400 text-sm font-medium col-span-full py-4 text-center">No doctors currently marked available.</p>';
                        }
                    } catch (e) { console.error(e); }
                }
            </script>
        <?php endif; ?>
        </div>
    </section>
</main>

<!-- Request Details Modal -->
<div id="requestDetailsModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden z-[10006] flex items-center justify-center p-4 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Visit <span
                    class="text-blue-600">Details</span></h3>
            <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
            <div id="detailsContent" class="space-y-6">
                <!-- Content injected via JS -->
            </div>
        </div>
        <div class="p-8 bg-gray-50 dark:bg-gray-900/50">
            <button onclick="closeDetailsModal()"
                class="w-full bg-gray-900 dark:bg-white dark:text-gray-900 text-white font-black py-4 rounded-2xl uppercase text-xs tracking-widest hover:opacity-90 transition-all">
                Close Details
            </button>
        </div>
    </div>
</div>

<!-- Assign Doctor Modal -->
<div id="assignDoctorModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden z-[10010] flex items-center justify-center p-4 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-[2rem] max-w-sm w-full shadow-2xl p-6 transform transition-all scale-100">
        <h3 class="text-lg font-black text-gray-900 dark:text-white mb-4">Select Doctor</h3>
        <p class="text-sm text-gray-500 mb-6">Choose an available doctor to assign this request to.</p>

        <input type="hidden" id="assignRequestId">
        <input type="hidden" id="assignRequestType">

        <div class="space-y-3 mb-6 max-h-60 overflow-y-auto custom-scrollbar" id="doctorSelectionList">
            <!-- Doctors injected here -->
        </div>

        <div class="flex gap-3">
            <button onclick="document.getElementById('assignDoctorModal').classList.add('hidden')"
                class="flex-1 py-3 text-sm font-bold text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Cancel</button>
            <button onclick="confirmAssignment()"
                class="flex-1 py-3 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-500/30 transition-all">Confirm</button>
        </div>
    </div>
</div>

<script>
    let selectedDoctorId = null;
    function viewRequestDetails(req) {
        const content = document.getElementById('detailsContent');
        const statusColors = {
            'pending': 'bg-amber-100 text-amber-700',
            'in_progress': 'bg-blue-100 text-blue-700',
            'completed': 'bg-emerald-100 text-emerald-700',
            'cancelled': 'bg-rose-100 text-rose-700',
            'rejected': 'bg-rose-100 text-rose-700'
        };
        const badgeClass = statusColors[req.status] || 'bg-gray-100 text-gray-700';

        // Format the date
        const date = req.preferred_date ? new Date(req.preferred_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : new Date(req.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        const dateLabel = req.preferred_date ? 'Scheduled Date' : 'Request Date';

        content.innerHTML = `
        <div class="flex items-center justify-between mb-8">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider ${badgeClass}">${req.status.replace('_', ' ')}</span>
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">#REQ-${req.id}</span>
        </div>
        
        <div class="space-y-1">
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest">Service Type</p>
            <p class="text-xl font-black text-gray-900 dark:text-white capitalize">${req.service_type.replace('-', ' ')}</p>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div class="space-y-1">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">${dateLabel}</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white">${date}</p>
            </div>
            <div class="space-y-1">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Urgency</p>
                <p class="text-sm font-bold text-gray-900 dark:text-white capitalize">${req.urgency}</p>
            </div>
        </div>

        <div class="space-y-1 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Personal Details</p>
            <p class="text-sm font-bold text-gray-900 dark:text-white">${req.full_name}</p>
            <p class="text-xs text-gray-500 font-medium">${req.phone || 'No phone provided'}</p>
            <p class="text-xs text-gray-500 font-medium">${req.address || 'No address provided'}</p>
        </div>

        <div class="space-y-2 pt-4 border-t border-gray-100 dark:border-gray-700">
            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Service Details / Additional Info</p>
            <div class="text-sm text-gray-600 dark:text-gray-400 font-medium leading-relaxed whitespace-pre-line bg-gray-50 dark:bg-gray-900/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                ${req.service_details}
            </div>
        </div>
    `;

        document.getElementById('requestDetailsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDetailsModal() {
        document.getElementById('requestDetailsModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on click outside
    document.getElementById('requestDetailsModal').addEventListener('click', function (e) {
        if (e.target === this) closeDetailsModal();
    });
    // Assign Logic
    function openAssignModal(id, type) {
        document.getElementById('assignRequestId').value = id;
        document.getElementById('assignRequestType').value = type;

        const container = document.getElementById('doctorSelectionList');
        if (!window.availableDoctors || !window.availableDoctors.length) {
            container.innerHTML = '<p class="text-sm text-rose-500 font-bold text-center">No doctors available!</p>';
        } else {
            container.innerHTML = window.availableDoctors.map(doc => `
                <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-gray-700 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group">
                    <input type="radio" name="selected_doctor" value="${doc.id}" class="peer w-4 h-4 text-emerald-600 focus:ring-emerald-500" onchange="selectedDoctorId = this.value">
                    <div class="flex items-center gap-3 peer-checked:text-emerald-600 transition-colors">
                         <div class="w-8 h-8 rounded-full bg-gray-100 overflow-hidden">
                            <img src="${doc.profile_picture || 'assets/img/default-avatar.png'}" class="w-full h-full object-cover">
                         </div>
                         <span class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-emerald-600">Dr. ${doc.first_name} ${doc.last_name}</span>
                    </div>
                </label>
            `).join('');
        }

        selectedDoctorId = null;
        document.getElementById('assignDoctorModal').classList.remove('hidden');
    }

    async function confirmAssignment() {
        if (!selectedDoctorId) {
            alert('Please select a doctor.');
            return;
        }
        const id = document.getElementById('assignRequestId').value;
        const type = document.getElementById('assignRequestType').value;

        const fd = new FormData();
        fd.append('id', id);
        fd.append('type', type);
        fd.append('doctor_id', selectedDoctorId);

        try {
            const resp = await fetch('api/queue_management.php?action=assign_doctor', { method: 'POST', body: fd });
            const data = await resp.json();
            if (data.success) {
                document.getElementById('assignDoctorModal').classList.add('hidden');
                fetchQueue();
            } else {
                alert('Failed to assign doctor.');
            }
        } catch (e) { console.error(e); }
    }

    async function declineRequest(id, type) {
        if (!confirm('Are you sure you want to decline this request?')) return;

        const fd = new FormData();
        fd.append('id', id);
        fd.append('type', type);

        try {
            const resp = await fetch('api/queue_management.php?action=decline_request', { method: 'POST', body: fd });
            const data = await resp.json();
            if (data.success) fetchQueue();
        } catch (e) { console.error(e); }
    }
</script>

<?php
include 'include/service_modals.php';
include 'footer.php';
?>