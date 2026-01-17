<?php
// Admin view-only list for SPI service requests: business-permit, health-inspection
$types = ['business-permit','health-inspection'];
$typeLabels = [
  'business-permit' => 'Business Permit',
  'health-inspection' => 'Health Inspection',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Error fetching SPI service requests: ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$countsByType = array_fill_keys($types, 0);
$countsByStatus = ['pending'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0];
foreach ($requests as $r) {
  $t = $r['service_type'] ?? '';
  if (isset($countsByType[$t])) $countsByType[$t]++;
  $s = $r['status'] ?? '';
  if (isset($countsByStatus[$s])) $countsByStatus[$s]++;
}
?>

<div class="mb-8 rounded-2xl p-8 border border-lime-100 shadow-lg bg-gradient-to-r from-lime-50 via-green-50 to-emerald-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">SPI Service Requests</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Business Permits and Health Inspections submissions.</p>
      <p class="mt-2 text-xs text-gray-500 dark:text-slate-400">View-only for admin. Full CRUD is available on the Inspector dashboard.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($requests); ?></span></div>
      <button id="spi-export" class="px-3 py-2 bg-lime-600 hover:bg-lime-500 text-white rounded-lg text-xs font-medium shadow-sm">Export CSV</button>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <?php foreach ($types as $t): ?>
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-lime-100 dark:border-lime-500/40 shadow-md hover:shadow-lg transition-all">
      <div class="text-sm font-medium text-gray-600 dark:text-slate-200"><?php echo h($typeLabels[$t]); ?></div>
      <div class="text-2xl font-bold text-lime-600 dark:text-lime-200"><?php echo (int)($countsByType[$t] ?? 0); ?></div>
    </div>
  <?php endforeach; ?>
  <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-blue-100 dark:border-blue-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Pending</div>
    <div class="text-2xl font-bold text-blue-600 dark:text-blue-200"><?php echo (int)($countsByStatus['pending'] ?? 0); ?></div>
  </div>
  <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-amber-100 dark:border-amber-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">In Progress</div>
    <div class="text-2xl font-bold text-amber-600 dark:text-amber-200"><?php echo (int)($countsByStatus['in_progress'] ?? 0); ?></div>
  </div>
  <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-500/40 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Completed</div>
    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-200"><?php echo (int)($countsByStatus['completed'] ?? 0); ?></div>
  </div>
  <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-600 shadow-md hover:shadow-lg transition-all">
    <div class="text-sm font-medium text-gray-600 dark:text-slate-200">Cancelled</div>
    <div class="text-2xl font-bold text-slate-600 dark:text-slate-200"><?php echo (int)($countsByStatus['cancelled'] ?? 0); ?></div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Quick Actions</h3>
  <div class="space-y-3 mb-6">
    <button onclick="location.href='DashboardOverview_new.php?page=spi&view=sanitation'" class="w-full text-left p-4 rounded-xl border border-lime-100 dark:border-lime-600/50 bg-lime-50 text-lime-700 dark:bg-lime-900/50 dark:text-lime-200 hover:bg-lime-100 hover:border-lime-200 dark:hover:bg-lime-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-lime-900/40 border border-lime-100 dark:border-lime-600 mr-3">
          <svg class="w-5 h-5 text-lime-500 dark:text-lime-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </span>
        <span class="font-semibold">Sanitation</span>
      </div>
    </button>
    <button onclick="location.href='DashboardOverview_new.php?page=spi&view=permits'" class="w-full text-left p-4 rounded-xl border border-green-100 dark:border-green-600/50 bg-green-50 text-green-700 dark:bg-green-900/50 dark:text-green-200 hover:bg-green-100 hover:border-green-200 dark:hover:bg-green-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-green-900/40 border border-green-100 dark:border-green-600 mr-3">
          <svg class="w-5 h-5 text-green-500 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </span>
        <span class="font-semibold">Permit</span>
      </div>
    </button>
  </div>
  <!-- Filters -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Service</label>
      <select id="spi-filter-type" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-lime-500 dark:focus:ring-lime-400">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="spi-filter-status" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-lime-500 dark:focus:ring-lime-400">
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
        <input id="spi-search" type="text" placeholder="Search name, email, phone, details..." class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-lime-500 dark:focus:ring-lime-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="spi-table">
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
      <tbody id="spi-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No SPI service requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 spi-row"
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
                <button class="px-3 py-1 bg-lime-600 text-white rounded-lg hover:bg-lime-500 text-xs mr-2 font-medium shadow-sm"
                  onclick='openSpiModal(<?php echo json_encode([
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
  <div id="spi-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- Details Modal -->
<div id="spi-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeSpiModal()"></div>
  <div class="relative modal-panel bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100">SPI Request Details</h3>
      <button class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100" onclick="closeSpiModal()">✕</button>
    </div>
    <div id="spi-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>
    #spi-modal.show { display:flex; }
  </style>
</div>

<style>
  .modal-field { background-color: rgba(55,65,81,0.4); border-color: #4b5563; }
  /* Light mode overrides for modal readability */
  @media (prefers-color-scheme: light) {
    .modal-panel { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    .modal-panel h3 { color: #111827 !important; }
    .modal-panel .text-white { color: #111827 !important; }
    .modal-panel .text-gray-400 { color: #374151 !important; }
    .modal-panel .text-gray-300 { color: #374151 !important; }
    .modal-panel .border-gray-600 { border-color: #d1d5db !important; }
    .modal-panel input,
    .modal-panel select,
    .modal-panel textarea { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    .modal-field { background-color: #f9fafb !important; border-color: #e5e7eb !important; color: #111827 !important; }
  }
  body.light .modal-panel,
  body[data-theme="light"] .modal-panel { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
  body.light .modal-panel h3,
  body[data-theme="light"] .modal-panel h3 { color: #111827 !important; }
  body.light .modal-panel .text-white,
  body[data-theme="light"] .modal-panel .text-white { color: #111827 !important; }
  body.light .modal-panel .text-gray-400,
  body[data-theme="light"] .modal-panel .text-gray-400 { color: #374151 !important; }
  body.light .modal-panel .text-gray-300,
  body[data-theme="light"] .modal-panel .text-gray-300 { color: #374151 !important; }
  body.light .modal-panel .border-gray-600,
  body[data-theme="light"] .modal-panel .border-gray-600 { border-color: #d1d5db !important; }
  body.light .modal-panel input,
  body[data-theme="light"] .modal-panel input,
  body.light .modal-panel select,
  body[data-theme="light"] .modal-panel select,
  body.light .modal-panel textarea,
  body[data-theme="light"] .modal-panel textarea { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
  body.light .modal-field,
  body[data-theme="light"] .modal-field { background-color: #f9fafb !important; border-color: #e5e7eb !important; color: #111827 !important; }
</style>

<script>
  const spiSearch = document.getElementById('spi-search');
  const spiBody = document.getElementById('spi-tbody');
  const spiFilterType = document.getElementById('spi-filter-type');
  const spiFilterStatus = document.getElementById('spi-filter-status');
  const spiTable = document.getElementById('spi-table');
  const spiExport = document.getElementById('spi-export');
  const spiPagination = document.getElementById('spi-pagination');
  let spiSortKey = 'id';
  let spiSortAsc = false;
  let spiPage = 1;
  const spiPageSize = 10;

  function spiRows(){ return Array.from(spiBody.querySelectorAll('.spi-row')); }

  function applySpiFilters() {
    const q = (spiSearch?.value || '').toLowerCase();
    const ft = (spiFilterType?.value || '').toLowerCase();
    const fs = (spiFilterStatus?.value || '').toLowerCase();
    if (!spiBody) return;
    let rows = spiRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.getAttribute('data-type')||'').toLowerCase();
      const s = (tr.getAttribute('data-status')||'').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t===ft) && (!fs || s===fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    // sort
    rows.sort((a,b)=>{
      const ka = (a.dataset[spiSortKey]||'').toLowerCase();
      const kb = (b.dataset[spiSortKey]||'').toLowerCase();
      if (ka < kb) return spiSortAsc ? -1 : 1;
      if (ka > kb) return spiSortAsc ? 1 : -1;
      return 0;
    });
    // pagination
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

  function renderSpiPagination(pages){
    if (!spiPagination) return;
    spiPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = spiPage <= 1;
    prev.onclick = () => { spiPage--; applySpiFilters(); };
    spiPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i===spiPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { spiPage = i; applySpiFilters(); };
      spiPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = spiPage >= pages;
    next.onclick = () => { spiPage++; applySpiFilters(); };
    spiPagination.appendChild(next);
  }

  [spiSearch, spiFilterType, spiFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applySpiFilters);
    if (el) el.addEventListener('change', applySpiFilters);
  });

  if (spiTable) {
    spiTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (spiSortKey === key) { spiSortAsc = !spiSortAsc; } else { spiSortKey = key; spiSortAsc = true; }
        applySpiFilters();
      });
    });
  }

  function field(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="modal-field border rounded p-3">
      <div class="text-gray-400 text-xs mb-1">${label}</div>
      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
    </div>`;
  }

  function openSpiModal(data) {
    const modal = document.getElementById('spi-modal');
    const details = document.getElementById('spi-details');
    if (!modal || !details) return;
    const typeLabel = {
      'business-permit':'Business Permit',
      'health-inspection':'Health Inspection'
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
  function closeSpiModal(){
    const modal = document.getElementById('spi-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }

  // Initialize filters on load
  applySpiFilters();

  // CSV export
  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function spiToCSV(){
    const headers = ['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = spiRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0,9).map(td => '"' + (td.innerText||'').replace(/"/g,'\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (spiExport) spiExport.addEventListener('click', () => download('spi_service_requests.csv', spiToCSV()));
</script>
