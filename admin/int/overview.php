<?php
// Admin INT Overview - Summary of Requests and Clinical Records
try {
  // Counts of service requests
  $stmt = $db->prepare("SELECT service_type, status, COUNT(*) as c FROM service_requests WHERE service_type IN ('vaccination','nutrition-monitoring') GROUP BY service_type, status");
  $stmt->execute();
  $reqData = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $countsByType = ['vaccination' => 0, 'nutrition-monitoring' => 0];
  $countsByStatus = ['pending' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
  foreach ($reqData as $row) {
    if (isset($countsByType[$row['service_type']]))
      $countsByType[$row['service_type']] += $row['c'];
    if (isset($countsByStatus[$row['status']]))
      $countsByStatus[$row['status']] += $row['c'];
  }

  // Counts of actual medical records
  $vaccineCount = (int) $db->query("SELECT COUNT(*) FROM immunizations")->fetchColumn();
  $nutritionCount = (int) $db->query("SELECT COUNT(*) FROM nutrition_records")->fetchColumn();

  // Fetch recent activity (mix of requests and records could be complex, let's just keep requests for the table for now but show record stats)
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN ('vaccination','nutrition-monitoring') ORDER BY created_at DESC LIMIT 50");
  $stmt->execute();
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Admin INT overview fetch error: ' . $e->getMessage());
  $requests = [];
  $vaccineCount = $nutritionCount = 0;
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
$typeLabels = ['vaccination' => 'Vaccination', 'nutrition-monitoring' => 'Nutrition Monitoring'];
?>

<div
  class="mb-8 rounded-2xl p-8 border border-purple-100 shadow-lg bg-gradient-to-r from-purple-50 via-violet-50 to-fuchsia-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Immunization & Nutrition (Admin)</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Read-only list of Vaccination and Nutrition Monitoring requests.
      </p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span
          class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($requests); ?></span></div>
      <button id="admin-int-export"
        class="px-3 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-xs font-medium shadow-sm">Export
        CSV</button>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
  <!-- Actual Clinical Records Stats -->
  <div
    class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Vaccinations Administered</div>
    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-200"><?php echo number_format($vaccineCount); ?>
    </div>
  </div>
  <div
    class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-sky-100 dark:border-sky-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Growth Assessments</div>
    <div class="text-2xl font-bold text-sky-600 dark:text-sky-200"><?php echo number_format($nutritionCount); ?></div>
  </div>
  <!-- Service Request Stats -->
  <div
    class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-purple-100 dark:border-purple-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Pending Requests</div>
    <div class="text-2xl font-bold text-purple-600 dark:text-purple-200">
      <?php echo (int) ($countsByStatus['pending'] ?? 0); ?>
    </div>
  </div>
  <div
    class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-blue-100 dark:border-blue-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Completed Requests</div>
    <div class="text-2xl font-bold text-blue-600 dark:text-blue-200">
      <?php echo (int) ($countsByStatus['completed'] ?? 0); ?>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Quick Actions</h3>
  <div class="space-y-3 mb-6">
    <button onclick="location.href='DashboardOverview_new.php?page=int&view=immunization'"
      class="w-full text-left p-4 rounded-xl border border-purple-100 dark:border-purple-600/50 bg-purple-50 text-purple-700 dark:bg-purple-900/50 dark:text-purple-200 hover:bg-purple-100 hover:border-purple-200 dark:hover:bg-purple-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span
          class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-purple-900/40 border border-purple-100 dark:border-purple-600 mr-3">
          <svg class="w-5 h-5 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z">
            </path>
          </svg>
        </span>
        <span class="font-semibold">Immunization</span>
      </div>
    </button>
    <button onclick="location.href='DashboardOverview_new.php?page=int&view=nutrition'"
      class="w-full text-left p-4 rounded-xl border border-violet-100 dark:border-violet-600/50 bg-violet-50 text-violet-700 dark:bg-violet-900/50 dark:text-violet-200 hover:bg-violet-100 hover:border-violet-200 dark:hover:bg-violet-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span
          class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-violet-900/40 border border-violet-100 dark:border-violet-600 mr-3">
          <svg class="w-5 h-5 text-violet-500 dark:text-violet-300" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z">
            </path>
          </svg>
        </span>
        <span class="font-semibold">Nutrition</span>
      </div>
    </button>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Service</label>
      <select id="admin-int-filter-type"
        class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="admin-int-filter-status"
        class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Search</label>
      <div class="relative">
        <input id="admin-int-search" type="text" placeholder="Search name, email, phone, details..."
          class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="admin-int-table">
      <thead>
        <tr class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
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
      <tbody id="admin-int-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr
              class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 admin-int-row"
              data-id="<?php echo h($r['id']); ?>"
              data-service="<?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>"
              data-type="<?php echo h($r['service_type']); ?>" data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>" data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>" data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>" data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium">
                <?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['full_name']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['email']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['phone']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <?php $u = strtolower($r['urgency'] ?? 'medium'); ?>
                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php
                echo $u === 'emergency' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : ($u === 'high' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200' : ($u === 'low' ? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'));
                ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
              </td>
              <td class="py-3 px-3">
                <?php $s = $r['status'] ?? 'pending'; ?>
                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php
                echo $s === 'completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : ($s === 'in_progress' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s === 'cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'));
                ?>"><?php echo h(strtoupper($s)); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['created_at']); ?></td>
              <td class="py-3 px-3 text-right">
                <button
                  class="px-3 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-500 text-xs font-medium shadow-sm"
                  onclick="openViewModal(this.closest('tr'))">View</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="admin-int-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- View Modal -->
<div id="admin-int-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div
    class="relative modal-panel bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Request Details</h3>
      <button class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100"
        onclick="closeViewModal()">✕</button>
    </div>
    <div id="admin-int-view-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>
    #admin-int-view-modal.show {
      display: flex;
    }
  </style>
