<div class="p-6">
  <div class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Wastewater Services Overview</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400">System performance and service metrics.</p>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php
    // Fetch real stats
    $db = $database->getConnection();
    $stats = [
      'total' => 0,
      'pending' => 0,
      'completed' => 0,
      'revenue' => 0
    ];
    try {
      $s1 = $db->query("SELECT COUNT(*) FROM service_requests WHERE service_type IN ('septic-registration','maintenance-service','wastewater-clearance','system-inspection')");
      $stats['total'] = $s1->fetchColumn();

      $s2 = $db->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending' AND service_type IN ('septic-registration','maintenance-service','wastewater-clearance','system-inspection')");
      $stats['pending'] = $s2->fetchColumn();

      $s3 = $db->query("SELECT COUNT(*) FROM service_requests WHERE status = 'completed' AND service_type IN ('septic-registration','maintenance-service','wastewater-clearance','system-inspection')");
      $stats['completed'] = $s3->fetchColumn();

      // Assuming we tracked revenue somewhere, but for now placeholder
    } catch (Throwable $e) {
    }
    ?>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Total Requests</h3>
      <p class="text-3xl font-black text-blue-600"><?php echo number_format($stats['total']); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Pending Review</h3>
      <p class="text-3xl font-black text-amber-500"><?php echo number_format($stats['pending']); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Completed</h3>
      <p class="text-3xl font-black text-emerald-500"><?php echo number_format($stats['completed']); ?></p>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider mb-2">Efficiency Rate</h3>
      <p class="text-3xl font-black text-purple-500">
        <?php echo $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0; ?>%
      </p>
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-8">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
      <h3 class="font-bold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 gap-4">
        <a href="?page=wss&view=septic"
          class="block p-4 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-xl hover:bg-blue-100 transition">
          <span class="block font-bold">Review Pending Requests</span>
          <span class="text-xs">Go to Manager &rarr;</span>
        </a>
        <div class="block p-4 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl">
          <span class="block font-bold">Generate Report</span>
          <span class="text-xs">Download PDF (Coming Soon)</span>
        </div>
      </div>
    </div>

    <div class="bg-gradient-to-br from-blue-600 to-cyan-500 rounded-2xl p-8 text-white relative overflow-hidden">
      <div class="relative z-10">
        <h3 class="text-2xl font-black mb-2">Sanitation Standards</h3>
        <p class="opacity-90 mb-6">Ensure all wastewater clearances comply with City Ordinance No. 8491.</p>
        <button class="bg-white text-blue-600 px-6 py-2 rounded-lg font-bold text-sm">View Guidelines</button>
      </div>
      <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
    </div>
  </div>
</div>