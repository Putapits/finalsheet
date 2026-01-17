<?php
// Admin view for Sanitary Permit Documents (Submission Step)
try {
  $stmt = $db->prepare("SELECT sps.*, spa.id AS application_id, spa.establishment_name, spa.owner_name, u.first_name, u.last_name, u.email, u.phone AS citizen_phone
                        FROM sanitary_permit_steps sps
                        JOIN sanitary_permit_applications spa ON sps.application_id = spa.id
                        JOIN users u ON spa.user_id = u.id
                        WHERE sps.step = 'submission'
                        ORDER BY sps.created_at DESC");
  $stmt->execute();
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Error fetching SPI sanitation documents: ' . $e->getMessage());
  $requests = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div
  class="mb-8 rounded-2xl p-8 border border-lime-100 shadow-lg bg-gradient-to-r from-lime-50 via-green-50 to-emerald-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Sanitation Documents</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Review documents submitted for Sanitary Permits.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total Submissions: <span
          class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($requests); ?></span></div>
      <button id="san-export"
        class="px-3 py-2 bg-lime-600 hover:bg-lime-500 text-white rounded-lg text-xs font-medium shadow-sm">Export
        CSV</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <!-- Filters -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="san-filter-status"
        class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-lime-500 dark:focus:ring-lime-400">
        <option value="">All</option>
        <option value="pending">Pending Review</option>
        <option value="completed">Accepted</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Search</label>
      <div class="relative">
        <input id="san-search" type="text" placeholder="Search establishment, owner, citizen..."
          class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-lime-500 dark:focus:ring-lime-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="san-table">
      <thead>
        <tr class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="establishment">Establishment</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="citizen">Submitted By</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="created">Submitted Date</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="san-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="7" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No document submissions found.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <?php
            $citizenName = $r['first_name'] . ' ' . $r['last_name'];
            ?>
            <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 san-row"
              data-id="<?php echo h($r['id']); ?>" data-establishment="<?php echo h($r['establishment_name']); ?>"
              data-citizen="<?php echo h($citizenName); ?>" data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>">
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium"><?php echo h($r['establishment_name']); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($citizenName); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['citizen_phone']); ?></td>
              <td class="py-3 px-3">
                <?php $s = $r['status'] ?? 'pending'; ?>
                <span class="px-2 py-1 rounded-full text-[10px] font-bold <?php
                echo $s === 'completed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s === 'rejected' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200');
                ?>"><?php echo h(strtoupper($s === 'completed' ? 'ACCEPTED' : $s)); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">
                <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
              </td>
              <td class="py-3 px-3 text-right">
                <button
                  class="px-3 py-1 bg-lime-600 text-white rounded-lg hover:bg-lime-500 text-xs mr-2 font-medium shadow-sm transition-all active:scale-95"
                  onclick='openSanModal(<?php echo json_encode([
                    "id" => $r["id"],
                    "application_id" => $r["application_id"],
                    "establishment_name" => $r["establishment_name"],
                    "owner_name" => $r["owner_name"],
                    "full_name" => $citizenName,
                    "email" => $r["email"],
                    "phone" => $r["citizen_phone"],
                    "details" => $r["details"],
                    "status" => $r["status"],
                    "created_at" => $r["created_at"],
                  ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>View Docs</button>
                <?php if ($s === 'completed'): ?>
                  <button
                    class="px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-500 text-xs font-medium shadow-sm transition-all active:scale-95 btn-assign"
                    data-app-id="<?php echo (int) $r['application_id']; ?>">Assign Inspector</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="san-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- Details Modal -->
<div id="san-modal" class="fixed inset-0 hidden items-center justify-center z-[1000] p-4">
  <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeSanModal()"></div>
  <div
    class="relative modal-panel bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-4xl w-full p-8 max-h-[90vh] overflow-y-auto shadow-2xl animate-in fade-in zoom-in duration-300">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h3 class="text-2xl font-black text-gray-900 dark:text-slate-100 tracking-tight">Submission Review</h3>
        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 uppercase tracking-widest font-bold">Document
          Verification</p>
      </div>
      <button
        class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 hover:bg-rose-500 hover:text-white transition-all"
        onclick="closeSanModal()">✕</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
      <div class="space-y-4">
        <h4
          class="text-[10px] font-black text-lime-600 dark:text-lime-400 uppercase tracking-widest border-b border-lime-100 dark:border-lime-900/50 pb-2">
          Establishment Info</h4>
        <div id="san-est-info" class="space-y-4"></div>
      </div>
      <div class="space-y-4">
        <h4
          class="text-[10px] font-black text-lime-600 dark:text-lime-400 uppercase tracking-widest border-b border-lime-100 dark:border-lime-900/50 pb-2">
          Citizen Info</h4>
        <div id="san-user-info" class="space-y-4"></div>
      </div>
    </div>

    <div class="space-y-4">
      <h4
        class="text-[10px] font-black text-lime-600 dark:text-lime-400 uppercase tracking-widest border-b border-lime-100 dark:border-lime-900/50 pb-2">
        Uploaded Requirements</h4>
      <div id="san-docs-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-4"></div>
    </div>

    <!-- Actions for Document Level -->
    <div id="san-modal-actions"
      class="mt-10 pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
      <button id="btn-reject-docs"
        class="px-5 py-2.5 rounded-xl bg-rose-100 text-rose-700 hover:bg-rose-200 font-bold text-sm transition-all shadow-sm">Reject
        Documents</button>
      <button id="btn-accept-docs"
        class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-500 font-bold text-sm transition-all shadow-lg shadow-emerald-500/20">Accept
        Documents</button>
    </div>
  </div>
</div>

<!-- Assign Inspector Modal (Similar to permits.php) -->
<div id="assignModal" class="fixed inset-0 z-[1100] hidden">
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-close="1" onclick="closeAssignModal()"></div>
  <div
    class="relative mx-auto mt-24 w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 p-6 scale-in-center overflow-hidden">
    <div class="mb-5">
      <h3 class="text-xl font-bold text-gray-900 dark:text-slate-100 mb-1">Assign Inspector</h3>
      <p class="text-xs text-gray-500 dark:text-slate-400">Select a qualified inspector to perform site verification.
      </p>
    </div>
    <div class="mb-6">
      <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Available
        Inspectors</label>
      <select id="assignInspectorSelect"
        class="w-full bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-slate-100 rounded-xl px-4 py-3 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all font-medium">
        <option value="">Loading...</option>
      </select>
    </div>
    <div class="flex items-center justify-end gap-3">
      <button onclick="closeAssignModal()"
        class="px-5 py-2.5 rounded-xl text-sm font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 transition-all">Cancel</button>
      <button id="assignSaveBtn"
        class="px-5 py-2.5 rounded-xl text-sm font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-500/20 transition-all active:scale-95">Save
        Assignment</button>
    </div>
  </div>
</div>


<script>
  const sanSearch = document.getElementById('san-search');
  const sanBody = document.getElementById('san-tbody');
  const sanFilterStatus = document.getElementById('san-filter-status');
  const sanTable = document.getElementById('san-table');
  const sanExport = document.getElementById('san-export');
  const sanPagination = document.getElementById('san-pagination');
  let sanSortKey = 'id';
  let sanSortAsc = false;
  let sanPage = 1;
  const sanPageSize = 10;

  function sanRows() { return Array.from(sanBody.querySelectorAll('.san-row')); }

  function applySanFilters() {
    const q = (sanSearch?.value || '').toLowerCase();
    const fs = (sanFilterStatus?.value || '').toLowerCase();
    if (!sanBody) return;
    let rows = sanRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const s = (tr.getAttribute('data-status') || '').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!fs || s === fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => {
      const ka = (a.dataset[sanSortKey] || '').toLowerCase();
      const kb = (b.dataset[sanSortKey] || '').toLowerCase();
      if (ka < kb) return sanSortAsc ? -1 : 1;
      if (ka > kb) return sanSortAsc ? 1 : -1;
      return 0;
    });
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / sanPageSize));
    if (sanPage > pages) sanPage = pages;
    const start = (sanPage - 1) * sanPageSize;
    const end = start + sanPageSize;
    const visible = new Set(rows.slice(start, end));
    sanRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderSanPagination(pages);
  }

  function renderSanPagination(pages) {
    if (!sanPagination) return;
    sanPagination.innerHTML = '';
    if (pages <= 1) return;
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-xs font-bold';
    prev.disabled = sanPage <= 1;
    prev.onclick = () => { sanPage--; applySanFilters(); };
    sanPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'w-8 h-8 rounded-lg text-xs font-bold transition-all ' + (i === sanPage ? 'bg-lime-600 text-white shadow-lg' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200');
      b.onclick = () => { sanPage = i; applySanFilters(); };
      sanPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors text-xs font-bold';
    next.disabled = sanPage >= pages;
    next.onclick = () => { sanPage++; applySanFilters(); };
    sanPagination.appendChild(next);
  }

  [sanSearch, sanFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applySanFilters);
    if (el) el.addEventListener('change', applySanFilters);
  });

  if (sanTable) {
    sanTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (sanSortKey === key) { sanSortAsc = !sanSortAsc; } else { sanSortKey = key; sanSortAsc = true; }
        applySanFilters();
      });
    });
  }

  function field(label, value) {
    return `<div>\n      <div class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">${label}</div>\n      <div class="text-sm font-bold text-gray-900 dark:text-white break-words">${value || 'N/A'}</div>\n    </div>`;
  }

  let currentData = null;

  function openSanModal(data) {
    currentData = data;
    const modal = document.getElementById('san-modal');
    const estInfo = document.getElementById('san-est-info');
    const userInfo = document.getElementById('san-user-info');
    const docsGrid = document.getElementById('san-docs-grid');
    const actions = document.getElementById('san-modal-actions');
    if (!modal || !estInfo || !userInfo || !docsGrid) return;

    estInfo.innerHTML = `
      ${field('Establishment Name', data.establishment_name)}
      ${field('Owner Name', data.owner_name)}
    `;
    userInfo.innerHTML = `
      ${field('Applied By', 'Citizen')}
      ${field('Email Address', data.email)}
      ${field('Contact Number', data.phone)}
    `;

    let subDetails = {};
    try { if (data.details) subDetails = JSON.parse(data.details); } catch (e) { }

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
          <a href="${path}" target="_blank" class="flex flex-col items-center justify-center p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 hover:border-lime-200 dark:hover:border-lime-900/50 hover:bg-white dark:hover:bg-slate-800 transition-all group shadow-sm hover:shadow-md">
            <div class="w-12 h-12 rounded-2xl bg-lime-100 dark:bg-lime-900/30 flex items-center justify-center mb-3 group-hover:bg-lime-600 group-hover:text-white transition-all">
              <svg class="w-6 h-6 text-lime-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <div class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-tighter text-center leading-tight">${docLabels[key]}</div>
          </a>
        `;
      }
    });

    docsGrid.innerHTML = docsHtml || '<div class="col-span-full py-12 text-center text-xs text-slate-400 italic bg-slate-50 dark:bg-slate-800/50 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">No documents found in this submission.</div>';

    // Show/hide actions based on status
    if (data.status === 'completed' || data.status === 'rejected') {
      actions.classList.add('hidden');
    } else {
      actions.classList.remove('hidden');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
  }

  async function updateStepStatus(id, status) {
    if (!id) return;
    try {
      const resp = await fetch('api/permits.php?action=update_step_status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ step_id: id, status: status })
      });
      const res = await resp.json();
      if (res.success) {
        alert('Status updated successfully.');
        location.reload();
      } else {
        alert(res.message || 'Failed to update status.');
      }
    } catch (e) {
      alert('Error updating status.');
    }
  }

  document.getElementById('btn-accept-docs')?.addEventListener('click', () => {
    if (confirm('Accept these documents and move to next step?')) {
      updateStepStatus(currentData.id, 'completed');
    }
  });

  document.getElementById('btn-reject-docs')?.addEventListener('click', () => {
    if (confirm('Reject these documents?')) {
      updateStepStatus(currentData.id, 'rejected');
    }
  });

  function closeSanModal() {
    const modal = document.getElementById('san-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  }

  // Assign Inspector Logic
  let assignAppId = null;
  const assignModal = document.getElementById('assignModal');
  const assignSelect = document.getElementById('assignInspectorSelect');

  function openAssignModal(id) {
    assignAppId = id;
    assignModal.classList.remove('hidden');
    loadInspectors();
  }

  function closeAssignModal() {
    assignModal.classList.add('hidden');
    assignAppId = null;
  }

  async function loadInspectors() {
    assignSelect.innerHTML = '<option value="">Loading...</option>';
    try {
      const resp = await fetch('api/permits.php?action=list_inspectors');
      const res = await resp.json();
      if (res.success && Array.isArray(res.inspectors)) {
        assignSelect.innerHTML = '<option value="">Select inspector...</option>';
        res.inspectors.forEach(it => {
          const o = document.createElement('option');
          o.value = it.id;
          o.textContent = it.name + (it.email ? ' (' + it.email + ')' : '');
          assignSelect.appendChild(o);
        });
      }
    } catch (e) {
      assignSelect.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  document.getElementById('assignSaveBtn')?.addEventListener('click', async () => {
    const inspectorId = assignSelect.value;
    if (!assignAppId || !inspectorId) {
      alert('Please select an inspector.');
      return;
    }
    try {
      const resp = await fetch('api/permits.php?action=assign_inspector', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ application_id: assignAppId, inspector_id: inspectorId })
      });
      const res = await resp.json();
      if (res.success) {
        alert('Inspector assigned successfully.');
        closeAssignModal();
        location.reload();
      } else {
        alert(res.message || 'Failed to assign.');
      }
    } catch (e) {
      alert('Error assigning inspector.');
    }
  });

  // Attach assign buttons
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-assign');
    if (btn) {
      const appId = btn.getAttribute('data-app-id');
      openAssignModal(appId);
    }
  });

  applySanFilters();

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function sanToCSV() {
    const headers = ['ID', 'Establishment', 'Citizen', 'Phone', 'Status', 'Date'];
    const rows = sanRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0, 6).map(td => '"' + (td.innerText || '').replace(/"/g, '\\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (sanExport) sanExport.addEventListener('click', () => download('sanitation_documents.csv', sanToCSV()));
</script>