</div>

<style>
  .modal-field {
    background-color: rgba(55, 65, 81, 0.4);
    border-color: #4b5563;
  }

  /* Light mode overrides for modal readability */
  @media (prefers-color-scheme: light) {
    .modal-panel {
      background-color: #ffffff !important;
      color: #111827 !important;
      border-color: #d1d5db !important;
    }

    .modal-panel h3 {
      color: #111827 !important;
    }

    .modal-panel .text-white {
      color: #111827 !important;
    }

    .modal-panel .text-gray-400 {
      color: #374151 !important;
    }

    .modal-panel .text-gray-300 {
      color: #374151 !important;
    }

    .modal-panel .border-gray-600 {
      border-color: #d1d5db !important;
    }

    .modal-panel input,
    .modal-panel select,
    .modal-panel textarea {
      background-color: #ffffff !important;
      color: #111827 !important;
      border-color: #d1d5db !important;
    }

    .modal-field {
      background-color: #f9fafb !important;
      border-color: #e5e7eb !important;
      color: #111827 !important;
    }
  }

  body.light .modal-panel,
  body[data-theme="light"] .modal-panel {
    background-color: #ffffff !important;
    color: #111827 !important;
    border-color: #d1d5db !important;
  }

  body.light .modal-panel h3,
  body[data-theme="light"] .modal-panel h3 {
    color: #111827 !important;
  }

  body.light .modal-panel .text-white,
  body[data-theme="light"] .modal-panel .text-white {
    color: #111827 !important;
  }

  body.light .modal-panel .text-gray-400,
  body[data-theme="light"] .modal-panel .text-gray-400 {
    color: #374151 !important;
  }

  body.light .modal-panel .text-gray-300,
  body[data-theme="light"] .modal-panel .text-gray-300 {
    color: #374151 !important;
  }

  body.light .modal-panel .border-gray-600,
  body[data-theme="light"] .modal-panel .border-gray-600 {
    border-color: #d1d5db !important;
  }

  body.light .modal-panel input,
  body[data-theme="light"] .modal-panel input,
  body.light .modal-panel select,
  body[data-theme="light"] .modal-panel select,
  body.light .modal-panel textarea,
  body[data-theme="light"] .modal-panel textarea {
    background-color: #ffffff !important;
    color: #111827 !important;
    border-color: #d1d5db !important;
  }

  body.light .modal-field,
  body[data-theme="light"] .modal-field {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #111827 !important;
  }
