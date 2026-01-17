<?php
require_once __DIR__ . '/../include/database.php';
startSecureSession();
requireRole('admin');
// Ensure per-admin read-tracking table exists and set current user id
try {
    $db->exec("CREATE TABLE IF NOT EXISTS admin_notification_reads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        kind VARCHAR(32) NOT NULL,
        item_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_user_item (user_id, kind, item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Throwable $e) { /* ignore */
}
$currentAdminId = (int) ($_SESSION['user_id'] ?? 0);
// Determine absolute admin base path for links (works from nested admin pages)
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($script, '/admin/');
$adminBase = ($pos !== false) ? substr($script, 0, $pos + 7) : '/admin/';
// Notifications: pending submissions from citizens
function adminTimeAgo($ts)
{
    $t = is_numeric($ts) ? (int) $ts : strtotime((string) $ts);
    if (!$t)
        return 'just now';
    $d = max(1, time() - $t);
    if ($d < 60)
        return $d . 's ago';
    if ($d < 3600)
        return floor($d / 60) . 'm ago';
    if ($d < 86400)
        return floor($d / 3600) . 'h ago';
    return floor($d / 86400) . 'd ago';
}
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM appointments a
        LEFT JOIN admin_notification_reads r ON r.user_id = :uid AND r.kind = 'hcs_appointment' AND r.item_id = a.id
        WHERE a.status='pending' AND a.deleted_at IS NULL AND r.id IS NULL");
    $stmt->execute([':uid' => $currentAdminId]);
    $apCnt = (int) $stmt->fetchColumn();
} catch (Throwable $e) {
    $apCnt = 0;
}
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM service_requests s
        LEFT JOIN admin_notification_reads r ON r.user_id = :uid AND r.kind = 'service_request' AND r.item_id = s.id
        WHERE s.status='pending' AND s.deleted_at IS NULL AND r.id IS NULL");
    $stmt->execute([':uid' => $currentAdminId]);
    $srCnt = (int) $stmt->fetchColumn();
} catch (Throwable $e) {
    $srCnt = 0;
}
$notifCount = $apCnt + $srCnt;
try {
    $stmt = $db->prepare("SELECT a.id, 'hcs_appointment' AS kind, a.appointment_type AS subtype, CONCAT(a.first_name,' ',a.last_name) AS name, a.created_at
        FROM appointments a
        LEFT JOIN admin_notification_reads r ON r.user_id = :uid AND r.kind = 'hcs_appointment' AND r.item_id = a.id
        WHERE a.status='pending' AND a.deleted_at IS NULL AND r.id IS NULL
        ORDER BY a.created_at DESC LIMIT 10");
    $stmt->execute([':uid' => $currentAdminId]);
    $apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $apps = [];
}
try {
    $stmt = $db->prepare("SELECT s.id, 'service_request' AS kind, s.service_type AS subtype, s.full_name AS name, s.created_at
        FROM service_requests s
        LEFT JOIN admin_notification_reads r ON r.user_id = :uid AND r.kind = 'service_request' AND r.item_id = s.id
        WHERE s.status='pending' AND s.deleted_at IS NULL AND r.id IS NULL
        ORDER BY s.created_at DESC LIMIT 10");
    $stmt->execute([':uid' => $currentAdminId]);
    $reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $reqs = [];
}
$notifications = array_merge($apps, $reqs);
usort($notifications, function ($a, $b) {
    return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
});
$notifications = array_slice($notifications, 0, 10);
$typeLabels = [
    'medical-consultation' => 'HCS: Consultation',
    'emergency-care' => 'HCS: Emergency Care',
    'preventive-care' => 'HCS: Preventive Care',
    'business-permit' => 'SPI: Business Permit',
    'health-inspection' => 'SPI: Health Inspection',
    'vaccination' => 'INT: Vaccination',
    'nutrition-monitoring' => 'INT: Nutrition Monitoring',
    'system-inspection' => 'WSS: System Inspection',
    'maintenance-service' => 'WSS: Maintenance',
    'installation-upgrade' => 'WSS: Installation & Upgrade',
    'disease-monitoring' => 'HSS: Disease Monitoring',
    'environmental-monitoring' => 'HSS: Environmental Monitoring',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Officer / Admin Dashboard - Health & Sanitation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#4CAF50',
                            light: '#66BB6A',
                            dark: '#2E7D32'
                        },
                        secondary: {
                            DEFAULT: '#4A90E2',
                            light: '#73B4F5',
                            dark: '#2F6BB5'
                        },
                        accent: {
                            DEFAULT: '#FDA811',
                            light: '#FDCB6A',
                            dark: '#DB8807'
                        },
                        background: '#FBFBFB',
                        surface: '#FFFFFF',
                        muted: '#6B7280',
                        border: '#E5E7EB',
                        'dark-bg': '#101827',
                        'dark-card': '#1f2937',
                        'dark-sidebar': '#0f172a'
                    }
                }
            }
        }
    </script>
    <script>
        // Base path for admin routes (for API beacons)
        window.__adminBase = <?php echo json_encode($adminBase, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    </script>
    <script>
        // Global admin sidebar helpers so every admin page has working navigation
        (function () {
            function toggleSidebar() {
                var sidebar = document.getElementById('sidebar');
                var mainContent = document.getElementById('main-content');
                var toggleButton = document.getElementById('sidebar-toggle');
                if (!sidebar) return;
                sidebar.classList.toggle('sidebar-collapsed');
                var collapsed = sidebar.classList.contains('sidebar-collapsed');
                if (mainContent) {
                    mainContent.classList.toggle('ml-64', !collapsed);
                    mainContent.classList.toggle('ml-16', collapsed);
                }
                if (toggleButton) {
                    toggleButton.classList.toggle('ml-64', !collapsed);
                    toggleButton.classList.toggle('ml-16', collapsed);
                }
            }
            function toggleDropdown(id) {
                // Prefer elements within the actual sidebar to avoid duplicate-ID collisions
                var root = document.getElementById('sidebar') || document;
                var dds = root.querySelectorAll("[id='" + id + "']");
                var arrows = root.querySelectorAll("[id='" + id.replace('-dropdown', '-arrow') + "']");
                if (!dds.length && root !== document) {
                    // Fallback to document in case markup structure changes
                    dds = document.querySelectorAll("[id='" + id + "']");
                    arrows = document.querySelectorAll("[id='" + id.replace('-dropdown', '-arrow') + "']");
                }
                dds.forEach(function (dd) { dd.classList.toggle('hidden'); });
                arrows.forEach(function (ar) { ar.classList.toggle('rotate-180'); });
            }
            window.toggleSidebar = toggleSidebar;
            window.toggleDropdown = toggleDropdown;
            document.addEventListener('DOMContentLoaded', function () {
                var btn = document.getElementById('sidebar-toggle');
                if (btn) btn.addEventListener('click', toggleSidebar);
            });
        })();
    </script>
    <script>
        (function () {
            function initNotifDropdown() {
                var bell = document.getElementById('notif-bell');
                var dd = document.getElementById('notif-dropdown');
                if (!(bell && dd)) return;
                function closeDD() { if (!dd.classList.contains('hidden')) dd.classList.add('hidden'); }
                document.addEventListener('click', function (e) {
                    var t = e.target;
                    if (t !== dd && !dd.contains(t) && t !== bell && !bell.contains(t)) { closeDD(); }
                });
                document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDD(); });
                // Global fallback used by inline onclick
                window.__toggleNotif = function (e) { e.preventDefault(); e.stopPropagation(); dd.classList.toggle('hidden'); return false; };
                // Read beacon
                dd.addEventListener('click', function (e) {
                    var a = e.target.closest('a[data-notif-kind][data-notif-id]');
                    if (!a) return;
                    try {
                        var kind = a.getAttribute('data-notif-kind');
                        var id = a.getAttribute('data-notif-id');
                        var fd = new FormData(); fd.append('action', 'read'); fd.append('kind', kind); fd.append('id', id);
                        navigator.sendBeacon(window.__adminBase + 'api/notifications.php', fd);
                        var badge = document.getElementById('notif-badge');
                        var total = document.getElementById('notif-total');
                        var cur = badge ? parseInt(badge.textContent || '0', 10) : 0;
                        if (cur > 0 && badge) { badge.textContent = (cur - 1); if (cur - 1 <= 0) { badge.remove(); } }
                        if (total) { var num = parseInt(total.textContent, 10); if (!isNaN(num) && num > 0) total.textContent = (num - 1) + ' total'; }
                    } catch (err) { /* ignore */ }
                });
            }
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initNotifDropdown);
            } else {
                initNotifDropdown();
            }
        })();
    </script>
    <!-- Preflight: apply saved theme early to avoid flicker -->
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme') || 'dark';
                var root = document.documentElement;
                // Apply theme instantly without transition
                root.style.transition = 'none';
                if (saved === 'dark') {
                    root.classList.add('dark');
                } else {
                    root.classList.remove('dark');
                }
                // Re-enable transitions after a frame
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        root.style.transition = '';
                    });
                });
            } catch (e) {
                // fallback: default dark
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
        :root {
            --color-background: #FBFBFB;
            --color-surface: #FFFFFF;
            --color-border: #E5E7EB;
            --color-muted: #6B7280;
            --color-text: #111827;
            --color-primary: #4CAF50;
            --color-primary-hover: #43A047;
            --color-secondary: #4A90E2;
            --color-secondary-hover: #357ABD;
            --color-accent: #FDA811;
            --color-accent-hover: #E08C08;
        }

        .dark {
            --color-background: #0f172a;
            --color-surface: #1e293b;
            --color-border: #334155;
            --color-muted: #94A3B8;
            --color-text: #F8FAFC;
            --color-primary: #4CAF50;
            --color-primary-hover: #66BB6A;
            --color-secondary: #73B4F5;
            --color-secondary-hover: #4A90E2;
            --color-accent: #FDBA4D;
            --color-accent-hover: #F59E0B;
        }

        .bg-background {
            background-color: var(--color-background) !important;
        }

        .bg-surface {
            background-color: var(--color-surface) !important;
        }

        .text-default {
            color: var(--color-text) !important;
        }

        .text-muted {
            color: var(--color-muted) !important;
        }

        .border-subtle {
            border-color: var(--color-border) !important;
        }

        .admin-card {
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
        }

        .dark .admin-card {
            background-color: var(--color-surface);
            border-color: var(--color-border);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.45);
        }

        .admin-quick-action {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            text-align: left;
            padding: 0.9rem 1.1rem;
            border-radius: 0.85rem;
            background-color: var(--color-surface);
            border: 1px solid var(--color-border);
            color: var(--color-text);
            gap: 0.75rem;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background-color 0.15s ease;
        }

        .admin-quick-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
        }

        .dark .admin-quick-action {
            background-color: var(--color-surface);
            border-color: var(--color-border);
        }

        .icon-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            margin-right: 0.85rem;
            flex-shrink: 0;
            color: var(--color-primary);
            background-color: rgba(76, 175, 80, 0.12);
        }

        .dark .icon-pill {
            background-color: rgba(102, 187, 106, 0.2);
        }

        .icon-pill.secondary {
            color: var(--color-secondary);
            background-color: rgba(74, 144, 226, 0.15);
        }

        .dark .icon-pill.secondary {
            background-color: rgba(115, 180, 245, 0.25);
        }

        .icon-pill.accent {
            color: var(--color-accent);
            background-color: rgba(253, 168, 17, 0.18);
        }

        .dark .icon-pill.accent {
            background-color: rgba(253, 186, 77, 0.28);
        }

        .admin-quick-action.primary:hover {
            border-color: var(--color-primary);
        }

        .admin-quick-action.secondary:hover {
            border-color: var(--color-secondary);
        }

        .admin-quick-action.accent:hover {
            border-color: var(--color-accent);
        }

        .admin-quick-action strong {
            font-weight: 600;
        }

        .sidebar-link {
            color: var(--color-muted);
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .sidebar-link:hover {
            background-color: rgba(74, 144, 226, 0.12);
            color: var(--color-text);
        }

        .sidebar-link.active {
            background-color: var(--color-primary);
            color: #ffffff !important;
        }

        .dark .sidebar-link {
            color: #cbd5f5;
        }

        .dark .sidebar-link:hover {
            background-color: rgba(115, 180, 245, 0.22);
            color: #ffffff;
        }

        .dark .sidebar-link.active {
            background-color: var(--color-secondary);
        }

        .sidebar-sub-link {
            display: block;
            border-radius: 0.75rem;
            color: var(--color-muted);
            padding: 0.5rem 0.75rem;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .sidebar-sub-link:hover {
            background-color: rgba(74, 144, 226, 0.12);
            color: var(--color-text);
        }

        .sidebar-sub-link.active {
            background-color: var(--color-primary);
            color: #ffffff !important;
        }

        .dark .sidebar-sub-link {
            color: #cbd5f5;
        }

        .dark .sidebar-sub-link:hover {
            background-color: rgba(115, 180, 245, 0.22);
            color: #ffffff;
        }

        .dark .sidebar-sub-link.active {
            background-color: var(--color-secondary);
        }

        /* Custom scrollbar for sidebar */
        #sidebar::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .dark #sidebar::-webkit-scrollbar-thumb {
            background: #4a5568;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .dark #sidebar::-webkit-scrollbar-thumb:hover {
            background: #2d3748;
        }

        #sidebar {
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        /* Collapsed sidebar styles */
        .sidebar-collapsed {
            width: 4rem !important;
        }

        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed span:not(.sidebar-icon):not(svg),
        .sidebar-collapsed h2,
        .sidebar-collapsed p,
        .sidebar-collapsed div.sidebar-text {
            display: none !important;
        }

        .sidebar-collapsed .flex.items-center {
            justify-content: center !important;
        }

        .sidebar-collapsed .sidebar-icon {
            margin: 0 !important;
        }

        .sidebar-collapsed .p-4.border-b {
            justify-content: center !important;
        }

        .sidebar-collapsed button,
        .sidebar-collapsed a {
            justify-content: center !important;
            padding: 0.75rem !important;
        }

        .sidebar-collapsed svg {
            margin: 0 !important;
        }

        /* Hide dropdown arrows when collapsed */
        .sidebar-collapsed #hcs-arrow,
        .sidebar-collapsed #spi-arrow,
        .sidebar-collapsed #int-arrow,
        .sidebar-collapsed #wss-arrow,
        .sidebar-collapsed #hss-arrow,
        .sidebar-collapsed #settings-arrow {
            display: none !important;
        }

        /* Hide dropdowns when collapsed */
        .sidebar-collapsed [id$="-dropdown"] {
            display: none !important;
        }

        /* Perfect icon centering */
        .sidebar-collapsed .w-5.h-5 {
            margin: 0 auto !important;
        }

        /* Light theme overrides (when html does NOT have .dark) */
        html:not(.dark) body {
            background-color: #f8fafc;
        }

        html:not(.dark) nav.bg-dark-bg {
            background-color: #ffffff !important;
            border-color: #e5e7eb !important;
        }

        html:not(.dark) #sidebar {
            background-color: #f1f5f9 !important;
            border-right: 1px solid #e5e7eb !important;
        }

        html:not(.dark) .bg-dark-bg {
            background-color: #f8fafc !important;
        }

        html:not(.dark) .bg-dark-card {
            background-color: #ffffff !important;
        }

        html:not(.dark) .border-gray-700,
        html:not(.dark) .border-gray-600 {
            border-color: #e5e7eb !important;
        }

        /* Text adjustments */
        html:not(.dark) .text-white {
            color: #111827 !important;
        }

        html:not(.dark) .text-gray-400 {
            color: #4b5563 !important;
        }

        html:not(.dark) .text-gray-300 {
            color: #374151 !important;
        }

        html:not(.dark) #sidebar .text-white {
            color: #111827 !important;
        }

        /* Keep button text readable on colored/dark backgrounds */
        html:not(.dark) .bg-primary .text-white,
        html:not(.dark) .bg-blue-600 .text-white,
        html:not(.dark) .bg-green-600 .text-white,
        html:not(.dark) .bg-red-600 .text-white,
        html:not(.dark) .bg-orange-600 .text-white,
        html:not(.dark) .bg-yellow-600 .text-white,
        html:not(.dark) .bg-gray-700 .text-white,
        html:not(.dark) .bg-gray-800 .text-white {
            color: #ffffff !important;
        }

        /* Text adjustments inside cards and main content */
        html:not(.dark) .bg-dark-card h1,
        html:not(.dark) .bg-dark-card h2,
        html:not(.dark) .bg-dark-card h3,
        html:not(.dark) .bg-dark-card p,
        html:not(.dark) .bg-dark-card span,
        html:not(.dark) #content-area h1,
        html:not(.dark) #content-area h2,
        html:not(.dark) #content-area h3,
        html:not(.dark) #content-area p,
        html:not(.dark) #content-area span {
            color: #111827 !important;
        }

        /* Keep button text readable on colored backgrounds */
        html:not(.dark) .bg-blue-600 .text-white,
        html:not(.dark) .bg-green-600 .text-white,
        html:not(.dark) .bg-red-600 .text-white,
        html:not(.dark) .bg-orange-600 .text-white,
        html:not(.dark) .bg-yellow-600 .text-white {
            color: #ffffff !important;
        }

        /* Light mode primary palette (blue) */
        /* Replace orange primary with blue for light mode without affecting dark mode */
        html:not(.dark) .bg-primary {
            background-color: #2563eb !important;
        }

        /* blue-600 */
        html:not(.dark) .hover\:bg-primary:hover {
            background-color: #1d4ed8 !important;
        }

        /* blue-700 */
        html:not(.dark) .text-primary {
            color: #2563eb !important;
        }

        html:not(.dark) .border-primary {
            border-color: #2563eb !important;
        }

        html:not(.dark) .focus\:border-primary:focus {
            border-color: #2563eb !important;
        }

        html:not(.dark) .ring-primary {
            --tw-ring-color: #2563eb !important;
        }

        html:not(.dark) .focus\:ring-primary:focus {
            --tw-ring-color: #2563eb !important;
        }

        html:not(.dark) .focus\:ring-2:focus {
            box-shadow: 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color), var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-shadow, 0 0 #0000) !important;
        }
    </style>
