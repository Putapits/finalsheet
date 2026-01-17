<?php
// Inspector CRUD list for Health Inspections only
$types = ['health-inspection'];
$type = $types[0];
$typeLabel = 'Health Inspection';
$inspectorId = (int) ($_SESSION['user_id'] ?? 0);

try {
  $requests = [];

  // 1. Fetch direct health-inspection requests
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? AND (assigned_to = ? OR assigned_to IS NULL) AND deleted_at IS NULL ORDER BY created_at DESC");
  $stmt->execute([$type, $inspectorId]);
  $serviceReqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($serviceReqs as $r) {
    $r['source'] = 'service_request';
    $requests[] = $r;
  }

  // 2. Fetch sanitary permit application inspections assigned to me
  $sqlPermit = "SELECT spa.*, u.first_name, u.last_name, u.email AS user_email, u.phone AS user_phone,
                       sps.status AS insp_status, sps.details AS insp_details, sps.user_id AS assigned_user_id
                FROM sanitary_permit_applications spa
                LEFT JOIN users u ON u.id = spa.user_id
                LEFT JOIN sanitary_permit_steps sps
                       ON sps.id = (
                          SELECT id FROM sanitary_permit_steps
                          WHERE application_id = spa.id AND step = 'inspection'
                          ORDER BY id DESC LIMIT 1
                       )
                WHERE sps.user_id = ? OR (sps.id IS NOT NULL AND sps.user_id IS NULL)
                ORDER BY spa.created_at DESC";
  $stmtP = $db->prepare($sqlPermit);
  $stmtP->execute([$inspectorId]);
  $permitRows = $stmtP->fetchAll(PDO::FETCH_ASSOC);
  foreach ($permitRows as $pr) {
    $det = [];
    if (!empty($pr['insp_details'])) {
      $tmp = json_decode($pr['insp_details'], true);
      if (is_array($tmp))
        $det = $tmp;
    }

    // Check if specifically assigned to this inspector (ignore if it belongs to someone else)
    $assignedId = (int) ($pr['assigned_user_id'] ?? 0);
    if ($assignedId !== 0 && $assignedId !== $inspectorId)
      continue;

    $full_name = trim((string) ($pr['owner_name'] ?? ''));
    if ($full_name === '')
      $full_name = trim((string) ($pr['first_name'] ?? '') . ' ' . (string) ($pr['last_name'] ?? ''));
    if ($full_name === '')
      $full_name = (string) ($pr['establishment_name'] ?? '');

    $requests[] = [
      'id' => (int) $pr['id'],
      'source' => 'sanitary_permit',
      'full_name' => $full_name,
      'email' => (string) ($pr['user_email'] ?? ''),
      'phone' => (string) ($pr['user_phone'] ?? ''),
      'preferred_date' => (string) ($det['scheduled_date'] ?? $pr['preferred_date'] ?? ''),
      'urgency' => (string) ($pr['urgency'] ?? 'medium'),
      'status' => (string) ($pr['insp_status'] ?? 'assigned'),
      'created_at' => (string) ($pr['created_at'] ?? ''),
      'address' => (string) ($pr['establishment_address'] ?? ''),
      'service_details' => "Establishment: " . ($pr['establishment_name'] ?? 'N/A') . "\nIndustry: " . ($pr['industry'] ?? 'N/A') . " (" . ($pr['business_line'] ?? 'N/A') . ")",
      'result_data' => $det // Helper for the JS
    ];
  }

  // Final sort by created_at desc
  usort($requests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
  });

} catch (Exception $e) {
  error_log('Error fetching SPI sanitation (inspector): ' . $e->getMessage());
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
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Health Inspections (Inspector)</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Track and manage every sanitation inspection request from intake
        to completion.</p>
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
      <button id="spi-add"
        class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add
        New</button>
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
          <th class="py-3 px-3 cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="urgency">Urgency</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3">Result</th> <!-- New Result Column -->
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="spi-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="7" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No health inspection requests
              found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <?php
            $source = $r['source'] ?? 'service_request';
            $result = 'PENDING';
            
            if ($source === 'sanitary_permit') {
              $result = strtoupper($r['result_data']['result'] ?? 'PENDING');
            } else {
              $details = $r['service_details'];
              if (strpos($details, '[RESULT: PASS]') !== false) $result = 'PASS';
              if (strpos($details, '[RESULT: FAIL]') !== false) $result = 'FAIL';
            }

            $resultClass = 'bg-gray-100 text-gray-600';
            if ($result === 'PASS' || $result === 'PASSED')
              $resultClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300';
            if ($result === 'FAIL' || $result === 'FAILED')
              $resultClass = 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300';
            ?>
            <tr
              class="spi-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>" 
              data-source="<?php echo h($source); ?>"
              data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>" 
              data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>" 
              data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>" 
              data-type="<?php echo $source === 'sanitary_permit' ? 'sanitation-permit' : 'health-inspection'; ?>"
              data-service="<?php echo $source === 'sanitary_permit' ? 'Sanitation Permit' : 'Health Inspection'; ?>" 
              data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>"
              <?php if($source === 'sanitary_permit'): ?>
              data-findings="<?php echo h($r['result_data']['findings'] ?? ''); ?>"
              data-result="<?php echo h($r['result_data']['result'] ?? ''); ?>"
              <?php endif; ?>
              >
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">
                <?php echo $source === 'sanitary_permit' ? '#' . h($r['id']) : h($r['id']); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300">
                <div class="font-medium"><?php echo h($r['full_name']); ?></div>
                <div class="text-[10px] uppercase font-bold text-gray-400"><?php echo $source === 'sanitary_permit' ? 'Permit' : 'Service'; ?></div>
                <div class="text-xs opacity-70"><?php echo h($r['address']); ?></div>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? '-'); ?></td>
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
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
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
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
              </td>
              <td class="py-3 px-3">
                <span
                  class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-bold tracking-wider uppercase <?php echo $resultClass; ?>">
                  <?php echo $result; ?>
                </span>
              </td>
              <td class="py-3 px-3 text-right">
                <button
                  class="mr-2 inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                  onclick="openCrudModal('edit', this.closest('tr'))">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                    </path>
                  </svg>
                  Inspect
                </button>
                <button
                  class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                  onclick="deleteRow(this.closest('tr'))">Delete</button>
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

