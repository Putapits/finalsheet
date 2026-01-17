<?php
// Nurse HSS - Disease Monitoring (CRUD)
$serviceType = 'disease-monitoring';
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? AND deleted_at IS NULL ORDER BY created_at DESC");
  $stmt->execute([$serviceType]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Nurse HSS disease fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<section class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
  <div class="flex flex-col gap-2">
    <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">Health Surveillance</span>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Disease Monitoring</h2>
    <p class="text-gray-600 dark:text-gray-400">Manage disease monitoring submissions.</p>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <button id="nhssdis-export" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Export CSV</button>
    <button id="nhssdis-add" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add Submission</button>
  </div>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
  <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="nhssdis-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 pr-12 text-sm text-gray-700 shadow-sm transition placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 dark:placeholder-gray-500">
        <svg class="pointer-events-none absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="nhssdis-filter-status" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-800 dark:text-gray-200" id="nhssdis-table">
      <thead class="bg-gray-50 text-gray-500 dark:bg-slate-900/60 dark:text-gray-400">
        <tr>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="name">Full Name</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="urgency">Urgency</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right text-xs font-semibold uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody id="nhssdis-tbody" class="divide-y divide-gray-100 dark:divide-slate-800">
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="py-6 px-3 text-center text-gray-400 dark:text-gray-500">No submissions found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php
            $urgency = strtolower($r['urgency'] ?? 'medium');
            $status = strtolower($r['status'] ?? 'pending');
            $urgencyClasses = [
              'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
              'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
              'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
              'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
            ];
            $statusClasses = [
              'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
              'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
              'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
              'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
            ];
            $urgencyClass = $urgencyClasses[$urgency] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
            $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
          ?>
          <tr class="nhssdis-row border-b border-transparent transition-colors hover:bg-gray-50 dark:hover:bg-slate-800/60"
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
            <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
            <td class="py-3 px-3">
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
            </td>
            <td class="py-3 px-3">
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($status)); ?></span>
            </td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
            <td class="py-3 px-3 text-right">
              <div class="flex justify-end gap-2">
                <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="openViewModal(this.closest('tr'))">View</button>
                <button class="inline-flex items-center rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
                <button class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-200 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/15" onclick="deleteRow(this.closest('tr'))">Delete</button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="nhssdis-pagination" class="mt-4 flex items-center justify-end gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="nhssdis-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="modal-panel relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Submission Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="nhssdis-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#nhssdis-view-modal.show{display:flex;align-items:center;justify-content:center;padding:1.5rem;}</style>
</div>

<!-- CRUD Modal -->
<div id="nhssdis-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="modal-panel relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="nhssdis-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add Submission</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="nhssdis-crud-form" class="grid grid-cols-1 gap-6 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Type</label>
        <input type="text" value="Disease Monitoring" disabled class="w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-sm text-gray-600 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300" />
        <input type="hidden" name="service_type" value="<?php echo $serviceType; ?>" />
      </div>

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition placeholder-gray-400 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 dark:placeholder-gray-500" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full Name</label>
        <input name="full_name" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
        <input name="email" type="email" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
        <input name="phone" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Details</label>
        <textarea name="service_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100"></textarea>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
        <select name="urgency" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="md:col-span-2 flex flex-col gap-2 pt-4 md:flex-row md:justify-end">
        <button type="button" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#nhssdis-crud-modal.show{display:flex;align-items:center;justify-content:center;padding:1.5rem;}</style>
</div>

