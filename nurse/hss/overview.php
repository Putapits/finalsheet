<?php
// Nurse HSS Overview (CRUD) for disease-monitoring + environmental-monitoring
$types = ['disease-monitoring','environmental-monitoring'];
$typeLabels = [
  'disease-monitoring' => 'Disease Monitoring',
  'environmental-monitoring' => 'Environmental Monitoring',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Nurse HSS overview fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
// summaries
$statusKeys = ['pending','in_progress','completed','cancelled'];
$byStatus = array_fill_keys($statusKeys, 0);
$byType = array_fill_keys($types, 0);
foreach ($rows as $r) {
  $s = strtolower($r['status'] ?? 'pending'); if (isset($byStatus[$s])) $byStatus[$s]++;
  $t = $r['service_type'] ?? ''; if (isset($byType[$t])) $byType[$t]++;
}
?>

<section class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
  <div class="flex flex-col gap-3">
    <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">Health Surveillance</span>
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">HSS Overview</h2>
      <p class="text-gray-600 dark:text-gray-400">Monitor disease and environmental reports, triage follow-ups, and coordinate rapid responses.</p>
    </div>
    <div class="inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-5 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
      Total Submissions: <span class="ml-2 text-base text-primary dark:text-primary-100"><?php echo number_format(count($rows)); ?></span>
    </div>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <button id="hss-export" type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Export CSV</button>
    <button id="hss-add" type="button" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add Submission</button>
  </div>
</section>

<section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6">
  <?php
    $metricCards = [
      ['label' => 'Total Submissions', 'value' => count($rows), 'icon' => 'M12 2a10 10 0 110 20 10 10 0 010-20z', 'accent' => 'text-primary dark:text-primary-200'],
      ['label' => 'Pending', 'value' => $byStatus['pending'], 'icon' => 'M12 6v6l3 1.5', 'accent' => 'text-amber-500'],
      ['label' => 'In Progress', 'value' => $byStatus['in_progress'], 'icon' => 'M5 13l4 4L19 7', 'accent' => 'text-emerald-500'],
      ['label' => 'Completed', 'value' => $byStatus['completed'], 'icon' => 'M12 8v8m0 0l-3-3m3 3l3-3', 'accent' => 'text-sky-500'],
      ['label' => 'Cancelled', 'value' => $byStatus['cancelled'], 'icon' => 'M18.364 5.636L5.636 18.364m0-12.728L18.364 18.364', 'accent' => 'text-rose-500'],
      ['label' => 'Disease Monitoring', 'value' => $byType['disease-monitoring'], 'icon' => 'M9 12l2 2 4-4', 'accent' => 'text-fuchsia-500'],
      ['label' => 'Environmental Monitoring', 'value' => $byType['environmental-monitoring'], 'icon' => 'M4.5 12.75l6 6 9-13.5', 'accent' => 'text-emerald-500'],
    ];
  ?>
  <?php foreach ($metricCards as $card): ?>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?php echo h($card['label']); ?></p>
          <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($card['value']); ?></p>
        </div>
        <span class="inline-flex rounded-full bg-primary/10 p-2 text-primary dark:bg-primary/15 dark:text-primary-200">
          <svg class="h-5 w-5 <?php echo h($card['accent']); ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo h($card['icon']); ?>" /></svg>
        </span>
      </div>
    </div>
  <?php endforeach; ?>
</section>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
  <button type="button" onclick="location.href='nurse.php?page=hss&view=disease'" class="flex w-full items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-left shadow-sm transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:hover:border-emerald-400/40 dark:hover:bg-emerald-500/15">
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Disease Monitoring</p>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Investigate outbreaks and coordinate case reporting workflows.</p>
    </div>
    <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L20 11m0 0l-6.5 6.5M20 11H4" /></svg>
  </button>
  <button type="button" onclick="location.href='nurse.php?page=hss&view=environmental'" class="flex w-full items-center justify-between rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-left shadow-sm transition hover:border-blue-300 hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:hover:border-blue-400/40 dark:hover:bg-blue-500/15">
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-blue-500 dark:text-blue-300">Environmental Monitoring</p>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Capture sanitation, water quality, and vector-risk observations.</p>
    </div>
    <svg class="h-5 w-5 text-blue-500 dark:text-blue-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L20 11m0 0l-6.5 6.5M20 11H4" /></svg>
  </button>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
  <div class="grid grid-cols-1 gap-3 md:grid-cols-4 md:gap-4 mb-4">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
      <select id="hss-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark.border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="hss-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark.border-slate-600 dark:bg-slate-800 dark.text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="hss-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark.border-slate-600 dark:bg-slate-800 dark.text-white dark:placeholder-gray-500">
        <svg class="pointer-events-none absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>
  <div class="flex justify-end">
    <button type="button" id="hss-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark.border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-800 dark:text-gray-200" id="hss-table">
      <thead class="bg-gray-50 text-gray-500 dark:bg-slate-900/60 dark:text-gray-400">
        <tr>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="service">Service</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="name">Full Name</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="urgency">Urgency</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right text-xs font-semibold uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody id="hss-tbody" class="divide-y divide-gray-100 dark:divide-slate-800">
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-400 dark:text-gray-500">No HSS submissions found.</td>
          </tr>
        <?php else: foreach ($rows as $r): ?>
          <?php
            $urgency = strtolower($r['urgency'] ?? 'medium');
            $status = strtolower($r['status'] ?? 'pending');
            $urgencyClasses = [
              'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
              'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
              'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
              'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
            ];
            $statusClasses = [
              'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
              'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
              'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
              'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
            ];
            $urgencyClass = $urgencyClasses[$urgency] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
            $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
          ?>
          <tr class="hss-row border-b border-transparent transition-colors hover:bg-gray-50 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>"
              data-service="<?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>"
              data-type="<?php echo h($r['service_type']); ?>"
              data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>"
              data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>"
              data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>"
              data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
            <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
            <td class="py-3 px-3">
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
            </td>
            <td class="py-3 px-3">
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
            </td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
            <td class="py-3 px-3 text-right">
              <button type="button" class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline.none focus:ring-2 focus:ring-primary/40" onclick="openViewModal(this.closest('tr'))">View</button>
              <button type="button" class="mr-2 inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline.none focus:ring-2 focus:ring-primary/30 dark.border-slate-600 dark:bg-slate-800 dark.text-gray-200 dark:hover:bg-slate-700" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
              <button type="button" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 focus:outline.none focus:ring-2 focus:ring-rose-200 dark.border-rose-500/40 dark:bg-rose-500/10 dark.text-rose-200 dark:hover:bg-rose-500/15" onclick="deleteRow(this.closest('tr'))">Delete</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="hss-pagination" class="mt-4 flex items-center justify-end gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="hss-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="modal-panel relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">HSS Submission Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="hss-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#hss-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="hss-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="modal-panel relative max-h-[90vh] w-[95%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="hss-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add HSS Submission</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="hss-crud-form" class="grid grid-cols-1 gap-6 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Type</label>
        <select name="service_type" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
          <option value="disease-monitoring">Disease Monitoring</option>
          <option value="environmental-monitoring">Environmental Monitoring</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 dark:placeholder-gray-500" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full Name</label>
        <input name="full_name" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
        <input name="email" type="email" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
        <input name="phone" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Details</label>
        <textarea name="service_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100"></textarea>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
        <select name="urgency" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="md:col-span-2 flex flex-col gap-2 pt-4 md:flex-row md:justify-end">
        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#hss-crud-modal.show{display:flex;}</style>
</div>

<style>
  .modal-panel { backdrop-filter: blur(12px); }
  .modal-field {
    background: linear-gradient(135deg, rgba(59,130,246,0.12), rgba(16,185,129,0.12));
    border: 1px solid rgba(148,163,184,0.4);
    border-radius: 0.75rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
  }
  .modal-field .modal-field-label {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #475569;
  }
  .modal-field .modal-field-value {
    color: #0f172a;
    font-size: 0.92rem;
    line-height: 1.35rem;
    word-break: break-word;
  }
  @media (prefers-color-scheme: dark) {
    .modal-field {
      background: linear-gradient(135deg, rgba(59,130,246,0.18), rgba(110,231,183,0.18));
      border-color: rgba(148,163,184,0.35);
    }
    .modal-field .modal-field-label { color: #cbd5f5; }
    .modal-field .modal-field-value { color: #f8fafc; }
  }
</style>

<script>
  const nsSearch = document.getElementById('hss-search');
  const nsBody = document.getElementById('hss-tbody');
  const nsFilterType = document.getElementById('hss-filter-type');
  const nsFilterStatus = document.getElementById('hss-filter-status');
  const nsTable = document.getElementById('hss-table');
  const nsExport = document.getElementById('hss-export');
  const nsPagination = document.getElementById('hss-pagination');
  const nsAdd = document.getElementById('hss-add');
  let nsSortKey='id', nsSortAsc=false, nsPage=1; const nsPageSize=10;

  function nsRows(){ return Array.from(nsBody?.querySelectorAll('.hss-row')||[]); }
  function applyNsFilters(){
    const q=(nsSearch?.value||'').toLowerCase(); const ft=(nsFilterType?.value||'').toLowerCase(); const fs=(nsFilterStatus?.value||'').toLowerCase();
    let rows=nsRows();
    rows.forEach(tr=>{ const text=tr.innerText.toLowerCase(); const t=(tr.dataset.type||'').toLowerCase(); const s=(tr.dataset.status||'').toLowerCase(); tr.dataset._match=(text.includes(q)&&(!ft||t===ft)&&(!fs||s===fs))?'1':'0'; });
    rows=rows.filter(tr=>tr.dataset._match==='1');
    rows.sort((a,b)=>{ const ka=(a.dataset[nsSortKey]||'').toLowerCase(); const kb=(b.dataset[nsSortKey]||'').toLowerCase(); if(ka<kb)return nsSortAsc?-1:1; if(ka>kb)return nsSortAsc?1:-1; return 0; });
    const total=rows.length; const pages=Math.max(1, Math.ceil(total/nsPageSize)); if(nsPage>pages) nsPage=pages; const start=(nsPage-1)*nsPageSize; const visible=new Set(rows.slice(start,start+nsPageSize));
    nsRows().forEach(tr=>tr.style.display='none'); visible.forEach(tr=>tr.style.display='');
    renderNsPagination(pages);
  }
  function renderNsPagination(pages){ if(!nsPagination)return; nsPagination.innerHTML=''; const prev=document.createElement('button'); prev.textContent='Prev'; prev.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; prev.disabled=nsPage<=1; prev.onclick=()=>{nsPage--;applyNsFilters();}; nsPagination.appendChild(prev); for(let i=1;i<=pages;i++){ const b=document.createElement('button'); b.textContent=i; b.className='px-2 py-1 rounded '+(i===nsPage?'bg-primary text-white':'bg-gray-700 hover:bg-gray-600 text-white'); b.onclick=()=>{nsPage=i;applyNsFilters();}; nsPagination.appendChild(b);} const next=document.createElement('button'); next.textContent='Next'; next.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; next.disabled=nsPage>=pages; next.onclick=()=>{nsPage++;applyNsFilters();}; nsPagination.appendChild(next); }
  [nsSearch, nsFilterType, nsFilterStatus].forEach(el=>{ if(el) el.addEventListener('input', applyNsFilters); if(el) el.addEventListener('change', applyNsFilters); });
  if (nsTable){ nsTable.querySelectorAll('th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ const key=th.getAttribute('data-key'); if(nsSortKey===key){nsSortAsc=!nsSortAsc;} else {nsSortKey=key; nsSortAsc=true;} applyNsFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function nsToCSV(){ const headers=['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created']; const rows=nsRows().filter(tr=>tr.style.display!=='none'); const lines=[headers.join(',')]; rows.forEach(tr=>{ const cells=Array.from(tr.children).slice(0,9).map(td=>'"'+(td.innerText||'').replace(/"/g,'\\"')+'"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (nsExport) nsExport.addEventListener('click', ()=>download('hss_submissions.csv', nsToCSV()));

  function viewField(label, value){
    const safe=(value??'').toString();
    return `<div class="modal-field">\n  <div class=\"modal-field-label\">${label}</div>\n  <div class=\"modal-field-value whitespace-pre-wrap\">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n</div>`;
  }
  function openViewModal(row){ const modal=document.getElementById('hss-view-modal'); const details=document.getElementById('hss-view-details'); if(!modal||!details||!row)return; const typeMap={'disease-monitoring':'Disease Monitoring','environmental-monitoring':'Environmental Monitoring'}; const typeLabel=typeMap[row.dataset.type]||row.dataset.type; details.innerHTML=`
    ${viewField('Request ID', '#'+(row.dataset.id||''))}
    ${viewField('Service', typeLabel)}
    ${viewField('Status', (row.dataset.status||'').toUpperCase())}
    ${viewField('Urgency', (row.dataset.urgency||'').toUpperCase())}
    ${viewField('Created At', row.dataset.created||'')}
    ${viewField('Full Name', row.dataset.name||'')}
    ${viewField('Email', row.dataset.email||'')}
    ${viewField('Phone', row.dataset.phone||'')}
    ${viewField('Preferred Date', row.dataset.preferred_date||'')}
    ${viewField('Address', row.dataset.address||'')}
    ${viewField('Details', row.dataset.details||'')}
  `; modal.classList.add('show'); modal.classList.remove('hidden'); }
  function closeViewModal(){ const modal=document.getElementById('hss-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal=openViewModal; window.closeViewModal=closeViewModal;

  const nsCrudModal=document.getElementById('hss-crud-modal'); const nsCrudForm=document.getElementById('hss-crud-form'); const nsCrudTitle=document.getElementById('hss-crud-title');
  function openCrudModal(mode,row){ if(!nsCrudModal)return; nsCrudForm.reset(); nsCrudForm.elements['mode'].value=mode; if(mode==='edit'&&row){ nsCrudTitle.textContent='Edit HSS Submission'; nsCrudForm.elements['id'].value=row.dataset.id||''; nsCrudForm.elements['service_type'].value=row.dataset.type||'disease-monitoring'; nsCrudForm.elements['full_name'].value=row.dataset.name||''; nsCrudForm.elements['email'].value=row.dataset.email||''; nsCrudForm.elements['phone'].value=row.dataset.phone||''; nsCrudForm.elements['address'].value=row.dataset.address||''; nsCrudForm.elements['service_details'].value=row.dataset.details||''; nsCrudForm.elements['preferred_date'].value=row.dataset.preferred_date||''; nsCrudForm.elements['urgency'].value=(row.dataset.urgency||'medium').toLowerCase(); nsCrudForm.elements['status'].value=(row.dataset.status||'pending').toLowerCase(); } else { nsCrudTitle.textContent='Add HSS Submission'; nsCrudForm.elements['status'].value='pending'; nsCrudForm.elements['urgency'].value='medium'; }
    nsCrudModal.classList.add('show'); nsCrudModal.classList.remove('hidden'); }
  function closeCrudModal(){ if(!nsCrudModal)return; nsCrudModal.classList.remove('show'); nsCrudModal.classList.add('hidden'); }
  window.openCrudModal=openCrudModal; window.closeCrudModal=closeCrudModal; if(nsAdd) nsAdd.addEventListener('click',()=>openCrudModal('create'));

  async function apiCall(payload){ const res=await fetch('hss/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}); const text=await res.text(); if(!res.ok) throw new Error(text||('HTTP '+res.status)); return JSON.parse(text); }
  async function deleteRow(row){ if(!row) return; const id=parseInt(row.dataset.id||'0',10); if(!id) return alert('Invalid ID'); if(!confirm('Delete this submission?')) return; try{ const result=await apiCall({action:'delete', id}); if(result.success){ row.remove(); applyNsFilters(); } else alert('Delete failed.'); }catch(e){ console.error(e); alert('Server error.'); } }
  window.deleteRow=deleteRow;

  nsCrudForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const mode=nsCrudForm.elements['mode'].value; const id=parseInt(nsCrudForm.elements['id'].value||'0',10); const payload={}; if(mode==='edit'){ if(!id) return alert('Invalid ID'); payload.action='update'; payload.id=id; ['status','urgency','preferred_date','address','service_details'].forEach(k=>{ const v=nsCrudForm.elements[k]?.value ?? null; if(v!==null) payload[k]=v; }); } else { payload.action='create'; payload.service_type=nsCrudForm.elements['service_type'].value; payload.user_email=nsCrudForm.elements['user_email'].value; payload.full_name=nsCrudForm.elements['full_name'].value; payload.email=nsCrudForm.elements['email'].value; payload.phone=nsCrudForm.elements['phone'].value; payload.address=nsCrudForm.elements['address'].value; payload.service_details=nsCrudForm.elements['service_details'].value; payload.preferred_date=nsCrudForm.elements['preferred_date'].value; payload.urgency=nsCrudForm.elements['urgency'].value; } try{ const result=await apiCall(payload); if(!result.success) throw new Error('Operation failed'); alert('Saved successfully'); location.reload(); } catch(err){ console.error(err); alert('Error saving data.'); } });

  applyNsFilters();
</script>
