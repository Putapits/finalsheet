<?php
$currentPage = strtolower($_GET['page'] ?? '');
$currentView = strtolower($_GET['view'] ?? '');
$scriptName = strtolower($_SERVER['SCRIPT_NAME'] ?? '');
$activeModule = 'dashboard';
$activeView = '';

if ($currentPage !== '') {
  $activeModule = $currentPage;
  $activeView = $currentView;
  if ($activeView === '') {
    $defaults = [
      'dashboard' => '',
      'hcs' => 'overview',
      'int' => 'overview',
      'hss' => 'overview',
      'account' => 'settings',
    ];
    $activeView = $defaults[$activeModule] ?? '';
  }
} else {
  $pathView = basename($scriptName, '.php');
  if (str_contains($scriptName, '/nurse/hcs/')) {
    $activeModule = 'hcs';
    $activeView = $pathView;
  } elseif (str_contains($scriptName, '/nurse/int/')) {
    $activeModule = 'int';
    $activeView = $pathView;
  } elseif (str_contains($scriptName, '/nurse/hss/')) {
    $activeModule = 'hss';
    $activeView = $pathView;
  } elseif (str_contains($scriptName, '/nurse/account/')) {
    $activeModule = 'account';
    $activeView = $pathView;
  }
}
$activeView = str_replace('-', '_', $activeView);
function nurseIsModuleActive(string $module): bool {
  return $GLOBALS['activeModule'] === $module;
}
function nurseIsSubActive(string $module, string $view): bool {
  return $GLOBALS['activeModule'] === $module && $GLOBALS['activeView'] === str_replace('-', '_', $view);
}
?>
<!-- Sidebar -->
<div id="sidebar" class="fixed left-0 top-0 w-64 bg-dark-sidebar shadow-lg transition-all duration-300 ease-in-out z-40 overflow-y-auto h-full">
  <div class="p-4 border-b border-gray-700 flex items-center justify-center">
    <div class="flex items-center">
      <img src="../img/HS.png" alt="GSM Logo" class="w-10 h-10 object-contain mr-3">
      <div class="sidebar-text">
        <h2 class="text-lg font-bold text-white">GSM</h2>
        <p class="text-xs text-gray-400">Nurse</p>
      </div>
    </div>
  </div>

  <div class="p-4">
    <div class="space-y-1">
      <div class="mb-2">
        <a href="nurse.php?page=dashboard" class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo nurseIsModuleActive('dashboard') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/></svg>
          <span class="sidebar-text">Dashboard</span>
        </a>
      </div>

      <div class="mb-1">
        <button onclick="toggleDropdown('hcs-dropdown')" class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo nurseIsModuleActive('hcs') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          <div class="sidebar-text flex items-center justify-between flex-1">
            <span>Health Center Services</span>
            <svg class="w-4 h-4 transition-transform" id="hcs-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </button>
        <div id="hcs-dropdown" class="sidebar-text ml-8 mt-2 space-y-1<?php echo nurseIsModuleActive('hcs') ? '' : ' hidden'; ?>">
          <a href="nurse.php?page=hcs&view=overview" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('hcs', 'overview') ? ' active' : ''; ?>">HCS Overview</a>
          <a href="nurse.php?page=hcs&view=appointment" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('hcs', 'appointment') ? ' active' : ''; ?>">Appointments</a>
          <a href="nurse.php?page=hcs&view=consultation" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('hcs', 'consultation') ? ' active' : ''; ?>">Consultations</a>
        </div>
      </div>

      <div class="mb-1">
        <button onclick="toggleDropdown('int-dropdown')" class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo nurseIsModuleActive('int') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 10c0 3-4 5-7 9-3-4-7-6-7-9a7 7 0 1114 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10a2 2 0 100-4 2 2 0 000 4z" />
          </svg>
          <div class="sidebar-text flex items-center justify-between flex-1">
            <span>Immunization & Nutrition</span>
            <svg class="w-4 h-4 transition-transform" id="int-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </button>
        <div id="int-dropdown" class="sidebar-text ml-8 mt-2 space-y-1<?php echo nurseIsModuleActive('int') ? '' : ' hidden'; ?>">
          <a href="nurse.php?page=int&view=overview" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('int', 'overview') ? ' active' : ''; ?>">INT Overview</a>
          <a href="nurse.php?page=int&view=vaccination" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('int', 'vaccination') ? ' active' : ''; ?>">Vaccination Programs</a>
          <a href="nurse.php?page=int&view=nutrition" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('int', 'nutrition') ? ' active' : ''; ?>">Nutrition Monitoring</a>
        </div>
      </div>

      <div class="mb-1">
        <button onclick="toggleDropdown('hss-dropdown')" class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo nurseIsModuleActive('hss') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <div class="sidebar-text flex items-center justify-between flex-1">
            <span>Health Surveillance System</span>
            <svg class="w-4 h-4 transition-transform" id="hss-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </button>
        <div id="hss-dropdown" class="sidebar-text ml-8 mt-2 space-y-1<?php echo nurseIsModuleActive('hss') ? '' : ' hidden'; ?>">
          <a href="nurse.php?page=hss&view=overview" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('hss', 'overview') ? ' active' : ''; ?>">HSS Overview</a>
          <a href="nurse.php?page=hss&view=disease" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('hss', 'disease') ? ' active' : ''; ?>">Disease Monitoring</a>
          <a href="nurse.php?page=hss&view=environmental" class="sidebar-sub-link text-sm<?php echo nurseIsSubActive('hss', 'environmental') ? ' active' : ''; ?>">Environmental Monitoring</a>
        </div>
      </div>
      <div class="mb-1">
        <a href="nurse.php?page=account&view=settings" class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo nurseIsModuleActive('account') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <span class="sidebar-text">Settings</span>
        </a>
      </div>
    </div>
  </div>
</div>
