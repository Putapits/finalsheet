<?php
// Admin list of HCS service requests: medical-consultation, emergency-care, preventive-care
$types = ['medical-consultation','emergency-care','preventive-care'];
$typeLabels = [
  'medical-consultation' => 'Medical Consultation',
  'emergency-care' => 'Emergency Care',
  'preventive-care' => 'Preventive Care',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Error fetching HCS service requests: ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8 rounded-2xl p-8 border border-emerald-100 shadow-lg bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">HCS Service Requests</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Medical Consultations, Emergency Care, and Preventive Care submissions.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($requests); ?></span></div>
      <button id="svc-export" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-medium shadow-sm">Export CSV</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <!-- Filters -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Service</label>
      <select id="svc-filter-type" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="svc-filter-status" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
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
        <input id="svc-search" type="text" placeholder="Search name, email, phone, details..." class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="svc-table">
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
      <tbody id="svc-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No service requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 svc-row"
                data-id="<?php echo h($r['id']); ?>"
                data-service="<?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>"
                data-name="<?php echo h($r['full_name']); ?>"
                data-email="<?php echo h($r['email']); ?>"
                data-phone="<?php echo h($r['phone']); ?>"
                data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
                data-urgency="<?php echo h($r['urgency'] ?? ''); ?>"
                data-status="<?php echo h($r['status']); ?>"
                data-created="<?php echo h($r['created_at']); ?>"
                data-type="<?php echo h($r['service_type']); ?>">
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium"><?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['full_name']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['email']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['phone']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <?php $u = strtolower($r['urgency'] ?? 'medium'); ?>
                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php
                  echo $u==='emergency' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : ($u==='high' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200' : ($u==='low' ? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'));
                ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
              </td>
              <td class="py-3 px-3">
                <?php $s = $r['status'] ?? 'pending'; ?>
                <span class="px-2 py-1 rounded-full text-xs font-semibold <?php
                  echo $s==='completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : ($s==='in_progress' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s==='cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'));
                ?>"><?php echo h(strtoupper($s)); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['created_at']); ?></td>
              <td class="py-3 px-3 text-right">
                <button class="px-3 py-1 bg-emerald-600 text-white rounded-lg hover:bg-emerald-500 text-xs mr-2 font-medium shadow-sm"
                  onclick='openSvcModal(<?php echo json_encode([
                    "id"=>$r["id"],
                    "service_type"=>$r["service_type"],
                    "full_name"=>$r["full_name"],
                    "email"=>$r["email"],
                    "phone"=>$r["phone"],
                    "address"=>$r["address"],
                    "service_details"=>$r["service_details"],
                    "preferred_date"=>$r["preferred_date"],
                    "urgency"=>$r["urgency"],
                    "status"=>$r["status"],
                    "created_at"=>$r["created_at"],
                  ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'>View</button>
                <!-- View-only for admin -->
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="svc-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- Details Modal -->
<div id="svc-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeSvcModal()"></div>
  <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Service Request Details</h3>
      <button class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100" onclick="closeSvcModal()">✕</button>
    </div>
    <div id="svc-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>
    #svc-modal.show { display:flex; }
  </style>
</div>

<script>
  const svcSearch = document.getElementById('svc-search');
  const svcBody = document.getElementById('svc-tbody');
  const svcFilterType = document.getElementById('svc-filter-type');
  const svcFilterStatus = document.getElementById('svc-filter-status');
  const svcTable = document.getElementById('svc-table');
  const svcExport = document.getElementById('svc-export');
  const svcPagination = document.getElementById('svc-pagination');
  let svcSortKey = 'id';
  let svcSortAsc = false;
  let svcPage = 1;
  const svcPageSize = 10;

  function svcRows(){ return Array.from(svcBody.querySelectorAll('.svc-row')); }

  function applySvcFilters() {
    const q = (svcSearch?.value || '').toLowerCase();
    const ft = (svcFilterType?.value || '').toLowerCase();
    const fs = (svcFilterStatus?.value || '').toLowerCase();
    if (!svcBody) return;
    let rows = svcRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.getAttribute('data-type')||'').toLowerCase();
      const s = (tr.getAttribute('data-status')||'').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t===ft) && (!fs || s===fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    // sort
    rows.sort((a,b)=>{
      const ka = (a.dataset[svcSortKey]||'').toLowerCase();
      const kb = (b.dataset[svcSortKey]||'').toLowerCase();
      if (ka < kb) return svcSortAsc ? -1 : 1;
      if (ka > kb) return svcSortAsc ? 1 : -1;
      return 0;
    });
    // pagination
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / svcPageSize));
    if (svcPage > pages) svcPage = pages;
    const start = (svcPage - 1) * svcPageSize;
    const end = start + svcPageSize;
    const visible = new Set(rows.slice(start, end));
    svcRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderSvcPagination(pages);
  }

  function renderSvcPagination(pages){
    if (!svcPagination) return;
    svcPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = svcPage <= 1;
    prev.onclick = () => { svcPage--; applySvcFilters(); };
    svcPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i===svcPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { svcPage = i; applySvcFilters(); };
      svcPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = svcPage >= pages;
    next.onclick = () => { svcPage++; applySvcFilters(); };
    svcPagination.appendChild(next);
  }

  [svcSearch, svcFilterType, svcFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applySvcFilters);
    if (el) el.addEventListener('change', applySvcFilters);
  });

  if (svcTable) {
    svcTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (svcSortKey === key) { svcSortAsc = !svcSortAsc; } else { svcSortKey = key; svcSortAsc = true; }
        applySvcFilters();
      });
    });
  }

  function field(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="bg-gray-700/40 border border-gray-600 rounded p-3">
      <div class="text-gray-400 text-xs mb-1">${label}</div>
      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
    </div>`;
  }

  function openSvcModal(data) {
    const modal = document.getElementById('svc-modal');
    const details = document.getElementById('svc-details');
    if (!modal || !details) return;
    const typeLabel = {
      'medical-consultation':'Medical Consultation',
      'emergency-care':'Emergency Care',
      'preventive-care':'Preventive Care'
    }[data.service_type] || data.service_type;
    details.innerHTML = `
      ${field('Request ID', '#' + (data.id || ''))}
      ${field('Service', typeLabel)}
      ${field('Status', (data.status || '').toUpperCase())}
      ${field('Urgency', (data.urgency || '').toUpperCase())}
      ${field('Created At', data.created_at || '')}
      ${field('Full Name', data.full_name || '')}
      ${field('Email', data.email || '')}
      ${field('Phone', data.phone || '')}
      ${field('Preferred Date', data.preferred_date || '')}
      ${field('Address', data.address || '')}
      ${field('Service Details', data.service_details || '')}
    `;
    modal.classList.add('show');
    modal.classList.remove('hidden');
  }
  function closeSvcModal(){
    const modal = document.getElementById('svc-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }

  // Initialize filters on load
  applySvcFilters();

  // CSV export
  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function svcToCSV(){
    const headers = ['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = svcRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0,9).map(td => '"' + (td.innerText||'').replace(/"/g,'\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (svcExport) svcExport.addEventListener('click', () => download('hcs_service_requests.csv', svcToCSV()));

  // View-only: no status updates for admin
</script>
