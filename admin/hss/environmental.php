<?php
if (!isset($db)) { require_once '../../include/database.php'; startSecureSession(); requireRole('admin'); }
// Admin HSS - Environmental Monitoring (Read-only)
$serviceType = 'environmental-monitoring';
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? AND deleted_at IS NULL ORDER BY created_at DESC");
  $stmt->execute([$serviceType]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Admin HSS environmental fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8 rounded-2xl p-8 border border-orange-100 shadow-lg bg-gradient-to-r from-orange-50 via-amber-50 to-yellow-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Environmental Monitoring (Admin)</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">View-only list of environmental monitoring submissions.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($rows); ?></span></div>
      <button id="hss-env-admin-export" class="px-3 py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-lg text-xs font-medium shadow-sm">Export CSV</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
    <div class="md:col-span-2">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Search</label>
      <div class="relative">
        <input id="hss-env-admin-search" type="text" placeholder="Search name, email, phone, details..." class="bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 border border-slate-200 dark:border-slate-700 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-400 w-full">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
    <div>
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="hss-env-admin-filter-status" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:focus:ring-orange-400">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="hss-env-admin-table">
      <thead>
        <tr class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
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
      <tbody id="hss-env-admin-tbody">
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No submissions found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 hss-env-admin-row"
              data-id="<?php echo h($r['id']); ?>"
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
            <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-medium"><?php echo h($r['full_name']); ?></td>
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
              <button class="px-3 py-1 bg-orange-600 text-white rounded-lg hover:bg-orange-500 text-xs font-medium shadow-sm" onclick="openHssEnvAdminView(this.closest('tr'))">View</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="hss-env-admin-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- View Modal -->
<div id="hss-env-admin-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeHssEnvAdminView()"></div>
  <div class="relative modal-panel bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-slate-100">Submission Details</h3>
      <button class="text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-slate-100" onclick="closeHssEnvAdminView()">✕</button>
    </div>
    <div id="hss-env-admin-view-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>#hss-env-admin-view-modal.show{display:flex;}</style>
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
  const hEnvSearch = document.getElementById('hss-env-admin-search');
  const hEnvBody = document.getElementById('hss-env-admin-tbody');
  const hEnvFilterStatus = document.getElementById('hss-env-admin-filter-status');
  const hEnvTable = document.getElementById('hss-env-admin-table');
  const hEnvExport = document.getElementById('hss-env-admin-export');
  const hEnvPagination = document.getElementById('hss-env-admin-pagination');
  let hEnvSortKey='id', hEnvSortAsc=false, hEnvPage=1; const hEnvPageSize=10;

  function hEnvRows(){ return Array.from(hEnvBody?.querySelectorAll('.hss-env-admin-row')||[]); }
  function applyHEnvFilters(){
    const q=(hEnvSearch?.value||'').toLowerCase(); const fs=(hEnvFilterStatus?.value||'').toLowerCase();
    let rows=hEnvRows();
    rows.forEach(tr=>{ const text=tr.innerText.toLowerCase(); const s=(tr.dataset.status||'').toLowerCase(); tr.dataset._match=(text.includes(q)&&(!fs||s===fs))?'1':'0'; });
    rows=rows.filter(tr=>tr.dataset._match==='1');
    rows.sort((a,b)=>{ const ka=(a.dataset[hEnvSortKey]||'').toLowerCase(); const kb=(b.dataset[hEnvSortKey]||'').toLowerCase(); if(ka<kb)return hEnvSortAsc?-1:1; if(ka>kb)return hEnvSortAsc?1:-1; return 0; });
    const total=rows.length; const pages=Math.max(1, Math.ceil(total/hEnvPageSize)); if(hEnvPage>pages) hEnvPage=pages; const start=(hEnvPage-1)*hEnvPageSize; const visible=new Set(rows.slice(start,start+hEnvPageSize));
    hEnvRows().forEach(tr=>tr.style.display='none'); visible.forEach(tr=>tr.style.display='');
    renderHEnvPagination(pages);
  }
  function renderHEnvPagination(pages){ if(!hEnvPagination)return; hEnvPagination.innerHTML=''; const prev=document.createElement('button'); prev.textContent='Prev'; prev.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; prev.disabled=hEnvPage<=1; prev.onclick=()=>{hEnvPage--;applyHEnvFilters();}; hEnvPagination.appendChild(prev); for(let i=1;i<=pages;i++){ const b=document.createElement('button'); b.textContent=i; b.className='px-2 py-1 rounded '+(i===hEnvPage?'bg-primary text-white':'bg-gray-700 hover:bg-gray-600 text-white'); b.onclick=()=>{hEnvPage=i;applyHEnvFilters();}; hEnvPagination.appendChild(b);} const next=document.createElement('button'); next.textContent='Next'; next.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; next.disabled=hEnvPage>=pages; next.onclick=()=>{hEnvPage++;applyHEnvFilters();}; hEnvPagination.appendChild(next); }
  [hEnvSearch, hEnvFilterStatus].forEach(el=>{ if(el) el.addEventListener('input', applyHEnvFilters); if(el) el.addEventListener('change', applyHEnvFilters); });
  if (hEnvTable){ hEnvTable.querySelectorAll('th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ const key=th.getAttribute('data-key'); if(hEnvSortKey===key){hEnvSortAsc=!hEnvSortAsc;} else {hEnvSortKey=key; hEnvSortAsc=true;} applyHEnvFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function hEnvToCSV(){ const headers=['ID','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created']; const rows=hEnvRows().filter(tr=>tr.style.display!=='none'); const lines=[headers.join(',')]; rows.forEach(tr=>{ const cells=Array.from(tr.children).slice(0,8).map(td=>'"'+(td.innerText||'').replace(/"/g,'\\"')+'"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (hEnvExport) hEnvExport.addEventListener('click', ()=>download('hss_environmental_admin.csv', hEnvToCSV()));

  function viewField(label, value){ const safe=(value??'').toString(); return `<div class=\"modal-field border rounded p-3\">\n  <div class=\"text-gray-400 text-xs mb-1\">${label}</div>\n  <div class=\"text-white break-words whitespace-pre-wrap\">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n</div>`; }
  function openHssEnvAdminView(row){ const modal=document.getElementById('hss-env-admin-view-modal'); const details=document.getElementById('hss-env-admin-view-details'); if(!modal||!details||!row)return; details.innerHTML=`
    ${viewField('Request ID', '#'+(row.dataset.id||''))}
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
  function closeHssEnvAdminView(){ const modal=document.getElementById('hss-env-admin-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openHssEnvAdminView=openHssEnvAdminView; window.closeHssEnvAdminView=closeHssEnvAdminView;

  applyHEnvFilters();
</script>
