<?php
// Admin read-only Immunization Records (Actual data from Health Workers)
try {
  $stmt = $db->prepare("SELECT i.*, d.first_name as child_fname, d.last_name as child_lname, 
                              CONCAT(u.first_name, ' ', u.last_name) as parent_name,
                              CONCAT(hw.first_name, ' ', hw.last_name) as worker_name
                       FROM immunizations i
                       JOIN dependents d ON i.dependent_id = d.id
                       JOIN users u ON d.user_id = u.id
                       LEFT JOIN users hw ON i.administered_by = hw.id
                       ORDER BY i.date_administered DESC");
  $stmt->execute();
  $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Admin Immunization fetch error: ' . $e->getMessage());
  $records = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div
  class="mb-8 rounded-2xl p-8 border border-purple-100 shadow-lg bg-gradient-to-r from-purple-50 via-violet-50 to-fuchsia-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Vaccination Records</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Direct clinical records updated by health staff.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span
          class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($records); ?></span></div>
      <button id="admin-imm-export"
        class="px-3 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-xs font-medium shadow-sm">Export
        CSV</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
    <div class="md:col-span-2">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Search</label>
      <div class="relative">
        <input id="admin-imm-search" type="text" placeholder="Search name, email, phone, details..."
          class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="admin-imm-filter-status"
        class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400">
        <option value="">All Statuses</option>
        <option value="completed">Completed</option>
        <option value="scheduled">Scheduled</option>
        <option value="missed">Missed</option>
      </select>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="admin-imm-table">
      <thead>
        <tr class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="child">Child</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="vaccine">Vaccine (Dose)</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="date">Date Administered</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="worker">Worker</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="admin-imm-tbody">
        <?php if (empty($records)): ?>
          <tr>
            <td colspan="7" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No vaccination records found.
            </td>
          </tr>
        <?php else:
          foreach ($records as $r): ?>
            <tr
              class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 admin-imm-row"
              data-id="<?php echo h($r['id']); ?>" data-child="<?php echo h($r['child_fname'] . ' ' . $r['child_lname']); ?>"
              data-parent="<?php echo h($r['parent_name']); ?>" data-vaccine="<?php echo h($r['vaccine_name']); ?>"
              data-dose="<?php echo h($r['dose_number']); ?>" data-batch="<?php echo h($r['batch_number'] ?? 'N/A'); ?>"
              data-date="<?php echo h($r['date_administered'] ?? ''); ?>" data-due="<?php echo h($r['date_due'] ?? ''); ?>"
              data-status="<?php echo h($r['status']); ?>" data-worker="<?php echo h($r['worker_name'] ?? 'System'); ?>"
              data-remarks="<?php echo h($r['remarks'] ?? ''); ?>">
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium">
                <?php echo h($r['child_fname'] . ' ' . $r['child_lname']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['vaccine_name']); ?>
                (#<?php echo h($r['dose_number']); ?>)</td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">
                <?php echo h($r['date_administered'] ?? 'Scheduled'); ?></td>
              <td class="py-3 px-3">
                <?php $s = $r['status'] ?? 'scheduled'; ?>
                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php
                echo $s === 'completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200';
                ?>"><?php echo h(strtoupper($s)); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['worker_name'] ?? 'System'); ?></td>
              <td class="py-3 px-3 text-right">
                <button
                  class="px-3 py-1 bg-purple-600 text-white rounded-lg hover:bg-purple-500 text-xs font-medium shadow-sm"
                  onclick="openViewModal(this.closest('tr'))">View</button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="admin-imm-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- View Modal -->
<div id="admin-imm-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div
    class="relative modal-panel bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Vaccination Request Details</h3>
      <button class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100"
        onclick="closeViewModal()">✕</button>
    </div>
    <div id="admin-imm-view-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>
    #admin-imm-view-modal.show {
      display: flex;
    }
  </style>
</div>

<style>
  .modal-field {
    background-color: rgba(55, 65, 81, 0.4);
    border-color: #4b5563;
  }

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

  body.light .modal-field,
  body[data-theme="light"] .modal-field {
    background-color: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #111827 !important;
  }
</style>

<script>
  const immSearch = document.getElementById('admin-imm-search');
  const immBody = document.getElementById('admin-imm-tbody');
  const immFilterStatus = document.getElementById('admin-imm-filter-status');
  const immTable = document.getElementById('admin-imm-table');
  const immExport = document.getElementById('admin-imm-export');
  const immPagination = document.getElementById('admin-imm-pagination');
  let immSortKey = 'id'; let immSortAsc = false; let immPage = 1; const immPageSize = 10;

  function immRows() { return Array.from(immBody?.querySelectorAll('.admin-imm-row') || []); }
  function applyImmFilters() {
    const q = (immSearch?.value || '').toLowerCase(); const fs = (immFilterStatus?.value || '').toLowerCase();
    let rows = immRows();
    rows.forEach(tr => { const text = tr.innerText.toLowerCase(); const s = (tr.dataset.status || '').toLowerCase(); tr.dataset._match = (text.includes(q) && (!fs || s === fs)) ? '1' : '0'; });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => { const ka = (a.dataset[immSortKey] || '').toLowerCase(); const kb = (b.dataset[immSortKey] || '').toLowerCase(); if (ka < kb) return immSortAsc ? -1 : 1; if (ka > kb) return immSortAsc ? 1 : -1; return 0; });
    const total = rows.length; const pages = Math.max(1, Math.ceil(total / immPageSize)); if (immPage > pages) immPage = pages; const start = (immPage - 1) * immPageSize; const visible = new Set(rows.slice(start, start + immPageSize));
    immRows().forEach(tr => tr.style.display = 'none'); visible.forEach(tr => tr.style.display = '');
    renderImmPagination(pages);
  }
  function renderImmPagination(pages) { if (!immPagination) return; immPagination.innerHTML = ''; const prev = document.createElement('button'); prev.textContent = 'Prev'; prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; prev.disabled = immPage <= 1; prev.onclick = () => { immPage--; applyImmFilters(); }; immPagination.appendChild(prev); for (let i = 1; i <= pages; i++) { const b = document.createElement('button'); b.textContent = i; b.className = 'px-2 py-1 rounded ' + (i === immPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white'); b.onclick = () => { immPage = i; applyImmFilters(); }; immPagination.appendChild(b); } const next = document.createElement('button'); next.textContent = 'Next'; next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; next.disabled = immPage >= pages; next.onclick = () => { immPage++; applyImmFilters(); }; immPagination.appendChild(next); }
  [immSearch, immFilterStatus].forEach(el => { if (el) el.addEventListener('input', applyImmFilters); if (el) el.addEventListener('change', applyImmFilters); });
  if (immTable) { immTable.querySelectorAll('th.sortable').forEach(th => { th.addEventListener('click', () => { const key = th.getAttribute('data-key'); if (immSortKey === key) { immSortAsc = !immSortAsc; } else { immSortKey = key; immSortAsc = true; } applyImmFilters(); }); }); }
  function download(filename, text) { const a = document.createElement('a'); a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(text); a.download = filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function immToCSV() { const headers = ['ID', 'Child', 'Vaccine', 'Dose', 'Date', 'Status', 'Worker']; const rows = immRows().filter(tr => tr.style.display !== 'none'); const lines = [headers.join(',')]; rows.forEach(tr => { const cells = Array.from(tr.children).slice(0, 7).map(td => '"' + (td.innerText || '').replace(/"/g, '\\"') + '"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (immExport) immExport.addEventListener('click', () => download('immunization_records.csv', immToCSV()));

  function viewField(label, value) { const safe = (value ?? '').toString(); return `<div class="modal-field border rounded p-3">\n  <div class="text-gray-400 text-xs mb-1">${label}</div>\n  <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>\n</div>`; }
  function openViewModal(row) {
    const modal = document.getElementById('admin-imm-view-modal'); const details = document.getElementById('admin-imm-view-details'); if (!modal || !details || !row) return; details.innerHTML = `
    ${viewField('Record ID', '#' + (row.dataset.id || ''))}
    ${viewField('Vaccine', row.dataset.vaccine || '')}
    ${viewField('Dose #', row.dataset.dose || '')}
    ${viewField('Batch #', row.dataset.batch || '')}
    ${viewField('Date Administered', row.dataset.date || '')}
    ${viewField('Date Due', row.dataset.due || '')}
    ${viewField('Status', (row.dataset.status || '').toUpperCase())}
    ${viewField('Child Name', row.dataset.child || '')}
    ${viewField('Parent Name', row.dataset.parent || '')}
    ${viewField('Administered By', row.dataset.worker || '')}
    ${viewField('Remarks', row.dataset.remarks || 'No remarks')}
  `; modal.classList.add('show'); modal.classList.remove('hidden');
  }
  function closeViewModal() { const modal = document.getElementById('admin-imm-view-modal'); if (!modal) return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  applyImmFilters();
</script>