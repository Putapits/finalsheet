<?php
$types = ['medical-consultation','emergency-care','preventive-care'];
$typeLabels = [
  'medical-consultation' => 'Medical Consultation',
  'emergency-care' => 'Emergency Care',
  'preventive-care' => 'Preventive Care',
];
try {
  $stmt = $db->prepare("SELECT service_type, status FROM service_requests WHERE service_type IN (?,?,?)");
  $stmt->execute($types);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $rows = [];
}
$countsByType = array_fill_keys($types, 0);
$countsByStatus = ['pending'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0];
foreach ($rows as $r) {
  $t = $r['service_type'] ?? '';
  if (isset($countsByType[$t])) $countsByType[$t]++;
  $s = $r['status'] ?? '';
  if (isset($countsByStatus[$s])) $countsByStatus[$s]++;
}
?>

<div class="mb-8 rounded-2xl p-8 border border-emerald-100 shadow-lg bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <h2 class="text-3xl font-bold text-gray-900 dark:text-slate-100">HCS Overview</h2>
  <p class="mt-2 text-gray-600 dark:text-slate-300">Health Center Services management interface.</p>
</div>
<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 mb-6 shadow-md">
  <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100 mb-4">Summary</h3>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <?php foreach ($types as $t): ?>
      <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-teal-100 dark:border-teal-500/40 shadow-md hover:shadow-lg transition-all">
        <div class="text-sm font-medium text-gray-600 dark:text-slate-200"><?php echo htmlspecialchars($typeLabels[$t], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="text-2xl font-bold text-teal-600 dark:text-teal-200"><?php echo (int)($countsByType[$t] ?? 0); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-blue-100 dark:border-blue-500/40 shadow-md hover:shadow-lg transition-all">
      <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Pending</div>
      <div class="text-2xl font-bold text-blue-600 dark:text-blue-200"><?php echo (int)($countsByStatus['pending'] ?? 0); ?></div>
    </div>
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-amber-100 dark:border-amber-500/40 shadow-md hover:shadow-lg transition-all">
      <div class="text-sm font-medium text-gray-600 dark:text-slate-200">In Progress</div>
      <div class="text-2xl font-bold text-amber-600 dark:text-amber-200"><?php echo (int)($countsByStatus['in_progress'] ?? 0); ?></div>
    </div>
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-500/40 shadow-md hover:shadow-lg transition-all">
      <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Completed</div>
      <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-200"><?php echo (int)($countsByStatus['completed'] ?? 0); ?></div>
    </div>
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-600 shadow-md hover:shadow-lg transition-all">
      <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Cancelled</div>
      <div class="text-2xl font-bold text-slate-600 dark:text-slate-200"><?php echo (int)($countsByStatus['cancelled'] ?? 0); ?></div>
    </div>
  </div>
</section>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 mb-6 shadow-md">
  <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Quick Actions</h3>
  <div class="space-y-3">
    <button onclick="location.href='DashboardOverview_new.php?page=hcs&view=appointment'" class="w-full text-left p-4 rounded-xl border border-emerald-100 dark:border-emerald-600/50 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-200 hover:bg-emerald-100 hover:border-emerald-200 dark:hover:bg-emerald-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-emerald-900/40 border border-emerald-100 dark:border-emerald-600 mr-3">
          <svg class="w-5 h-5 text-emerald-500 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </span>
        <span class="font-semibold">Appointment</span>
      </div>
    </button>
    <button onclick="location.href='DashboardOverview_new.php?page=hcs&view=consultation'" class="w-full text-left p-4 rounded-xl border border-sky-100 dark:border-sky-600/50 bg-sky-50 text-sky-700 dark:bg-sky-900/50 dark:text-sky-200 hover:bg-sky-100 hover:border-sky-200 dark:hover:bg-sky-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-sky-900/40 border border-sky-100 dark:border-sky-600 mr-3">
          <svg class="w-5 h-5 text-sky-500 dark:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </span>
        <span class="font-semibold">Consultation</span>
      </div>
    </button>
  </div>
</section>
