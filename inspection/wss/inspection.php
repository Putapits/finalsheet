<?php
// Inspector WSS - System Inspections & Registrations
// Handles both 'system-inspection' and 'septic-registration' types
try {
  $uid = $_SESSION['user_id'] ?? 0;
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN ('system-inspection', 'septic-registration', 'wastewater-clearance') AND assigned_inspector_id = :uid ORDER BY created_at DESC");
  $stmt->execute([':uid' => $uid]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('WSS inspection fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
$statusKeys = ['pending', 'in_progress', 'completed', 'cancelled'];
$statusCounts = array_fill_keys($statusKeys, 0);
foreach ($rows as $r) {
  $s = strtolower($r['status'] ?? 'pending');
  if (isset($statusCounts[$s]))
    $statusCounts[$s]++;
}
?>

<div class="mb-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">System Inspections & Registrations</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Manage septic tank registrations and system inspection requests.
      </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div
        class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
        Total Requests: <span class="ml-1"><?php echo number_format(count($rows)); ?></span>
      </div>
      <button id="wssi-clear"
        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear
        Filters</button>
      <button id="wssi-export"
        class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export
        CSV</button>
      <button id="wssi-add"
        class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add
        Inspection</button>
    </div>
  </div>
</div>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
  <div
    class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-200">Pending</p>
    <span
      class="mt-2 block text-2xl font-bold text-amber-600 dark:text-amber-200"><?php echo number_format($statusCounts['pending']); ?></span>
  </div>
  <div
    class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-200">In Progress</p>
    <span
      class="mt-2 block text-2xl font-bold text-emerald-600 dark:text-emerald-200"><?php echo number_format($statusCounts['in_progress']); ?></span>
  </div>
  <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4 shadow-sm dark:border-sky-500/30 dark:bg-sky-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-200">Completed</p>
    <span
      class="mt-2 block text-2xl font-bold text-sky-600 dark:text-sky-200"><?php echo number_format($statusCounts['completed']); ?></span>
  </div>
  <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-rose-600 dark:text-rose-200">Cancelled</p>
    <span
      class="mt-2 block text-2xl font-bold text-rose-600 dark:text-rose-200"><?php echo number_format($statusCounts['cancelled']); ?></span>
  </div>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="md:col-span-2">
      <label
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="wssi-search" type="text" placeholder="Search name, type, email, details..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
    <div>
      <label
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="wssi-filter-status"
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="wssi-table">
      <thead>
        <tr
          class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="name">Applicant / Location</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="type">Type</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="date">Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3">Condition / Outcome</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="wssi-tbody">
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="7" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No requests found.</td>
          </tr>
        <?php else:
          foreach ($rows as $r): ?>
            <?php
            // Parse Condition from service_details
            $details = $r['service_details'];
            $septic = 'Pending';
            $compliance = 'Pending';

            if (strpos($details, '[SEPTIC: Good]') !== false)
              $septic = 'Good';
            if (strpos($details, '[SEPTIC: Cleaning Req.]') !== false)
              $septic = 'Cleaning Needed';
            if (strpos($details, '[SEPTIC: Damaged]') !== false)
              $septic = 'Damaged';

            if (strpos($details, '[COMPLIANCE: Yes]') !== false)
              $compliance = 'Compliant';
            if (strpos($details, '[COMPLIANCE: No]') !== false)
              $compliance = 'Non-Compliant';

            $condClass = 'text-gray-500';
            if ($compliance === 'Compliant')
              $condClass = 'text-emerald-600 font-semibold';
            if ($compliance === 'Non-Compliant' || $septic === 'Damaged')
              $condClass = 'text-rose-600 font-bold';

            $summary = ($septic !== 'Pending' || $compliance !== 'Pending')
              ? "<span class='block text-[10px] uppercase tracking-wide text-gray-400'>Septic:</span> $septic <br> <span class='block text-[10px] uppercase tracking-wide text-gray-400 mt-1'>Compliance:</span> $compliance"
              : '<span class="text-gray-400 italic">Pending Inspection</span>';

            // Determine Type Label
            $sType = $r['service_type'];
            $typeLabel = ($sType === 'septic-registration') ? 'Registration' : 'Inspection';
            $typeClass = ($sType === 'septic-registration') ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-200';
            ?>
            <tr
              class="wssi-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>" data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>" data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>" data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>" data-type="<?php echo h($typeLabel); ?>"
              data-service="<?php echo h($r['service_type']); ?>" data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300">
                <div class="font-medium"><?php echo h($r['full_name']); ?></div>
                <div class="text-xs opacity-70 truncate max-w-[200px]"><?php echo h($r['address']); ?></div>
              </td>
              <td class="py-3 px-3">
                <span
                  class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo $typeClass; ?>">
                  <?php echo h($typeLabel); ?>
                </span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300">
                <div class="text-xs">Pref: <?php echo h($r['preferred_date'] ?? '-'); ?></div>
                <div class="text-[10px] opacity-60">Created: <?php echo h(date('M j', strtotime($r['created_at']))); ?>
                </div>
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
              <td class="py-3 px-3 text-xs <?php echo $condClass; ?>">
                <?php echo $summary; ?>
              </td>
              <td class="py-3 px-3 text-right">
                <button
                  class="mr-2 inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500/40"
                  onclick="openCrudModal('edit', this.closest('tr'))">
                  <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Inspect
                </button>
                <button
                  class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                  onclick="deleteRow(this.closest('tr'))">Delete</button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="wssi-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300">
  </div>
</section>

<!-- View Modal -->
<div id="wssi-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div
    class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Request Details</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeViewModal()">✕</button>
    </div>
    <div id="wssi-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200">
    </div>
  </div>
  <style>
    #wssi-view-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- Inspection Modal -->
<div id="wssi-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCrudModal()"></div>
  <div
    class="relative max-h-[90vh] w-[95%] max-w-2xl overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900 flex flex-col">
    <div
      class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-900">
      <h3 id="wssi-crud-title" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
          </path>
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
      <form id="wssi-crud-form" class="space-y-6">
        <input type="hidden" name="mode" value="create" />
        <input type="hidden" name="id" />
        <textarea name="service_details" class="hidden"></textarea>

        <!-- Request Details (Read-only) -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800/50">
          <label class="block text-xs font-bold uppercase tracking-wider text-blue-800 dark:text-blue-300 mb-3">Service
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
              <p class="mt-1 text-gray-700 dark:text-gray-300 italic whitespace-pre-wrap" id="display-details">-</p>
            </div>
          </div>
        </div>

        <!-- Inspection Findings Form -->
        <div>
          <label
            class="block text-sm font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-slate-800 pb-2">Inspection
            Findings</label>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Septic Tank
                Condition</label>
              <select id="insp-septic"
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm font-medium shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="Unknown">Pending / Unknown</option>
                <option value="Good">✅ Good / Functional</option>
                <option value="Cleaning Req.">⚠️ Needs Cleaning / Full</option>
                <option value="Damaged">❌ Damaged / Leaking</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Wastewater
                Compliance</label>
              <select id="insp-compliance"
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm font-medium shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="Unknown">Pending Assessment</option>
                <option value="Yes">✅ Compliant</option>
                <option value="No">❌ Non-Compliant</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Status Update</label>
              <select name="status" id="insp-status"
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm font-medium shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div class="md:col-span-2">
              <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Inspector Remarks &
                Recommendations</label>
              <textarea id="insp-remarks" rows="4"
                placeholder="Record specifics about the septic tank condition, disposal compliance issues, or maintenance recommendations..."
                class="w-full rounded-lg border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
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
        class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-lg shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">Submit
        Report</button>
    </div>
  </div>
</div>

<script>
  const wssiSearch = document.getElementById('wssi-search');
  const wssiBody = document.getElementById('wssi-tbody');
  const wssiFilterStatus = document.getElementById('wssi-filter-status');
  const wssiTable = document.getElementById('wssi-table');
  const wssiExport = document.getElementById('wssi-export');
  const wssiPagination = document.getElementById('wssi-pagination');
  const wssiAdd = document.getElementById('wssi-add');
  const wssiClear = document.getElementById('wssi-clear');
  let wssiSortKey = 'id'; let wssiSortAsc = false; let wssiPage = 1; const wssiPageSize = 10;

  function wssiRows() { return Array.from(wssiBody?.querySelectorAll('.wssi-row') || []); }
  function applyWssiFilters() {
    const q = (wssiSearch?.value || '').toLowerCase(); const fs = (wssiFilterStatus?.value || '').toLowerCase();
    let rows = wssiRows();
    rows.forEach(tr => { const text = tr.innerText.toLowerCase(); const s = (tr.dataset.status || '').toLowerCase(); tr.dataset._match = (text.includes(q) && (!fs || s === fs)) ? '1' : '0'; });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => { const ka = (a.dataset[wssiSortKey] || '').toLowerCase(); const kb = (b.dataset[wssiSortKey] || '').toLowerCase(); if (ka < kb) return wssiSortAsc ? -1 : 1; if (ka > kb) return wssiSortAsc ? 1 : -1; return 0; });
    const total = rows.length; const pages = Math.max(1, Math.ceil(total / wssiPageSize)); if (wssiPage > pages) wssiPage = pages; const start = (wssiPage - 1) * wssiPageSize; const end = start + wssiPageSize;
    const visible = new Set(rows.slice(start, end));
    wssiRows().forEach(tr => tr.style.display = 'none'); visible.forEach(tr => tr.style.display = '');
    renderWssiPagination(pages);
  }
  function renderWssiPagination(pages) {
    if (!wssiPagination) return; wssiPagination.innerHTML = ''; const baseBtn = () => { const btn = document.createElement('button'); btn.type = 'button'; btn.className = 'px-2 py-1 rounded-lg border text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 border-gray-300 bg-white text-gray-600 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700'; return btn; };
    const prev = baseBtn(); prev.textContent = 'Prev'; prev.disabled = wssiPage <= 1; if (prev.disabled) { prev.classList.add('opacity-60', 'cursor-not-allowed'); } else { prev.onclick = () => { wssiPage--; applyWssiFilters(); }; }
    wssiPagination.appendChild(prev); for (let i = 1; i <= pages; i++) {
      const b = baseBtn(); b.textContent = i; if (i === wssiPage) { b.className = 'px-2 py-1 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40'; } else { b.onclick = () => { wssiPage = i; applyWssiFilters(); }; }
      wssiPagination.appendChild(b);
    } const next = baseBtn(); next.textContent = 'Next'; next.disabled = wssiPage >= pages; if (next.disabled) { next.classList.add('opacity-60', 'cursor-not-allowed'); } else { next.onclick = () => { wssiPage++; applyWssiFilters(); }; }
    wssiPagination.appendChild(next);
  }
  [wssiSearch, wssiFilterStatus].forEach(el => { if (el) el.addEventListener('input', applyWssiFilters); if (el) el.addEventListener('change', applyWssiFilters); });
  if (wssiClear) { wssiClear.addEventListener('click', () => { if (wssiSearch) wssiSearch.value = ''; if (wssiFilterStatus) wssiFilterStatus.value = ''; wssiPage = 1; applyWssiFilters(); }); }
  if (wssiTable) { wssiTable.querySelectorAll('th.sortable').forEach(th => { th.addEventListener('click', () => { const key = th.getAttribute('data-key'); if (wssiSortKey === key) { wssiSortAsc = !wssiSortAsc; } else { wssiSortKey = key; wssiSortAsc = true; } applyWssiFilters(); }); }); }
  function download(filename, text) { const a = document.createElement('a'); a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(text); a.download = filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function wssiToCSV() { const headers = ['ID', 'Service', 'Full Name', 'Email', 'Phone', 'Preferred Date', 'Urgency', 'Status', 'Created']; const rows = wssiRows().filter(tr => tr.style.display !== 'none'); const lines = [headers.join(',')]; rows.forEach(tr => { const cells = [tr.dataset.id || '', tr.dataset.service || 'System Inspection', tr.dataset.name || '', tr.dataset.email || '', tr.dataset.phone || '', tr.dataset.preferred_date || '', tr.dataset.urgency || '', tr.dataset.status || '', tr.dataset.created || '',].map(val => '"' + (val || '').replace(/"/g, '""') + '"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (wssiExport) wssiExport.addEventListener('click', () => download('wss_inspections.csv', wssiToCSV()));

  function viewField(label, value) { const safe = (value ?? '').toString(); return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">\n  <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>\n  <div class="break-words text-sm text-gray-900 whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>\n</div>`; }
  function openViewModal(row) {
    const modal = document.getElementById('wssi-view-modal'); const details = document.getElementById('wssi-view-details'); if (!modal || !details || !row) return; details.innerHTML = `
    ${viewField('Request ID', '#' + (row.dataset.id || ''))}
    ${viewField('Type', (row.dataset.type || ''))}
    ${viewField('Status', (row.dataset.status || '').toUpperCase())}
    ${viewField('Urgency', (row.dataset.urgency || '').toUpperCase())}
    ${viewField('Created At', row.dataset.created || '')}
    ${viewField('Full Name', row.dataset.name || '')}
    ${viewField('Email', row.dataset.email || '')}
    ${viewField('Phone', row.dataset.phone || '')}
    ${viewField('Preferred Date', row.dataset.preferred_date || '')}
    ${viewField('Address', row.dataset.address || '')}
    ${viewField('Service Details', row.dataset.details || '')}
  `; modal.classList.add('show'); modal.classList.remove('hidden');
  }
  function closeViewModal() { const modal = document.getElementById('wssi-view-modal'); if (!modal) return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  const wssiCrudModal = document.getElementById('wssi-crud-modal'); const wssiCrudForm = document.getElementById('wssi-crud-form'); const wssiCrudTitle = document.getElementById('wssi-crud-title');
  let wssiCurrentRow = null;
  function statusBadgeClass(status) { switch ((status || '').toLowerCase()) { case 'completed': return 'bg-blue-600 text-white'; case 'in_progress': return 'bg-green-600 text-white'; case 'cancelled': return 'bg-red-600 text-white'; default: return 'bg-yellow-600 text-white'; } }
  function urgencyBadgeClass(urgency) { switch ((urgency || '').toLowerCase()) { case 'emergency': return 'bg-red-600 text-white'; case 'high': return 'bg-orange-600 text-white'; case 'low': return 'bg-gray-500 text-white'; default: return 'bg-yellow-600 text-white'; } }

  function openCrudModal(mode, row) {
    if (!wssiCrudModal) return;
    wssiCrudForm.reset();
    wssiCrudForm.elements['mode'].value = mode;
    wssiCurrentRow = null;

    document.getElementById('display-name').textContent = '-';
    document.getElementById('display-address').textContent = '-';
    document.getElementById('display-details').textContent = '-';
    document.getElementById('insp-septic').value = 'Unknown';
    document.getElementById('insp-compliance').value = 'Unknown';
    document.getElementById('insp-remarks').value = '';

    if (mode === 'edit' && row) {
      wssiCrudTitle.innerHTML = `<svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Conduct Inspection`;
      wssiCurrentRow = row;
      wssiCrudForm.elements['id'].value = row.dataset.id || '';
      wssiCrudForm.elements['status'].value = (row.dataset.status || 'pending').toLowerCase();

      document.getElementById('display-name').textContent = row.dataset.name || '-';
      document.getElementById('display-address').textContent = row.dataset.address || '-';

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

      if (reportPart) {
        if (reportPart.includes('[SEPTIC: Good]')) document.getElementById('insp-septic').value = 'Good';
        if (reportPart.includes('[SEPTIC: Cleaning Req.]')) document.getElementById('insp-septic').value = 'Cleaning Req.';
        if (reportPart.includes('[SEPTIC: Damaged]')) document.getElementById('insp-septic').value = 'Damaged';

        if (reportPart.includes('[COMPLIANCE: Yes]')) document.getElementById('insp-compliance').value = 'Yes';
        if (reportPart.includes('[COMPLIANCE: No]')) document.getElementById('insp-compliance').value = 'No';

        let clean = reportPart
          .replace(/\[SEPTIC: [^\]]+\]/g, '')
          .replace(/\[COMPLIANCE: [^\]]+\]/g, '')
          .trim();
        document.getElementById('insp-remarks').value = clean;
      }

    } else {
      wssiCrudTitle.textContent = 'Add Inspection Request';
      wssiCrudForm.elements['status'].value = 'pending';
    }

    wssiCrudModal.classList.add('flex');
    wssiCrudModal.classList.remove('hidden');
  }

  function closeCrudModal() { if (!wssiCrudModal) return; wssiCrudModal.classList.remove('flex'); wssiCrudModal.classList.add('hidden'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal; if (wssiAdd) wssiAdd.addEventListener('click', () => openCrudModal('create'));

  async function apiCall(payload) { const res = await fetch('wss/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }); const text = await res.text(); if (!res.ok) throw new Error(text || ('HTTP ' + res.status)); return JSON.parse(text); }
  async function deleteRow(row) { if (!row) return; const id = parseInt(row.dataset.id || '0', 10); if (!id) return alert('Invalid ID'); if (!confirm('Delete this request?')) return; try { const result = await apiCall({ action: 'delete', id }); if (result.success) { row.remove(); applyWssiFilters(); } else alert('Delete failed.'); } catch (e) { console.error(e); alert('Server error.'); } }
  window.deleteRow = deleteRow;

  async function submitCrudForm() {
    const mode = wssiCrudForm.elements['mode'].value;
    const id = parseInt(wssiCrudForm.elements['id'].value || '0', 10);
    const status = document.getElementById('insp-status').value;
    const septic = document.getElementById('insp-septic').value;
    const compliance = document.getElementById('insp-compliance').value;
    const remarks = document.getElementById('insp-remarks').value;

    const requestDetails = document.getElementById('display-details').textContent;
    const separator = '\n\n--- INSPECTION REPORT ---\n';
    let reportString = '';

    if (septic !== 'Unknown') reportString += `[SEPTIC: ${septic}] `;
    if (compliance !== 'Unknown') reportString += `[COMPLIANCE: ${compliance}] `;
    reportString += '\n' + remarks;

    const newServiceDetails = requestDetails + separator + reportString;

    const payload = {
      action: 'update',
      id: id,
      status: status,
      service_details: newServiceDetails
    };
    if (mode !== 'edit') {
      // fallback for create
      payload.action = 'create';
      payload.service_type = 'system-inspection'; // Default to inspection if creating from admin
      payload.service_details = wssiCrudForm.elements['service_details'].value;
    }

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

  document.getElementById('insp-septic').addEventListener('change', function () {
    if (this.value !== 'Unknown') document.getElementById('insp-status').value = 'completed';
  });

  applyWssiFilters();
</script>