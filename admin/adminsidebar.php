<?php
$currentPage = strtolower($_GET['page'] ?? '');
$currentView = strtolower($_GET['view'] ?? '');
$scriptName = strtolower($_SERVER['SCRIPT_NAME'] ?? '');
$activeModule = 'dashboard';
$activeView = '';
if (!isset($adminBase)) {
    $scr = $_SERVER['SCRIPT_NAME'] ?? '';
    $pos = strpos($scr, '/admin/');
    $adminBase = ($pos !== false) ? substr($scr, 0, $pos + 7) : '/admin/';
}
$rootBase = preg_replace('#/admin/$#', '/', $adminBase);

if ($currentPage !== '') {
    $activeModule = $currentPage;
    $activeView = $currentView;
    if ($activeView === '') {
        $defaults = [
            'hcs' => 'overview',
            'spi' => 'overview',
            'int' => 'overview',
            'wss' => 'overview',
            'hss' => 'overview',
            'contact' => 'messages',
            'account' => 'settings',
            'audit' => 'logs',
            'restore' => 'data',
            'notifications' => 'list',
        ];
        $activeView = $defaults[$activeModule] ?? '';
    }
} else {
    $pathView = basename($scriptName, '.php');
    if (str_contains($scriptName, '/admin/hcs/')) {
        $activeModule = 'hcs';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/spi/')) {
        $activeModule = 'spi';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/int/')) {
        $activeModule = 'int';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/wss/')) {
        $activeModule = 'wss';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/hss/')) {
        $activeModule = 'hss';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/users/')) {
        $activeModule = 'users';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, 'add_user.php')) {
        $activeModule = 'add_user';
    } elseif (str_contains($scriptName, 'verification_user_profile.php')) {
        $activeModule = 'verification';
    } elseif (str_contains($scriptName, '/admin/contact/')) {
        $activeModule = 'contact';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/account/')) {
        $activeModule = 'account';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/audit/')) {
        $activeModule = 'audit';
        $activeView = $pathView;
    } elseif (str_contains($scriptName, '/admin/restore/')) {
        $activeModule = 'restore';
        $activeView = $pathView;
    }
}
$activeView = str_replace('-', '_', $activeView);
function isModuleActive(string $module): bool
{
    return $GLOBALS['activeModule'] === $module;
}
function isSubActive(string $module, string $view): bool
{
    return $GLOBALS['activeModule'] === $module && $GLOBALS['activeView'] === $view;
}
?>

