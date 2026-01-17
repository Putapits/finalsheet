<?php
// Inspector view: Assigned Sanitary Permit Inspections
$inspectorId = (int) ($_SESSION['user_id'] ?? 0);
try {
  $sql = "SELECT spa.*, u.first_name, u.last_name, u.email AS user_email, u.phone AS user_phone,
                 sps.status AS insp_status, sps.details AS insp_details, sps.user_id AS assigned_user_id
          FROM sanitary_permit_applications spa
          LEFT JOIN users u ON u.id = spa.user_id
          LEFT JOIN sanitary_permit_steps sps
                 ON sps.id = (
                    SELECT id FROM sanitary_permit_steps
                    WHERE application_id = spa.id AND step = 'inspection'
                    ORDER BY id DESC LIMIT 1
                 )
          ORDER BY spa.created_at DESC";
  $stmt = $db->prepare($sql);
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $requests = [];
  foreach ($rows as $r) {
    $det = [];
    if (!empty($r['insp_details'])) {
      $tmp = json_decode($r['insp_details'], true);
      if (is_array($tmp))
        $det = $tmp;
    }

    // Check if assigned to this inspector (check both JSON and column)
    $assignedId = null;
    if (isset($det['assigned_inspector_id'])) {
      $assignedId = (int) $det['assigned_inspector_id'];
    } elseif (isset($det['inspector_id'])) {
      $assignedId = (int) $det['inspector_id'];
    } elseif (!empty($r['assigned_user_id'])) {
      $assignedId = (int) $r['assigned_user_id'];
    }

    if ($assignedId !== (int) $inspectorId) {
      continue;
    }

    $full_name = trim((string) ($r['owner_name'] ?? ''));
    if ($full_name === '')
      $full_name = trim((string) ($r['first_name'] ?? '') . ' ' . (string) ($r['last_name'] ?? ''));
    if ($full_name === '')
      $full_name = (string) ($r['establishment_name'] ?? '');

    $requests[] = [
      'id' => (int) $r['id'],
      'application_id' => (int) $r['id'],
      'full_name' => $full_name,
      'email' => (string) ($r['user_email'] ?? ''),
      'phone' => (string) ($r['user_phone'] ?? ''),
      'preferred_date' => (string) ($det['scheduled_date'] ?? $r['preferred_date'] ?? ''),
      'urgency' => (string) ($r['urgency'] ?? 'medium'),
      'status' => (string) ($r['insp_status'] ?? 'assigned'),
      'created_at' => (string) ($r['created_at'] ?? ''),
      'address' => (string) ($r['establishment_address'] ?? ''),
      'service_details' => 'Establishment: ' . (string) ($r['establishment_name'] ?? ''),
      'establishment_name' => (string) ($r['establishment_name'] ?? ''),
      'scheduled_date' => (string) ($det['scheduled_date'] ?? ''),
      'result' => (string) ($det['result'] ?? ''),
      'findings' => (string) ($det['findings'] ?? ''),
      'inspector_name' => (string) ($det['assigned_inspector_name'] ?? ($det['inspector_name'] ?? '')),
    ];
  }
} catch (Exception $e) {
  error_log('Error fetching assigned SPI inspections: ' . $e->getMessage());
  $requests = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="mb-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Assigned Sanitary Permit Inspections</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">View assigned applications and submit your inspection schedule
        and results.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div
        class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
        Total Requests: <span class="ml-1"><?php echo number_format(count($requests)); ?></span>
      </div>
      <button id="spi-clear"
        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear
        Filters</button>
      <button id="spi-export"
        class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export
        CSV</button>
    </div>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <!-- Filters -->
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-4">
    <div>
      <label
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="spi-filter-status"
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div>
      <label
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
      <select id="spi-filter-urgency"
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
        <option value="emergency">Emergency</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="spi-search" type="text" placeholder="Search name, email, phone, details..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="spi-table">
      <thead>
        <tr
          class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="name">Full Name</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="preferred_date">Scheduled Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="urgency">Urgency</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="spi-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No assigned inspections found.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr
              class="spi-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>" data-app_id="<?php echo h($r['application_id']); ?>"
              data-name="<?php echo h($r['full_name']); ?>" data-email="<?php echo h($r['email']); ?>"
              data-phone="<?php echo h($r['phone']); ?>" data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>" data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>" data-type="sanitation-permit"
              data-service="Sanitation Permit" data-address="<?php echo h($r['address']); ?>"
              data-establishment="<?php echo h($r['establishment_name'] ?? ''); ?>"
              data-inspector_name="<?php echo h($r['inspector_name'] ?? ''); ?>"
              data-scheduled_date="<?php echo h($r['scheduled_date'] ?? ''); ?>"
              data-result="<?php echo h($r['result'] ?? ''); ?>" data-findings="<?php echo h($r['findings'] ?? ''); ?>">
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
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
                <span
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
              </td>
              <td class="py-3 px-3">
                <?php $s = strtolower($r['status'] ?? 'pending');
                $statusClasses = [
                  'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                  'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                  'assigned' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-100',
                ];
                $statusClass = $statusClasses[$s] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
                ?>
                <span
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
              <td class="py-3 px-3 text-right">
                <button
                  class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40"
                  onclick="openInspectionModal(this.closest('tr'))">Inspect</button>
                <button
                  class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                  onclick="openViewModal(this.closest('tr'))">View</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="spi-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300">
  </div>
</section>

<!-- View Modal -->
<div id="spi-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div
    class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Sanitary Permit - Inspection Details</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeViewModal()">✕</button>
    </div>
    <div id="spi-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200">
    </div>
  </div>
  <style>
    #spi-view-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- Inspection Modal -->
<div id="spi-inspection-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeInspectionModal()"></div>
  <div
    class="relative max-h-[90vh] w-[92%] max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Record Inspection</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeInspectionModal()">✕</button>
    </div>
    <form id="inspection-form" class="grid grid-cols-1 gap-4 text-sm">
      <input type="hidden" name="application_id" />
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Scheduled
          Date</label>
        <input name="scheduled_date" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Result</label>
        <select name="result"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="">-- Select --</option>
          <option value="passed">Passed</option>
          <option value="failed">Failed</option>
        </select>
      </div>
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Findings</label>
        <textarea name="findings" rows="4"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
          onclick="closeInspectionModal()">Cancel</button>
        <button id="inspection-save" type="submit"
          class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>
    #spi-inspection-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- CRUD Modal -->
<div id="spi-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div
    class="relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Edit Business Permit</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeCrudModal()">✕</button>
    </div>
    <form id="crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="edit" />
      <input type="hidden" name="id" />
      <input type="hidden" name="service_type" value="business-permit" />

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User
          Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full
          Name</label>
        <input name="full_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
        <input name="email" type="email"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
        <input name="phone" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service
          Details</label>
        <textarea name="service_details" rows="4"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>

      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred
          Date</label>
        <input name="preferred_date" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
        <select name="urgency"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
          onclick="closeCrudModal()">Cancel</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>
    #spi-crud-modal.show {
      display: flex;
    }
  </style>