<!-- Inspection Modal -->
<div id="spi-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCrudModal()"></div>
  <div
    class="relative modal-panel bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl max-w-2xl w-[95%] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
    <div
      class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-900">
      <h3 id="crud-title" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Conduct Inspection
      </h3>
      <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
        onclick="closeCrudModal()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>

    <div class="overflow-y-auto flex-1 p-6">
      <form id="crud-form" class="space-y-6">
        <input type="hidden" name="mode" value="edit" />
        <input type="hidden" name="id" />
        <input type="hidden" name="service_type" value="health-inspection" />
        <!-- Hidden real details field -->
        <textarea name="service_details" class="hidden"></textarea>

        <!-- Citizen Request Section (Read Only) -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800/50">
          <label class="block text-xs font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300 mb-3">Citizen
            Request Details</label>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="block text-xs text-gray-500 dark:text-gray-400">Applicant</span>
              <span class="font-semibold text-gray-900 dark:text-white" id="display-name">-</span>
            </div>
            <div>
              <span class="block text-xs text-gray-500 dark:text-gray-400">Address</span>
              <span class="font-semibold text-gray-900 dark:text-white" id="display-address">-</span>
            </div>
            <div class="col-span-2">
              <span class="block text-xs text-gray-500 dark:text-gray-400">Request Notes</span>
              <p class="mt-1 text-gray-700 dark:text-gray-300 italic" id="display-details">-</p>
            </div>
          </div>
        </div>

        <!-- Inspector Findings Section -->
        <div>
          <label
            class="block text-sm font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-slate-800 pb-2">Inspection
            Findings</label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Inspection Result</label>
              <select id="insp-result"
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm font-medium shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="PENDING">Pending Assessment</option>
                <option value="PASS">✅ Pass</option>
                <option value="FAIL">❌ Fail</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Scheduled Inspection
                Date</label>
              <input type="date" id="insp-date"
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Status Update</label>
              <select name="status" id="insp-status"
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm font-medium shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress (Ongoing)</option>
                <option value="completed">Completed (Inspected)</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Inspector Remarks &
                Recommendations</label>
              <textarea id="insp-remarks" rows="4"
                placeholder="Enter detailed findings, corrective actions, or recommendations..."
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
            </div>
          </div>
        </div>

      </form>
    </div>

    <div class="p-5 border-t border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-900 flex justify-end gap-3">
      <button type="button"
        class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:bg-slate-800 dark:text-gray-200 dark:border-slate-600 dark:hover:bg-slate-700"
        onclick="closeCrudModal()">Cancel</button>
      <button type="button" onclick="submitCrudForm()"
        class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Submit
        Report</button>
    </div>
  </div>