<!-- Sidebar -->
<div id="sidebar"
    class="fixed left-0 top-0 w-64 bg-dark-sidebar shadow-lg transition-all duration-300 ease-in-out z-40 overflow-y-auto h-full">
    <!-- Logo and Header Section -->
    <div class="p-4 border-b border-gray-700 flex items-center justify-center">
        <div class="flex items-center">
            <img src="<?php echo htmlspecialchars($rootBase . 'img/HS.png', ENT_QUOTES, 'UTF-8'); ?>" alt="GSM Logo"
                class="w-12 h-12 object-contain mr-3">
            <div class="sidebar-text">
                <h2 class="text-lg font-bold text-white">GSM</h2>
                <p class="text-xs text-gray-400">Health Officer
                    <br> Admin Dashboard
                </p>
            </div>
        </div>
    </div>

    <div class="p-4">
        <div class="space-y-1">
            <!-- Dashboard -->
            <div class="mb-2">
                <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=dashboard"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('dashboard') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </div>

            <!-- Health Center Services -->
            <div class="mb-1">
                <button onclick="toggleDropdown('hcs-dropdown')"
                    class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo isModuleActive('hcs') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <div class="sidebar-text flex items-center justify-between flex-1">
                        <span>Health Center Services</span>
                        <svg class="w-4 h-4 transition-transform<?php echo isModuleActive('hcs') ? ' rotate-180' : ''; ?>"
                            id="hcs-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </button>
                <div id="hcs-dropdown"
                    class="sidebar-text ml-8 mt-2 space-y-1<?php echo isModuleActive('hcs') ? '' : ' hidden'; ?>">
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hcs&view=overview"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hcs', 'overview') ? ' active' : ''; ?>">HCS
                        Overview</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hcs&view=appointment"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hcs', 'appointment') ? ' active' : ''; ?>">Appointment</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hcs&view=consultation"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hcs', 'consultation') ? ' active' : ''; ?>">Consultation</a>
                </div>
            </div>

            <!-- Sanitation Permit & Inspection -->
            <div class="mb-1">
                <button onclick="toggleDropdown('spi-dropdown')"
                    class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo isModuleActive('spi') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 22a10 10 0 110-20 10 10 0 010 20z" />
                    </svg>
                    <div class="sidebar-text flex items-center justify-between flex-1">
                        <span>Sanitation Permit & Inspection</span>
                        <svg class="w-4 h-4 transition-transform<?php echo isModuleActive('spi') ? ' rotate-180' : ''; ?>"
                            id="spi-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </button>
                <div id="spi-dropdown" class="ml-8 mt-2 space-y-1<?php echo isModuleActive('spi') ? '' : ' hidden'; ?>">
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=spi&view=overview"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('spi', 'overview') ? ' active' : ''; ?>">SPI
                        Overview</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=spi&view=sanitation"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('spi', 'sanitation') ? ' active' : ''; ?>">Sanitation</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=spi&view=permits"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('spi', 'permits') ? ' active' : ''; ?>">Permits</a>
                </div>
            </div>

            <!-- Immunization & Nutrition Tracker -->
            <div class="mb-1">
                <button onclick="toggleDropdown('int-dropdown')"
                    class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo isModuleActive('int') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 10c0 3-4 5-7 9-3-4-7-6-7-9a7 7 0 1114 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10a2 2 0 100-4 2 2 0 000 4z" />
                    </svg>
                    <div class="sidebar-text flex items-center justify-between flex-1">
                        <span>Immunization & Nutrition Tracker</span>
                        <svg class="w-4 h-4 transition-transform<?php echo isModuleActive('int') ? ' rotate-180' : ''; ?>"
                            id="int-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </button>
                <div id="int-dropdown" class="ml-8 mt-2 space-y-1<?php echo isModuleActive('int') ? '' : ' hidden'; ?>">
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=int&view=overview"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('int', 'overview') ? ' active' : ''; ?>">INT
                        Overview</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=int&view=immunization"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('int', 'immunization') ? ' active' : ''; ?>">Immunization</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=int&view=nutrition"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('int', 'nutrition') ? ' active' : ''; ?>">Nutrition
                        Tracker</a>
                </div>
            </div>

            <!-- Wastewater & Septic Services -->
            <div class="mb-1">
                <button onclick="toggleDropdown('wss-dropdown')"
                    class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo isModuleActive('wss') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z">
                        </path>
                    </svg>
                    <div class="sidebar-text flex items-center justify-between flex-1">
                        <span>Wastewater & Septic Services</span>
                        <svg class="w-4 h-4 transition-transform<?php echo isModuleActive('wss') ? ' rotate-180' : ''; ?>"
                            id="wss-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </button>
                <div id="wss-dropdown" class="ml-8 mt-2 space-y-1<?php echo isModuleActive('wss') ? '' : ' hidden'; ?>">
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=wss&view=overview"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('wss', 'overview') ? ' active' : ''; ?>">WSS
                        Overview</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=wss&view=septic"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('wss', 'septic') ? ' active' : ''; ?>">Septic
                        Requests</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=wss&view=assignment"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('wss', 'assignment') ? ' active' : ''; ?>">Assignment</a>
                </div>
            </div>

            <!-- Health Surveillance System -->
            <div class="mb-1">
                <button onclick="toggleDropdown('hss-dropdown')"
                    class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo isModuleActive('hss') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <div class="sidebar-text flex items-center justify-between flex-1">
                        <span>Health Surveillance System</span>
                        <svg class="w-4 h-4 transition-transform<?php echo isModuleActive('hss') ? ' rotate-180' : ''; ?>"
                            id="hss-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </button>
                <div id="hss-dropdown" class="ml-8 mt-2 space-y-1<?php echo isModuleActive('hss') ? '' : ' hidden'; ?>">
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hss&view=overview"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hss', 'overview') ? ' active' : ''; ?>">HSS
                        Overview</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hss&view=alerts"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hss', 'alerts') ? ' active' : ''; ?>">Alerts</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hss&view=disease"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hss', 'disease') ? ' active' : ''; ?>">Disease
                        Monitoring</a>
                    <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=hss&view=environmental"
                        class="sidebar-sub-link text-sm<?php echo isSubActive('hss', 'environmental') ? ' active' : ''; ?>">Environmental
                        Monitoring</a>
                </div>
            </div>

            <!-- Payments (Consolidated) -->
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=payments&view=overview"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('payments') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="sidebar-text">Payments</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>add_user.php"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('add_user') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 12h4m-2 2v-4M4 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <span class="sidebar-text">Add User</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>users/list.php"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('users') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 100 7.75" />
                    </svg>
                    <span class="sidebar-text">User Management</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>verification_user_profile.php"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('verification') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5a3 3 0 016 0v2" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11h14" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 11v7a2 2 0 002 2h6a2 2 0 002-2v-7" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15l2 2 4-4" />
                    </svg>
                    <span class="sidebar-text">User Verification</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=contact&view=messages"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('contact') && isSubActive('contact', 'messages') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-18 8h16a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2z" />
                    </svg>
                    <span class="sidebar-text">Contact Messages</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=account&view=settings"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('account') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="sidebar-text">Settings</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=audit&view=logs"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('audit') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5h14M5 9h10M5 13h14M5 17h10" />
                    </svg>
                    <span class="sidebar-text">Audit Logs</span>
                </a>
            </div>
            <div class="mb-1">
                <a href="<?php echo $adminBase; ?>DashboardOverview_new.php?page=restore&view=data"
                    class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo isModuleActive('restore') ? ' active' : ''; ?>">
                    <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v8m0 0l-3-3m3 3l3-3" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-9-9" />
                    </svg>
                    <span class="sidebar-text">Restore Data</span>
                </a>
            </div>
        </div>
    </div>
</div>