</head>

<body class="bg-background dark:bg-dark-bg min-h-screen text-default">
    <!-- Navigation -->
    <nav class="bg-surface dark:bg-dark-bg border-b border-subtle dark:border-gray-700 fixed w-full top-0 z-40">
        <div class="max-w-full mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <!-- Toggle Button -->
                    <button id="sidebar-toggle" onclick="toggleSidebar()"
                        class="p-2 text-gray-400 hover:text-white mr-4 transition-colors ml-64">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex items-center">
                        <div class="ml-3">
                            <h1 class="text-xl font-bold text-white">Health &amp; Sanitation</h1>
                            <p class="text-sm text-gray-400">Admin Portal</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Search Bar -->
                    <div class="relative z-50 pointer-events-auto">
                        <input type="text" placeholder="Search..."
                            class="bg-background dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 border border-gray-200 dark:border-transparent rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-primary w-64">
                        <svg class="absolute right-3 top-2.5 h-5 w-5 text-secondary dark:text-gray-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <!-- Notification -->
                    <div class="relative z-50 pointer-events-auto">
                        <button id="notif-bell" type="button"
                            onclick="if(window.__toggleNotif){return window.__toggleNotif(event);} (function(e){e.preventDefault();e.stopPropagation();var d=document.getElementById('notif-dropdown');if(d){d.classList.toggle('hidden');}})(event)"
                            class="relative p-2 text-gray-400 hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-5 5v-5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.73 21a2 2 0 01-3.46 0"></path>
                            </svg>
                            <?php if ($notifCount > 0): ?>
                                <span id="notif-badge"
                                    class="absolute -top-1 -right-1 h-5 min-w-[1.25rem] px-1 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium"><?php echo (int) $notifCount; ?></span>
                            <?php endif; ?>
                        </button>
                        <div id="notif-dropdown"
                            class="hidden absolute right-0 mt-2 w-80 bg-dark-card border border-gray-700 rounded-lg shadow-lg overflow-hidden z-50">
                            <div class="p-3 border-b border-gray-700 flex items-center justify-between">
                                <span class="text-sm font-semibold text-white">New Submissions</span>
                                <span id="notif-total" class="text-xs text-gray-400"><?php echo (int) $notifCount; ?>
                                    total</span>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <?php if (empty($notifications)): ?>
                                    <div class="p-4 text-sm text-gray-400">No new submissions.</div>
                                <?php else:
                                    foreach ($notifications as $n): ?>
                                        <?php
                                        $kind = (string) ($n['kind'] ?? '');
                                        $sub = (string) ($n['subtype'] ?? '');
                                        $name = trim((string) ($n['name'] ?? ''));
                                        $when = (string) ($n['created_at'] ?? '');
                                        $title = ($kind === 'hcs_appointment') ? 'HCS: Appointment' : ($typeLabels[$sub] ?? ucfirst(str_replace('-', ' ', $sub)));
                                        // Destination URL per module
                                        $serviceRoutes = [
                                            // HCS consultations go to HCS consultation view
                                            'medical-consultation' => 'DashboardOverview_new.php?page=hcs&view=consultation',
                                            'emergency-care' => 'DashboardOverview_new.php?page=hcs&view=consultation',
                                            'preventive-care' => 'DashboardOverview_new.php?page=hcs&view=consultation',
                                            // SPI
                                            'business-permit' => 'DashboardOverview_new.php?page=spi&view=permits',
                                            'health-inspection' => 'DashboardOverview_new.php?page=spi&view=sanitation',
                                            // INT
                                            'vaccination' => 'DashboardOverview_new.php?page=int&view=immunization',
                                            'nutrition-monitoring' => 'DashboardOverview_new.php?page=int&view=nutrition',
                                            // WSS
                                            'system-inspection' => 'DashboardOverview_new.php?page=wss&view=septic',
                                            'maintenance-service' => 'DashboardOverview_new.php?page=wss&view=assessment',
                                            'installation-upgrade' => 'DashboardOverview_new.php?page=wss&view=assessment',
                                            // HSS
                                            'disease-monitoring' => 'DashboardOverview_new.php?page=hss&view=disease',
                                            'environmental-monitoring' => 'DashboardOverview_new.php?page=hss&view=environmental',
                                        ];
                                        if ($kind === 'hcs_appointment') {
                                            $url = 'DashboardOverview_new.php?page=hcs&view=appointment';
                                        } else {
                                            $url = $serviceRoutes[$sub] ?? 'DashboardOverview_new.php';
                                        }
                                        ?>
                                        <a href="<?php echo htmlspecialchars($adminBase . $url, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-notif-kind="<?php echo htmlspecialchars($kind, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-notif-id="<?php echo (int) ($n['id'] ?? 0); ?>"
                                            class="block p-3 hover:bg-gray-700/40 cursor-pointer">
                                            <div class="flex items-start">
                                                <div class="w-2 h-2 rounded-full mt-2 bg-yellow-500"></div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-white">
                                                        <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php echo $name ? ' - ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : ''; ?>
                                                    </p>
                                                    <p class="text-xs text-gray-400"><?php echo adminTimeAgo($when); ?></p>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endforeach; endif; ?>
                            </div>
                            <div class="p-2 border-t border-gray-700 text-center">
                                <a href="<?php echo htmlspecialchars($adminBase . 'DashboardOverview_new.php?page=notifications&view=list', ENT_QUOTES, 'UTF-8'); ?>"
                                    class="text-xs text-blue-400 hover:text-blue-300">View all notifications</a>
                            </div>
                        </div>
                    </div>
                    <!-- Theme toggle -->
                    <button class="p-2 text-gray-400 hover:text-white" id="theme-toggle" aria-label="Toggle theme">
                        <!-- Sun icon (light mode) -->
                        <svg id="theme-icon-sun" class="h-6 w-6 hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05L5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z">
                            </path>
                        </svg>
                        <!-- Moon icon (dark mode) -->
                        <svg id="theme-icon-moon" class="h-6 w-6 hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 1112.646 3.646 7 7 0 0020.354 15.354z"></path>
                        </svg>
                    </button>
                    <!-- Logout -->
                    <a href="../logout.php"
                        class="flex items-center p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors"
                        onclick="return confirm('Are you sure you want to logout?')">
                        <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span class="text-sm font-medium">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <script>
        (function () {
            const themeToggle = document.getElementById('theme-toggle');
            const sunIcon = document.getElementById('theme-icon-sun');
            const moonIcon = document.getElementById('theme-icon-moon');
            const htmlRoot = document.documentElement;

            function renderThemeIcons() {
                const isDark = htmlRoot.classList.contains('dark');
                if (sunIcon && moonIcon) {
                    if (isDark) {
                        sunIcon.classList.remove('hidden');
                        moonIcon.classList.add('hidden');
                    } else {
                        sunIcon.classList.add('hidden');
                        moonIcon.classList.remove('hidden');
                    }
                }
            }

            // Render icons based on current theme (set by preflight)
            renderThemeIcons();

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    // Temporarily disable transitions for instant theme switch
                    htmlRoot.style.transition = 'none';
                    htmlRoot.classList.toggle('dark');
                    localStorage.setItem('theme', htmlRoot.classList.contains('dark') ? 'dark' : 'light');
                    renderThemeIcons();
                    // Re-enable transitions after theme is applied
                    requestAnimationFrame(function () {
                        htmlRoot.style.transition = '';
                    });
                });
            }
        })();
    </script>