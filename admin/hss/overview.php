<?php
// Admin HSS overview: read-only list for disease-monitoring + environmental-monitoring
$types = ['disease-monitoring','environmental-monitoring'];
$typeLabels = [
  'disease-monitoring' => 'Disease Monitoring',
  'environmental-monitoring' => 'Environmental Monitoring',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?) ORDER BY created_at DESC");
  $stmt->execute($types);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Admin HSS overview fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$countsByType = array_fill_keys($types, 0);
$countsByStatus = ['pending'=>0, 'in_progress'=>0, 'completed'=>0, 'cancelled'=>0];
foreach ($rows as $r) {
  $t = $r['service_type'] ?? '';
  if (isset($countsByType[$t])) $countsByType[$t]++;
  $s = $r['status'] ?? '';
  if (isset($countsByStatus[$s])) $countsByStatus[$s]++;
}
?>

<div class="mb-8 rounded-2xl p-8 border border-rose-100 shadow-lg bg-gradient-to-r from-rose-50 via-pink-50 to-red-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Health Surveillance System (Admin)</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Read-only list of Disease and Environmental Monitoring submissions.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($rows); ?></span></div>
      <button id="hss-admin-export" class="px-3 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-medium shadow-sm">Export CSV</button>
    </div>
  </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <?php foreach ($types as $t): ?>
    <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 border border-rose-100 dark:border-rose-500/40 shadow-md hover:shadow-lg transition-all">
      <div class="text-sm font-medium text-gray-600 dark:text-slate-200"><?php echo h($typeLabels[$t]); ?></div>
      <div class="text-2xl font-bold text-rose-600 dark:text-rose-200"><?php echo (int)($countsByType[$t] ?? 0); ?></div>
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
    <button onclick="location.href='DashboardOverview_new.php?page=hss&view=alerts'" class="w-full text-left p-4 rounded-xl border border-rose-100 dark:border-rose-600/50 bg-rose-50 text-rose-700 dark:bg-rose-900/50 dark:text-rose-200 hover:bg-rose-100 hover:border-rose-200 dark:hover:bg-rose-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-rose-900/40 border border-rose-100 dark:border-rose-600 mr-3">
          <svg class="w-5 h-5 text-rose-500 dark:text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </span>
        <span class="font-semibold">Alerts</span>
      </div>
    </button>
    <button onclick="location.href='DashboardOverview_new.php?page=hss&view=reports'" class="w-full text-left p-4 rounded-xl border border-pink-100 dark:border-pink-600/50 bg-pink-50 text-pink-700 dark:bg-pink-900/50 dark:text-pink-200 hover:bg-pink-100 hover:border-pink-200 dark:hover:bg-pink-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-pink-900/40 border border-pink-100 dark:border-pink-600 mr-3">
          <svg class="w-5 h-5 text-pink-500 dark:text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </span>
        <span class="font-semibold">Reports</span>
      </div>
    </button>
    <button onclick="location.href='DashboardOverview_new.php?page=hss&view=disease'" class="w-full text-left p-4 rounded-xl border border-red-100 dark:border-red-600/50 bg-red-50 text-red-700 dark:bg-red-900/50 dark:text-red-200 hover:bg-red-100 hover:border-red-200 dark:hover:bg-red-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-red-900/40 border border-red-100 dark:border-red-600 mr-3">
          <svg class="w-5 h-5 text-red-500 dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        </span>
        <span class="font-semibold">Disease Monitoring</span>
      </div>
    </button>
    <button onclick="location.href='DashboardOverview_new.php?page=hss&view=environmental'" class="w-full text-left p-4 rounded-xl border border-orange-100 dark:border-orange-600/50 bg-orange-50 text-orange-700 dark:bg-orange-900/50 dark:text-orange-200 hover:bg-orange-100 hover:border-orange-200 dark:hover:bg-orange-900/40 shadow-sm transition-all">
      <div class="flex items-center">
        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-white/80 dark:bg-orange-900/40 border border-orange-100 dark:border-orange-600 mr-3">
          <svg class="w-5 h-5 text-orange-500 dark:text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </span>
        <span class="font-semibold">Environmental Monitoring</span>
      </div>
    </button>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Service</label>
      <select id="hss-admin-filter-type" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:focus:ring-rose-400">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="hss-admin-filter-status" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:focus:ring-rose-400">
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
        <input id="hss-admin-search" type="text" placeholder="Search name, email, phone, details..." class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:focus:ring-rose-400">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="hss-admin-table">
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
      <tbody id="hss-admin-tbody">
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No submissions found.</td>
          </tr>
        <?php else: foreach ($rows as $r): ?>
          <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 hss-admin-row"
              data-id="<?php echo h($r['id']); ?>"
              data-service="<?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>"
              data-type="<?php echo h($r['service_type']); ?>"
              data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>"
              data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>"
              data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>"
              data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
            <td class="py-3 px-3 text-gray-600 dark:text-slate-300">#<?php echo h($r['id']); ?></td>
            <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium"><?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['full_name']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['email']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['phone']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
            <td class="py-3 px-3">
              <?php $u = strtolower($r['urgency'] ?? 'medium'); ?>
              <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $u==='emergency' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : ($u==='high' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200' : ($u==='low' ? 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200')); ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
            </td>
            <td class="py-3 px-3">
              <?php $s = $r['status'] ?? 'pending'; ?>
              <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $s==='completed' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200' : ($s==='in_progress' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s==='cancelled' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200')); ?>"><?php echo h(strtoupper($s)); ?></span>
            </td>
            <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($r['created_at']); ?></td>
            <td class="py-3 px-3 text-right">
              <button class="px-3 py-1 bg-rose-600 text-white rounded-lg hover:bg-rose-500 text-xs font-medium shadow-sm" onclick="openHssAdminView(this.closest('tr'))">View</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="hss-admin-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- View Modal -->
