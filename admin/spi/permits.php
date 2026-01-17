<?php
// Admin list for SPI Sanitary Permit Applications (received from citizen portal)
try {
  $stmt = $db->prepare("SELECT spa.*, CONCAT(u.first_name, ' ', u.last_name) AS full_name, u.email, u.phone, 
                                 COALESCE(ss.completed_steps, 0) AS completed_steps,
                                 sub.details AS submission_details,
                                 insp.details AS inspection_details,
                                 insp.status AS inspection_status,
                                 insp_u.first_name AS inspector_fname, insp_u.last_name AS inspector_lname,
                                 iss.details AS issuance_details,
                                 iss.status AS issuance_status
                          FROM sanitary_permit_applications spa
                          LEFT JOIN users u ON u.id = spa.user_id
                          LEFT JOIN (
                              SELECT application_id, COUNT(DISTINCT CASE WHEN status='completed' THEN step END) AS completed_steps
                              FROM sanitary_permit_steps
                              GROUP BY application_id
                          ) ss ON ss.application_id = spa.id
                          LEFT JOIN (
                              SELECT s1.application_id, s1.details
                              FROM sanitary_permit_steps s1
                              INNER JOIN (
                                  SELECT application_id, MAX(created_at) as max_c
                                  FROM sanitary_permit_steps
                                  WHERE step = 'submission'
                                  GROUP BY application_id
                              ) s2 ON s1.application_id = s2.application_id AND s1.created_at = s2.max_c
                          ) sub ON sub.application_id = spa.id
                          LEFT JOIN (
                              SELECT s3.application_id, s3.details, s3.status, s3.user_id
                              FROM sanitary_permit_steps s3
                              INNER JOIN (
                                  SELECT application_id, MAX(created_at) as max_c
                                  FROM sanitary_permit_steps
                                  WHERE step = 'inspection'
                                  GROUP BY application_id
                              ) s4 ON s3.application_id = s4.application_id AND s3.created_at = s4.max_c
                          ) insp ON insp.application_id = spa.id
                          LEFT JOIN users insp_u ON insp_u.id = insp.user_id
                          LEFT JOIN (
                              SELECT s5.application_id, s5.details, s5.status
                              FROM sanitary_permit_steps s5
                              INNER JOIN (
                                  SELECT application_id, MAX(created_at) as max_c
                                  FROM sanitary_permit_steps
                                  WHERE step = 'issuance'
                                  GROUP BY application_id
                              ) s6 ON s5.application_id = s6.application_id AND s5.created_at = s6.max_c
                          ) iss ON iss.application_id = spa.id
                          GROUP BY spa.id
                          ORDER BY spa.created_at DESC");
  $stmt->execute();
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  error_log('Error fetching sanitary permit applications: ' . $e->getMessage());
  $rows = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div
  class="mb-8 rounded-2xl p-8 border border-blue-100 shadow-lg bg-gradient-to-r from-blue-50 via-emerald-50 to-sky-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Sanitary Permit Applications</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Submissions coming from the citizen sanitation permit page.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span
          class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($rows); ?></span></div>
      <button id="spa-export"
        class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-medium shadow-sm">Export
        CSV</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="spa-filter-status"
        class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
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
        <input id="spa-search" type="text" placeholder="Search establishment, owner, email, phone..."
          class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="spa-table">
      <thead>
        <tr
          class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700 font-bold uppercase text-[10px] tracking-wider">
          <th class="py-3 px-3">ID</th>
          <th class="py-3 px-3">Establishment & Owner</th>
          <th class="py-3 px-3">Classification</th>
          <th class="py-3 px-3">Employees</th>
          <th class="py-3 px-3">Contact</th>
          <th class="py-3 px-3">Progress</th>
          <th class="py-3 px-3">Status</th>
          <th class="py-3 px-3 text-center">Actions</th>
          <th class="py-3 px-3">Created</th>
        </tr>
      </thead>
      <tbody id="spa-tbody">
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="11" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No applications found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <tr
              class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
              <td class="py-3 px-3 text-gray-500 font-mono text-xs">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3">
                <div class="text-gray-900 dark:text-slate-100 font-bold"><?php echo h($r['establishment_name']); ?></div>
                <div class="text-[10px] text-gray-500 dark:text-slate-400 italic mb-1">
                  <?php echo h($r['establishment_address'] ?? ''); ?></div>
                <div class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Owner:
                  <?php echo h($r['owner_name'] ?? 'N/A'); ?></div>
              </td>
              <td class="py-3 px-3">
                <div class="flex flex-col gap-0.5">
                  <span class="text-[10px] uppercase font-black text-slate-400 tracking-tighter">Type: <span
                      class="text-slate-700 dark:text-slate-300"><?php echo h($r['app_type'] ?? ''); ?></span></span>
                  <span
                    class="text-[11px] font-bold text-slate-800 dark:text-slate-200"><?php echo h($r['industry'] ?? ''); ?></span>
                  <span
                    class="text-[10px] text-slate-500 dark:text-slate-400 truncate max-w-[150px]"><?php echo h($r['sub_industry'] ?? ''); ?></span>
                  <div
                    class="mt-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[9px] font-bold text-slate-500 border border-slate-200 dark:border-slate-700 self-start">
                    <?php echo h($r['business_line'] ?? ''); ?></div>
                </div>
              </td>
              <td class="py-3 px-3">
                <div
                  class="flex flex-col items-center justify-center p-2 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-700 min-w-[70px]">
                  <div class="text-sm font-black text-slate-800 dark:text-slate-100">
                    <?php echo (int) ($r['total_employees'] ?? 0); ?></div>
                  <div class="text-[9px] uppercase font-bold text-slate-400">Total</div>
                  <div class="flex gap-2 mt-1 pt-1 border-t border-slate-200 dark:border-slate-700 w-full justify-center">
                    <span
                      class="text-[9px] font-bold text-emerald-500"><?php echo (int) ($r['employees_with_health_cert'] ?? 0); ?></span>
                    <span
                      class="text-[9px] font-bold text-rose-500"><?php echo (int) ($r['employees_without_health_cert'] ?? 0); ?></span>
                  </div>
                </div>
              </td>
              <td class="py-3 px-3">
                <div class="text-xs font-bold text-slate-700 dark:text-slate-200"><?php echo h($r['full_name'] ?? ''); ?>
                </div>
                <div class="text-[10px] text-slate-500 truncate max-w-[120px]"><?php echo h($r['email'] ?? ''); ?></div>
                <div class="text-[10px] text-indigo-500 font-medium"><?php echo h($r['phone'] ?? ''); ?></div>
              </td>
              <td class="py-3 px-3">
                <div class="flex items-center gap-2">
                  <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 max-w-[40px]">
                    <div class="bg-indigo-500 h-1.5 rounded-full"
                      style="width: <?php echo ((int) $r['completed_steps'] / 5) * 100; ?>%"></div>
                  </div>
                  <span
                    class="text-[10px] font-bold text-slate-600 dark:text-slate-300"><?php echo (int) $r['completed_steps']; ?>/5</span>
                </div>
              </td>
              <td class="py-3 px-3">
                <?php $s = $r['status'] ?? 'pending'; ?>
                <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest <?php
                echo $s === 'completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : ($s === 'in_progress' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s === 'cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'));
                ?> border border-current shadow-sm"><?php echo h($s); ?></span>
              </td>
              <td class="py-3 px-3 text-center">
                <button
                  class="px-4 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl bg-white dark:bg-slate-800 hover:bg-indigo-600 hover:text-white text-indigo-600 border-2 border-indigo-600/20 hover:border-indigo-600 transition-all shadow-sm btn-view"
                  data-app='<?php echo h(json_encode($r)); ?>'>View</button>
              </td>
              <td class="py-3 px-3 text-gray-400 text-[10px] whitespace-nowrap">
                <?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
  (function () {
    const searchInput = document.getElementById('spa-search');
    const statusSel = document.getElementById('spa-filter-status');
    const tbody = document.getElementById('spa-tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    function normalize(s) { return (s || '').toString().toLowerCase(); }

    function applyFilters() {
      const q = normalize(searchInput.value);
      const st = statusSel.value;
      rows.forEach(tr => {
        if (!tr.querySelector) return;
        const tds = Array.from(tr.querySelectorAll('td')).map(td => normalize(td.innerText));
        const status = (tr.getAttribute('data-status') || '').toLowerCase();
        const matchQ = !q || tds.some(t => t.includes(q));
        const matchS = !st || status === st;
        tr.style.display = (matchQ && matchS) ? '' : 'none';
      });
    }

    searchInput?.addEventListener('input', applyFilters);
    statusSel?.addEventListener('change', applyFilters);

    document.getElementById('spa-export')?.addEventListener('click', () => {
      const header = ['ID', 'Establishment', 'Owner', 'App Type', 'Industry', 'Business Line', 'Status', 'Insp Result', 'Created'];
      const rowsCsv = [header];
      const trs = tbody.querySelectorAll('tr');
      trs.forEach(tr => {
        const tds = tr.querySelectorAll('td');
        if (tds.length < 11) return;
        const id = tds[0].innerText.trim();
        const est = tds[1].innerText.trim().split('\n')[0];
        const owner = tds[2].innerText.trim();
        const appType = tds[3].innerText.trim();
        const industry = tds[4].innerText.trim();
        const bizLine = tds[6].innerText.trim();
        const status = tds[10].innerText.trim();
        const created = tds[12].innerText.trim();
        rowsCsv.push([id, est, owner, appType, industry, bizLine, status, '', created]);
      });
      const csv = rowsCsv.map(r => r.map(v => '"' + (v || '').replaceAll('"', '""') + '"').join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'sanitary_permit_applications.csv';
      a.click();
    });
  })();
</script>

<!-- Assign Inspector Modal -->
<div id="assignModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" data-close="1"></div>
  <div
    class="relative mx-auto mt-24 w-full max-w-md bg-white dark:bg-slate-900 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 p-6">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Assign Inspector</h3>
    <div class="mb-4">
      <label class="block text-xs text-gray-700 dark:text-slate-300 mb-1">Inspector</label>
      <select id="assignInspectorSelect"
        class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
        <option value="">Loading...</option>
      </select>
    </div>
    <div class="flex items-center justify-end gap-2">
      <button id="assignCancelBtn"
        class="px-3 py-2 rounded-md text-sm bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200">Cancel</button>
      <button id="assignSaveBtn"
        class="px-3 py-2 rounded-md text-sm bg-indigo-600 hover:bg-indigo-500 text-white">Save</button>
    </div>
  </div>
</div>

<!-- View Application Modal -->
<div id="viewModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" data-close="1"></div>
  <div
    class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh]">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
      <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">Application Details</h3>
      <button data-close="1"
        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div id="viewModalContent" class="p-6 overflow-y-auto custom-scrollbar space-y-6">
      <!-- Data will be injected here -->
    </div>
    <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
      <button onclick="closeViewModal()"
        class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 transition-all">Close</button>
    </div>
  </div>
</div>

<script>
  (function () {
    let assignAppId = null;
    const modal = document.getElementById('assignModal');
    const sel = document.getElementById('assignInspectorSelect');
    const btnSave = document.getElementById('assignSaveBtn');
    const btnCancel = document.getElementById('assignCancelBtn');
    const openModal = () => { modal.classList.remove('hidden'); };
    const closeModal = () => { modal.classList.add('hidden'); assignAppId = null; };

    function loadInspectors() {
      sel.innerHTML = '<option value="">Loading...</option>';
      const paths = [
        'api/permits.php?action=list_inspectors',
        '../api/permits.php?action=list_inspectors',
        (location.pathname.replace(/\/[^\/]*$/, '/') + 'api/permits.php?action=list_inspectors')
      ];
      (async () => {
        for (const url of paths) {
          try {
            const r = await fetch(url, { credentials: 'same-origin' });
            const t = await r.text();
            const j = JSON.parse(t);
            if (j && j.success && Array.isArray(j.inspectors)) {
              sel.innerHTML = '<option value="">Select inspector...</option>';
              j.inspectors.forEach(it => {
                const o = document.createElement('option');
                o.value = it.id;
                o.textContent = it.name + (it.email ? ' (' + it.email + ')' : '');
                sel.appendChild(o);
              });
              sel.dataset.apiBase = url.split('?')[0];
              return;
            }
          } catch (e) { }
        }
        sel.innerHTML = '<option value="">Failed to load</option>';
      })();
    }

    document.querySelectorAll('.btn-assign').forEach(btn => {
      btn.addEventListener('click', (e) => {
        assignAppId = parseInt(btn.getAttribute('data-app-id')) || null;
        if (!assignAppId) return;
        openModal();
        loadInspectors();
      });
    });

    btnCancel?.addEventListener('click', () => closeModal());
    modal?.addEventListener('click', (e) => { if (e.target && e.target.getAttribute('data-close')) closeModal(); });

    btnSave?.addEventListener('click', () => {
      const inspectorId = parseInt(sel.value) || 0;
      if (!assignAppId || !inspectorId) { alert('Please select an inspector.'); return; }
      btnSave.disabled = true; btnSave.textContent = 'Saving...';
      const base = sel.dataset.apiBase || 'api/permits.php';
      const url = base + (base.includes('?') ? '&' : '?') + 'action=assign_inspector';
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ application_id: assignAppId, inspector_id: inspectorId })
      }).then(r => r.json()).then(j => {
        alert(j && j.success ? 'Inspector assigned.' : (j.message || 'Failed to assign'));
        closeModal();
      }).catch(() => alert('Failed to assign')).finally(() => { btnSave.disabled = false; btnSave.textContent = 'Save'; });
    });

    // View Modal Logic
    const viewModal = document.getElementById('viewModal');
    const viewContent = document.getElementById('viewModalContent');

    const showViewModal = (data) => {
      let html = `
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
        <div class="space-y-4">
          <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 border-b border-slate-100 dark:border-slate-800 pb-1">Establishment Information</h4>
          <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Establishment Name</label>
            <div class="text-slate-800 dark:text-slate-200 font-semibold">${data.establishment_name || 'N/A'}</div>
          </div>
          <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Address</label>
            <div class="text-slate-800 dark:text-slate-200">${data.establishment_address || 'N/A'}</div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Industry</label>
              <div class="text-slate-800 dark:text-slate-200">${data.industry || 'N/A'}</div>
            </div>
            <div>
              <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Sub-Industry</label>
              <div class="text-slate-800 dark:text-slate-200">${data.sub_industry || 'N/A'}</div>
            </div>
          </div>
          <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Business Line</label>
            <div class="text-slate-800 dark:text-slate-200">${data.business_line || 'N/A'}</div>
          </div>
        </div>

        <div class="space-y-4">
          <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 border-b border-slate-100 dark:border-slate-800 pb-1">Owner & Contact</h4>
          <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Owner Name</label>
            <div class="text-slate-800 dark:text-slate-200 font-semibold">${data.owner_name || 'N/A'}</div>
          </div>
          <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Applied By</label>
            <div class="text-slate-800 dark:text-slate-200 font-semibold">Citizen</div>
          </div>
          <div>
            <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Contact Number</label>
            <div class="text-slate-800 dark:text-slate-200 font-medium text-sm">${data.phone || 'N/A'}</div>
          </div>
        </div>

        <div class="space-y-4">
          <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 border-b border-slate-100 dark:border-slate-800 pb-1">Employee Breakdown</h4>
          <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-800 self-start">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <div class="text-lg font-bold text-slate-800 dark:text-slate-100">${data.total_employees || 0}</div>
                <div class="text-[10px] text-slate-400 uppercase">Total</div>
              </div>
              <div>
                <div class="text-lg font-bold text-emerald-500">${data.employees_with_health_cert || 0}</div>
                <div class="text-[10px] text-slate-400 uppercase">With HC</div>
              </div>
              <div>
                <div class="text-lg font-bold text-rose-500">${data.employees_without_health_cert || 0}</div>
                <div class="text-[10px] text-slate-400 uppercase">No HC</div>
              </div>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 border-b border-slate-100 dark:border-slate-800 pb-1">Application Details</h4>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Type</label>
              <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200 uppercase border border-blue-200/50 dark:border-blue-700/50">${data.app_type || 'N/A'}</span>
            </div>
            <div>
              <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Status</label>
              <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200 uppercase border border-amber-200/50 dark:border-amber-700/50">${data.status || 'PENDING'}</span>
            </div>
            <div>
              <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Urgency</label>
              <div class="text-slate-800 dark:text-slate-200 font-medium italic capitalize text-sm">${data.urgency || 'Normal'}</div>
            </div>
            <div>
              <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5">Preferred Date</label>
              <div class="text-slate-800 dark:text-slate-200 text-sm">${data.preferred_date || 'N/A'}</div>
            </div>
             ${data.assigned_inspector_name ? `
            <div class="col-span-2 mt-2 pt-2 border-t border-slate-100 dark:border-slate-800">
              <label class="block text-[10px] uppercase font-bold text-emerald-500 mb-0.5">Assigned Inspector</label>
              <div class="text-slate-800 dark:text-white font-bold text-sm flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                ${data.assigned_inspector_name}
              </div>
            </div>
            ` : ''}
          </div>
        </div>

        <div class="space-y-4 md:col-span-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 border-b border-slate-100 dark:border-slate-800 pb-1 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Official Inspection Report
          </h4>
          <div class="bg-emerald-50/50 dark:bg-emerald-900/10 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-900/30">
            ${(() => {
          let insp = {};
          try { if (data.inspection_details) insp = JSON.parse(data.inspection_details); } catch (e) { }
          if (!insp.result && !data.inspection_status) return '<div class="text-xs text-slate-400 italic text-center py-4">No inspection report submitted yet by the inspector.</div>';

          const res = (insp.result || data.inspection_status || 'Assigned').toUpperCase();
          const resClass = res === 'PASSED' ? 'text-emerald-600' : (res === 'FAILED' ? 'text-rose-600' : 'text-amber-600');
          const inspectorName = data.inspector_fname ? `${data.inspector_fname} ${data.inspector_lname}` : (insp.assigned_inspector_name || 'Assigned Inspector');

          return `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="bg-white dark:bg-slate-900/50 p-3 rounded-xl border border-emerald-100 dark:border-emerald-900/40">
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5 tracking-tight">Overall Result</label>
                    <div class="font-black text-xl ${resClass} flex items-center gap-2">
                      <div class="w-2 h-2 rounded-full ${res === 'PASSED' ? 'bg-emerald-500' : (res === 'FAILED' ? 'bg-rose-500' : 'bg-amber-500')} animate-pulse"></div>
                      ${res}
                    </div>
                  </div>
                  <div class="bg-white dark:bg-slate-900/50 p-3 rounded-xl border border-emerald-100 dark:border-emerald-900/40">
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5 tracking-tight">Inspection Date</label>
                    <div class="text-slate-800 dark:text-slate-100 font-bold">${insp.scheduled_date || 'In Progress'}</div>
                  </div>
                  <div class="bg-white dark:bg-slate-900/50 p-3 rounded-xl border border-emerald-100 dark:border-emerald-900/40">
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-0.5 tracking-tight">Inspector In-Charge</label>
                    <div class="text-slate-800 dark:text-slate-100 font-bold truncate">${inspectorName}</div>
                  </div>
                  <div class="md:col-span-3">
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1 px-1">Technical Findings & Recommendations</label>
                    <div class="text-sm text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4 leading-relaxed shadow-sm min-h-[100px] whitespace-pre-wrap">
                      ${insp.findings || 'No formal findings recorded yet.'}
                    </div>
                  </div>
                </div>
              `;
        })()}
          </div>
        </div>
        <!-- Submitted Documents -->
        <div class="space-y-4 md:col-span-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400 border-b border-slate-100 dark:border-slate-800 pb-1">Submitted Documents</h4>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            ${(() => {
          let subDetails = {};
          try { if (data.submission_details) subDetails = JSON.parse(data.submission_details); } catch (e) { }
          const docLabels = {
            'business_permit_image': 'Business Permit',
            'permit_receipt_image': 'Permit Receipt',
            'health_certificates_image': 'Health Certs',
            'occupancy_permit_image': 'Occupancy Permit',
            'water_analysis_image': 'Water Analysis',
            'pest_control_image': 'Pest Control'
          };
          let docsHtml = '';
          Object.keys(docLabels).forEach(key => {
            if (subDetails[key]) {
              const path = '../' + subDetails[key];
              docsHtml += `
                        <a href="${path}" target="_blank" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 hover:border-indigo-200 dark:hover:border-indigo-900/50 hover:bg-white dark:hover:bg-slate-800 transition-all group">
                          <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mb-2 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 transition-colors">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                          </div>
                          <div class="text-[9px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-center leading-tight">${docLabels[key]}</div>
                        </a>
                      `;
            }
          });
          return docsHtml || '<div class="col-span-full py-4 text-center text-xs text-slate-400 italic bg-slate-50/50 dark:bg-slate-800/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">No documents uploaded for this application yet.</div>';
        })()}
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="md:col-span-2 pt-6 flex flex-col items-center gap-6 border-t border-slate-100 dark:border-slate-800">
           <div class="w-full">
            <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-500 mb-4 flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              Final Decision & Licensing
            </h4>
            <div class="p-5 rounded-2xl border-2 border-dashed border-indigo-200 dark:border-slate-800 bg-indigo-50/30 dark:bg-slate-800/20">
              <p class="text-[11px] text-slate-600 dark:text-slate-400 mb-4 leading-relaxed text-center font-medium">
                Review the inspector's report above. If satisfied with compliance, approve to **Pass to Permit & Licensing**.
              </p>
              <div class="flex flex-col items-center gap-4">
                 <label class="flex items-center gap-3 p-4 rounded-2xl border border-indigo-200 dark:border-indigo-900/40 bg-white dark:bg-slate-900 cursor-pointer hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-800 transition-all w-full md:w-auto">
                    <input type="checkbox" id="eval-compliance" class="w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 border-indigo-200">
                    <div class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-tight">I verify that this establishment meets PD 856 Sanitary Standards.</div>
                 </label>
                 
                 <div class="flex gap-4 w-full justify-center">
                   <button onclick="handleDecision('rejected', ${data.id})" class="flex-1 max-w-[200px] px-6 py-4 rounded-xl bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700 font-bold text-xs uppercase tracking-widest transition-all">Reject Application</button>
                   <button onclick="handleDecision('completed', ${data.id})" class="flex-1 max-w-[200px] px-6 py-4 rounded-xl bg-indigo-600 text-white hover:bg-indigo-500 font-bold text-xs uppercase tracking-widest transition-all shadow-xl shadow-indigo-500/20">Approve & Send to Licensing</button>
                 </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    `;

      viewContent.innerHTML = html;
      viewModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    };

    window.closeViewModal = () => {
      viewModal.classList.add('hidden');
      document.body.style.overflow = '';
    };

    window.handleDecision = async (status, appId) => {
      if (status === 'completed') {
        const evalBox = document.getElementById('eval-compliance');
        if (!evalBox || !evalBox.checked) {
          alert('Please confirm compliance evaluation (PD 856) before approving.');
          return;
        }
      }

      if (!confirm(`Are you sure you want to ${status === 'completed' ? 'APPROVE' : 'REJECT'} this sanitation clearance?`)) return;

      try {
        const resp = await fetch('api/permits.php?action=final_decision', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ application_id: appId, status: status })
        });
        const res = await resp.json();
        if (res.success) {
          alert('Decision processed successfully.');
          location.reload();
        } else {
          alert(res.message || 'Failed to process decision.');
        }
      } catch (e) {
        alert('Error processing decision.');
      }
    };

    document.querySelectorAll('.btn-view').forEach(btn => {
      btn.addEventListener('click', () => {
        try {
          const data = JSON.parse(btn.getAttribute('data-app'));
          showViewModal(data);
        } catch (e) {
          console.error('Error parsing application data', e);
        }
      });
    });

    viewModal?.addEventListener('click', (e) => {
      const closeBtn = e.target.closest('[data-close]');
      if (closeBtn) closeViewModal();
    });
  })();
</script>