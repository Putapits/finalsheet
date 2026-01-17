<?php
// Doctor HSS - Disease Monitoring (CRUD)
$serviceType = 'disease-monitoring';
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? AND deleted_at IS NULL ORDER BY created_at DESC");
  $stmt->execute([$serviceType]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Doctor HSS disease fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="mb-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Disease Monitoring</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Manage disease monitoring submissions.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button id="hssdis-clear"
        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear
        Filters</button>
      <button id="hssdis-export"
        class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export
        CSV</button>
      <button id="hssdis-add"
        class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add
        New</button>
      <div class="text-sm text-gray-600 dark:text-gray-300">Total: <span
          class="font-semibold text-gray-900 dark:text-white"><?php echo count($rows); ?></span></div>
    </div>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="md:col-span-2">
      <label
        class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="hssdis-search" type="text" placeholder="Search name, email, phone, details..."
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
      <select id="hssdis-filter-status"
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
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="hssdis-table">
      <thead>
        <tr
          class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
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
      <tbody id="hssdis-tbody">
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No submissions found.</td>
          </tr>
        <?php else:
          foreach ($rows as $r): ?>
            <tr
              class="hssdis-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>" data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>" data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>" data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>" data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
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
                  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                  'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
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
                  onclick="openViewModal(this.closest('tr'))">View</button>
                <button
                  class="mr-2 inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60"
                  onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
                <button
                  class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                  onclick="deleteRow(this.closest('tr'))">Delete</button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="hssdis-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300">
  </div>
</section>

<!-- View Modal -->
<div id="hssdis-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div
    class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Submission Details</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeViewModal()">✕</button>
    </div>
    <div id="hssdis-view-details"
      class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>
    #hssdis-view-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- CRUD Modal -->
<div id="hssdis-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div
    class="relative max-h-[90vh] w-[95%] max-w-4xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-6 flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
      <div>
        <h3 id="hssdis-crud-title" class="text-xl font-bold text-gray-900 dark:text-white">Submit Surveillance Report
        </h3>
        <p class="text-sm text-gray-500 mt-1">Encode case information for disease monitoring.</p>
      </div>
      <button
        class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeCrudModal()">✕</button>
    </div>
    <form id="hssdis-crud-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />
      <input type="hidden" name="service_type" value="<?php echo $serviceType; ?>" />

      <!-- Patient Demographics Section -->
      <div class="md:col-span-2">
        <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600 mb-3">Patient Demographics</h4>
      </div>

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full
          Name / Alias</label>
        <input name="full_name" type="text" placeholder="Patient Name"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label
            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Age</label>
          <input name="age" type="number" placeholder="Years"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        </div>
        <div>
          <label
            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gender</label>
          <select name="gender"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contact
          Number (Optional)</label>
        <input name="phone" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address
          / Location</label>
        <input name="address" type="text" placeholder="House No, Street, Barangay"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>

      <!-- Case Information Section -->
      <div class="md:col-span-2 pt-4 border-t border-gray-100 dark:border-gray-700">
        <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600 mb-3">Case Information</h4>
      </div>

      <div>
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Diagnosis
          Category</label>
        <select name="diagnosis_category"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="Acute Bloody Diarrhea">Acute Bloody Diarrhea</option>
          <option value="Acute Encephalitis Syndrome">Acute Encephalitis Syndrome</option>
          <option value="Acute Hemorrhagic Fever Syndrome">Acute Hemorrhagic Fever Syndrome</option>
          <option value="Acute Viral Hepatitis">Acute Viral Hepatitis</option>
          <option value="Anthrax">Anthrax</option>
          <option value="Cholera">Cholera</option>
          <option value="COVID-19">COVID-19</option>
          <option value="Dengue">Dengue</option>
          <option value="Influenza-like Illness">Influenza-like Illness</option>
          <option value="Leptospirosis">Leptospirosis</option>
          <option value="Measles">Measles</option>
          <option value="Meningococcal Disease">Meningococcal Disease</option>
          <option value="Rabies">Rabies</option>
          <option value="Typhoid Fever">Typhoid Fever</option>
          <option value="Unusual Symptoms">Unusual Symptoms (Potential Outbreak)</option>
          <option value="Outbreak Sign">Outbreak Sign / Cluster</option>
          <option value="Other">Other (Specify in Symptoms)</option>
        </select>
      </div>

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date of
          Onset</label>
        <input name="preferred_date" type="date" value="<?php echo date('Y-m-d'); ?>"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>

      <div class="md:col-span-2">
        <label
          class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Symptoms &
          Clinical Signs / Identifying Observations</label>
        <textarea name="symptoms" rows="4"
          placeholder="Describe symptoms, duration, clinical observations, or signs of an outbreak..."
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>

      <div class="hidden">
        <!-- Hidden/Default fields -->
        <input name="email" value="hss-report@gsm.local">
        <input name="user_email" value="hss-report@gsm.local">
        <input name="urgency" value="medium">
        <input name="status" value="pending">
      </div>

      <div class="md:col-span-2 flex justify-end gap-2 pt-4">
        <button type="button"
          class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors"
          onclick="closeCrudModal()">Cancel</button>
        <button type="submit"
          class="px-4 py-2 rounded-lg bg-emerald-600 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">Submit
          Report</button>
      </div>
    </form>
  </div>
  <style>
    #hssdis-crud-modal.show {
      display: flex;
    }
  </style>