<style>
  .modal-panel { backdrop-filter: blur(12px); }
  .modal-field {
    background: linear-gradient(135deg, rgba(59,130,246,0.12), rgba(16,185,129,0.12));
    border: 1px solid rgba(148,163,184,0.4);
    border-radius: 0.75rem;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
  }
  .modal-field .modal-field-label {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #475569;
  }
  .modal-field .modal-field-value {
    color: #0f172a;
    font-size: 0.92rem;
    line-height: 1.35rem;
    word-break: break-word;
  }
  @media (prefers-color-scheme: dark) {
    .modal-field {
      background: linear-gradient(135deg, rgba(59,130,246,0.18), rgba(110,231,183,0.18));
      border-color: rgba(148,163,184,0.35);
    }
    .modal-field .modal-field-label { color: #cbd5f5; }
    .modal-field .modal-field-value { color: #f8fafc; }
  }
</style>

<script>
  const ndisSearch = document.getElementById('nhssdis-search');
  const ndisBody = document.getElementById('nhssdis-tbody');
  const ndisFilterStatus = document.getElementById('nhssdis-filter-status');
  const ndisTable = document.getElementById('nhssdis-table');
  const ndisExport = document.getElementById('nhssdis-export');
  const ndisPagination = document.getElementById('nhssdis-pagination');
  const ndisAdd = document.getElementById('nhssdis-add');
  let ndisSortKey='id', ndisSortAsc=false, ndisPage=1; const ndisPageSize=10;

  function ndisRows(){ return Array.from(ndisBody?.querySelectorAll('.nhssdis-row')||[]); }
  function applyNdisFilters(){
    const q=(ndisSearch?.value||'').toLowerCase(); const fs=(ndisFilterStatus?.value||'').toLowerCase();
    let rows=ndisRows();
    rows.forEach(tr=>{ const text=tr.innerText.toLowerCase(); const s=(tr.dataset.status||'').toLowerCase(); tr.dataset._match=(text.includes(q)&&(!fs||s===fs))?'1':'0'; });
    rows=rows.filter(tr=>tr.dataset._match==='1');
    rows.sort((a,b)=>{ const ka=(a.dataset[ndisSortKey]||'').toLowerCase(); const kb=(b.dataset[ndisSortKey]||'').toLowerCase(); if(ka<kb)return ndisSortAsc?-1:1; if(ka>kb)return ndisSortAsc?1:-1; return 0; });
    const total=rows.length; const pages=Math.max(1, Math.ceil(total/ndisPageSize)); if(ndisPage>pages) ndisPage=pages; const start=(ndisPage-1)*ndisPageSize; const visible=new Set(rows.slice(start,start+ndisPageSize));
    ndisRows().forEach(tr=>tr.style.display='none'); visible.forEach(tr=>tr.style.display='');
    renderNdisPagination(pages);
  }
  function renderNdisPagination(pages){
    if(!ndisPagination) return;
    const baseBtn='inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
    const activeBtn='inline-flex items-center justify-center rounded-lg border border-primary bg-primary px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary/40';
    ndisPagination.innerHTML='';
    const prev=document.createElement('button');
    prev.textContent='Prev';
    prev.className=baseBtn;
    prev.disabled=ndisPage<=1;
    if(prev.disabled) prev.classList.add('opacity-50','cursor-not-allowed');
    prev.onclick=()=>{ndisPage--;applyNdisFilters();};
    ndisPagination.appendChild(prev);
    for(let i=1;i<=pages;i++){
      const b=document.createElement('button');
      b.textContent=i;
      b.className=(i===ndisPage?activeBtn:baseBtn);
      b.onclick=()=>{ndisPage=i;applyNdisFilters();};
      ndisPagination.appendChild(b);
    }
    const next=document.createElement('button');
    next.textContent='Next';
    next.className=baseBtn;
    next.disabled=ndisPage>=pages;
    if(next.disabled) next.classList.add('opacity-50','cursor-not-allowed');
    next.onclick=()=>{ndisPage++;applyNdisFilters();};
    ndisPagination.appendChild(next);
  }
  [ndisSearch, ndisFilterStatus].forEach(el=>{ if(el) el.addEventListener('input', applyNdisFilters); if(el) el.addEventListener('change', applyNdisFilters); });
  if (ndisTable){ ndisTable.querySelectorAll('th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ const key=th.getAttribute('data-key'); if(ndisSortKey===key){ndisSortAsc=!ndisSortAsc;} else {ndisSortKey=key; ndisSortAsc=true;} applyNdisFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function ndisToCSV(){ const headers=['ID','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created']; const rows=ndisRows().filter(tr=>tr.style.display!=='none'); const lines=[headers.join(',')]; rows.forEach(tr=>{ const cells=Array.from(tr.children).slice(0,8).map(td=>'"'+(td.innerText||'').replace(/"/g,'\\"')+'"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (ndisExport) ndisExport.addEventListener('click', ()=>download('hss_disease.csv', ndisToCSV()));

  function viewField(label, value){
    const safe=(value??'').toString();
    return `<div class="modal-field">
  <div class="modal-field-label">${label}</div>
  <div class="modal-field-value whitespace-pre-wrap">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
</div>`;
  }
  function openViewModal(row){ const modal=document.getElementById('nhssdis-view-modal'); const details=document.getElementById('nhssdis-view-details'); if(!modal||!details||!row)return; details.innerHTML=`
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
  function closeViewModal(){ const modal=document.getElementById('nhssdis-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal=openViewModal; window.closeViewModal=closeViewModal;

  const ndisCrudModal=document.getElementById('nhssdis-crud-modal'); const ndisCrudForm=document.getElementById('nhssdis-crud-form'); const ndisCrudTitle=document.getElementById('nhssdis-crud-title');
  function fillFormFromRow(row){ const f=ndisCrudForm; f.elements['id'].value=row.dataset.id||''; f.elements['full_name'].value=row.dataset.name||''; f.elements['email'].value=row.dataset.email||''; f.elements['phone'].value=row.dataset.phone||''; f.elements['address'].value=row.dataset.address||''; f.elements['service_details'].value=row.dataset.details||''; f.elements['preferred_date'].value=row.dataset.preferred_date||''; f.elements['urgency'].value=(row.dataset.urgency||'medium').toLowerCase(); f.elements['status'].value=(row.dataset.status||'pending').toLowerCase(); }
  function openCrudModal(mode,row){ if(!ndisCrudModal)return; ndisCrudForm.reset(); ndisCrudForm.elements['mode'].value=mode; if(mode==='edit'&&row){ ndisCrudTitle.textContent='Edit Submission'; fillFormFromRow(row);} else { ndisCrudTitle.textContent='Add Submission'; ndisCrudForm.elements['status'].value='pending'; ndisCrudForm.elements['urgency'].value='medium'; } ndisCrudModal.classList.add('show'); ndisCrudModal.classList.remove('hidden'); }
  function closeCrudModal(){ if(!ndisCrudModal)return; ndisCrudModal.classList.remove('show'); ndisCrudModal.classList.add('hidden'); }
  window.openCrudModal=openCrudModal; window.closeCrudModal=closeCrudModal; if(ndisAdd) ndisAdd.addEventListener('click',()=>openCrudModal('create'));

  async function apiCall(payload){ const res=await fetch('hss/api.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},credentials:'same-origin',body:JSON.stringify(payload)}); const text=await res.text(); let json=null; try{ json=JSON.parse(text);}catch{} if(!res.ok){ throw new Error((json&&json.message)||text||('HTTP '+res.status)); } return json||{}; }
  async function deleteRow(row){ if(!row) return; const id=parseInt(row.dataset.id||'0',10); if(!id) return alert('Invalid ID'); if(!confirm('Delete this submission?')) return; try{ const result=await apiCall({action:'delete', id}); if(result.success){ row.remove(); applyNdisFilters(); } else alert('Delete failed.'); }catch(e){ console.error(e); alert('Server error.'); } }
  window.deleteRow=deleteRow;

  ndisCrudForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const mode=ndisCrudForm.elements['mode'].value; const id=parseInt(ndisCrudForm.elements['id'].value||'0',10); const payload={}; if(mode==='edit'){ if(!id) return alert('Invalid ID'); payload.action='update'; payload.id=id; ['status','urgency','preferred_date','address','service_details','full_name','email','phone'].forEach(k=>{ const v=ndisCrudForm.elements[k]?.value ?? null; if(v!==null) payload[k]=v; }); } else { payload.action='create'; payload.service_type='<?php echo $serviceType; ?>'; payload.user_email=ndisCrudForm.elements['user_email'].value; payload.full_name=ndisCrudForm.elements['full_name'].value; payload.email=ndisCrudForm.elements['email'].value; payload.phone=ndisCrudForm.elements['phone'].value; payload.address=ndisCrudForm.elements['address'].value; payload.service_details=ndisCrudForm.elements['service_details'].value; payload.preferred_date=ndisCrudForm.elements['preferred_date'].value; payload.urgency=ndisCrudForm.elements['urgency'].value; } try{ const result=await apiCall(payload); if(!result.success) throw new Error(JSON.stringify(result)); alert('Saved successfully'); location.reload(); } catch(err){ console.error(err); let msg='Error saving data.'; try{ const j=JSON.parse(err.message); if(j&&j.message) msg=j.message; }catch{} alert(msg); } });

  applyNdisFilters();
</script>