<div id="hss-admin-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeHssAdminView()"></div>
  <div class="relative modal-panel bg-dark-card border border-gray-600 rounded-lg max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-white">HSS Submission Details</h3>
      <button class="text-gray-400 hover:text-white" onclick="closeHssAdminView()">✕</button>
    </div>
    <div id="hss-admin-view-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>#hss-admin-view-modal.show{display:flex;}</style>
</div>

<style>
  .modal-field { background-color: rgba(55,65,81,0.4); border-color: #4b5563; }
  @media (prefers-color-scheme: light) {
    .modal-panel { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    .modal-panel h3 { color: #111827 !important; }
    .modal-panel .text-white { color: #111827 !important; }
    .modal-panel .text-gray-400 { color: #374151 !important; }
    .modal-panel .text-gray-300 { color: #374151 !important; }
    .modal-panel .border-gray-600 { border-color: #d1d5db !important; }
    .modal-panel input, .modal-panel select, .modal-panel textarea { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
    .modal-field { background-color: #f9fafb !important; border-color: #e5e7eb !important; color: #111827 !important; }
  }
  body.light .modal-panel, body[data-theme="light"] .modal-panel { background-color: #ffffff !important; color: #111827 !important; border-color: #d1d5db !important; }
  body.light .modal-field, body[data-theme="light"] .modal-field { background-color: #f9fafb !important; border-color: #e5e7eb !important; color: #111827 !important; }
</style>

<script>
  const hssASearch = document.getElementById('hss-admin-search');
  const hssABody = document.getElementById('hss-admin-tbody');
  const hssAFilterType = document.getElementById('hss-admin-filter-type');
  const hssAFilterStatus = document.getElementById('hss-admin-filter-status');
  const hssATable = document.getElementById('hss-admin-table');
  const hssAExport = document.getElementById('hss-admin-export');
  const hssAPagination = document.getElementById('hss-admin-pagination');
  let hssASortKey='id', hssASortAsc=false, hssAPage=1; const hssAPageSize=10;

  function hssARows(){ return Array.from(hssABody?.querySelectorAll('.hss-admin-row')||[]); }
  function applyHssAFilters(){
    const q=(hssASearch?.value||'').toLowerCase(); const ft=(hssAFilterType?.value||'').toLowerCase(); const fs=(hssAFilterStatus?.value||'').toLowerCase();
    let rows=hssARows();
    rows.forEach(tr=>{ const text=tr.innerText.toLowerCase(); const t=(tr.dataset.type||'').toLowerCase(); const s=(tr.dataset.status||'').toLowerCase(); tr.dataset._match=(text.includes(q)&&(!ft||t===ft)&&(!fs||s===fs))?'1':'0'; });
    rows = rows.filter(tr=>tr.dataset._match==='1');
    rows.sort((a,b)=>{ const ka=(a.dataset[hssASortKey]||'').toLowerCase(); const kb=(b.dataset[hssASortKey]||'').toLowerCase(); if(ka<kb)return hssASortAsc?-1:1; if(ka>kb)return hssASortAsc?1:-1; return 0; });
    const total=rows.length; const pages=Math.max(1, Math.ceil(total/hssAPageSize)); if(hssAPage>pages) hssAPage=pages; const start=(hssAPage-1)*hssAPageSize; const visible=new Set(rows.slice(start,start+hssAPageSize));
    hssARows().forEach(tr=>tr.style.display='none'); visible.forEach(tr=>tr.style.display='');
    renderHssAPagination(pages);
  }
  function renderHssAPagination(pages){ if(!hssAPagination)return; hssAPagination.innerHTML=''; const prev=document.createElement('button'); prev.textContent='Prev'; prev.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; prev.disabled=hssAPage<=1; prev.onclick=()=>{hssAPage--;applyHssAFilters();}; hssAPagination.appendChild(prev); for(let i=1;i<=pages;i++){ const b=document.createElement('button'); b.textContent=i; b.className='px-2 py-1 rounded '+(i===hssAPage?'bg-primary text-white':'bg-gray-700 hover:bg-gray-600 text-white'); b.onclick=()=>{hssAPage=i;applyHssAFilters();}; hssAPagination.appendChild(b);} const next=document.createElement('button'); next.textContent='Next'; next.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; next.disabled=hssAPage>=pages; next.onclick=()=>{hssAPage++;applyHssAFilters();}; hssAPagination.appendChild(next); }
  [hssASearch, hssAFilterType, hssAFilterStatus].forEach(el=>{ if(el) el.addEventListener('input', applyHssAFilters); if(el) el.addEventListener('change', applyHssAFilters); });
  if (hssATable){ hssATable.querySelectorAll('th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ const key=th.getAttribute('data-key'); if(hssASortKey===key){hssASortAsc=!hssASortAsc;} else {hssASortKey=key; hssASortAsc=true;} applyHssAFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function hssAToCSV(){ const headers=['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created']; const rows=hssARows().filter(tr=>tr.style.display!=='none'); const lines=[headers.join(',')]; rows.forEach(tr=>{ const cells=Array.from(tr.children).slice(0,9).map(td=>'"'+(td.innerText||'').replace(/"/g,'\\"')+'"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (hssAExport) hssAExport.addEventListener('click', ()=>download('hss_submissions_admin.csv', hssAToCSV()));

  function viewField(label, value){ const safe=(value??'').toString(); return `<div class=\"modal-field border rounded p-3\">\n  <div class=\"text-gray-400 text-xs mb-1\">${label}</div>\n  <div class=\"text-white break-words whitespace-pre-wrap\">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n</div>`; }
  function openHssAdminView(row){ const modal=document.getElementById('hss-admin-view-modal'); const details=document.getElementById('hss-admin-view-details'); if(!modal||!details||!row)return; const typeMap={'disease-monitoring':'Disease Monitoring','environmental-monitoring':'Environmental Monitoring'}; const typeLabel=typeMap[row.dataset.type]||row.dataset.type; details.innerHTML=`
    ${viewField('Request ID', '#'+(row.dataset.id||''))}
    ${viewField('Service', typeLabel)}
    ${viewField('Status', (row.dataset.status||'').toUpperCase())}
    ${viewField('Urgency', (row.dataset.urgency||'').toUpperCase())}
    ${viewField('Created At', row.dataset.created||'')}
    ${viewField('Full Name', row.dataset.name||'')}
    ${viewField('Email', row.dataset.email||'')}
    ${viewField('Phone', row.dataset.phone||'')}
    ${viewField('Preferred Date', row.dataset.preferred_date||'')}
    ${viewField('Address', row.dataset.address||'')}
    ${viewField('Details', row.dataset.details||'')}
  `; modal.classList.add('show'); modal.classList.remove('hidden'); }
  function closeHssAdminView(){ const modal=document.getElementById('hss-admin-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openHssAdminView=openHssAdminView; window.closeHssAdminView=closeHssAdminView;

  applyHssAFilters();
</script>
