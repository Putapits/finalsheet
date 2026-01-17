<?php
/**
 * Immunization & Nutrition Tracker Page - Revised Process Flow
 */
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);

// Determine verification status
$isVerified = false;
$dependents = [];
$reminders = [];
$viewingExternal = false;
$externalUser = null;

if ($isLoggedIn) {
    $isVerified = $database->isUserVerified($_SESSION['user_id']);
    $role = $_SESSION['role'];
    $isStaff = in_array($role, ['health_worker', 'admin', 'nurse', 'doctor']);
    $canEditRecords = in_array($role, ['health_worker', 'nurse', 'doctor']);

    // Support searching: If staff is viewing a specific citizen
    $targetUserId = isset($_GET['view_user']) ? (int) $_GET['view_user'] : $_SESSION['user_id'];

    if ($isStaff && $targetUserId !== $_SESSION['user_id']) {
        $viewingExternal = true;
        $externalUser = $database->getUserById($targetUserId);
        $dependents = $database->getDependents($targetUserId);
        $reminders = $database->getFamilyUpcomingReminders($targetUserId);
    } else {
        $dependents = $database->getDependents($_SESSION['user_id']);
        $reminders = $database->getFamilyUpcomingReminders($_SESSION['user_id']);
    }
}

// SEO Meta Tags
$pageTitle = "My Family Health Tracker | Immunization & Nutrition";
$metaDescription = "Track your child's immunization schedule, nutritional status, and health records online.";

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

    .dependent-card-premium {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dependent-card-premium.active-card {
        border-color: #9333ea !important;
        background: rgba(147, 51, 234, 0.05);
        transform: scale(1.02);
    }
</style>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-16 gap-8">
            <div class="space-y-2">
                <span
                    class="inline-block px-4 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full text-xs font-black uppercase tracking-widest">Family
                    Health</span>
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tighter">
                    Family Health <span class="text-purple-600">Tracker</span>
                </h1>
                <p class="text-lg text-gray-500 dark:text-gray-400 font-medium">Protect and monitor your family's health
                    journey with ease.</p>
                <?php if ($isLoggedIn && $_SESSION['role'] === 'citizen'): ?>
                    <div
                        class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-gray-100 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Your Family ID:</span>
                        <span
                            class="text-sm font-bold text-purple-600 dark:text-purple-400"><?php echo $_SESSION['user_id']; ?></span>
                    </div>
                <?php endif; ?>
                <?php if (in_array($_SESSION['role'] ?? '', ['health_worker', 'admin', 'nurse', 'doctor'])): ?>
                    <div class="mt-6 space-y-4 relative">
                        <div class="relative max-w-md group">
                            <input type="text" id="patientSearchInput" oninput="searchPatients()"
                                placeholder="Search Patient (Name, Email, or ID)..."
                                class="w-full bg-white dark:bg-gray-800 border border-purple-500/20 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none transition-all pr-20">
                            <div class="absolute right-2 top-2 flex items-center gap-1">
                                <button onclick="clearSearch()" id="clearSearchBtn"
                                    class="hidden p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <button onclick="searchPatients()"
                                    class="p-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="searchResultsList"
                            class="hidden absolute z-50 left-0 right-0 md:right-auto md:w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 max-h-80 overflow-y-auto p-2 space-y-1">
                            <!-- Results will appear here -->
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($viewingExternal && $externalUser): ?>
                    <div
                        class="mt-4 flex items-center gap-3 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl">
                        <div class="p-2 bg-purple-600 text-white rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-purple-600 dark:text-purple-400">Viewing Records
                                for</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($externalUser['first_name'] . ' ' . $externalUser['last_name']); ?>
                                (<?php echo htmlspecialchars($externalUser['email']); ?>)
                            </p>
                        </div>
                        <a href="immunization_tracker.php"
                            class="ml-auto text-xs font-bold text-purple-600 hover:underline">Back to My Family</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($isLoggedIn && $isVerified): ?>
                <button onclick="openServiceForm('child-registration', { parent_id: '<?php echo $targetUserId; ?>' })"
                    class="group flex items-center gap-3 bg-purple-600 hover:bg-purple-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-purple-500/30 transition-all hover:-translate-y-1">
                    <div class="bg-white/20 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <?php echo $viewingExternal ? 'Add Dependent for Patient' : 'Add Dependent'; ?>
                </button>
            <?php endif; ?>
        </div>

        <?php if (!$isLoggedIn): ?>
            <!-- Guest View -->
            <div class="glass-card text-center py-24 rounded-[3rem] shadow-2xl border-purple-500/10">
                <div
                    class="w-24 h-24 bg-purple-100 dark:bg-purple-900/40 rounded-[2rem] flex items-center justify-center mx-auto mb-8 text-purple-600 shadow-inner group transition-transform hover:rotate-12">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-gray-900 dark:text-white mb-4 tracking-tight">Secure Family Access</h2>
                <p class="text-gray-500 max-w-lg mx-auto mb-10 font-medium px-4">Log in to safely manage immunization
                    records, nutritional updates, and milestone tracking for your family.</p>
                <a href="index.php"
                    class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-black py-4 px-12 rounded-2xl shadow-xl shadow-purple-500/30 transition-all">Login
                    Now</a>
            </div>
        <?php else: ?>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

                <!-- LEFT COLUMN: Dependents List -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Reminders Card -->
                    <div class="glass-card rounded-[2rem] p-6 border-amber-500/20 shadow-xl overflow-hidden relative">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-amber-500/10 blur-2xl rounded-full"></div>
                        <h4
                            class="text-amber-800 dark:text-amber-300 font-black flex items-center gap-3 mb-4 text-sm uppercase tracking-wider">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Coming Up
                        </h4>
                        <div class="space-y-4">
                            <?php if (empty($reminders)): ?>
                                <p class="text-[10px] font-bold text-gray-400 italic">No upcoming health events.</p>
                            <?php else: ?>
                                <?php foreach ($reminders as $rem): ?>
                                    <div
                                        class="flex items-start gap-3 bg-white/40 dark:bg-black/20 p-3 rounded-xl border border-amber-500/10 hover:border-amber-500/30 transition-colors">
                                        <div
                                            class="w-1.5 h-1.5 <?php echo ($rem['type'] === 'immunization') ? 'bg-indigo-500' : 'bg-emerald-500'; ?> rounded-full mt-1.5 shadow-sm">
                                        </div>
                                        <p class="text-[10px] font-bold text-gray-700 dark:text-amber-100">
                                            <span
                                                class="text-gray-900 dark:text-white"><?php echo htmlspecialchars($rem['name']); ?>:</span>
                                            <?php echo htmlspecialchars($rem['detail']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Family
                            Members</h3>

                        <div class="space-y-4">
                            <?php if (empty($dependents)): ?>
                                <div class="text-center py-10 glass-card rounded-[2rem] border-dashed">
                                    <p class="text-sm font-bold text-gray-400">No dependents added yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($dependents as $index => $dep):
                                    $initial = strtoupper(substr($dep['first_name'], 0, 1));
                                    $isActive = ($index === 0);
                                    $cardId = "dep-" . $dep['id'];
                                    ?>
                                    <div onclick="showDependentDetails('<?php echo $dep['id']; ?>')"
                                        class="dependent-card-premium cursor-pointer glass-card p-6 rounded-[2rem] shadow-xl border-2 border-transparent hover:border-purple-500/50 group <?php echo $isActive ? 'active-card' : ''; ?>"
                                        id="card-<?php echo $dep['id']; ?>">
                                        <div class="flex items-center gap-5">
                                            <div
                                                class="w-14 h-14 <?php echo ($index % 2 == 0) ? 'bg-purple-600' : 'bg-pink-500'; ?> text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-purple-500/20 group-hover:scale-110 transition-transform">
                                                <?php echo $initial; ?>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-black text-gray-900 dark:text-white text-lg">
                                                    <?php echo htmlspecialchars($dep['first_name'] . ' ' . $dep['last_name']); ?>
                                                </h4>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span
                                                        class="w-2 h-2 <?php echo ($dep['fic_status'] === 'active') ? 'bg-green-500 animate-pulse' : 'bg-gray-400'; ?> rounded-full"></span>
                                                    <span
                                                        class="text-xs font-black <?php echo ($dep['fic_status'] === 'active') ? 'text-green-600 dark:text-green-400' : 'text-gray-400'; ?> uppercase tracking-widest">
                                                        <?php echo ($dep['fic_status'] === 'active') ? 'Up to Date' : 'Inactive'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($isLoggedIn && $isVerified): ?>
                                <button
                                    onclick="openServiceForm('child-registration', { parent_id: '<?php echo $targetUserId; ?>' })"
                                    class="w-full mt-6 py-4 glass-card border-dashed border-2 border-purple-500/30 text-purple-600 dark:text-purple-400 font-black text-sm uppercase tracking-widest hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/10 transition-all flex items-center justify-center gap-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add Member
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Detailed Views -->
                <div class="lg:col-span-2">

                    <?php if (empty($dependents)): ?>
                        <div class="glass-card rounded-[2.5rem] p-20 text-center shadow-2xl">
                            <div
                                class="w-20 h-20 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-2">Build Your Family Profile</h3>
                            <p class="text-gray-500 mb-8 max-w-sm mx-auto">Add your children or family members to start tracking
                                their immunization and nutrition progress.</p>
                            <?php if ($isLoggedIn && $isVerified): ?>
                                <button onclick="openServiceForm('child-registration')"
                                    class="bg-purple-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-purple-700 transition-all shadow-lg shadow-purple-500/20">Add
                                    First Member</button>
                            <?php else: ?>
                                <p
                                    class="text-xs text-amber-600 font-bold bg-amber-50 dark:bg-amber-900/20 px-4 py-2 rounded-lg inline-block italic">
                                    Account verification required to add members.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($dependents as $index => $dep):
                            $imms = $database->getImmunizations($dep['id']);
                            $nutri = $database->getNutritionRecords($dep['id']);
                            $isActive = ($index === 0);
                            ?>
                            <div id="view-<?php echo $dep['id']; ?>"
                                class="child-view space-y-8 animate-in fade-in slide-in-from-right-8 duration-500 <?php echo $isActive ? '' : 'hidden'; ?>">
                                <div class="glass-card rounded-[2.5rem] p-10 shadow-2xl relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-64 h-64 bg-purple-500/5 blur-[100px] rounded-full"></div>

                                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6">
                                        <div>
                                            <div class="flex items-center gap-4">
                                                <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                                                    <?php echo htmlspecialchars($dep['first_name'] . ' ' . $dep['last_name']); ?>
                                                </h2>
                                                <button onclick="openServiceForm('edit-dependent', {
                                                    record_id: '<?php echo $dep['id']; ?>',
                                                    first_name: '<?php echo addslashes($dep['first_name']); ?>',
                                                    last_name: '<?php echo addslashes($dep['last_name']); ?>',
                                                    date_of_birth: '<?php echo $dep['date_of_birth']; ?>',
                                                    place_of_birth: '<?php echo addslashes($dep['place_of_birth'] ?? ''); ?>',
                                                    gender: '<?php echo $dep['gender']; ?>',
                                                    relationship: '<?php echo addslashes($dep['relationship'] ?? ''); ?>',
                                                    fic_status: '<?php echo $dep['fic_status']; ?>'
                                                })"
                                                    class="px-3 py-1 bg-gray-100 dark:bg-white/5 text-gray-500 hover:text-purple-600 rounded-lg text-[10px] font-black uppercase tracking-widest transition-colors">
                                                    Edit Info
                                                </button>
                                            </div>
                                            <div class="flex flex-wrap gap-6 mt-3">
                                                <div class="flex items-center gap-2 text-sm font-bold text-gray-500">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    DOB: <?php echo date('M d, Y', strtotime($dep['date_of_birth'])); ?>
                                                </div>
                                                <?php if ($dep['place_of_birth']): ?>
                                                    <div class="flex items-center gap-2 text-sm font-bold text-gray-500">
                                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                        <?php echo htmlspecialchars($dep['place_of_birth']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div
                                            class="px-5 py-2 <?php echo ($dep['fic_status'] === 'active') ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-gray-500/10 text-gray-600 border-gray-500/20'; ?> border rounded-2xl text-xs font-black uppercase tracking-widest">
                                            FIC Status: <?php echo ucfirst($dep['fic_status']); ?>
                                        </div>
                                    </div>

                                    <!-- Tabs Toggle (Switch between Vax and Nutrition) -->
                                    <div class="flex flex-col space-y-8">
                                        <!-- Immunization Table -->
                                        <div>
                                            <div class="flex justify-between items-center mb-6">
                                                <h3
                                                    class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                                                    <span
                                                        class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-sm">V</span>
                                                    Vaccination Records
                                                </h3>
                                                <?php if ($canEditRecords): ?>
                                                    <button type="button"
                                                        onclick="console.log('Vax button clicked for child:', '<?php echo $dep['id']; ?>'); openServiceForm('immunization-record', { child_id: '<?php echo $dep['id']; ?>' })"
                                                        class="relative z-10 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-500/30 transition-all flex items-center gap-2 cursor-pointer">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                                d="M12 4v16m8-8H4" />
                                                        </svg>
                                                        Add Vax Record
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <div class="overflow-x-auto rounded-3xl border border-gray-100 dark:border-gray-800">
                                                <table class="w-full text-left">
                                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                                        <tr>
                                                            <th
                                                                class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">
                                                                Vaccine / Batch</th>
                                                            <th
                                                                class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">
                                                                Dose</th>
                                                            <th
                                                                class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">
                                                                Date</th>
                                                            <th
                                                                class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">
                                                                Status</th>
                                                            <th
                                                                class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest">
                                                                Health Worker</th>
                                                            <?php if ($isStaff): ?>
                                                                <th
                                                                    class="px-8 py-5 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                                                    Actions</th>
                                                            <?php endif; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                        <?php if (empty($imms)): ?>
                                                            <tr>
                                                                <td colspan="<?php echo $isStaff ? '6' : '5'; ?>"
                                                                    class="px-8 py-10 text-center text-sm font-bold text-gray-400 italic">
                                                                    No vaccination records found.</td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach ($imms as $imm): ?>
                                                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                                                    <td class="px-8 py-5">
                                                                        <div class="font-black text-gray-900 dark:text-white">
                                                                            <?php echo htmlspecialchars($imm['vaccine_name']); ?>
                                                                        </div>
                                                                        <?php if (!empty($imm['batch_number'])): ?>
                                                                            <div
                                                                                class="text-[10px] text-indigo-500 font-bold uppercase tracking-tight">
                                                                                Batch: <?php echo htmlspecialchars($imm['batch_number']); ?>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td class="px-8 py-5 text-sm font-bold text-gray-500">
                                                                        <?php echo $imm['dose_number']; ?>
                                                                    </td>
                                                                    <td class="px-8 py-5 text-sm font-bold text-gray-500 italic">
                                                                        <?php
                                                                        if ($imm['status'] === 'administered') {
                                                                            echo $imm['date_administered'] ? date('M d, Y', strtotime($imm['date_administered'])) : '---';
                                                                        } else {
                                                                            echo 'Due: ' . ($imm['date_due'] ? date('M d, Y', strtotime($imm['date_due'])) : '---');
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td class="px-8 py-5">
                                                                        <?php
                                                                        $statusClass = 'bg-blue-100 text-blue-700';
                                                                        if ($imm['status'] === 'administered')
                                                                            $statusClass = 'bg-green-100 text-green-700';
                                                                        if ($imm['status'] === 'overdue')
                                                                            $statusClass = 'bg-red-100 text-red-700';
                                                                        ?>
                                                                        <span
                                                                            class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?php echo $statusClass; ?>">
                                                                            <?php echo htmlspecialchars($imm['status']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td class="px-8 py-5">
                                                                        <div class="flex items-center gap-2">
                                                                            <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                                                                            <span
                                                                                class="text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase">
                                                                                <?php echo htmlspecialchars($imm['health_worker_name'] ?? 'System'); ?>
                                                                            </span>
                                                                        </div>
                                                                    </td>
                                                                    <?php if ($isStaff): ?>
                                                                        <td class="px-8 py-5 text-right">
                                                                            <div class="flex items-center justify-end gap-4">
                                                                                <!-- View -->
                                                                                <button
                                                                                    onclick="openServiceForm('edit-immunization', {
                                                                                                                                                             record_id: '<?php echo $imm['id']; ?>',
                                                                                                                                                             vaccine_name: '<?php echo addslashes($imm['vaccine_name']); ?>',
                                                                                                                                                             dose_number: '<?php echo $imm['dose_number']; ?>',
                                                                                                                                                             batch_number: '<?php echo addslashes($imm['batch_number'] ?? ''); ?>',
                                                                                                                                                             date_administered: '<?php echo $imm['date_administered']; ?>',
                                                                                                                                                             date_due: '<?php echo $imm['date_due']; ?>',
                                                                                                                                                             status: '<?php echo $imm['status']; ?>',
                                                                                                                                                             remarks: '<?php echo addslashes($imm['remarks'] ?? ''); ?>',
                                                                                                                                                             readOnly: true
                                                                                                                                                         })"
                                                                                    class="text-blue-600 hover:text-blue-900 font-bold text-[10px] uppercase tracking-widest">View</button>

                                                                                <!-- Edit -->
                                                                                <button
                                                                                    onclick="openServiceForm('edit-immunization', {
                                                                                                                                                             record_id: '<?php echo $imm['id']; ?>',
                                                                                                                                                             vaccine_name: '<?php echo addslashes($imm['vaccine_name']); ?>',
                                                                                                                                                             dose_number: '<?php echo $imm['dose_number']; ?>',
                                                                                                                                                             batch_number: '<?php echo addslashes($imm['batch_number'] ?? ''); ?>',
                                                                                                                                                             date_administered: '<?php echo $imm['date_administered']; ?>',
                                                                                                                                                             date_due: '<?php echo $imm['date_due']; ?>',
                                                                                                                                                             status: '<?php echo $imm['status']; ?>',
                                                                                                                                                             remarks: '<?php echo addslashes($imm['remarks'] ?? ''); ?>'
                                                                                                                                                         })"
                                                                                    class="<?php echo $canEditRecords ? '' : 'hidden'; ?> text-indigo-600 hover:text-indigo-900 font-bold text-[10px] uppercase tracking-widest">Edit</button>

                                                                                <!-- Delete -->
                                                                                <button
                                                                                    onclick="deleteMedicalRecord('delete-immunization', '<?php echo $imm['id']; ?>')"
                                                                                    class="<?php echo $canEditRecords ? '' : 'hidden'; ?> text-red-500 hover:text-red-700 font-bold text-[10px] uppercase tracking-widest">Delete</button>
                                                                            </div>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Nutrition Records -->
                                        <div class="pt-8 border-t border-gray-100 dark:border-gray-800">
                                            <div class="flex justify-between items-center mb-6">
                                                <h3
                                                    class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                                                    <span
                                                        class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center text-sm">G</span>
                                                    Growth & Nutrition
                                                </h3>
                                                <?php if ($canEditRecords): ?>
                                                    <button
                                                        onclick="openServiceForm('nutrition-update', { child_id: '<?php echo $dep['id']; ?>' })"
                                                        class="bg-emerald-600 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-700 shadow-lg shadow-emerald-500/20">
                                                        Update Growth
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (empty($nutri)): ?>
                                                <div class="bg-gray-100 dark:bg-white/5 p-10 rounded-3xl text-center">
                                                    <p class="text-sm font-bold text-gray-400 italic">No nutrition records available.
                                                    </p>
                                                </div>
                                            <?php else:
                                                $latest = $nutri[0];
                                                ?>
                                                <!-- Latest Nutrition Summary Card -->
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                                    <div
                                                        class="bg-emerald-50 dark:bg-emerald-900/10 p-6 rounded-[2rem] border border-emerald-100 dark:border-emerald-800/30">
                                                        <p
                                                            class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">
                                                            Weight</p>
                                                        <p class="text-3xl font-black text-gray-900 dark:text-white">
                                                            <?php echo $latest['weight_kg']; ?> <span
                                                                class="text-sm font-medium text-gray-400">kg</span>
                                                        </p>
                                                        <span
                                                            class="inline-block mt-3 px-3 py-1 bg-white dark:bg-emerald-800/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[8px] font-black tracking-widest uppercase">Latest</span>
                                                    </div>
                                                    <div
                                                        class="bg-emerald-50 dark:bg-emerald-900/10 p-6 rounded-[2rem] border border-emerald-100 dark:border-emerald-800/30">
                                                        <p
                                                            class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">
                                                            Height</p>
                                                        <p class="text-3xl font-black text-gray-900 dark:text-white">
                                                            <?php echo $latest['height_cm']; ?> <span
                                                                class="text-sm font-medium text-gray-400">cm</span>
                                                        </p>
                                                        <span
                                                            class="inline-block mt-3 px-3 py-1 bg-white dark:bg-emerald-800/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[8px] font-black tracking-widest uppercase"><?php echo strtoupper($latest['status']); ?></span>
                                                    </div>
                                                    <div
                                                        class="bg-gray-100 dark:bg-white/5 p-6 rounded-[2rem] border border-gray-200 dark:border-gray-800">
                                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">
                                                            Next Scheduled Check</p>
                                                        <p class="text-xl font-black text-gray-900 dark:text-white">
                                                            <?php echo $latest['next_visit_date'] ? date('M d, Y', strtotime($latest['next_visit_date'])) : 'TBD'; ?>
                                                        </p>
                                                        <p class="text-[8px] font-bold text-gray-500 uppercase tracking-tight mt-1">
                                                            Based on last visit:
                                                            <?php echo date('M d, Y', strtotime($latest['visit_date'])); ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Nutrition History Table -->
                                                <div class="overflow-x-auto rounded-3xl border border-gray-100 dark:border-gray-800">
                                                    <table class="w-full text-left">
                                                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                                                            <tr>
                                                                <th
                                                                    class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                                    Date</th>
                                                                <th
                                                                    class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                                    Weight</th>
                                                                <th
                                                                    class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                                    Height</th>
                                                                <th
                                                                    class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                                    Status</th>
                                                                <?php if ($isStaff): ?>
                                                                    <th
                                                                        class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                                                                        Actions</th>
                                                                <?php endif; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                            <?php foreach ($nutri as $n): ?>
                                                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                                                    <td class="px-6 py-4 text-xs font-bold text-gray-900 dark:text-white">
                                                                        <?php echo date('M d, Y', strtotime($n['visit_date'])); ?>
                                                                    </td>
                                                                    <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                                                        <?php echo $n['weight_kg']; ?> kg
                                                                    </td>
                                                                    <td class="px-6 py-4 text-xs font-bold text-gray-500">
                                                                        <?php echo $n['height_cm']; ?> cm
                                                                    </td>
                                                                    <td class="px-6 py-4">
                                                                        <span
                                                                            class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700">
                                                                            <?php echo $n['status']; ?>
                                                                        </span>
                                                                    </td>
                                                                    <?php if ($isStaff): ?>
                                                                        <td class="px-6 py-4 text-right">
                                                                            <div class="flex items-center justify-end gap-4">
                                                                                <!-- View -->
                                                                                <button
                                                                                    onclick="openServiceForm('edit-nutrition', {
                                                                                                                                                                     record_id: '<?php echo $n['id']; ?>',
                                                                                                                                                                     weight: '<?php echo $n['weight_kg']; ?>',
                                                                                                                                                                     height: '<?php echo $n['height_cm']; ?>',
                                                                                                                                                                     nutritional_status: '<?php echo $n['status']; ?>',
                                                                                                                                                                     assessment_date: '<?php echo $n['visit_date']; ?>',
                                                                                                                                                                     next_visit: '<?php echo $n['next_visit_date']; ?>',
                                                                                                                                                                     remarks: '<?php echo addslashes($n['remarks'] ?? ''); ?>',
                                                                                                                                                                     readOnly: true
                                                                                                                                                                 })"
                                                                                    class="text-blue-600 hover:text-blue-900 font-bold text-[10px] uppercase tracking-widest">View</button>

                                                                                <!-- Edit -->
                                                                                <button
                                                                                    onclick="openServiceForm('edit-nutrition', {
                                                                                                                                                                     record_id: '<?php echo $n['id']; ?>',
                                                                                                                                                                     weight: '<?php echo $n['weight_kg']; ?>',
                                                                                                                                                                     height: '<?php echo $n['height_cm']; ?>',
                                                                                                                                                                     nutritional_status: '<?php echo $n['status']; ?>',
                                                                                                                                                                     assessment_date: '<?php echo $n['visit_date']; ?>',
                                                                                                                                                                     next_visit: '<?php echo $n['next_visit_date']; ?>',
                                                                                                                                                                     remarks: '<?php echo addslashes($n['remarks'] ?? ''); ?>'
                                                                                                                                                                 })"
                                                                                    class="<?php echo $canEditRecords ? '' : 'hidden'; ?> text-indigo-600 hover:text-indigo-900 font-bold text-[10px] uppercase tracking-widest">Edit</button>

                                                                                <!-- Delete -->
                                                                                <button
                                                                                    onclick="deleteMedicalRecord('delete-nutrition', '<?php echo $n['id']; ?>')"
                                                                                    class="<?php echo $canEditRecords ? '' : 'hidden'; ?> text-red-500 hover:text-red-700 font-bold text-[10px] uppercase tracking-widest">Delete</button>
                                                                            </div>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    function showDependentDetails(childId) {
        document.querySelectorAll('.child-view').forEach(el => el.classList.add('hidden'));
        document.getElementById('view-' + childId).classList.remove('hidden');

        document.querySelectorAll('.dependent-card-premium').forEach(el => el.classList.remove('active-card'));
        const card = document.getElementById('card-' + childId);
        if (card) card.classList.add('active-card');
    }

    let searchTimeout;
    async function searchPatients() {
        const input = document.getElementById('patientSearchInput');
        const query = input.value.trim();
        const resultsBox = document.getElementById('searchResultsList');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');

        if (query.length < 1) {
            resultsBox.classList.add('hidden');
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                const resp = await fetch(`api/search_patients.php?q=${encodeURIComponent(query)}`);
                const data = await resp.json();
                if (data.success) {
                    resultsBox.innerHTML = '';
                    if (data.results.length > 0) {
                        resultsBox.classList.remove('hidden');

                        // Group results by parent (user_id) to show unique families
                        const families = {};
                        data.results.forEach(p => {
                            const parentId = p.user_id;
                            if (!families[parentId]) {
                                const parentName = (p.first_name || '') + ' ' + (p.last_name || '');
                                families[parentId] = {
                                    name: parentName.trim() || 'User #' + parentId,
                                    email: p.email || 'No email',
                                    id: parentId
                                };
                            }
                        });

                        Object.values(families).forEach(fam => {
                            const item = document.createElement('div');
                            item.className = "flex items-center justify-between p-3 hover:bg-purple-50 dark:hover:bg-purple-900/40 rounded-xl cursor-pointer transition-colors border border-transparent hover:border-purple-200 dark:hover:border-purple-700";
                            item.onclick = () => window.location.href = `immunization_tracker.php?view_user=${fam.id}`;
                            item.innerHTML = `
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center font-black text-sm shadow-sm">
                                        ${fam.name.charAt(0)}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-black text-gray-900 dark:text-white truncate uppercase tracking-tight">${fam.name}</p>
                                        <p class="text-[9px] text-gray-500 font-bold truncate">${fam.email}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-purple-500 bg-purple-50 dark:bg-purple-900/30 px-2 py-0.5 rounded shadow-sm">ID: ${fam.id}</span>
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            `;
                            resultsBox.appendChild(item);
                        });
                    } else {
                        resultsBox.innerHTML = '<div class="p-6 text-center"><p class="text-xs font-black text-gray-400 uppercase tracking-widest italic">No matching families found</p><p class="text-[10px] text-gray-400 mt-1">Try searching by Full Name, Email, or ID</p></div>';
                        resultsBox.classList.remove('hidden');
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }, 300);
    }

    function clearSearch() {
        const input = document.getElementById('patientSearchInput');
        input.value = '';
        document.getElementById('clearSearchBtn').classList.add('hidden');
        document.getElementById('searchResultsList').classList.add('hidden');
        input.focus();
    }

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
        const input = document.getElementById('patientSearchInput');
        const box = document.getElementById('searchResultsList');
        if (input && box && !input.contains(e.target) && !box.contains(e.target)) {
            box.classList.add('hidden');
        }
    });
</script>

<?php
include 'include/service_modals.php';
include 'footer.php';
?>