</div>

<script>
  const disSearch = document.getElementById('hssdis-search');
  const disBody = document.getElementById('hssdis-tbody');
  const disFilterStatus = document.getElementById('hssdis-filter-status');
  const disTable = document.getElementById('hssdis-table');
  const disExport = document.getElementById('hssdis-export');
  const disPagination = document.getElementById('hssdis-pagination');
  const disAdd = document.getElementById('hssdis-add');
  let disSortKey = 'id', disSortAsc = false, disPage = 1; const disPageSize = 10;

  function disRows() { return Array.from(disBody?.querySelectorAll('.hssdis-row') || []); }
  function applyDisFilters() {
    const q = (disSearch?.value || '').toLowerCase(); const fs = (disFilterStatus?.value || '').toLowerCase();
    let rows = disRows();
    rows.forEach(tr => { const text = tr.innerText.toLowerCase(); const s = (tr.dataset.status || '').toLowerCase(); tr.dataset._match = (text.includes(q) && (!fs || s === fs)) ? '1' : '0'; });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => { const ka = (a.dataset[disSortKey] || '').toLowerCase(); const kb = (b.dataset[disSortKey] || '').toLowerCase(); if (ka < kb) return disSortAsc ? -1 : 1; if (ka > kb) return disSortAsc ? 1 : -1; return 0; });
    const total = rows.length; const pages = Math.max(1, Math.ceil(total / disPageSize)); if (disPage > pages) disPage = pages; const start = (disPage - 1) * disPageSize; const visible = new Set(rows.slice(start, start + disPageSize));
    disRows().forEach(tr => tr.style.display = 'none'); visible.forEach(tr => tr.style.display = '');
    renderDisPagination(pages);
  }
  function renderDisPagination(pages) {
    if (!disPagination) return;
    disPagination.innerHTML = '';
    const baseBtn = () => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'px-3 py-1 rounded-lg border text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 border-gray-300 bg-white text-gray-600 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
      return btn;
    };
    const prev = baseBtn();
    prev.textContent = 'Prev';
    prev.disabled = disPage <= 1;
    if (prev.disabled) { prev.classList.add('opacity-60', 'cursor-not-allowed'); } else { prev.onclick = () => { disPage--; applyDisFilters(); }; }
    disPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = baseBtn();
      b.textContent = i;
      if (i === disPage) {
        b.className = 'px-3 py-1 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        b.onclick = () => { disPage = i; applyDisFilters(); };
      }
      disPagination.appendChild(b);
    }
    const next = baseBtn();
    next.textContent = 'Next';
    next.disabled = disPage >= pages;
    if (next.disabled) { next.classList.add('opacity-60', 'cursor-not-allowed'); } else { next.onclick = () => { disPage++; applyDisFilters(); }; }
    disPagination.appendChild(next);
  }
  [disSearch, disFilterStatus].forEach(el => { if (el) el.addEventListener('input', applyDisFilters); if (el) el.addEventListener('change', applyDisFilters); });
  const disClearBtn = document.getElementById('hssdis-clear');
  if (disClearBtn) { disClearBtn.addEventListener('click', function () { if (disSearch) disSearch.value = ''; if (disFilterStatus) disFilterStatus.value = ''; disPage = 1; applyDisFilters(); }); }
  if (disTable) { disTable.querySelectorAll('th.sortable').forEach(th => { th.addEventListener('click', () => { const key = th.getAttribute('data-key'); if (disSortKey === key) { disSortAsc = !disSortAsc; } else { disSortKey = key; disSortAsc = true; } applyDisFilters(); }); }); }
  function download(filename, text) { const a = document.createElement('a'); a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(text); a.download = filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function disToCSV() { const headers = ['ID', 'Full Name', 'Email', 'Phone', 'Preferred Date', 'Urgency', 'Status', 'Created']; const rows = disRows().filter(tr => tr.style.display !== 'none'); const lines = [headers.join(',')]; rows.forEach(tr => { const cells = Array.from(tr.children).slice(0, 8).map(td => '"' + (td.innerText || '').replace(/"/g, '\\"') + '"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (disExport) disExport.addEventListener('click', () => download('hss_disease.csv', disToCSV()));

  function viewField(label, value) {
    const safe = (value ?? '').toString(); return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
  <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>
  <div class="break-words text-sm text-gray-900 whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
</div>`;
  }
  function openViewModal(row) {
    const modal = document.getElementById('hssdis-view-modal'); const details = document.getElementById('hssdis-view-details'); if (!modal || !details || !row) return; details.innerHTML = `
    ${viewField('Request ID', '#' + (row.dataset.id || ''))}
    ${viewField('Status', (row.dataset.status || '').toUpperCase())}
    ${viewField('Urgency', (row.dataset.urgency || '').toUpperCase())}
    ${viewField('Created At', row.dataset.created || '')}
    ${viewField('Full Name', row.dataset.name || '')}
    ${viewField('Email', row.dataset.email || '')}
    ${viewField('Phone', row.dataset.phone || '')}
    ${viewField('Preferred Date', row.dataset.preferred_date || '')}
    ${viewField('Address', row.dataset.address || '')}
    ${viewField('Details', row.dataset.details || '')}
  `; modal.classList.add('show'); modal.classList.remove('hidden');
  }
  function closeViewModal() { const modal = document.getElementById('hssdis-view-modal'); if (!modal) return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal = openViewModal; window.closeViewModal = closeViewModal;

  const disCrudModal = document.getElementById('hssdis-crud-modal'); const disCrudForm = document.getElementById('hssdis-crud-form'); const disCrudTitle = document.getElementById('hssdis-crud-title');
  function fillFormFromRow(row) {
    const f = disCrudForm;
    f.elements['id'].value = row.dataset.id || '';
    f.elements['full_name'].value = row.dataset.name || '';
    f.elements['address'].value = row.dataset.address || '';

    // Populate symptoms with the full details for editing context
    f.elements['symptoms'].value = row.dataset.details || '';

    f.elements['preferred_date'].value = row.dataset.preferred_date || '';
    if (f.elements['phone']) f.elements['phone'].value = row.dataset.phone || '';

    // Try to match gender/diagnosis if simple text, otherwise they stay default (editing raw text in symptoms is fallback)
  }

  function openCrudModal(mode, row) {
    if (!disCrudModal) return;
    disCrudForm.reset();
    disCrudForm.elements['mode'].value = mode;
    if (mode === 'edit' && row) {
      disCrudTitle.textContent = 'Edit Submission';
      fillFormFromRow(row);
    } else {
      disCrudTitle.textContent = 'Submit Surveillance Report';
      // Defaults
    }
    disCrudModal.classList.add('show');
    disCrudModal.classList.remove('hidden');
  }
  function closeCrudModal() { if (!disCrudModal) return; disCrudModal.classList.remove('show'); disCrudModal.classList.add('hidden'); }
  window.openCrudModal = openCrudModal; window.closeCrudModal = closeCrudModal; if (disAdd) disAdd.addEventListener('click', () => openCrudModal('create'));

  async function apiCall(payload) { const res = await fetch('hss/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(payload) }); const text = await res.text(); if (!res.ok) throw new Error(text || ('HTTP ' + res.status)); return JSON.parse(text); }
  async function deleteRow(row) { if (!row) return; const id = parseInt(row.dataset.id || '0', 10); if (!id) return alert('Invalid ID'); if (!confirm('Delete this submission?')) return; try { const result = await apiCall({ action: 'delete', id }); if (result.success) { row.remove(); applyDisFilters(); } else alert('Delete failed.'); } catch (e) { console.error(e); alert('Server error.'); } }
  window.deleteRow = deleteRow;

  disCrudForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const mode = disCrudForm.elements['mode'].value;
    const id = parseInt(disCrudForm.elements['id'].value || '0', 10);
    const payload = {};
    const f = disCrudForm.elements;

    if (mode === 'edit') {
      if (!id) return alert('Invalid ID');
      payload.action = 'update';
      payload.id = id;
      payload.full_name = f['full_name'].value;
      payload.address = f['address'].value;
      payload.preferred_date = f['preferred_date'].value;
      if (f['phone']) payload.phone = f['phone'].value;

      // In edit mode, we just save whatever is in the textarea as the details
      payload.service_details = f['symptoms'].value;

    } else {
      // CREATE MODE - Format the Data
      payload.action = 'create';
      payload.service_type = '<?php echo $serviceType; ?>';
      payload.full_name = f['full_name'].value;

      // Use phone as contact if available
      if (f['phone']) payload.phone = f['phone'].value; else payload.phone = '';
      payload.email = f['email'].value;
      payload.user_email = f['user_email'].value;

      payload.address = f['address'].value;
      payload.preferred_date = f['preferred_date'].value;
      payload.urgency = 'medium'; // Default for reports

      // Format Details
      const age = f['age'].value;
      const gender = f['gender'].value;
      const diagnosis = f['diagnosis_category'].value;
      const symptoms = f['symptoms'].value;

      const report = `SURVEILLANCE REPORT\n` +
        `Diagnosis/Category: ${diagnosis}\n` +
        `Patient Age: ${age}\n` +
        `Patient Gender: ${gender}\n\n` +
        `SYMPTOMS & SIGNS:\n${symptoms}`;

      payload.service_details = report;
    }

    try {
      const result = await apiCall(payload);
      if (!result.success) throw new Error('Operation failed');
      alert('Report Submitted Successfully');
      location.reload();
    } catch (err) { console.error(err); alert('Error saving data.'); }
  });

  applyDisFilters();
</script>