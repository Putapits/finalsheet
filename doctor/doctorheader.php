<?php
require_once '../include/database.php';
startSecureSession();
requireRole('doctor');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - Health & Sanitation</title>
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
  <!-- Preflight theme -->
  <script>
    (function(){
      try {
        var saved = localStorage.getItem('theme') || 'dark';
        if (saved === 'dark') document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
      } catch(e) {
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
    /* Sidebar scrollbar */
    #sidebar::-webkit-scrollbar { width: 6px; }
    #sidebar::-webkit-scrollbar-track { background: transparent; }
    #sidebar::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 3px; }
    .dark #sidebar::-webkit-scrollbar-thumb { background: #4a5568; }
    #sidebar::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
    .dark #sidebar::-webkit-scrollbar-thumb:hover { background: #2d3748; }
    /* Collapsed */
    .sidebar-collapsed { width: 4rem !important; }
    .sidebar-collapsed .sidebar-text,
    .sidebar-collapsed span:not(.sidebar-icon):not(svg),
    .sidebar-collapsed h2,
    .sidebar-collapsed p,
    .sidebar-collapsed div.sidebar-text { display:none !important; }
    .sidebar-collapsed .flex.items-center { justify-content:center !important; }
    .sidebar-collapsed .sidebar-icon { margin:0 !important; }
    .sidebar-collapsed .p-4.border-b { justify-content:center !important; }
    .sidebar-collapsed button,
    .sidebar-collapsed a { justify-content:center !important; padding:0.75rem !important; }
    .sidebar-collapsed svg { margin:0 !important; }
    .sidebar-collapsed [id$="-dropdown"] { display:none !important; }

    .sidebar-link {
      color: var(--color-muted);
      transition: color 0.2s ease, background-color 0.2s ease;
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
      transition: color 0.2s ease, background-color 0.2s ease;
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
      background-color: #4A90E2;
    }
    /* Light theme overrides */
    html:not(.dark) body { background-color: var(--color-background); }
    html:not(.dark) nav.bg-dark-bg { background-color: var(--color-surface) !important; border-color: var(--color-border) !important; }
    html:not(.dark) #sidebar { background-color: var(--color-surface) !important; border-right: 1px solid var(--color-border) !important; }
    html:not(.dark) .bg-dark-bg { background-color: var(--color-background) !important; }
    html:not(.dark) .bg-dark-card { background-color: var(--color-surface) !important; border-color: var(--color-border) !important; }
    html:not(.dark) .border-gray-700, html:not(.dark) .border-gray-600 { border-color: var(--color-border) !important; }
    html:not(.dark) .text-white { color: var(--color-text) !important; }
    html:not(.dark) .text-gray-400 { color: #4b5563 !important; }
    html:not(.dark) .text-gray-300 { color: #374151 !important; }
    html:not(.dark) #sidebar .text-white { color: var(--color-text) !important; }
    html:not(.dark) .bg-primary .text-white,
    html:not(.dark) .bg-blue-600 .text-white,
    html:not(.dark) .bg-green-600 .text-white,
    html:not(.dark) .bg-red-600 .text-white,
    html:not(.dark) .bg-orange-600 .text-white,
    html:not(.dark) .bg-yellow-600 .text-white,
    html:not(.dark) .bg-gray-700 .text-white,
    html:not(.dark) .bg-gray-800 .text-white { color:#ffffff !important; }
  </style>
</head>
<body class="min-h-screen bg-slate-50 dark:bg-dark-bg text-gray-900 dark:text-white">
  <nav class="bg-white dark:bg-dark-bg border-b border-gray-200 dark:border-gray-700 fixed w-full top-0 z-40">
    <div class="max-w-full mx-auto px-6">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <button id="sidebar-toggle" class="p-2 text-gray-600 hover:text-gray-900 transition-colors ml-64 dark:text-gray-400 dark:hover:text-white">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div class="flex items-center ml-4">
            <div class="hidden sm:block">
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">Health &amp; Sanitation</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Doctor Portal</p>
            </div>
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input type="text" placeholder="Search..." class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 border border-gray-200 dark:border-transparent rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-primary w-64">
            <svg class="absolute right-3 top-2.5 h-5 w-5 text-secondary dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
          <button class="p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors" id="theme-toggle" aria-label="Toggle theme">
            <svg id="theme-icon-sun" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364 6.364l-1.414-1.414M7.05 7.05L5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
            <svg id="theme-icon-moon" class="h-6 w-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 1112.646 3.646 7 7 0 0020.354 15.354z"/></svg>
          </button>
          <a href="../logout.php" class="flex items-center p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors" onclick="return confirm('Are you sure you want to logout?')">
            <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            <span class="text-sm font-medium">Logout</span>
          </a>
        </div>
      </div>
    </div>
  </nav>

  <script>
    (function(){
      const themeToggle = document.getElementById('theme-toggle');
      const sunIcon = document.getElementById('theme-icon-sun');
      const moonIcon = document.getElementById('theme-icon-moon');
      const htmlRoot = document.documentElement;
      function renderThemeIcons(){
        const isDark = htmlRoot.classList.contains('dark');
        if (sunIcon && moonIcon){
          if (isDark){ sunIcon.classList.remove('hidden'); moonIcon.classList.add('hidden'); }
          else { sunIcon.classList.add('hidden'); moonIcon.classList.remove('hidden'); }
        }
      }
      renderThemeIcons();
      if (themeToggle){
        themeToggle.addEventListener('click', function(){
          htmlRoot.classList.toggle('dark');
          localStorage.setItem('theme', htmlRoot.classList.contains('dark') ? 'dark' : 'light');
          renderThemeIcons();
        });
      }
    })();
  </script>
