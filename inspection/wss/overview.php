<?php
// Inspector WSS Overview (CRUD) across all wastewater service types
$types = ['system-inspection','maintenance-service','installation-upgrade'];
$typeLabels = [
  'system-inspection' => 'System Inspection',
  'maintenance-service' => 'Maintenance Service',
  'installation-upgrade' => 'Installation & Upgrade',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Inspector WSS overview fetch error: ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
// summaries
$statusKeys = ['pending','in_progress','completed','cancelled'];
$byStatus = array_fill_keys($statusKeys, 0);
$byType = array_fill_keys($types, 0);
foreach ($requests as $r) {
  $s = strtolower($r['status'] ?? 'pending'); if (isset($byStatus[$s])) $byStatus[$s]++;
  $t = $r['service_type'] ?? ''; if (isset($byType[$t])) $byType[$t]++;
}
?>

<div class="mb-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Wastewater &amp; Septic Services (Inspector)</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Monitor every wastewater request across inspections, maintenance, and installations.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
        Total Requests: <span class="ml-1"><?php echo number_format(count($requests)); ?></span>
      </div>
      <button id="wss-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
      <button id="wss-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
      <button id="wss-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add New</button>
    </div>
  </div>
</div>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
  <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Requests</p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo number_format(count($requests)); ?></span>
    </div>
  </div>
  <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-200">Pending</p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-amber-600 dark:text-amber-200"><?php echo number_format($byStatus['pending']); ?></span>
    </div>
  </div>
  <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-200">In Progress</p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-200"><?php echo number_format($byStatus['in_progress']); ?></span>
    </div>
  </div>
  <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4 shadow-sm dark:border-sky-500/30 dark:bg-sky-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-200">Completed</p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-sky-600 dark:text-sky-200"><?php echo number_format($byStatus['completed']); ?></span>
    </div>
  </div>
  <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-rose-600 dark:text-rose-200">Cancelled</p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-rose-600 dark:text-rose-200"><?php echo number_format($byStatus['cancelled']); ?></span>
    </div>
  </div>
</section>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
  <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?php echo h($typeLabels['system-inspection']); ?></p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($byType['system-inspection']); ?></span>
    </div>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?php echo h($typeLabels['maintenance-service']); ?></p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($byType['maintenance-service']); ?></span>
    </div>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white/80 p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?php echo h($typeLabels['installation-upgrade']); ?></p>
    <div class="mt-2 flex items-baseline gap-2">
      <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($byType['installation-upgrade']); ?></span>
    </div>
  </div>
</section>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
  <button type="button" onclick="location.href='inspector.php?page=wss&view=inspection'" class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-primary hover:shadow-lg dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center gap-3">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-white">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
      </span>
      <div>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">Inspection Queue</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">System inspections awaiting action</p>
      </div>
    </div>
    <svg class="h-5 w-5 text-gray-400 transition group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
  </button>
  <button type="button" onclick="location.href='inspector.php?page=wss&view=maintenance'" class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-primary hover:shadow-lg dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center gap-3">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-white">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
      </span>
      <div>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">Maintenance Jobs</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">Scheduled and active upkeep tasks</p>
      </div>
    </div>
    <svg class="h-5 w-5 text-gray-400 transition group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
  </button>
  <button type="button" onclick="location.href='inspector.php?page=wss&view=installation'" class="group flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-primary hover:shadow-lg dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center gap-3">
      <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-white">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
      </span>
      <div>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">Installations &amp; Upgrades</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">New systems and improvement requests</p>
      </div>
    </div>
    <svg class="h-5 w-5 text-gray-400 transition group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
  </button>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
      <select id="wss-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="wss-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
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
        <input id="wss-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="wss-table">
      <thead>
        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="service">Service</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="name">Full Name</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="urgency">Urgency</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="wss-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No WSS requests found.</td>
          </tr>
        <?php else: foreach ($requests as $r): ?>
          <tr class="wss-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
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
              <?php $u = strtolower($r['urgency'] ?? 'medium');
                $urgencyClasses = [
                  'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
                  'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                ];
                $urgencyClass = $urgencyClasses[$u] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
              ?>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
            </td>
            <td class="py-3 px-3">
              <?php $s = strtolower($r['status'] ?? 'pending');
                $statusClasses = [
                  'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                  'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                ];
                $statusClass = $statusClasses[$s] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
              ?>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
            </td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
            <td class="py-3 px-3 text-right">
              <button class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openViewModal(this.closest('tr'))">View</button>
              <button class="mr-2 inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
              <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="deleteRow(this.closest('tr'))">Delete</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="wss-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="wss-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">WSS Request Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="wss-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#wss-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="wss-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="wss-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add WSS Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="wss-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Type</label>
        <select name="service_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="system-inspection">System Inspection</option>
          <option value="maintenance-service">Maintenance Service</option>
          <option value="installation-upgrade">Installation & Upgrade</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full Name</label>
        <input name="full_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
        <input name="email" type="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
        <input name="phone" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Details</label>
        <textarea name="service_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
        <select name="urgency" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#wss-crud-modal.show{display:flex;}</style>
</div>

<script>
  const wsSearch = document.getElementById('wss-search');
  const wsBody = document.getElementById('wss-tbody');
  const wsFilterType = document.getElementById('wss-filter-type');
  const wsFilterStatus = document.getElementById('wss-filter-status');
  const wsTable = document.getElementById('wss-table');
  const wsExport = document.getElementById('wss-export');
  const wsPagination = document.getElementById('wss-pagination');
  const wsAdd = document.getElementById('wss-add');
  const wsClear = document.getElementById('wss-clear');
  let wsSortKey = 'id';
  let wsSortAsc = false;
  let wsPage = 1;
  const wsPageSize = 10;

  function wsRows(){ return Array.from(wsBody?.querySelectorAll('.wss-row') || []); }
  function applyWsFilters(){
    const q = (wsSearch?.value || '').toLowerCase();
    const ft = (wsFilterType?.value || '').toLowerCase();
    const fs = (wsFilterStatus?.value || '').toLowerCase();
    let rows = wsRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.dataset.type || '').toLowerCase();
      const s = (tr.dataset.status || '').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t === ft) && (!fs || s === fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => {
      const va = (a.dataset[wsSortKey] || '').toLowerCase();
      const vb = (b.dataset[wsSortKey] || '').toLowerCase();
      if (wsSortKey === 'id') {
        return (wsSortAsc ? 1 : -1) * ((parseInt(va, 10) || 0) - (parseInt(vb, 10) || 0));
      }
      if (va < vb) return wsSortAsc ? -1 : 1;
      if (va > vb) return wsSortAsc ? 1 : -1;
      return 0;
    });
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / wsPageSize));
    if (wsPage > pages) wsPage = pages;
    const start = (wsPage - 1) * wsPageSize;
    const end = start + wsPageSize;
    const visible = new Set(rows.slice(start, end));
    wsRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderWsPagination(pages);
  }
  function renderWsPagination(pages){
    if (!wsPagination) return;
    wsPagination.innerHTML = '';
    const baseBtn = () => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'px-3 py-1 rounded-lg border text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 border-gray-300 bg-white text-gray-600 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
      return btn;
    };
    const prev = baseBtn();
    prev.textContent = 'Prev';
    prev.disabled = wsPage <= 1;
    if (prev.disabled) {
      prev.classList.add('opacity-60','cursor-not-allowed');
    } else {
      prev.onclick = () => { wsPage--; applyWsFilters(); };
    }
    wsPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = baseBtn();
      b.textContent = i;
      if (i === wsPage) {
        b.className = 'px-3 py-1 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        b.onclick = () => { wsPage = i; applyWsFilters(); };
      }
      wsPagination.appendChild(b);
    }
    const next = baseBtn();
    next.textContent = 'Next';
    next.disabled = wsPage >= pages;
    if (next.disabled) {
      next.classList.add('opacity-60','cursor-not-allowed');
    } else {
      next.onclick = () => { wsPage++; applyWsFilters(); };
    }
    wsPagination.appendChild(next);
  }
  [wsSearch, wsFilterType, wsFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applyWsFilters);
    if (el) el.addEventListener('change', applyWsFilters);
  });
  if (wsClear) {
    wsClear.addEventListener('click', () => {
      if (wsSearch) wsSearch.value = '';
      if (wsFilterType) wsFilterType.value = '';
      if (wsFilterStatus) wsFilterStatus.value = '';
      wsPage = 1;
      applyWsFilters();
    });
  }
  if (wsTable){ wsTable.querySelectorAll('th.sortable').forEach(th => { th.addEventListener('click', () => { const key = th.getAttribute('data-key'); if (wsSortKey === key) { wsSortAsc = !wsSortAsc; } else { wsSortKey = key; wsSortAsc = true; } applyWsFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function wsToCSV(){
    const headers = ['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = wsRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = [
        tr.dataset.id || '',
        tr.dataset.service || '',
        tr.dataset.name || '',
        tr.dataset.email || '',
        tr.dataset.phone || '',
        tr.dataset.preferred_date || '',
        tr.dataset.urgency || '',
        tr.dataset.status || '',
        tr.dataset.created || '',
      ].map(val => '"' + (val || '').replace(/"/g,'""') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (wsExport) wsExport.addEventListener('click', ()=>download('wss_requests.csv', wsToCSV()));

  function viewField(label, value){ const safe=(value??'').toString(); return `<div class=\"modal-field border rounded p-3\">\n  <div class=\"text-gray-400 text-xs mb-1\">${label}</div>\n  <div class=\"text-white break-words whitespace-pre-wrap\">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n</div>`; }
  function openViewModal(row){ const modal=document.getElementById('wss-view-modal'); const details=document.getElementById('wss-view-details'); if(!modal||!details||!row)return; const typeMap={'system-inspection':'System Inspection','maintenance-service':'Maintenance Service','installation-upgrade':'Installation & Upgrade'}; const typeLabel=typeMap[row.dataset.type]||row.dataset.type; details.innerHTML=`
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
    ${viewField('Service Details', row.dataset.details||'')}
  `; modal.classList.add('show'); modal.classList.remove('hidden'); }
  function closeViewModal(){ const modal=document.getElementById('wss-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal=openViewModal; window.closeViewModal=closeViewModal;

  const wsCrudModal=document.getElementById('wss-crud-modal'); const wsCrudForm=document.getElementById('wss-crud-form'); const wsCrudTitle=document.getElementById('wss-crud-title');
  function fillFormFromRow(row){ const f=wsCrudForm; f.elements['id'].value=row.dataset.id||''; f.elements['service_type'].value=row.dataset.type||'system-inspection'; f.elements['full_name'].value=row.dataset.name||''; f.elements['email'].value=row.dataset.email||''; f.elements['phone'].value=row.dataset.phone||''; f.elements['address'].value=row.dataset.address||''; f.elements['service_details'].value=row.dataset.details||''; f.elements['preferred_date'].value=row.dataset.preferred_date||''; f.elements['urgency'].value=(row.dataset.urgency||'medium').toLowerCase(); f.elements['status'].value=(row.dataset.status||'pending').toLowerCase(); }
  function openCrudModal(mode,row){ if(!wsCrudModal)return; wsCrudForm.reset(); wsCrudForm.elements['mode'].value=mode; if(mode==='edit'&&row){ wsCrudTitle.textContent='Edit WSS Request'; fillFormFromRow(row);} else { wsCrudTitle.textContent='Add WSS Request'; wsCrudForm.elements['status'].value='pending'; wsCrudForm.elements['urgency'].value='medium'; } wsCrudModal.classList.add('show'); wsCrudModal.classList.remove('hidden'); }
  function closeCrudModal(){ if(!wsCrudModal)return; wsCrudModal.classList.remove('show'); wsCrudModal.classList.add('hidden'); }
  window.openCrudModal=openCrudModal; window.closeCrudModal=closeCrudModal; if(wsAdd) wsAdd.addEventListener('click',()=>openCrudModal('create'));

  async function apiCall(payload){ const res=await fetch('wss/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}); const text=await res.text(); if(!res.ok) throw new Error(text||('HTTP '+res.status)); return JSON.parse(text); }
  async function deleteRow(row){ if(!row) return; const id=parseInt(row.dataset.id||'0',10); if(!id) return alert('Invalid ID'); if(!confirm('Delete this request?')) return; try{ const result=await apiCall({action:'delete', id}); if(result.success){ row.remove(); applyWsFilters(); } else alert('Delete failed.'); }catch(e){ console.error(e); alert('Server error.'); } }
  window.deleteRow=deleteRow;

  wsCrudForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const mode=wsCrudForm.elements['mode'].value; const id=parseInt(wsCrudForm.elements['id'].value||'0',10); const payload={}; if(mode==='edit'){ if(!id) return alert('Invalid ID'); payload.action='update'; payload.id=id; ['status','urgency','preferred_date','address','service_details'].forEach(k=>{ const v=wsCrudForm.elements[k]?.value ?? null; if(v!==null) payload[k]=v; }); } else { payload.action='create'; payload.service_type=wsCrudForm.elements['service_type'].value; payload.user_email=wsCrudForm.elements['user_email'].value; payload.full_name=wsCrudForm.elements['full_name'].value; payload.email=wsCrudForm.elements['email'].value; payload.phone=wsCrudForm.elements['phone'].value; payload.address=wsCrudForm.elements['address'].value; payload.service_details=wsCrudForm.elements['service_details'].value; payload.preferred_date=wsCrudForm.elements['preferred_date'].value; payload.urgency=wsCrudForm.elements['urgency'].value; } try{ const result=await apiCall(payload); if(!result.success) throw new Error('Operation failed'); alert('Saved successfully'); location.reload(); } catch(err){ console.error(err); alert('Error saving data.'); } });

  applyWsFilters();
</script>
