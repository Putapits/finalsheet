<?php
// Fetch all appointments for admin view
try {
    $stmt = $db->query("SELECT a.* FROM appointments a WHERE a.deleted_at IS NULL ORDER BY a.created_at DESC");
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('Error fetching appointments: ' . $e->getMessage());
    $appointments = [];
}
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8 rounded-2xl p-8 border border-emerald-100 shadow-lg bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Appointments</h2>
  <p class="text-gray-600 dark:text-slate-300 mt-1">All appointment submissions from citizens.</p>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <div class="relative w-full md:w-80">
      <input id="appt-search"  type="text" placeholder="Search..." class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 w-80">
      <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>
    <div class="flex items-center gap-2">
      <button id="appt-export" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-medium shadow-sm">Export CSV</button>
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span class="font-semibold text-gray-900 dark:text-slate-100"><?php echo count($appointments); ?></span></div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="appt-table">
      <thead>
        <tr class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="name">Name</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="type">Type</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="appt-tbody">
        <?php if (empty($appointments)): ?>
          <tr>
            <td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No appointments found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($appointments as $a): ?>
            <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 appt-row"
                data-id="<?php echo h($a['id']); ?>"
                data-name="<?php echo h(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))); ?>"
                data-email="<?php echo h($a['email'] ?? ''); ?>"
                data-phone="<?php echo h($a['phone'] ?? ''); ?>"
                data-type="<?php echo h($a['appointment_type'] ?? ''); ?>"
                data-preferred_date="<?php echo h($a['preferred_date'] ?? ''); ?>"
                data-status="<?php echo h($a['status'] ?? 'pending'); ?>"
                data-created="<?php echo h($a['created_at'] ?? ''); ?>">
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">#<?php echo h($a['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium"><?php echo h(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($a['email'] ?? ''); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($a['phone'] ?? ''); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($a['appointment_type'] ?? ''); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($a['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <span class="px-2 py-1 rounded-full text-xs font-semibold
                  <?php 
                    $s = $a['status'] ?? 'pending';
                    echo $s==='confirmed' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s==='completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : ($s==='cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200')); 
                  ?>">
                  <?php echo h(strtoupper($a['status'] ?? 'pending')); ?>
                </span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($a['created_at'] ?? ''); ?></td>
              <td class="py-3 px-3 text-right">
                <button
                  class="px-3 py-1 bg-emerald-600 text-white rounded-lg hover:bg-emerald-500 text-xs mr-2 font-medium shadow-sm"
                  onclick='openApptModal(<?php echo json_encode([
                    "id"=>$a["id"],
                    "first_name"=>$a["first_name"],
                    "middle_name"=>$a["middle_name"],
                    "last_name"=>$a["last_name"],
                    "email"=>$a["email"],
                    "phone"=>$a["phone"],
                    "birth_date"=>$a["birth_date"],
                    "gender"=>$a["gender"],
                    "civil_status"=>$a["civil_status"],
                    "address"=>$a["address"],
                    "appointment_type"=>$a["appointment_type"],
                    "preferred_date"=>$a["preferred_date"],
                    "health_concerns"=>$a["health_concerns"],
                    "medical_history"=>$a["medical_history"],
                    "current_medications"=>$a["current_medications"],
                    "allergies"=>$a["allergies"],
                    "emergency_contact_name"=>$a["emergency_contact_name"],
                    "emergency_contact_phone"=>$a["emergency_contact_phone"],
                    "status"=>$a["status"],
                    "created_at"=>$a["created_at"],
                  ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'>View</button>
                <!-- View-only for admin -->
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="appt-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- Details Modal -->
<div id="appt-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeApptModal()"></div>
  <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Appointment Details</h3>
      <button class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100" onclick="closeApptModal()">✕</button>
    </div>
    <div id="appt-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>
    /* ensure modal uses flex when shown */
    #appt-modal.show { display:flex; }
  </style>
</div>

<script>
  // Simple search filter
  const apptSearch = document.getElementById('appt-search');
  const apptBody = document.getElementById('appt-tbody');
  const apptTable = document.getElementById('appt-table');
  const apptExport = document.getElementById('appt-export');
  const apptPagination = document.getElementById('appt-pagination');
  let apptSortKey = 'id';
  let apptSortAsc = false;
  let apptPage = 1;
  const apptPageSize = 10;

  function apptRows() { return Array.from(apptBody.querySelectorAll('.appt-row')); }

  function apptApply() {
    const q = (apptSearch?.value || '').toLowerCase();
    let rows = apptRows();
    // filter by search
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      tr.dataset._match = text.includes(q) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    // sort
    rows.sort((a,b) => {
      const ka = (a.dataset[apptSortKey] || '').toLowerCase();
      const kb = (b.dataset[apptSortKey] || '').toLowerCase();
      if (ka < kb) return apptSortAsc ? -1 : 1;
      if (ka > kb) return apptSortAsc ? 1 : -1;
      return 0;
    });
    // pagination
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / apptPageSize));
    if (apptPage > pages) apptPage = pages;
    const start = (apptPage - 1) * apptPageSize;
    const end = start + apptPageSize;
    const visible = new Set(rows.slice(start, end));
    apptRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    // render pagination
    renderApptPagination(pages);
  }

  function renderApptPagination(pages) {
    if (!apptPagination) return;
    apptPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = apptPage <= 1;
    prev.onclick = () => { apptPage--; apptApply(); };
    apptPagination.appendChild(prev);
    for (let i=1;i<=pages;i++) {
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i===apptPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { apptPage = i; apptApply(); };
      apptPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = apptPage >= pages;
    next.onclick = () => { apptPage++; apptApply(); };
    apptPagination.appendChild(next);
  }

  // sorting handlers
  if (apptTable) {
    apptTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (apptSortKey === key) {
          apptSortAsc = !apptSortAsc;
        } else {
          apptSortKey = key; apptSortAsc = true;
        }
        apptApply();
      });
    });
  }

  if (apptSearch) apptSearch.addEventListener('input', apptApply);

  // CSV export
  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function apptToCSV() {
    const headers = ['ID','Name','Email','Phone','Type','Preferred Date','Status','Created'];
    const rows = apptRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0,8).map(td => '"' + (td.innerText||'').replace(/"/g,'\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (apptExport) apptExport.addEventListener('click', () => download('appointments.csv', apptToCSV()));

  // initial render
  apptApply();

  function field(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="bg-gray-700/40 border border-gray-600 rounded p-3">
      <div class="text-gray-400 text-xs mb-1">${label}</div>
      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
    </div>`;
  }

  function openApptModal(data) {
    const modal = document.getElementById('appt-modal');
    const details = document.getElementById('appt-details');
    if (!modal || !details) return;
    const fullName = [data.first_name, data.middle_name, data.last_name].filter(Boolean).join(' ');
    details.innerHTML = `
      ${field('Appointment ID', '#' + (data.id || ''))}
      ${field('Status', (data.status || '').toUpperCase())}
      ${field('Created At', data.created_at || '')}
      ${field('Full Name', fullName)}
      ${field('Email', data.email || '')}
      ${field('Phone', data.phone || '')}
      ${field('Birth Date', data.birth_date || '')}
      ${field('Gender', data.gender || '')}
      ${field('Civil Status', data.civil_status || '')}
      ${field('Address', data.address || '')}
      ${field('Appointment Type', data.appointment_type || '')}
      ${field('Preferred Date', data.preferred_date || '')}
      ${field('Health Concerns', data.health_concerns || '')}
      ${field('Medical History', data.medical_history || '')}
      ${field('Current Medications', data.current_medications || '')}
      ${field('Allergies', data.allergies || '')}
      ${field('Emergency Contact Name', data.emergency_contact_name || '')}
      ${field('Emergency Contact Phone', data.emergency_contact_phone || '')}
    `;
    modal.classList.add('show');
    modal.classList.remove('hidden');
  }
  function closeApptModal(){
    const modal = document.getElementById('appt-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }
</script>