</div>

<script>
  // Inspector ID for self-assignment
  const currentInspectorId = <?php echo $inspectorId; ?>;

  // ... (keep existing filters/pagination vars) ...
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

  function spiRows() { return spiBody ? Array.from(spiBody.querySelectorAll('.spi-row')) : []; }

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
      const ka = (a.dataset[spiSortKey] || '').toLowerCase();
      const kb = (b.dataset[spiSortKey] || '').toLowerCase();
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

  function renderSpiPagination(pages) {
    if (!spiPagination) return;
    spiPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-200 dark:bg-slate-700 hover:bg-gray-300 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200 rounded text-xs';
    prev.disabled = spiPage <= 1;
    prev.onclick = () => { spiPage--; applySpiFilters(); };
    spiPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded text-xs ' + (i === spiPage ? 'bg-emerald-600 text-white' : 'bg-gray-200 dark:bg-slate-700 hover:bg-gray-300 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200');
      b.onclick = () => { spiPage = i; applySpiFilters(); };
      spiPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-200 dark:bg-slate-700 hover:bg-gray-300 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-200 rounded text-xs';
    next.disabled = spiPage >= pages;
    next.onclick = () => { spiPage++; applySpiFilters(); };
    spiPagination.appendChild(next);
  }

  [spiSearch, spiFilterStatus, spiFilterUrgency].forEach(el => {
    if (el) el.addEventListener('input', applySpiFilters);
    if (el) el.addEventListener('change', applySpiFilters);
  });

  // Table sort
  if (spiTable) {
    spiTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (spiSortKey === key) { spiSortAsc = !spiSortAsc; } else { spiSortKey = key; spiSortAsc = true; }
        applySpiFilters();
      });
    });
  }

  // CRUD modal
  const crudModal = document.getElementById('spi-crud-modal');
  const crudForm = document.getElementById('crud-form');
  const crudTitle = document.getElementById('crud-title');
  let crudCurrentRow = null;

  function openCrudModal(mode, row) {
    if (!crudModal) return;
    crudForm.reset();
    crudForm.elements['mode'].value = mode;
    crudCurrentRow = null;

    // Clear custom fields
    document.getElementById('display-name').textContent = '-';
    document.getElementById('display-address').textContent = '-';
    document.getElementById('display-details').textContent = '-';
    document.getElementById('insp-result').value = 'PENDING';
    document.getElementById('insp-remarks').value = '';

    if (mode === 'edit' && row) {
      crudTitle.innerHTML = `<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Conduct Inspection`;
      crudCurrentRow = row;
      const source = row.dataset.source || 'service_request';
      crudForm.elements['id'].value = row.dataset.id || '';
      crudForm.elements['status'].value = (row.dataset.status || 'pending').toLowerCase();

      // Populate Read-Only Request Details
      document.getElementById('display-name').textContent = row.dataset.name || '-';
      document.getElementById('display-address').textContent = row.dataset.address || '-';

      if (source === 'sanitary_permit') {
        document.getElementById('display-details').textContent = row.dataset.details || '';
        document.getElementById('insp-date').value = row.dataset.preferred_date || '';
        document.getElementById('insp-result').value = (row.dataset.result || 'PENDING').toUpperCase();
        document.getElementById('insp-remarks').value = row.dataset.findings || '';
      } else {
        // Parse service_details for service_request
        const fullDetails = row.dataset.details || '';
        const separator = '--- INSPECTION REPORT ---';
        let requestPart = fullDetails;
        let reportPart = '';

        if (fullDetails.includes(separator)) {
          const parts = fullDetails.split(separator);
          requestPart = parts[0].trim();
          reportPart = parts[1] ? parts[1].trim() : '';
        }

        document.getElementById('display-details').textContent = requestPart;
        document.getElementById('insp-date').value = row.dataset.preferred_date || '';

        if (reportPart) {
          if (reportPart.includes('[RESULT: PASS]')) document.getElementById('insp-result').value = 'PASS';
          else if (reportPart.includes('[RESULT: FAIL]')) document.getElementById('insp-result').value = 'FAIL';

          const dateMatch = reportPart.match(/\[SCHEDULE: (.*?)\]/);
          if (dateMatch) document.getElementById('insp-date').value = dateMatch[1];

          let cleanRemarks = reportPart.replace(/\[RESULT: (PASS|FAIL)\]/, '').replace(/\[SCHEDULE: (.*?)\]/, '').trim();
          document.getElementById('insp-remarks').value = cleanRemarks;
        }
      }
    }
    crudModal.classList.remove('hidden');
    crudModal.classList.add('flex');
  }

  function closeCrudModal() { crudModal.classList.add('hidden'); crudModal.classList.remove('flex'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal;

  async function apiCall(payload) {
    const res = await fetch('spi/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const text = await res.text();
    if (!res.ok) throw new Error(text || ('HTTP ' + res.status));
    return JSON.parse(text);
  }

  async function submitCrudForm() {
    const mode = crudForm.elements['mode'].value;
    const id = parseInt(crudForm.elements['id'].value || '0', 10);
    const source = crudCurrentRow?.dataset.source || 'service_request';
    const result = document.getElementById('insp-result').value;
    const remarks = document.getElementById('insp-remarks').value;
    const status = document.getElementById('insp-status').value;
    const inspDate = document.getElementById('insp-date').value;

    if (source === 'sanitary_permit') {
      const payload = {
        service_type: 'sanitation-workflow',
        workflow_step: 'inspection',
        application_id: id,
        scheduled_date: inspDate || null,
        result: result.toLowerCase() === 'pending' ? null : (result.toLowerCase() === 'pass' ? 'passed' : 'failed'),
        findings: remarks || null,
        status: (result !== 'PENDING') ? 'completed' : 'in_progress'
      };

      try {
        const resp = await fetch('../process_service_request.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const res = await resp.json();
        if (res.success) {
          alert('Permit inspection updated successfully!');
          location.reload();
        } else {
          alert('Failed: ' + (res.message || 'Unknown error'));
        }
      } catch (e) {
        console.error(e);
        alert('Error submitting permit inspection.');
      }
      return;
    }

    // Default: service_request logic
    const requestDetails = document.getElementById('display-details').textContent || '';
    const separator = '\n\n--- INSPECTION REPORT ---\n';
    let reportString = '';

    if (result !== 'PENDING') {
      reportString += `[RESULT: ${result}]\n`;
    }
    if (inspDate) {
      reportString += `[SCHEDULE: ${inspDate}]\n`;
    }
    reportString += remarks;

    const newServiceDetails = requestDetails + separator + reportString;

    const payload = {
      action: 'update',
      id: id,
      status: status,
      service_details: newServiceDetails,
      preferred_date: inspDate || null
    };

    try {
      const resp = await apiCall(payload);
      if (resp.success) {
        alert('Inspection report submitted successfully!');
        location.reload();
      } else {
        alert('Failed: ' + (resp.message || 'Unknown error'));
      }
    } catch (e) {
      console.error(e);
      alert('Error submitting report.');
    }
  }




  // Auto-set status to completed if Pass/Fail is selected
  document.getElementById('insp-result').addEventListener('change', function () {
    if (this.value === 'PASS' || this.value === 'FAIL') {
      document.getElementById('insp-status').value = 'completed';
    }
  });

  applySpiFilters();
</script>