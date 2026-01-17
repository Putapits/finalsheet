<?php
// Admin Audit Logs page (read-only)
// Assumes $database is available from DashboardOverview_new.php
try { $logs = $database->getAuditLogs(); } catch (Throwable $e) { $logs = []; }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<div class="mb-8 rounded-2xl p-8 border border-indigo-100 shadow-lg bg-gradient-to-r from-indigo-50 via-slate-50 to-blue-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Audit Logs</h2>
  <p class="text-gray-600 dark:text-slate-300 mt-1">Tracks login/logout, profile changes, and password updates for all roles.</p>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-5">
    <div>
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Role</label>
      <select id="al-role" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
        <option value="">All</option>
        <option value="admin">Admin</option>
        <option value="doctor">Doctor</option>
        <option value="nurse">Nurse</option>
        <option value="inspector">Inspector</option>
        <option value="citizen">Citizen</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Action</label>
      <select id="al-action" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
        <option value="">All</option>
        <option>login</option>
        <option>logout</option>
        <option>profile_update</option>
        <option>password_change</option>
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Date From</label>
      <input id="al-from" type="date" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Date To</label>
      <input id="al-to" type="date" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
    </div>
    <div>
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Search</label>
      <input id="al-q" type="text" placeholder="Search details/name/email" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400" />
    </div>
  </div>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
      <button id="al-apply" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium shadow-sm">Apply</button>
    </div>
    <div class="flex items-center gap-3">
      <label class="text-gray-700 dark:text-slate-200 text-sm flex items-center">Rows per page
        <select id="al-pagesize" class="ml-2 bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-2 py-1 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </label>
      <button id="al-export" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium shadow-sm">Export CSV</button>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
      <thead class="bg-slate-100 dark:bg-slate-900/60">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Time</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">User</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Role</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Action</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Details</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">IP</th>
        </tr>
      </thead>
      <tbody id="al-body" class="bg-white dark:bg-slate-900/50 divide-y divide-slate-200 dark:divide-slate-700"></tbody>
    </table>
  </div>
  <div id="al-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<script>
(function(){
  const role = document.getElementById('al-role');
  const action = document.getElementById('al-action');
  const from = document.getElementById('al-from');
  const to = document.getElementById('al-to');
  const q = document.getElementById('al-q');
  const pageWrap = document.getElementById('al-pagination');
  const pageSizeSel = document.getElementById('al-pagesize');
  const tbody = document.getElementById('al-body');
  let page = 1;

  function apiBase(){ const parts = location.pathname.split('/').filter(Boolean); const root='/' + (parts[0]||''); return root + '/api/audit_logs.php'; }
  function pageSize(){ return parseInt(pageSizeSel && pageSizeSel.value || '10') || 10; }
  function params(){
    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('size', String(pageSize()));
    if (role.value) p.set('role', role.value);
    if (action.value) p.set('action', action.value);
    if (from.value) p.set('date_from', from.value);
    if (to.value) p.set('date_to', to.value);
    if (q.value) p.set('q', q.value.trim());
    return p;
  }
  function renderRows(items){
    tbody.innerHTML='';
    if (!items || !items.length){
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = 6; td.className='px-4 py-6 text-center text-gray-400';
      td.textContent = 'No results'; tr.appendChild(td); tbody.appendChild(tr); return;
    }
    const frag = document.createDocumentFragment();
    items.forEach(r=>{
      const tr = document.createElement('tr'); tr.className='al-row';
      tr.dataset.role=(r.role||'').toLowerCase(); tr.dataset.action=(r.action||'').toLowerCase(); tr.dataset.timeMs=String(r.time_ms||0);
      tr.dataset.details=(r.details||'').toLowerCase(); tr.dataset.user=((r.user||'')+' '+(r.email||'')).toLowerCase();
      tr.innerHTML = `
        <td class="px-4 py-2 text-sm text-gray-300">${new Date(r.time).toLocaleString()}</td>
        <td class="px-4 py-2 text-sm text-gray-300">${escapeHtml(r.user||'')} <span class="text-gray-500 text-xs">${escapeHtml(r.email||'')}</span></td>
        <td class="px-4 py-2 text-sm text-gray-300">${escapeHtml(r.role||'')}</td>
        <td class="px-4 py-2 text-sm text-gray-300">${escapeHtml(r.action||'')}</td>
        <td class="px-4 py-2 text-sm text-gray-300 whitespace-pre-wrap">${escapeHtml(r.details||'')}</td>
        <td class="px-4 py-2 text-sm text-gray-300">${escapeHtml(r.ip||'')}</td>
      `;
      frag.appendChild(tr);
    });
    tbody.appendChild(frag);
  }
  function renderPagination(meta){
    const pages = meta.pages || 1; const current = meta.page || 1;
    pageWrap.innerHTML='';
    const mkBtn=(label, handler, disabled=false, active=false)=>{ const b=document.createElement('button'); b.textContent=label; b.className='px-2 py-1 rounded '+(active?'bg-primary text-white':'bg-gray-700 hover:bg-gray-600 text-white'); b.disabled=!!disabled; if(disabled){ b.classList.add('opacity-50','cursor-not-allowed'); } b.addEventListener('click', handler); return b; };
    pageWrap.appendChild(mkBtn('Prev', ()=>{ if (page>1){ page--; load(); } }, page<=1));
    const maxButtons=9; let start=Math.max(1, current-Math.floor(maxButtons/2)); let end=Math.min(pages, start+maxButtons-1); if(end-start+1<maxButtons) start=Math.max(1, end-maxButtons+1);
    for(let i=start;i<=end;i++){ pageWrap.appendChild(mkBtn(String(i), ()=>{ page=i; load(); }, false, i===current)); }
    pageWrap.appendChild(mkBtn('Next', ()=>{ if (page<pages){ page++; load(); } }, page>=pages));
  }
  async function load(){
    const url = apiBase() + '?' + params().toString();
    const res = await fetch(url, { credentials:'same-origin' });
    const json = await res.json();
    if (!res.ok || !json.success) throw new Error(json.message||('HTTP '+res.status));
    renderRows(json.data||[]);
    renderPagination(json);
  }
  function exportCsv(){
    const p = params(); p.set('export','csv');
    const url = apiBase() + '?' + p.toString();
    window.location.href = url;
  }
  function escapeHtml(s){ return String(s).replace(/[&<>"']/g,(c)=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[c])); }

  document.getElementById('al-apply').addEventListener('click', ()=>{ page=1; load(); });
  [role, action, from, to].forEach(el=>{ if (el) el.addEventListener('change', ()=>{ page=1; load(); }); });
  if (q) q.addEventListener('input', ()=>{ page=1; load(); });
  if (pageSizeSel) pageSizeSel.addEventListener('change', ()=>{ page=1; load(); });
  document.getElementById('al-export').addEventListener('click', exportCsv);

  load();
})();
</script>
