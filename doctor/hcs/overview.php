<?php
// Doctor HCS Overview: totals by status and type
// Assumes included by doctor.php and $db is available

try {
  $stmt = $db->query("SELECT appointment_type, status FROM appointments ORDER BY created_at DESC");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Doctor HCS overview fetch error: ' . $e->getMessage());
  $rows = [];
}

// Fetch HCS consultations from service_requests (medical-consultation, emergency-care, preventive-care)
$hcsConsultTypes = ['medical-consultation', 'emergency-care', 'preventive-care'];
try {
  $cs = $db->prepare("SELECT service_type, status FROM service_requests WHERE deleted_at IS NULL AND service_type IN (?,?,?)");
  $cs->execute($hcsConsultTypes);
  $consults = $cs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Doctor HCS consult fetch error: ' . $e->getMessage());
  $consults = [];
}

$total = count($rows);
$statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
$byStatus = array_fill_keys($statuses, 0);
$byType = [];
foreach ($rows as $r) {
  $s = strtolower(trim((string) ($r['status'] ?? 'pending')));
  if (isset($byStatus[$s]))
    $byStatus[$s]++;
  $t = trim((string) ($r['appointment_type'] ?? ''));
  if ($t !== '')
    $byType[$t] = ($byType[$t] ?? 0) + 1;
}
// Consultations aggregation
$consultByStatus = ['pending' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
$consultByType = array_fill_keys($hcsConsultTypes, 0);
foreach ($consults as $r) {
  $s = strtolower(trim((string) ($r['status'] ?? 'pending')));
  if ($s === 'in_progress')
    $s = 'confirmed'; // Treat in_progress as confirmed for summary

  if (isset($consultByStatus[$s]))
    $consultByStatus[$s]++;
  else if (in_array($s, $statuses))
    $consultByStatus[$s]++; // catch standard ones

  $t = (string) ($r['service_type'] ?? '');
  // Normalize type name for display
  $tName = ucwords(str_replace('-', ' ', $t));
  if ($tName !== '')
    $consultByType[$tName] = ($consultByType[$tName] ?? 0) + 1;
}

// Combine for Overview
$combinedStatus = [];
foreach ($statuses as $s) {
  $combinedStatus[$s] = ($byStatus[$s] ?? 0) + ($consultByStatus[$s] ?? 0);
}
$totalAll = count($rows) + count($consults);

// Combine Types
$combinedType = $byType;
foreach ($consultByType as $t => $c) {
  if ($c > 0)
    $combinedType[$t] = ($combinedType[$t] ?? 0) + $c;
}
ksort($combinedType);

function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="mb-6">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-white">HCS Overview</h2>
  <p class="text-gray-600 dark:text-gray-400 mt-1">Snapshot of referral totals by status and type.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
  <div
    class="p-5 rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl dark:border-slate-700 dark:bg-slate-800">
    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending Referrals</div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2"><?php echo (int) $combinedStatus['pending']; ?>
    </div>
  </div>
  <div
    class="p-5 rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl dark:border-slate-700 dark:bg-slate-800">
    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending Consultations
    </div>
    <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
      <?php echo (int) ($consultByStatus['pending'] + $consultByStatus['in_progress'] ?? 0); ?>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
  <button onclick="location.href='doctor.php?page=hcs&view=appointment'"
    class="group w-full text-left p-4 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-md transition-all hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-200 dark:from-blue-600 dark:to-blue-500 dark:focus:ring-blue-400/40">
    <div class="flex items-center">
      <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/15 mr-3 shadow-inner">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
          </path>
        </svg>
      </span>
      <span class="text-lg font-semibold">Referrals</span>
    </div>
  </button>
  <button onclick="location.href='doctor.php?page=hcs&view=consultation'"
    class="group w-full text-left p-4 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-md transition-all hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:from-emerald-600 dark:to-emerald-500 dark:focus:ring-emerald-400/40">
    <div class="flex items-center">
      <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/15 mr-3 shadow-inner">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </span>
      <span class="text-lg font-semibold">Consultations</span>
    </div>
  </button>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
    <div
      class="p-5 rounded-xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white shadow-sm text-indigo-700 dark:border-indigo-400/40 dark:from-indigo-500/20 dark:to-indigo-500/10 dark:text-indigo-100">
      <div class="text-xs font-semibold uppercase tracking-wide">Total Referrals</div>
      <div class="text-2xl font-bold mt-2 text-current"><?php echo (int) $totalAll; ?></div>
    </div>
    <div
      class="p-5 rounded-xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white shadow-sm text-amber-700 dark:border-amber-400/40 dark:from-amber-500/25 dark:to-amber-500/10 dark:text-amber-100">
      <div class="text-xs font-semibold uppercase tracking-wide">Pending</div>
      <div class="text-2xl font-bold mt-2 text-current"><?php echo (int) $combinedStatus['pending']; ?></div>
    </div>
    <div
      class="p-5 rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white shadow-sm text-emerald-700 dark:border-emerald-400/40 dark:from-emerald-500/25 dark:to-emerald-500/10 dark:text-emerald-100">
      <div class="text-xs font-semibold uppercase tracking-wide">Confirmed</div>
      <div class="text-2xl font-bold mt-2 text-current"><?php echo (int) $combinedStatus['confirmed']; ?></div>
    </div>
    <div
      class="p-5 rounded-xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white shadow-sm text-sky-700 dark:border-sky-400/40 dark:from-sky-500/25 dark:to-sky-500/10 dark:text-sky-100">
      <div class="text-xs font-semibold uppercase tracking-wide">Completed</div>
      <div class="text-2xl font-bold mt-2 text-current"><?php echo (int) $combinedStatus['completed']; ?></div>
    </div>
    <div
      class="p-5 rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white shadow-sm text-rose-700 dark:border-rose-400/40 dark:from-rose-500/25 dark:to-rose-500/10 dark:text-rose-100">
      <div class="text-xs font-semibold uppercase tracking-wide">Cancelled</div>
      <div class="text-2xl font-bold mt-2 text-current"><?php echo (int) $combinedStatus['cancelled']; ?></div>
    </div>
  </div>

  <div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">By Appointment Type</h3>
    <?php if (empty($combinedType)): ?>
      <div
        class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500 dark:border-slate-600 dark:bg-slate-800/40 dark:text-gray-300">
        No appointment types found.</div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($combinedType as $type => $count): ?>
          <div
            class="p-4 rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg dark:border-slate-700 dark:bg-slate-800">
            <div class="text-sm font-medium text-gray-600 dark:text-gray-300"><?php echo h($type); ?></div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-white mt-2"><?php echo (int) $count; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="mt-8 flex justify-end">
    <a href="doctor.php?page=hcs&view=appointment"
      class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-md transition-colors hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
      Go to Referrals
    </a>
  </div>
</section>