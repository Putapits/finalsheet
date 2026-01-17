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
      'spi' => 'overview',
      'wss' => 'overview',
      'account' => 'settings',
    ];
    $activeView = $defaults[$activeModule] ?? '';
  }
} else {
  $pathView = basename($scriptName, '.php');
  if (str_contains($scriptName, '/inspection/spi/')) {
    $activeModule = 'spi';
    $activeView = $pathView;
  } elseif (str_contains($scriptName, '/inspection/wss/')) {
    $activeModule = 'wss';
    $activeView = $pathView;
  } elseif (str_contains($scriptName, '/inspection/account/')) {
    $activeModule = 'account';
    $activeView = $pathView;
  }
}
$activeView = str_replace('-', '_', $activeView);
function inspectorIsModuleActive(string $module): bool {
  return $GLOBALS['activeModule'] === $module;
}
function inspectorIsSubActive(string $module, string $view): bool {
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
        <p class="text-xs text-gray-400">Inspector</p>
      </div>
    </div>
  </div>

  <div class="p-4">
    <div class="space-y-1">
      <div class="mb-2">
        <a href="inspector.php?page=dashboard" class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo inspectorIsModuleActive('dashboard') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/></svg>
          <span class="sidebar-text">Dashboard</span>
        </a>
      </div>

      <div class="mb-1">
        <button onclick="toggleDropdown('spi-dropdown')" class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo inspectorIsModuleActive('spi') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a10 10 0 110-20 10 10 0 010 20z" />
          </svg>
          <div class="sidebar-text flex items-center justify-between flex-1">
            <span>Sanitation Permit & Inspection</span>
            <svg class="w-4 h-4 transition-transform" id="spi-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </button>
        <div id="spi-dropdown" class="sidebar-text ml-8 mt-2 space-y-1<?php echo inspectorIsModuleActive('spi') ? '' : ' hidden'; ?>">
          <a href="inspector.php?page=spi&view=overview" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('spi', 'overview') ? ' active' : ''; ?>">SPI Overview</a>
          <a href="inspector.php?page=spi&view=sanitation" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('spi', 'sanitation') ? ' active' : ''; ?>">Sanitation</a>
          <a href="inspector.php?page=spi&view=permit" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('spi', 'permit') ? ' active' : ''; ?>">Assigned Inspections</a>
        </div>
      </div>

      <div class="mb-1">
        <button onclick="toggleDropdown('wss-dropdown')" class="sidebar-link flex items-center w-full p-3 rounded-lg transition-all<?php echo inspectorIsModuleActive('wss') ? ' active' : ''; ?>">
          <svg class="w-5 h-5 sidebar-icon mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"/></svg>
          <div class="sidebar-text flex items-center justify-between flex-1">
            <span>Wastewater & Septic Services</span>
            <svg class="w-4 h-4 transition-transform" id="wss-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
          </div>
        </button>
        <div id="wss-dropdown" class="sidebar-text ml-8 mt-2 space-y-1<?php echo inspectorIsModuleActive('wss') ? '' : ' hidden'; ?>">
          <a href="inspector.php?page=wss&view=overview" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('wss', 'overview') ? ' active' : ''; ?>">WSS Overview</a>
          <a href="inspector.php?page=wss&view=inspection" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('wss', 'inspection') ? ' active' : ''; ?>">System Inspections</a>
          <a href="inspector.php?page=wss&view=maintenance" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('wss', 'maintenance') ? ' active' : ''; ?>">Maintenance Services</a>
          <a href="inspector.php?page=wss&view=installation" class="sidebar-sub-link text-sm<?php echo inspectorIsSubActive('wss', 'installation') ? ' active' : ''; ?>">Installation & Upgrades</a>
        </div>
      </div>
      <div class="mb-1">
        <a href="inspector.php?page=account&view=settings" class="sidebar-link flex items-center p-3 rounded-lg transition-all<?php echo inspectorIsModuleActive('account') ? ' active' : ''; ?>">
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