</style>

<script>
  const aSearch = document.getElementById('admin-int-search');
  const aBody = document.getElementById('admin-int-tbody');
  const aFilterType = document.getElementById('admin-int-filter-type');
  const aFilterStatus = document.getElementById('admin-int-filter-status');
  const aTable = document.getElementById('admin-int-table');
  const aExport = document.getElementById('admin-int-export');
  const aPagination = document.getElementById('admin-int-pagination');
  let aSortKey = 'id';
  let aSortAsc = false;
  let aPage = 1;
  const aPageSize = 10;

  function aRows() { return Array.from(aBody?.querySelectorAll('.admin-int-row') || []); }

  function applyAFilters() {
    const q = (aSearch?.value || '').toLowerCase();
    const ft = (aFilterType?.value || '').toLowerCase();
    const fs = (aFilterStatus?.value || '').toLowerCase();
    if (!aBody) return;
    let rows = aRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.getAttribute('data-type') || '').toLowerCase();
      const s = (tr.getAttribute('data-status') || '').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t === ft) && (!fs || s === fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    // sort
    rows.sort((a, b) => {
      const ka = (a.dataset[aSortKey] || '').toLowerCase();
      const kb = (b.dataset[aSortKey] || '').toLowerCase();
      if (ka < kb) return aSortAsc ? -1 : 1;
      if (ka > kb) return aSortAsc ? 1 : -1;
      return 0;
    });
    // pagination
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / aPageSize));
    if (aPage > pages) aPage = pages;
    const start = (aPage - 1) * aPageSize;
    const end = start + aPageSize;
    const visible = new Set(rows.slice(start, end));
    aRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderAPagination(pages);
  }

  function renderAPagination(pages) {
    if (!aPagination) return;
    aPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = aPage <= 1;
    prev.onclick = () => { aPage--; applyAFilters(); };
    aPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i === aPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { aPage = i; applyAFilters(); };
      aPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = aPage >= pages;
    next.onclick = () => { aPage++; applyAFilters(); };
    aPagination.appendChild(next);
  }

  [aSearch, aFilterType, aFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applyAFilters);
    if (el) el.addEventListener('change', applyAFilters);
  });

  if (aTable) {
    aTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (aSortKey === key) { aSortAsc = !aSortAsc; } else { aSortKey = key; aSortAsc = true; }
        applyAFilters();
      });
    });
  }

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function adminIntToCSV() {
    const headers = ['ID', 'Service', 'Full Name', 'Email', 'Phone', 'Preferred Date', 'Urgency', 'Status', 'Created'];
    const rows = aRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0, 9).map(td => '"' + (td.innerText || '').replace(/"/g, '\\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (aExport) aExport.addEventListener('click', () => download('int_requests_admin.csv', adminIntToCSV()));

  function viewField(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="modal-field border rounded p-3">\n      <div class="text-gray-400 text-xs mb-1">${label}</div>\n      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>\n    </div>`;
  }
  function openViewModal(row) {
    const modal = document.getElementById('admin-int-view-modal');
    const details = document.getElementById('admin-int-view-details');
    if (!modal || !details || !row) return;
    const typeLabel = { 'vaccination': 'Vaccination', 'nutrition-monitoring': 'Nutrition Monitoring' }[row.dataset.type] || row.dataset.type;
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
  function closeViewModal() {
    const modal = document.getElementById('admin-int-view-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  applyAFilters();
</script>