</div>

<script>
  const spiBody = document.getElementById('spi-tbody');
  const spiSearch = document.getElementById('spi-search');
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

  function spiRows() { return Array.from(spiBody?.querySelectorAll('.spi-row') || []); }

  function applySpiFilters() {
    const q = (spiSearch?.value || '').toLowerCase();
    const fs = (spiFilterStatus?.value || '').toLowerCase();
    const fu = (spiFilterUrgency?.value || '').toLowerCase();
    if (!spiBody) return;
    let rows = spiRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const s = (tr.getAttribute('data-status') || '').toLowerCase();
      const u = (tr.getAttribute('data-urgency') || '').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!fs || s === fs) && (!fu || u === fu)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => {
      const va = (a.dataset[spiSortKey] || '').toLowerCase();
      const vb = (b.dataset[spiSortKey] || '').toLowerCase();
      if (spiSortKey === 'id') {
        return (spiSortAsc ? 1 : -1) * ((parseInt(va, 10) || 0) - (parseInt(vb, 10) || 0));
      }
      if (va < vb) return spiSortAsc ? -1 : 1;
      if (va > vb) return spiSortAsc ? 1 : -1;
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

  function renderSpiPagination(pages) {
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
      prev.classList.add('opacity-60', 'cursor-not-allowed');
    } else {
      prev.onclick = () => { spiPage--; applySpiFilters(); };
    }
    spiPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
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
      next.classList.add('opacity-60', 'cursor-not-allowed');
    } else {
      next.onclick = () => { spiPage++; applySpiFilters(); };
    }
    spiPagination.appendChild(next);
  }

  [spiSearch, spiFilterStatus, spiFilterUrgency].forEach(el => {
    if (el) el.addEventListener('input', applySpiFilters);
    if (el) el.addEventListener('change', applySpiFilters);
  });
  const spiClearBtn = document.getElementById('spi-clear');
  if (spiClearBtn) { spiClearBtn.addEventListener('click', function () { if (spiSearch) spiSearch.value = ''; if (spiFilterStatus) spiFilterStatus.value = ''; if (spiFilterUrgency) spiFilterUrgency.value = ''; spiPage = 1; applySpiFilters(); }); }

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
  function spiToCSV() {
    const headers = ['ID', 'Service', 'Full Name', 'Email', 'Phone', 'Scheduled Date', 'Urgency', 'Status', 'Created'];
    const rows = spiRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = [
        tr.dataset.id || '',
        tr.dataset.service || 'Sanitation Permit',
        tr.dataset.name || '',
        tr.dataset.email || '',
        tr.dataset.phone || '',
        tr.dataset.preferred_date || '',
        tr.dataset.urgency || '',
        tr.dataset.status || '',
        tr.dataset.created || '',
      ].map(val => '"' + (val || '').replace(/"/g, '""') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (spiExport) spiExport.addEventListener('click', () => download('spi_assigned_inspections.csv', spiToCSV()));

  // View modal helpers
  function viewField(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">\n  <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>\n  <div class="break-words text-sm text-gray-900 whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>\n</div>`;
  }
  function openViewModal(row) {
    const modal = document.getElementById('spi-view-modal');
    const details = document.getElementById('spi-view-details');
    if (!modal || !details || !row) return;
    details.innerHTML = `
      ${viewField('Request ID', '#' + (row.dataset.id || ''))}
      ${viewField('Service', 'Sanitation Permit')}
      ${viewField('Status', (row.dataset.status || '').toUpperCase())}
      ${viewField('Urgency', (row.dataset.urgency || '').toUpperCase())}
      ${viewField('Created At', row.dataset.created || '')}
      ${viewField('Full Name', row.dataset.name || '')}
      ${viewField('Email', row.dataset.email || '')}
      ${viewField('Phone', row.dataset.phone || '')}
      ${viewField('Scheduled Date', row.dataset.scheduled_date || row.dataset.preferred_date || '')}
      ${viewField('Establishment Address', row.dataset.address || '')}
      ${viewField('Establishment', row.dataset.establishment || '')}
      ${viewField('Inspector', row.dataset.inspector_name || '')}
      ${viewField('Result', (row.dataset.result || '').toUpperCase())}
      ${viewField('Findings', row.dataset.findings || '')}
    `;
    modal.classList.add('show');
    modal.classList.remove('hidden');
  }
  function closeViewModal() {
    const modal = document.getElementById('spi-view-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  // Inspection modal logic
  const inspModal = document.getElementById('spi-inspection-modal');
  const inspForm = document.getElementById('inspection-form');
  const inspSave = document.getElementById('inspection-save');
  let inspRow = null;

  function openInspectionModal(row) {
    inspRow = row;
    if (!inspModal || !inspForm || !row) return;
    inspForm.reset();
    inspForm.elements['application_id'].value = row.dataset.app_id || '';
    inspForm.elements['scheduled_date'].value = row.dataset.scheduled_date || row.dataset.preferred_date || '';
    inspForm.elements['findings'].value = row.dataset.findings || '';
    const res = (row.dataset.result || '').toLowerCase();
    if (res === 'passed' || res === 'failed') inspForm.elements['result'].value = res; else inspForm.elements['result'].value = '';
    inspModal.classList.add('show');
    inspModal.classList.remove('hidden');
  }
  function closeInspectionModal() { if (!inspModal) return; inspModal.classList.remove('show'); inspModal.classList.add('hidden'); inspRow = null; }
  window.openInspectionModal = openInspectionModal; window.closeInspectionModal = closeInspectionModal;

  inspForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const appId = parseInt(inspForm.elements['application_id'].value || '0', 10);
    if (!appId) { alert('Missing application ID.'); return; }
    const payload = {
      service_type: 'sanitation-workflow',
      workflow_step: 'inspection',
      application_id: appId,
      scheduled_date: inspForm.elements['scheduled_date'].value || null,
      result: inspForm.elements['result'].value || null,
      findings: inspForm.elements['findings'].value || null,
    };
    // Determine status
    payload.status = payload.result ? 'completed' : 'in_progress';
    try {
      inspSave.disabled = true; const old = inspSave.textContent; inspSave.textContent = 'Saving...';
      const res = await fetch('../process_service_request.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const text = await res.text();
      if (!res.ok) throw new Error(text || ('HTTP ' + res.status));
      const j = JSON.parse(text);
      if (!j.success) throw new Error(j.message || 'Failed');
      // Update row
      if (inspRow) {
        if (payload.scheduled_date) { inspRow.dataset.scheduled_date = payload.scheduled_date; inspRow.dataset.preferred_date = payload.scheduled_date; const cell = inspRow.children[4]; if (cell) cell.innerText = payload.scheduled_date; }
        if (payload.result) { inspRow.dataset.result = payload.result; }
        if (payload.findings) { inspRow.dataset.findings = payload.findings; }
        inspRow.dataset.status = payload.status;
        const cell = inspRow.children[6];
        if (cell) {
          const span = cell.querySelector('span');
          if (span) { span.textContent = payload.status.toUpperCase(); span.className = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ' + (payload.status === 'completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100' : (payload.status === 'in_progress' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-100')); }
        }
      }
      closeInspectionModal();
      alert('Inspection saved.');
    } catch (err) { console.error(err); alert('Failed to save inspection.'); }
    finally { inspSave.disabled = false; inspSave.textContent = 'Save'; }
  });

  // CRUD modal handling
  const crudModal = document.getElementById('spi-crud-modal');
  const crudForm = document.getElementById('crud-form');
  const crudTitle = document.getElementById('crud-title');
  let crudCurrentRow = null;

  function statusBadgeClass(status) {
    const base = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ';
    switch ((status || '').toLowerCase()) {
      case 'completed': return base + 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100';
      case 'in_progress': return base + 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100';
      case 'cancelled': return base + 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100';
      default: return base + 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200';
    }
  }
  function urgencyBadgeClass(urgency) {
    const base = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ';
    switch ((urgency || '').toLowerCase()) {
      case 'emergency': return base + 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100';
      case 'high': return base + 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100';
      case 'low': return base + 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
      default: return base + 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200';
    }
  }

  function fillFormFromRow(row) {
    const f = crudForm;
    f.elements['id'].value = row.dataset.id || '';
    f.elements['full_name'].value = row.dataset.name || '';
    f.elements['email'].value = row.dataset.email || '';
    f.elements['phone'].value = row.dataset.phone || '';
    f.elements['address'].value = row.dataset.address || '';
    f.elements['service_details'].value = row.dataset.details || '';
    f.elements['preferred_date'].value = row.dataset.preferred_date || '';
    f.elements['urgency'].value = (row.dataset.urgency || 'medium').toLowerCase();
    f.elements['status'].value = (row.dataset.status || 'pending').toLowerCase();
  }

  function openCrudModal(mode, row) {
    if (!crudModal) return;
    crudForm.reset();
    crudForm.elements['mode'].value = mode;
    crudCurrentRow = null;
    if (mode === 'edit' && row) {
      crudTitle.textContent = 'Edit Business Permit';
      fillFormFromRow(row);
      crudCurrentRow = row;
    } else {
      crudTitle.textContent = 'Add Business Permit';
      crudForm.elements['status'].value = 'pending';
      crudForm.elements['urgency'].value = 'medium';
    }
    crudModal.classList.add('show');
    crudModal.classList.remove('hidden');
  }
  function closeCrudModal() { if (!crudModal) return; crudModal.classList.remove('show'); crudModal.classList.add('hidden'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal;
  if (spiAdd) spiAdd.addEventListener('click', () => openCrudModal('create'));

  async function apiCall(payload) {
    const res = await fetch('spi/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const text = await res.text();
    if (!res.ok) throw new Error(text || ('HTTP ' + res.status));
    return JSON.parse(text);
  }

  async function deleteRow(row) {
    if (!row) return;
    const id = parseInt(row.dataset.id || '0', 10);
    if (!id) return alert('Invalid ID');
    if (!confirm('Delete this request? This cannot be undone.')) return;
    try {
      const result = await apiCall({ action: 'delete', id });
      if (result.success) { row.remove(); applySpiFilters(); }
      else alert('Delete failed.');
    } catch (e) { console.error(e); alert('Server error.'); }
  }
  window.deleteRow = deleteRow;

  crudForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const mode = crudForm.elements['mode'].value;
    const id = parseInt(crudForm.elements['id'].value || '0', 10);
    const payload = {};
    if (mode === 'edit') {
      if (!id) return alert('Invalid ID');
      payload.action = 'update';
      payload.id = id;
      ['status', 'urgency', 'preferred_date', 'address', 'service_details', 'full_name', 'email', 'phone'].forEach(k => {
        const v = crudForm.elements[k]?.value ?? null;
        if (v !== null) payload[k] = v;
      });
    } else {
      payload.action = 'create';
      payload.service_type = 'business-permit';
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
      if (mode === 'edit') {
        if (crudCurrentRow) {
          const row = crudCurrentRow;
          if (payload.preferred_date !== undefined) {
            row.dataset.preferred_date = payload.preferred_date;
            const cell = row.children[4];
            if (cell) cell.innerText = payload.preferred_date;
          }
          if (payload.urgency !== undefined) {
            row.dataset.urgency = payload.urgency;
            const cell = row.children[5];
            if (cell) {
              const span = cell.querySelector('span');
              if (span) {
                span.textContent = (payload.urgency || '').toUpperCase();
                span.className = urgencyBadgeClass(payload.urgency);
              }
            }
          }
          if (payload.status !== undefined) {
            row.dataset.status = payload.status;
            const cell = row.children[6];
            if (cell) {
              const span = cell.querySelector('span');
              if (span) {
                span.textContent = (payload.status || '').toUpperCase();
                span.className = statusBadgeClass(payload.status);
              }
            }
          }
          if (payload.full_name !== undefined) {
            row.dataset.name = payload.full_name;
            const cell = row.children[1];
            if (cell) cell.innerText = payload.full_name;
          }
          if (payload.email !== undefined) {
            row.dataset.email = payload.email;
            const cell = row.children[2];
            if (cell) cell.innerText = payload.email;
          }
          if (payload.phone !== undefined) {
            row.dataset.phone = payload.phone;
            const cell = row.children[3];
            if (cell) cell.innerText = payload.phone;
          }
          if (payload.address !== undefined) row.dataset.address = payload.address;
          if (payload.service_details !== undefined) row.dataset.details = payload.service_details;
        }
        crudCurrentRow = null;
        closeCrudModal();
        applySpiFilters();
        alert('Save Changes Successfully.');
      } else {
        alert('Saved successfully');
        location.reload();
      }
    } catch (err) { console.error(err); alert('Error saving data.'); }
  });

  applySpiFilters();
</script>