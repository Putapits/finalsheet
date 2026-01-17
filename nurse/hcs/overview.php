<?php
// Nurse HCS Overview: totals by status and type (read-only)
// Assumes included by nurse.php and $db is available

try {
  $stmt = $db->query("SELECT appointment_type, status FROM appointments ORDER BY created_at DESC");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Nurse HCS overview fetch error: ' . $e->getMessage());
  $rows = [];
}

// Fetch HCS consultations for nurse view
$hcsConsultTypes = ['medical-consultation','emergency-care','preventive-care'];
try {
  $cs = $db->prepare("SELECT service_type, status FROM service_requests WHERE deleted_at IS NULL AND service_type IN (?,?,?)");
  $cs->execute($hcsConsultTypes);
  $consults = $cs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Nurse HCS consult fetch error: ' . $e->getMessage());
  $consults = [];
}

$total = count($rows);
$statuses = ['pending','confirmed','completed','cancelled'];
$byStatus = array_fill_keys($statuses, 0);
$byType = [];
foreach ($rows as $r) {
  $s = strtolower(trim((string)($r['status'] ?? 'pending')));
  if (isset($byStatus[$s])) $byStatus[$s]++;
  $t = trim((string)($r['appointment_type'] ?? ''));
  if ($t !== '') $byType[$t] = ($byType[$t] ?? 0) + 1;
}
// Consultations aggregation
$consultByStatus = ['pending'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0];
foreach ($consults as $r) {
  $s = strtolower(trim((string)($r['status'] ?? 'pending')));
  if (isset($consultByStatus[$s])) $consultByStatus[$s]++;
}
$totalConsultations = count($consults);
$pendingConsultations = (int)$consultByStatus['pending'];
$totalConfirmed = (int)$byStatus['confirmed'];
$totalCompleted = (int)$byStatus['completed'];
ksort($byType);
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8 flex flex-col gap-2">
  <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">Health Center Services</span>
  <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Nurse Overview</h2>
  <p class="text-gray-600 dark:text-gray-400">Track appointment status, consultation workload, and jump into key workflows.</p>
</div>

<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-200">Pending Appointments</p>
    <div class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-100"><?php echo (int)$byStatus['pending']; ?></div>
  </div>
  <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4 shadow-sm dark:border-sky-500/30 dark:bg-sky-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-200">Pending Consultations</p>
    <div class="mt-2 text-3xl font-bold text-sky-700 dark:text-sky-100"><?php echo $pendingConsultations; ?></div>
  </div>
  <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-200">Confirmed Slots</p>
    <div class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-100"><?php echo $totalConfirmed; ?></div>
  </div>
  <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4 shadow-sm dark:border-indigo-500/30 dark:bg-indigo-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-200">Completed Visits</p>
    <div class="mt-2 text-3xl font-bold text-indigo-700 dark:text-indigo-100"><?php echo $totalCompleted; ?></div>
  </div>
</div>

<div class="mb-10 grid grid-cols-1 gap-4 md:grid-cols-2">
  <button onclick="location.href='nurse.php?page=hcs&view=appointment'" class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-5 py-4 text-left text-sm font-semibold text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/40 hover:bg-primary/10 dark:border-slate-700 dark:bg-slate-900/60 dark:text-gray-200 dark:hover:border-primary/30 dark:hover:bg-primary/20">
    <div class="flex items-center gap-3">
      <span class="rounded-full bg-primary/10 p-2 text-primary dark:bg-primary/20 dark:text-primary-200">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
      </span>
      <span class="text-base">Manage Appointments</span>
    </div>
    <span class="text-xs font-semibold text-gray-500 group-hover:text-primary dark:text-gray-400 dark:group-hover:text-primary-200">Open</span>
  </button>
  <button onclick="location.href='nurse.php?page=hcs&view=consultation'" class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-5 py-4 text-left text-sm font-semibold text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/40 hover:bg-primary/10 dark:border-slate-700 dark:bg-slate-900/60 dark:text-gray-200 dark:hover:border-primary/30 dark:hover:bg-primary/20">
    <div class="flex items-center gap-3">
      <span class="rounded-full bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-200">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
      </span>
      <span class="text-base">Consultation Queue</span>
    </div>
    <span class="text-xs font-semibold text-gray-500 group-hover:text-primary dark:text-gray-400 dark:group-hover:text-primary-200">Open</span>
  </button>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
    <?php $statusCards = [
      ['label'=>'Total Appointments','value'=>$total,'classes'=>'border-gray-200 bg-slate-50 text-gray-800 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100'],
      ['label'=>'Pending','value'=>$byStatus['pending'],'classes'=>'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/15 dark:text-amber-200'],
      ['label'=>'Confirmed','value'=>$byStatus['confirmed'],'classes'=>'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:text-emerald-200'],
      ['label'=>'Completed','value'=>$byStatus['completed'],'classes'=>'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/15 dark:text-sky-200'],
      ['label'=>'Cancelled','value'=>$byStatus['cancelled'],'classes'=>'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-200'],
    ]; ?>
    <?php foreach ($statusCards as $card): ?>
      <div class="rounded-2xl border <?php echo $card['classes']; ?> p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide"><?php echo h($card['label']); ?></p>
        <div class="mt-2 text-2xl font-bold"><?php echo number_format((int)$card['value']); ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Appointment Types</h3>
    <p class="text-sm text-gray-600 dark:text-gray-400">Distribution of appointments across service categories.</p>
    <?php if (empty($byType)): ?>
      <div class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-gray-300">No appointment types found.</div>
    <?php else: ?>
      <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($byType as $type => $count): ?>
          <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-300"><?php echo h($type); ?></p>
            <div class="mt-2 text-xl font-semibold text-gray-900 dark:text-white"><?php echo (int)$count; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="mt-8 flex flex-wrap items-center gap-3 text-sm">
    <a href="nurse.php?page=hcs&view=appointment" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Go to Appointments</a>
    <a href="nurse.php?page=hcs&view=consultation" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-semibold text-gray-600 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">View Consultations</a>
  </div>
</section>
