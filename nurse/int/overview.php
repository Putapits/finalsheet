<?php
// Nurse view + CRUD for Vaccination and Nutrition Monitoring
$types = ['vaccination','nutrition-monitoring'];
$typeLabels = [
  'vaccination' => 'Vaccination',
  'nutrition-monitoring' => 'Nutrition Monitoring',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Error fetching INT requests: ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<section class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
  <div class="flex flex-col gap-3">
    <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">Immunization & Nutrition</span>
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">INT Service Overview</h2>
      <p class="text-gray-600 dark:text-gray-400">Track vaccination drives, monitor nutrition requests, and manage incoming actions.</p>
    </div>
    <div class="inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-5 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
      Total Requests: <span class="ml-2 text-base text-primary dark:text-primary-100"><?php echo number_format(count($requests)); ?></span>
    </div>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <button id="int-export" type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Export CSV</button>
    <button id="int-add" type="button" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add Request</button>
  </div>
</section>

<?php
  $statusKeys = ['pending','in_progress','completed','cancelled'];
  $byStatus = array_fill_keys($statusKeys, 0);
  $byType = array_fill_keys($types, 0);
  foreach ($requests as $r) {
    $s = strtolower($r['status'] ?? 'pending'); if (isset($byStatus[$s])) $byStatus[$s]++;
    $t = $r['service_type'] ?? ''; if (isset($byType[$t])) $byType[$t]++;
  }
?>

<section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-6">
  <?php
    $metricCards = [
      [
        'label' => 'Total Requests',
        'value' => count($requests),
        'icon' => 'M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20 10 10 0 010-20z',
        'accent' => 'text-primary dark:text-primary-200',
      ],
      [
        'label' => 'Pending',
        'value' => $byStatus['pending'],
        'icon' => 'M12 6v6l4 2',
        'accent' => 'text-amber-500',
      ],
      [
        'label' => 'In Progress',
        'value' => $byStatus['in_progress'],
        'icon' => 'M5 13l4 4L19 7',
        'accent' => 'text-emerald-500',
      ],
      [
        'label' => 'Completed',
        'value' => $byStatus['completed'],
        'icon' => 'M12 8v8m0 0l-3-3m3 3l3-3',
        'accent' => 'text-sky-500',
      ],
      [
        'label' => 'Cancelled',
        'value' => $byStatus['cancelled'],
        'icon' => 'M18.364 5.636L5.636 18.364m0-12.728L18.364 18.364',
        'accent' => 'text-rose-500',
      ],
      [
        'label' => 'Vaccination',
        'value' => $byType['vaccination'],
        'icon' => 'M12 8c-1.657 0-3 1.79-3 4s1.343 4 3 4 3-1.79 3-4-1.343-4-3-4zm0-6a9 9 0 00-9 9c0 4.636 3.582 10.065 8.236 11.575a1 1 0 00.528 0C17.418 21.065 21 15.636 21 11a9 9 0 00-9-9z',
        'accent' => 'text-fuchsia-500',
      ],
      [
        'label' => 'Nutrition Monitoring',
        'value' => $byType['nutrition-monitoring'],
        'icon' => 'M12 3v18m9-9H3',
        'accent' => 'text-orange-500',
      ],
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
  <button type="button" onclick="location.href='nurse.php?page=int&view=vaccination'" class="flex w-full items-center justify-between rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-left shadow-sm transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:hover:border-emerald-400/40 dark:hover:bg-emerald-500/15">
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">Vaccination</p>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Schedule immunization drives and review citizen eligibility.</p>
    </div>
    <svg class="h-5 w-5 text-emerald-500 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L20 11m0 0l-6.5 6.5M20 11H4" /></svg>
  </button>
  <button type="button" onclick="location.href='nurse.php?page=int&view=nutrition'" class="flex w-full items-center justify-between rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4 text-left shadow-sm transition hover:border-orange-300 hover:bg-orange-100 dark:border-orange-500/30 dark:bg-orange-500/10 dark:hover:border-orange-400/40 dark:hover:bg-orange-500/15">
    <div>
      <p class="text-xs font-semibold uppercase tracking-wide text-orange-500 dark:text-orange-300">Nutrition Monitoring</p>
      <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Manage nutritional assistance and prioritize follow-ups.</p>
    </div>
    <svg class="h-5 w-5 text-orange-500 dark:text-orange-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L20 11m0 0l-6.5 6.5M20 11H4" /></svg>
  </button>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
  <!-- Filters -->
  <div class="grid grid-cols-1 gap-3 md:grid-cols-4 md:gap-4 mb-4">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
      <select id="int-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="int-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
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
        <input id="int-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:placeholder-gray-500">
        <svg class="pointer-events-none absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>
  <div class="flex justify-end">
    <button type="button" id="int-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-800 dark:text-gray-200" id="int-table">
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
      <tbody id="int-tbody" class="divide-y divide-gray-100 dark:divide-slate-800">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-400 dark:text-gray-500">No INT requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
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
              $serviceLabel = $typeLabels[$r['service_type']] ?? $r['service_type'];
            ?>
            <tr class="int-row border-b border-transparent transition-colors hover:bg-gray-50 dark:hover:bg-slate-800/60"
                data-id="<?php echo h($r['id']); ?>"
                data-service="<?php echo h($serviceLabel); ?>"
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
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($serviceLabel); ?></td>
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
                <button type="button" class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openViewModal(this.closest('tr'))">View</button>
                <button type="button" class="mr-2 inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
                <button type="button" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-200 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/15" onclick="deleteRow(this.closest('tr'))">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="int-pagination" class="mt-4 flex items-center justify-end gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="int-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">INT Request Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="int-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#int-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="int-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[95%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Edit INT Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="edit" />
      <input type="hidden" name="id" />

      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Type</label>
        <select name="service_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="vaccination">Vaccination</option>
          <option value="nutrition-monitoring">Nutrition Monitoring</option>
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
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#int-crud-modal.show { display:flex; }</style>
</div>

<script>
  const intSearch = document.getElementById('int-search');
  const intBody = document.getElementById('int-tbody');
  const intFilterType = document.getElementById('int-filter-type');
  const intFilterStatus = document.getElementById('int-filter-status');
  const intTable = document.getElementById('int-table');
  const intExport = document.getElementById('int-export');
  const intPagination = document.getElementById('int-pagination');
  const intAdd = document.getElementById('int-add');
  let intSortKey = 'id';
  let intSortAsc = false;
  let intPage = 1;
  const intPageSize = 10;

  function intRows(){ return Array.from(intBody.querySelectorAll('.int-row')); }

  function applyIntFilters() {
    const q = (intSearch?.value || '').toLowerCase();
    const ft = (intFilterType?.value || '').toLowerCase();
    const fs = (intFilterStatus?.value || '').toLowerCase();
    if (!intBody) return;
    let rows = intRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.getAttribute('data-type')||'').toLowerCase();
      const s = (tr.getAttribute('data-status')||'').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t===ft) && (!fs || s===fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    // sort
    rows.sort((a,b)=>{
      const ka = (a.dataset[intSortKey]||'').toLowerCase();
      const kb = (b.dataset[intSortKey]||'').toLowerCase();
      if (ka < kb) return intSortAsc ? -1 : 1;
      if (ka > kb) return intSortAsc ? 1 : -1;
      return 0;
    });
    // pagination
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / intPageSize));
    if (intPage > pages) intPage = pages;
    const start = (intPage - 1) * intPageSize;
    const end = start + intPageSize;
    const visible = new Set(rows.slice(start, end));
    intRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderIntPagination(pages);
  }

  function renderIntPagination(pages){
    if (!intPagination) return;
    intPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = intPage <= 1;
    prev.onclick = () => { intPage--; applyIntFilters(); };
    intPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i===intPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { intPage = i; applyIntFilters(); };
      intPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = intPage >= pages;
    next.onclick = () => { intPage++; applyIntFilters(); };
    intPagination.appendChild(next);
  }

  [intSearch, intFilterType, intFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applyIntFilters);
    if (el) el.addEventListener('change', applyIntFilters);
  });

  if (intTable) {
    intTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (intSortKey === key) { intSortAsc = !intSortAsc; } else { intSortKey = key; intSortAsc = true; }
        applyIntFilters();
      });
    });
  }

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function intToCSV(){
    const headers = ['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = intRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0,9).map(td => '"' + (td.innerText||'').replace(/"/g,'\\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (intExport) intExport.addEventListener('click', () => download('int_requests.csv', intToCSV()));

  // View modal helpers
  function viewField(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="modal-field border rounded p-3">\n      <div class="text-gray-400 text-xs mb-1">${label}</div>\n      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n    </div>`;
  }
  function openViewModal(row){
    const modal = document.getElementById('int-view-modal');
    const details = document.getElementById('int-view-details');
    if (!modal || !details || !row) return;
    const typeLabel = {'vaccination':'Vaccination','nutrition-monitoring':'Nutrition Monitoring'}[row.dataset.type] || row.dataset.type;
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
    const modal = document.getElementById('int-view-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  // CRUD modal handling
  const crudModal = document.getElementById('int-crud-modal');
  const crudForm = document.getElementById('crud-form');
  const crudTitle = document.getElementById('crud-title');

  function fillFormFromRow(row){
    const f = crudForm;
    f.elements['id'].value = row.dataset.id || '';
    f.elements['service_type'].value = row.dataset.type || 'vaccination';
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
      crudTitle.textContent = 'Edit INT Request';
      fillFormFromRow(row);
    } else {
      crudTitle.textContent = 'Add INT Request';
      crudForm.elements['status'].value = 'pending';
      crudForm.elements['urgency'].value = 'medium';
    }
    crudModal.classList.add('show');
    crudModal.classList.remove('hidden');
  }
  function closeCrudModal(){ if (!crudModal) return; crudModal.classList.remove('show'); crudModal.classList.add('hidden'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal;
  if (intAdd) intAdd.addEventListener('click', () => openCrudModal('create'));

  async function apiCall(payload){
    const res = await fetch('int/api.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
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
      if (result.success){ row.remove(); applyIntFilters(); }
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
      payload.user_email = crudForm.elements['user_email'].value;
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

  // Initialize filters on load
  applyIntFilters();
</script>
