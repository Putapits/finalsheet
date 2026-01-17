<?php
require_once '../include/database.php';
startSecureSession();
requireRole('admin');

// Includes for header and sidebar
include 'adminheader.php';
include 'adminsidebar.php';
?>


<!-- Main Content -->
<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">

    <div id="content-area" class="p-6 bg-dark-bg min-h-screen">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
        if (in_array($page, ['hcs', 'spi', 'int', 'wss', 'hss', 'payments', 'contact', 'account', 'audit', 'restore', 'notifications'])) {
            $allowedViews = [
                'hcs' => ['overview', 'appointment', 'consultation'],
                'spi' => ['overview', 'sanitation', 'permits'],
                'int' => ['overview', 'immunization', 'nutrition'],
                'wss' => ['overview', 'septic', 'assignment'],
                'hss' => ['overview', 'disease', 'environmental'],
                'payments' => ['overview'],
                'restore' => ['data'],
                'contact' => ['messages'],
                'account' => ['settings'],
                'audit' => ['logs'],
                'notifications' => ['list'],
            ];
            $defaultView = [
                'hcs' => 'overview',
                'spi' => 'overview',
                'int' => 'overview',
                'wss' => 'overview',
                'hss' => 'overview',
                'payments' => 'overview',
                'restore' => 'data',
                'contact' => 'messages',
                'account' => 'settings',
                'audit' => 'logs',
                'notifications' => 'list',
            ];
            $view = (isset($_GET['view']) && in_array($_GET['view'], $allowedViews[$page]))
                ? $_GET['view']
                : ($defaultView[$page] ?? 'overview');
            include $page . '/' . $view . '.php';
        } else {
            ?>
            <!-- Dashboard Content (Default) -->
            <div id="dashboard-content">

                <!-- Dashboard Overview Header -->
                <div
                    class="mb-8 rounded-2xl p-8 border border-blue-100 shadow-lg bg-gradient-to-r from-blue-50 via-emerald-50 to-sky-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-slate-100 mb-2">Dashboard Overview</h2>
                            <p class="text-sm text-gray-600 dark:text-slate-300">Monitor key metrics and pending workloads
                                across all services.</p>
                        </div>
                        <div class="hidden md:block">
                            <svg class="w-16 h-16 text-blue-200 dark:text-slate-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <?php
                // Dynamic metrics
                $roleCounts = ['doctor' => 0, 'nurse' => 0, 'inspector' => 0, 'citizen' => 0];
                try {
                    $stmt = $db->prepare("SELECT role, COUNT(*) AS c FROM users WHERE role IN ('doctor','nurse','inspector','citizen') GROUP BY role");
                    $stmt->execute();
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        if (isset($roleCounts[$row['role']]))
                            $roleCounts[$row['role']] = (int) $row['c'];
                    }
                } catch (Throwable $e) { /* ignore */
                }
                // Pending counts by service/module
                $hcsConsultTypes = ['medical-consultation', 'emergency-care', 'preventive-care'];
                $spiTypes = ['business-permit', 'health-inspection'];
                $intTypes = ['vaccination', 'nutrition-monitoring'];
                $wssTypes = ['system-inspection', 'maintenance-service', 'installation-upgrade'];
                $hssTypes = ['disease-monitoring', 'environmental-monitoring'];

                function countPendingAppointments(PDO $db)
                {
                    try {
                        $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE status='pending' AND deleted_at IS NULL");
                        $stmt->execute();
                        return (int) $stmt->fetchColumn();
                    } catch (Throwable $e) {
                        return 0;
                    }
                }

                function countPendingByTypes(PDO $db, array $types)
                {
                    if (!$types)
                        return [];
                    try {
                        $ph = implode(',', array_fill(0, count($types), '?'));
                        $sql = "SELECT service_type, COUNT(*) c FROM service_requests WHERE deleted_at IS NULL AND status='pending' AND service_type IN ($ph) GROUP BY service_type";
                        $stmt = $db->prepare($sql);
                        $stmt->execute(array_values($types));
                        $out = [];
                        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                            $out[$r['service_type']] = (int) $r['c'];
                        }
                        return $out;
                    } catch (Throwable $e) {
                        return [];
                    }
                }

                $hcsApptPending = countPendingAppointments($db);
                $hcsConsultByType = countPendingByTypes($db, $hcsConsultTypes);
                $spiByType = countPendingByTypes($db, $spiTypes);
                $intByType = countPendingByTypes($db, $intTypes);
                $wssByType = countPendingByTypes($db, $wssTypes);
                $hssByType = countPendingByTypes($db, $hssTypes);

                $pendingModules = [
                    [
                        'key' => 'hcs',
                        'title' => 'Health Center Services (HCS)',
                        'services' => [
                            ['label' => 'Appointment', 'count' => $hcsApptPending],
                            ['label' => 'Consultations', 'count' => array_sum($hcsConsultByType)],
                        ],
                    ],
                    [
                        'key' => 'spi',
                        'title' => 'Sanitation Permit & Inspection (SPI)',
                        'services' => [
                            ['label' => 'Business Permit', 'count' => (int) ($spiByType['business-permit'] ?? 0)],
                            ['label' => 'Health Inspection', 'count' => (int) ($spiByType['health-inspection'] ?? 0)],
                        ],
                    ],
                    [
                        'key' => 'int',
                        'title' => 'Immunization & Nutrition (INT)',
                        'services' => [
                            ['label' => 'Vaccination', 'count' => (int) ($intByType['vaccination'] ?? 0)],
                            ['label' => 'Nutrition Monitoring', 'count' => (int) ($intByType['nutrition-monitoring'] ?? 0)],
                        ],
                    ],
                    [
                        'key' => 'wss',
                        'title' => 'Wastewater & Septic Services (WSS)',
                        'services' => [
                            ['label' => 'System Inspection', 'count' => (int) ($wssByType['system-inspection'] ?? 0)],
                            ['label' => 'Maintenance Service', 'count' => (int) ($wssByType['maintenance-service'] ?? 0)],
                            ['label' => 'Installation & Upgrade', 'count' => (int) ($wssByType['installation-upgrade'] ?? 0)],
                        ],
                    ],
                    [
                        'key' => 'hss',
                        'title' => 'Health Surveillance System (HSS)',
                        'services' => [
                            ['label' => 'Disease Monitoring', 'count' => (int) ($hssByType['disease-monitoring'] ?? 0)],
                            ['label' => 'Environmental Monitoring', 'count' => (int) ($hssByType['environmental-monitoring'] ?? 0)],
                        ],
                    ],
                ];

                // Recent admin activity
                function timeAgo($ts)
                {
                    $t = is_numeric($ts) ? (int) $ts : strtotime((string) $ts);
                    if (!$t)
                        return 'just now';
                    $d = max(1, time() - $t);
                    if ($d < 60)
                        return $d . ' seconds ago';
                    if ($d < 3600)
                        return floor($d / 60) . ' minutes ago';
                    if ($d < 86400)
                        return floor($d / 3600) . ' hours ago';
                    return floor($d / 86400) . ' days ago';
                }
                try {
                    $recentAdmin = $database->getAuditLogsPaginated(['role' => 'admin'], 5, 0);
                } catch (Throwable $e) {
                    $recentAdmin = [];
                }
                ?>

                <!-- User Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Doctors Card -->
                    <div
                        class="rounded-2xl bg-white dark:bg-slate-900/50 border border-emerald-100 dark:border-emerald-500/40 shadow-md hover:shadow-lg transition-all duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-200">Active</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-slate-200 mb-1">Doctors</h3>
                            <p class="text-3xl font-semibold text-emerald-600 dark:text-emerald-200">
                                <?php echo number_format($roleCounts['doctor']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Nurses Card -->
                    <div
                        class="rounded-2xl bg-white dark:bg-slate-900/50 border border-sky-100 dark:border-sky-500/40 shadow-md hover:shadow-lg transition-all duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-900/40 dark:text-sky-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 21l-7.682-7.682a4.5 4.5 0 010-6.364z" />
                                    </svg>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-200">Active</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-slate-200 mb-1">Nurses</h3>
                            <p class="text-3xl font-semibold text-sky-600 dark:text-sky-200">
                                <?php echo number_format($roleCounts['nurse']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Inspectors Card -->
                    <div
                        class="rounded-2xl bg-white dark:bg-slate-900/50 border border-indigo-100 dark:border-indigo-500/40 shadow-md hover:shadow-lg transition-all duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-200">Active</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-slate-200 mb-1">Inspectors</h3>
                            <p class="text-3xl font-semibold text-indigo-600 dark:text-indigo-200">
                                <?php echo number_format($roleCounts['inspector']); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Citizens Card -->
                    <div
                        class="rounded-2xl bg-white dark:bg-slate-900/50 border border-amber-100 dark:border-amber-500/40 shadow-md hover:shadow-lg transition-all duration-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div
                                    class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/40 dark:text-amber-200">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M12 6a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm-1.5 8a4 4 0 0 0-4 4 2 2 0 0 0 2 2h7a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-3Zm6.82-3.096a5.51 5.51 0 0 0-2.797-6.293 3.5 3.5 0 1 1 2.796 6.292ZM19.5 18h.5a2 2 0 0 0 2-2 4 4 0 0 0-4-4h-1.1a5.503 5.503 0 0 1-.471.762A5.998 5.998 0 0 1 19.5 18ZM4 7.5a3.5 3.5 0 0 1 5.477-2.889 5.5 5.5 0 0 0-2.796 6.293A3.501 3.501 0 0 1 4 7.5ZM7.1 12H6a4 4 0 0 0-4 4 2 2 0 0 0 2 2h.5a5.998 5.998 0 0 1 3.071-5.238A5.505 5.505 0 0 1 7.1 12Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-200">Active</span>
                            </div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-slate-200 mb-1">Citizens</h3>
                            <p class="text-3xl font-semibold text-amber-600 dark:text-amber-200">
                                <?php echo number_format($roleCounts['citizen']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests per Service -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php foreach ($pendingModules as $mod): ?>
                        <div
                            class="rounded-2xl bg-white dark:bg-slate-900/50 border border-blue-100 dark:border-slate-700/60 p-6 shadow-md hover:shadow-lg transition-all duration-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white"><?php echo $mod['title']; ?>
                                </h3>
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-200">Pending</span>
                            </div>
                            <div class="space-y-3">
                                <?php foreach ($mod['services'] as $svc): ?>
                                    <div
                                        class="flex items-center justify-between p-3 rounded-xl bg-sky-50 border border-sky-100 hover:bg-sky-100 transition-colors dark:bg-slate-800/60 dark:border-slate-700 dark:hover:bg-slate-800">
                                        <span
                                            class="text-sm font-medium text-sky-700 dark:text-slate-100"><?php echo $svc['label']; ?></span>
                                        <span
                                            class="inline-flex items-center justify-center px-2.5 py-1 text-sm font-semibold rounded-full bg-white text-sky-600 border border-sky-100 dark:bg-slate-900/60 dark:text-sky-200 dark:border-sky-700"><?php echo number_format((int) $svc['count']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Quick Actions and Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Quick Actions -->
                    <div
                        class="rounded-2xl bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 p-6 shadow-md">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100">Quick Actions</h3>
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- Health Center Services -->
                            <button onclick="location.href='DashboardOverview_new.php?page=hcs'"
                                class="w-full text-left p-4 rounded-xl border border-emerald-100 dark:border-emerald-600/50 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200 hover:bg-emerald-100 hover:border-emerald-200 dark:hover:bg-emerald-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-emerald-900/40 border border-emerald-100 dark:border-emerald-600 mr-3">
                                        <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="8" stroke-width="2" fill="none"></circle>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v8m-4-4h8"></path>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Health Center Services</span>
                                </div>
                            </button>
                            <!-- Sanitation & Permits -->
                            <button onclick="location.href='DashboardOverview_new.php?page=spi'"
                                class="w-full text-left p-4 rounded-xl border border-lime-100 dark:border-lime-600/50 bg-lime-50 text-lime-700 dark:bg-lime-900/50 dark:text-lime-200 hover:bg-lime-100 hover:border-lime-200 dark:hover:bg-lime-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-lime-900/40 border border-lime-100 dark:border-lime-600 mr-3">
                                        <svg class="w-5 h-5 text-lime-500 dark:text-lime-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 22a10 10 0 110-20 10 10 0 010 20z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Sanitation & Permits</span>
                                </div>
                            </button>
                            <!-- Immunization and Nutrition Tracker -->
                            <button onclick="location.href='DashboardOverview_new.php?page=int'"
                                class="w-full text-left p-4 rounded-xl border border-rose-100 dark:border-rose-600/50 bg-rose-50 text-rose-700 dark:bg-rose-900/50 dark:text-rose-200 hover:bg-rose-100 hover:border-rose-200 dark:hover:bg-rose-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-rose-900/40 border border-rose-100 dark:border-rose-600 mr-3">
                                        <svg class="w-5 h-5 text-rose-500 dark:text-rose-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4"></path>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Immunization and Nutrition Tracker</span>
                                </div>
                            </button>
                            <!-- Wastewater and Septic Services -->
                            <button onclick="location.href='DashboardOverview_new.php?page=wss'"
                                class="w-full text-left p-4 rounded-xl border border-sky-100 dark:border-sky-600/50 bg-sky-50 text-sky-700 dark:bg-sky-900/50 dark:text-sky-200 hover:bg-sky-100 hover:border-sky-200 dark:hover:bg-sky-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-sky-900/40 border border-sky-100 dark:border-sky-600 mr-3">
                                        <svg class="w-5 h-5 text-sky-500 dark:text-sky-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z">
                                            </path>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Wastewater and Septic Services</span>
                                </div>
                            </button>
                            <!-- Health and Surveillance System -->
                            <button onclick="location.href='DashboardOverview_new.php?page=hss'"
                                class="w-full text-left p-4 rounded-xl border border-teal-100 dark:border-teal-600/50 bg-teal-50 text-teal-700 dark:bg-teal-900/50 dark:text-teal-200 hover:bg-teal-100 hover:border-teal-200 dark:hover:bg-teal-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-teal-900/40 border border-teal-100 dark:border-teal-600 mr-3">
                                        <svg class="w-5 h-5 text-teal-500 dark:text-teal-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Health and Surveillance System</span>
                                </div>
                            </button>
                            <!-- System Settings -->
                            <button onclick="location.href='DashboardOverview_new.php?page=dashboard#settings'"
                                class="w-full text-left p-4 rounded-xl border border-amber-100 dark:border-amber-600/50 bg-amber-50 text-amber-700 dark:bg-amber-900/50 dark:text-amber-200 hover:bg-amber-100 hover:border-amber-200 dark:hover:bg-amber-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-amber-900/40 border border-amber-100 dark:border-amber-600 mr-3">
                                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">System Settings</span>
                                </div>
                            </button>
                            <!-- User Verification -->
                            <button onclick="location.href='verification_user_profile.php'"
                                class="w-full text-left p-4 rounded-xl border border-green-100 dark:border-green-600/50 bg-green-50 text-green-700 dark:bg-green-900/50 dark:text-green-200 hover:bg-green-100 hover:border-green-200 dark:hover:bg-green-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-green-900/40 border border-green-100 dark:border-green-600 mr-3">
                                        <svg class="w-5 h-5 text-green-500 dark:text-green-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5a3 3 0 016 0v2" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 11h14" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 11v7a2 2 0 002 2h6a2 2 0 002-2v-7" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 15l2 2 4-4" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">User Verification</span>
                                </div>
                            </button>
                            <!-- Contact Messages -->
                            <button onclick="location.href='DashboardOverview_new.php?page=contact&view=messages'"
                                class="w-full text-left p-4 rounded-xl border border-blue-100 dark:border-blue-600/50 bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-200 hover:bg-blue-100 hover:border-blue-200 dark:hover:bg-blue-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-blue-900/40 border border-blue-100 dark:border-blue-600 mr-3">
                                        <svg class="w-5 h-5 text-blue-500 dark:text-blue-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h16a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Contact Messages</span>
                                </div>
                            </button>
                            <!-- Account Settings -->
                            <button onclick="location.href='DashboardOverview_new.php?page=account&view=settings'"
                                class="w-full text-left p-4 rounded-xl border border-violet-100 dark:border-violet-600/50 bg-violet-50 text-violet-700 dark:bg-violet-900/50 dark:text-violet-200 hover:bg-violet-100 hover:border-violet-200 dark:hover:bg-violet-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-violet-900/40 border border-violet-100 dark:border-violet-600 mr-3">
                                        <svg class="w-5 h-5 text-violet-500 dark:text-violet-300" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M17 10v1.126c.367.095.714.24 1.032.428l.796-.797 1.415 1.415-.797.796c.188.318.333.665.428 1.032H21v2h-1.126c-.095.367-.24.714-.428 1.032l.797.796-1.415 1.415-.796-.797a3.979 3.979 0 0 1-1.032.428V20h-2v-1.126a3.977 3.977 0 0 1-1.032-.428l-.796.797-1.415-1.415.797-.796A3.975 3.975 0 0 1 12.126 16H11v-2h1.126c.095-.367.24-.714.428-1.032l-.797-.796 1.415-1.415.796.797A3.977 3.977 0 0 1 15 11.126V10h2Zm.406 3.578.016.016c.354.358.574.85.578 1.392v.028a2 2 0 0 1-3.409 1.406l-.01-.012a2 2 0 0 1 2.826-2.83ZM5 8a4 4 0 1 1 7.938.703 7.029 7.029 0 0 0-3.235 3.235A4 4 0 0 1 5 8Zm4.29 5H7a4 4 0 0 0-4 4v1a2 2 0 0 0 2 2h6.101A6.979 6.979 0 0 1 9 15c0-.695.101-1.366.29-2Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Account Settings</span>
                                </div>
                            </button>
                            <!-- Audit Logs -->
                            <button onclick="location.href='DashboardOverview_new.php?page=audit&view=logs'"
                                class="w-full text-left p-4 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 text-slate-700 dark:bg-slate-900/50 dark:text-slate-200 hover:bg-slate-100 hover:border-slate-300 dark:hover:bg-slate-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-slate-900/40 border border-slate-200 dark:border-slate-600 mr-3">
                                        <svg class="w-5 h-5 text-slate-500 dark:text-slate-200" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 5h14M5 9h10M5 13h14M5 17h10" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Audit Logs</span>
                                </div>
                            </button>
                            <!-- Restore Data -->
                            <button onclick="location.href='DashboardOverview_new.php?page=restore&view=data'"
                                class="w-full text-left p-4 rounded-xl border border-orange-100 dark:border-orange-600/50 bg-orange-50 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200 hover:bg-orange-100 hover:border-orange-200 dark:hover:bg-orange-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-orange-900/40 border border-orange-100 dark:border-orange-600 mr-3">
                                        <svg class="w-5 h-5 text-orange-500 dark:text-orange-300" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v8m0 0l-3-3m3 3l3-3" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 11-9-9" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Restore Data</span>
                                </div>
                            </button>
                            <!-- Add User -->
                            <button onclick="location.href='add_user.php'"
                                class="w-full text-left p-4 rounded-xl border border-purple-100 dark:border-purple-600/50 bg-purple-50 text-purple-700 dark:bg-purple-900/50 dark:text-purple-200 hover:bg-purple-100 hover:border-purple-200 dark:hover:bg-purple-900/40 shadow-sm">
                                <div class="flex items-center">
                                    <span
                                        class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-purple-900/40 border border-purple-100 dark:border-purple-600 mr-3">
                                        <svg class="w-5 h-5 text-purple-500 dark:text-purple-300" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M16 12h4m-2 2v-4M4 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </span>
                                    <span class="text-sm font-semibold">Add User</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div
                        class="rounded-2xl bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 p-6 shadow-md">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100">Recent Activity</h3>
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="space-y-3">
                            <?php if (empty($recentAdmin)): ?>
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-slate-400">No recent admin activity</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentAdmin as $ra): ?>
                                    <?php
                                    $fn = trim(((string) ($ra['first_name'] ?? '')) . ' ' . ((string) ($ra['last_name'] ?? '')));
                                    $act = strtolower((string) ($ra['action'] ?? ''));
                                    $dot = ($act === 'login' ? 'bg-emerald-400' : ($act === 'logout' ? 'bg-slate-400' : ($act === 'profile_update' ? 'bg-amber-400' : ($act === 'password_change' ? 'bg-sky-400' : 'bg-indigo-400'))));
                                    ?>
                                    <div
                                        class="flex items-start space-x-3 p-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100 transition-all dark:border-slate-700 dark:bg-slate-900/40 dark:hover:bg-slate-900/60">
                                        <div class="w-3 h-3 rounded-full mt-1.5 <?php echo $dot; ?>"></div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-700 dark:text-slate-100">
                                                <?php echo htmlspecialchars((string) ($ra['action'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                <?php echo $fn ? ' - ' . htmlspecialchars($fn, ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                                <?php echo timeAgo($ra['created_at'] ?? ''); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</main>

<script>
    // Sidebar functionality
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const toggleButton = document.getElementById('sidebar-toggle');

        sidebar.classList.toggle('sidebar-collapsed');

        if (sidebar.classList.contains('sidebar-collapsed')) {
            // Sidebar is collapsed
            mainContent.classList.remove('ml-64');
            mainContent.classList.add('ml-16');
            toggleButton.classList.remove('ml-64');
            toggleButton.classList.add('ml-16');
        } else {
            // Sidebar is expanded
            mainContent.classList.remove('ml-16');
            mainContent.classList.add('ml-64');
            toggleButton.classList.remove('ml-16');
            toggleButton.classList.add('ml-64');
        }
    }

    // Sidebar toggle button
    document.getElementById('sidebar-toggle').addEventListener('click', toggleSidebar);

    // Dropdown functionality
    function toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const arrow = document.getElementById(dropdownId.replace('-dropdown', '-arrow'));
        if (dropdown) dropdown.classList.toggle('hidden');
        if (arrow) arrow.classList.toggle('rotate-180');
    }

    // Theme toggle is initialized in adminheader.php

</script>
</body>

</html>