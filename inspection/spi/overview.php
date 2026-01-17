<?php
// Inspector full-CRUD list for SPI: business-permit, health-inspection
$types = ['business-permit','health-inspection'];
$typeLabels = [
  'business-permit' => 'Business Permit',
  'health-inspection' => 'Health Inspection',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Error fetching SPI service requests (inspector): ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Aggregate stats for overview
$totals = [
  'all' => 0,
  'permits' => 0,
  'inspections' => 0,
  'pending' => 0,
  'in_progress' => 0,
  'completed' => 0,
  'cancelled' => 0,
  'unique_users' => 0,
];
$userMap = [];
foreach ($requests as $row) {
  $totals['all']++;
  if (($row['service_type'] ?? '') === 'business-permit') $totals['permits']++;
  if (($row['service_type'] ?? '') === 'health-inspection') $totals['inspections']++;
  $st = $row['status'] ?? 'pending';
  if (isset($totals[$st])) $totals[$st]++;
  if (isset($row['user_id'])) $userMap[(int)$row['user_id']] = true;
}
$totals['unique_users'] = count($userMap);
?>

<div class="mb-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">SPI Requests (Inspector)</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Create, review, and manage Business Permits and Health Inspections end-to-end.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
        Total Requests: <span class="ml-1"><?php echo number_format((int)$totals['all']); ?></span>
      </div>
      <button id="spi-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
      <button id="spi-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
      <button id="spi-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add New</button>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="mt-4 flex flex-wrap items-center gap-3">
    <a href="inspector.php?page=spi&view=permit" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-indigo-500/40 dark:bg-indigo-500/20 dark:text-indigo-100 dark:hover:bg-indigo-500/30">Business Permits</a>
    <a href="inspector.php?page=spi&view=sanitation" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Health Inspections</a>
  </div>

  <!-- Stats Cards -->
  <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
      <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unique Citizens</div>
      <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white"><?php echo (int)$totals['unique_users']; ?></div>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-lg dark:border-amber-500/40 dark:bg-amber-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-100">Business Permits</div>
      <div class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-100"><?php echo (int)$totals['permits']; ?></div>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-lg dark:border-emerald-500/40 dark:bg-emerald-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-100">Health Inspections</div>
      <div class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-100"><?php echo (int)$totals['inspections']; ?></div>
    </div>
    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-lg dark:border-sky-500/40 dark:bg-sky-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-100">Pending</div>
      <div class="mt-2 text-3xl font-bold text-sky-700 dark:text-sky-100"><?php echo (int)$totals['pending']; ?></div>
    </div>
  </div>
  <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5 shadow-lg dark:border-violet-500/40 dark:bg-violet-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-100">In Progress</div>
      <div class="mt-2 text-2xl font-semibold text-violet-700 dark:text-violet-100"><?php echo (int)$totals['in_progress']; ?></div>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-lg dark:border-emerald-500/40 dark:bg-emerald-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-100">Completed</div>
      <div class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-100"><?php echo (int)$totals['completed']; ?></div>
    </div>
    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-lg dark:border-rose-500/40 dark:bg-rose-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-100">Cancelled</div>
      <div class="mt-2 text-2xl font-semibold text-rose-700 dark:text-rose-100"><?php echo (int)$totals['cancelled']; ?></div>
    </div>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <!-- Filters -->
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-5">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
      <select id="spi-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="spi-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
      <select id="spi-filter-urgency" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
        <option value="emergency">Emergency</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="spi-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="spi-table">
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
      <tbody id="spi-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No SPI service requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr class="spi-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
                data-id="<?php echo h($r['id']); ?>"
                data-service="<?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>"
                data-name="<?php echo h($r['full_name']); ?>"
                data-email="<?php echo h($r['email']); ?>"
                data-phone="<?php echo h($r['phone']); ?>"
                data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
                data-urgency="<?php echo h($r['urgency'] ?? ''); ?>"
                data-status="<?php echo h($r['status']); ?>"
                data-created="<?php echo h($r['created_at']); ?>"
                data-type="<?php echo h($r['service_type']); ?>"
                data-address="<?php echo h($r['address']); ?>"
                data-details="<?php echo h($r['service_details']); ?>">
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-white"><?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?></td>
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
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="spi-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="spi-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">SPI Request Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="spi-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#spi-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="spi-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Edit SPI Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="edit" />
      <input type="hidden" name="id" />

      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
        <select name="service_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="business-permit">Business Permit</option>
          <option value="health-inspection">Health Inspection</option>
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
  <style>#spi-crud-modal.show { display:flex; }</style>
</div>

<script>
  const spiBody = document.getElementById('spi-tbody');
  const spiSearch = document.getElementById('spi-search');
  const spiFilterType = document.getElementById('spi-filter-type');
  const spiFilterStatus = document.getElementById('spi-filter-status');
  const spiFilterUrgency = document.getElementById('spi-filter-urgency');
  const spiTable = document.getElementById('spi-table');
  const spiExport = document.getElementById('spi-export');
  const spiPagination = document.getElementById('spi-pagination');
  const spiAdd = document.getElementById('spi-add');
  let spiSortKey = 'id';
  let spiSortAsc = false;
  let spiPage = 1;
  const spiPageSize = 10;

  function spiRows(){ return Array.from(spiBody?.querySelectorAll('.spi-row')||[]); }

  function applySpiFilters() {
    const q = (spiSearch?.value || '').toLowerCase();
    const ft = (spiFilterType?.value || '').toLowerCase();
    const fs = (spiFilterStatus?.value || '').toLowerCase();
    const fu = (spiFilterUrgency?.value || '').toLowerCase();
    if (!spiBody) return;
    let rows = spiRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.getAttribute('data-type')||'').toLowerCase();
      const s = (tr.getAttribute('data-status')||'').toLowerCase();
      const u = (tr.getAttribute('data-urgency')||'').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t===ft) && (!fs || s===fs) && (!fu || u===fu)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a,b)=>{
      const ka = (a.dataset[spiSortKey]||'').toLowerCase();
      const kb = (b.dataset[spiSortKey]||'').toLowerCase();
      if (ka < kb) return spiSortAsc ? -1 : 1;
      if (ka > kb) return spiSortAsc ? 1 : -1;
      return 0;
    });
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / spiPageSize));
    if (spiPage > pages) spiPage = pages;
    const start = (spiPage - 1) * spiPageSize;
    const end = start + spiPageSize;
    const visible = new Set(rows.slice(start, end));
    spiRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderSpiPagination(pages);
  }

  function renderSpiPagination(pages){
    if (!spiPagination) return;
    spiPagination.innerHTML = '';
    const baseBtn = () => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'px-3 py-1 rounded-lg border text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 border-gray-300 bg-white text-gray-600 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
      return btn;
    };
    const prev = baseBtn();
    prev.textContent = 'Prev';
    prev.disabled = spiPage <= 1;
    if (prev.disabled) {
      prev.classList.add('opacity-60','cursor-not-allowed');
    } else {
      prev.onclick = () => { spiPage--; applySpiFilters(); };
    }
    spiPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b = baseBtn();
      b.textContent = i;
      if (i === spiPage) {
        b.className = 'px-3 py-1 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        b.onclick = () => { spiPage = i; applySpiFilters(); };
      }
      spiPagination.appendChild(b);
    }
    const next = baseBtn();
    next.textContent = 'Next';
    next.disabled = spiPage >= pages;
    if (next.disabled) {
      next.classList.add('opacity-60','cursor-not-allowed');
    } else {
      next.onclick = () => { spiPage++; applySpiFilters(); };
    }
    spiPagination.appendChild(next);
  }

  [spiSearch, spiFilterType, spiFilterStatus, spiFilterUrgency].forEach(el => {
    if (el) el.addEventListener('input', applySpiFilters);
    if (el) el.addEventListener('change', applySpiFilters);
  });
  const spiClearBtn = document.getElementById('spi-clear');
  if (spiClearBtn){ spiClearBtn.addEventListener('click', function(){ if(spiSearch) spiSearch.value=''; if(spiFilterType) spiFilterType.value=''; if(spiFilterStatus) spiFilterStatus.value=''; if(spiFilterUrgency) spiFilterUrgency.value=''; spiPage=1; applySpiFilters(); }); }

  if (spiTable) {
    spiTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (spiSortKey === key) { spiSortAsc = !spiSortAsc; } else { spiSortKey = key; spiSortAsc = true; }
        applySpiFilters();
      });
    });
  }

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function spiToCSV(){
    const headers = ['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = spiRows().filter(tr => tr.style.display !== 'none');
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
      ].map(val => '"' + (val||'').replace(/"/g,'""') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (spiExport) spiExport.addEventListener('click', () => download('spi_requests.csv', spiToCSV()));

  function viewField(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">\n  <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>\n  <div class="break-words text-sm text-gray-900 whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n</div>`;
  }
  function openViewModal(row){
    const modal = document.getElementById('spi-view-modal');
    const details = document.getElementById('spi-view-details');
    if (!modal || !details || !row) return;
    const typeLabel = {
      'business-permit':'Business Permit',
      'health-inspection':'Health Inspection'
    }[row.dataset.type] || row.dataset.type || '';
    details.innerHTML = `
      ${viewField('Request ID', '#' + (row.dataset.id || ''))}
      ${viewField('Service', typeLabel)}
      ${viewField('Status', (row.dataset.status || '').toUpperCase())}
      ${viewField('Urgency', (row.dataset.urgency || '').toUpperCase())}
      ${viewField('Created At', row.dataset.created || '')}
      ${viewField('Full Name', row.dataset.name || '')}
      ${viewField('Email', row.dataset.email || '')}
      ${viewField('Phone', row.dataset.phone || '')}
      ${viewField('Preferred Date', row.dataset.preferred_date || '')}
      ${viewField('Address', row.dataset.address || '')}
      ${viewField('Service Details', row.dataset.details || '')}
    `;
    modal.classList.add('show');
    modal.classList.remove('hidden');
  }
  function closeViewModal(){
    const modal = document.getElementById('spi-view-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  // CRUD modal handling
  const crudModal = document.getElementById('spi-crud-modal');
  const crudForm = document.getElementById('crud-form');
  const crudTitle = document.getElementById('crud-title');

  function fillFormFromRow(row){
    const f = crudForm;
    f.elements['id'].value = row.dataset.id || '';
    f.elements['service_type'].value = row.dataset.type || '';
    f.elements['full_name'].value = row.dataset.name || '';
    f.elements['email'].value = row.dataset.email || '';
    f.elements['phone'].value = row.dataset.phone || '';
    f.elements['address'].value = row.dataset.address || '';
    f.elements['service_details'].value = row.dataset.details || '';
    f.elements['preferred_date'].value = row.dataset.preferred_date || '';
    f.elements['urgency'].value = (row.dataset.urgency || 'medium').toLowerCase();
    f.elements['status'].value = (row.dataset.status || 'pending').toLowerCase();
  }

  function openCrudModal(mode, row){
    if (!crudModal) return;
    crudForm.reset();
    crudForm.elements['mode'].value = mode;
    if (mode === 'edit' && row) {
      crudTitle.textContent = 'Edit SPI Request';
      fillFormFromRow(row);
    } else {
      crudTitle.textContent = 'Add SPI Request';
      // defaults
      crudForm.elements['status'].value = 'pending';
      crudForm.elements['urgency'].value = 'medium';
    }
    crudModal.classList.add('show');
    crudModal.classList.remove('hidden');
  }
  function closeCrudModal(){ if (!crudModal) return; crudModal.classList.remove('show'); crudModal.classList.add('hidden'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal;
  if (spiAdd) spiAdd.addEventListener('click', () => openCrudModal('create'));

  async function apiCall(payload){
    const res = await fetch('spi/api.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
    const text = await res.text();
    if (!res.ok) throw new Error(text || ('HTTP '+res.status));
    return JSON.parse(text);
  }

  async function deleteRow(row){
    if (!row) return;
    const id = parseInt(row.dataset.id||'0',10);
    if (!id) return alert('Invalid ID');
    if (!confirm('Delete this request? This cannot be undone.')) return;
    try {
      const result = await apiCall({ action:'delete', id });
      if (result.success){ row.remove(); applySpiFilters(); }
      else alert('Delete failed.');
    } catch(e){ console.error(e); alert('Server error.'); }
  }
  window.deleteRow = deleteRow;

  crudForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const mode = crudForm.elements['mode'].value;
    const id = parseInt(crudForm.elements['id'].value||'0',10);
    const payload = {};
    if (mode === 'edit') {
      if (!id) return alert('Invalid ID');
      payload.action = 'update';
      payload.id = id;
      ['status','urgency','preferred_date','address','service_details'].forEach(k=>{
        const v = crudForm.elements[k]?.value ?? null;
        if (v !== null) payload[k] = v;
      });
    } else {
      payload.action = 'create';
      payload.service_type = crudForm.elements['service_type'].value;
      payload.user_email = crudForm.elements['user_email'].value; // lookup
      payload.full_name = crudForm.elements['full_name'].value;
      payload.email = crudForm.elements['email'].value;
      payload.phone = crudForm.elements['phone'].value;
      payload.address = crudForm.elements['address'].value;
      payload.service_details = crudForm.elements['service_details'].value;
      payload.preferred_date = crudForm.elements['preferred_date'].value;
      payload.urgency = crudForm.elements['urgency'].value;
    }
    try {
      const result = await apiCall(payload);
      if (!result.success) throw new Error('Operation failed');
      alert('Saved successfully');
      location.reload();
    } catch(err){ console.error(err); alert('Error saving data.'); }
  });

  // Initialize filters
  applySpiFilters();
</script>
