<?php
// Admin Restore Data page
// Lists soft-deleted records and allows restoring them (single or bulk)
?>

<div class="mb-8 rounded-2xl p-8 border border-emerald-100 shadow-lg bg-gradient-to-r from-emerald-50 via-teal-50 to-lime-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Restore Data</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">View and restore soft-deleted records. Choose an entity and optionally filter by service type.</p>
    </div>
    <div class="flex items-center gap-2">
      <button id="rst-refresh" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-100 rounded-lg text-sm font-medium shadow-sm">Refresh</button>
      <button id="rst-restore-selected" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium shadow-sm" title="Restore selected rows on this page">Restore Selected</button>
      <button id="rst-restore-all" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium shadow-sm" title="Restore all filtered rows across all pages">Restore All</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-5">
    <div>
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Entity</label>
      <select id="rst-entity" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
        <option value="service_requests">Service Requests</option>
        <option value="appointments">Appointments</option>
      </select>
    </div>
    <div id="rst-module-wrap">
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Module</label>
      <select id="rst-module" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
        <option value="">All</option>
        <option value="hcs">Health Center Services</option>
        <option value="spi">Sanitation Permit & Inspection</option>
        <option value="int">Immunization & Nutrition</option>
        <option value="wss">Wastewater & Septic Services</option>
        <option value="hss">Health Surveillance System</option>
      </select>
    </div>
    <div id="rst-service-type-wrap">
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Service Type</label>
      <select id="rst-service-type" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
        <option value="">All</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-xs font-medium text-gray-700 dark:text-slate-200 mb-1">Search</label>
      <div class="relative">
        <input id="rst-search" type="text" placeholder="Search name, email, phone, details..." class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 rounded-lg px-4 py-2 pr-10 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm" id="rst-table">
      <thead id="rst-thead">
        <!-- Dynamically rendered -->
      </thead>
      <tbody id="rst-tbody">
        <tr><td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">Loading...</td></tr>
      </tbody>
    </table>
  </div>
  <div id="rst-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<style>
  .modal-field { background-color: rgba(55,65,81,0.4); border-color: #4b5563; }
</style>

<script>
(function(){
  const entitySel = document.getElementById('rst-entity');
  const moduleWrap = document.getElementById('rst-module-wrap');
  const moduleSel = document.getElementById('rst-module');
  const typeWrap = document.getElementById('rst-service-type-wrap');
  const typeSel = document.getElementById('rst-service-type');
  const searchEl = document.getElementById('rst-search');
  const refreshBtn = document.getElementById('rst-refresh');
  const restoreSelectedBtn = document.getElementById('rst-restore-selected');
  const restoreAllBtn = document.getElementById('rst-restore-all');
  const table = document.getElementById('rst-table');
  const thead = document.getElementById('rst-thead');
  const tbody = document.getElementById('rst-tbody');
  const pager = document.getElementById('rst-pagination');

  let items = [];
  let sortKey = 'deleted_at';
  let sortAsc = false;
  let page = 1;
  const pageSize = 10;
  let serviceMap = {};

  // Module to service type mapping
  const moduleGroups = {
    hcs: ['medical-consultation','emergency-care','preventive-care'],
    spi: ['business-permit','health-inspection'],
    int: ['vaccination','nutrition-monitoring'],
    wss: ['system-inspection','maintenance-service','installation-upgrade'],
    hss: ['disease-monitoring','environmental-monitoring']
  };

  async function api(payload){
    const res = await fetch('api/restore.php', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, credentials:'same-origin', body: JSON.stringify(payload) });
    const text = await res.text();
    let json=null; try{ json=JSON.parse(text);}catch{}
    if (!res.ok) throw new Error((json&&json.message) || text || ('HTTP '+res.status));
    return json || {};
  }

  async function restoreAllFiltered(){
    const entity = entitySel.value;
    const list = filtered();
    if (!list.length) return alert('No records to restore for the current filters.');
    const ids = list.map(it => parseInt(it.id||0,10)).filter(Boolean);
    try {
      if (!confirm(`Restore ALL ${ids.length} filtered record(s)?`)) return;
      const res = await api({ action:'restore_bulk', entity, ids });
      if (res.success) { alert(`Restored ${res.restored ?? ids.length} record(s).`); await load(); }
      else alert(res.message || 'Restore failed');
    } catch(e){ console.error(e); alert('Server error'); }
  }

  function setColumns(){
    const entity = entitySel.value;
    let headers = [];
    if (entity === 'appointments') {
      headers = [
        {key:'_select', label:'', sortable:false},
        {key:'id', label:'ID', sortable:true},
        {key:'name', label:'Full Name', sortable:true},
        {key:'email', label:'Email', sortable:true},
        {key:'phone', label:'Phone', sortable:true},
        {key:'appointment_type', label:'Appointment Type', sortable:true},
        {key:'preferred_date', label:'Preferred Date', sortable:true},
        {key:'status', label:'Status', sortable:true},
        {key:'deleted_at', label:'Deleted', sortable:true},
        {key:'_actions', label:'Actions', sortable:false},
      ];
      typeWrap.style.display = 'none';
      moduleWrap.style.display = 'none';
    } else {
      headers = [
        {key:'_select', label:'', sortable:false},
        {key:'id', label:'ID', sortable:true},
        {key:'service_type', label:'Service', sortable:true},
        {key:'full_name', label:'Full Name', sortable:true},
        {key:'email', label:'Email', sortable:true},
        {key:'phone', label:'Phone', sortable:true},
        {key:'preferred_date', label:'Preferred Date', sortable:true},
        {key:'urgency', label:'Urgency', sortable:true},
        {key:'status', label:'Status', sortable:true},
        {key:'deleted_at', label:'Deleted', sortable:true},
        {key:'_actions', label:'Actions', sortable:false},
      ];
      typeWrap.style.display = '';
      moduleWrap.style.display = '';
    }
    thead.innerHTML = '<tr class="text-left text-gray-300 border-b border-gray-600">' + headers.map(h => {
      if (h.key === '_select') {
        return `<th class=\"py-3 px-3\"><input type=\"checkbox\" id=\"rst-select-all\" title=\"Select all on this page\"></th>`;
      }
      if (!h.sortable) return `<th class=\"py-3 px-3\">${h.label}</th>`;
      return `<th class=\"py-3 px-3 cursor-pointer sortable\" data-key=\"${h.key}\">${h.label}</th>`;
    }).join('') + '</tr>';
    thead.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (sortKey === key) sortAsc = !sortAsc; else { sortKey = key; sortAsc = true; }
        render();
      });
    });
    const selAll = document.getElementById('rst-select-all');
    if (selAll) selAll.addEventListener('change', (e)=>{ toggleSelectAll(!!e.target.checked); });
  }

  function safe(v){ return (v==null?'':String(v)).replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function fullName(it){ return [it.first_name||'', it.middle_name||'', it.last_name||''].filter(Boolean).join(' ').trim(); }

  function filtered(){
    const q = (searchEl.value||'').toLowerCase();
    const entity = entitySel.value;
    const stype = (typeSel.value||'').toLowerCase();
    return items.filter(it => {
      if (entity === 'service_requests' && stype && (String(it.service_type||'').toLowerCase() !== stype)) return false;
      const text = Object.values(it).join(' ').toLowerCase();
      return text.includes(q);
    });
  }

  function render(){
    const rows = filtered().slice();
    rows.sort((a,b)=>{
      const ka = (a[sortKey] ?? '').toString().toLowerCase();
      const kb = (b[sortKey] ?? '').toString().toLowerCase();
      if (ka < kb) return sortAsc ? -1 : 1;
      if (ka > kb) return sortAsc ? 1 : -1;
      return 0;
    });

    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / pageSize));
    if (page > pages) page = pages;
    const start = (page - 1) * pageSize;
    const vis = rows.slice(start, start + pageSize);

    const entity = entitySel.value;
    if (vis.length === 0) {
      tbody.innerHTML = '<tr><td colspan="10" class="py-6 px-3 text-center text-gray-400">No deleted records found.</td></tr>';
    } else {
      tbody.innerHTML = vis.map(it => rowHtml(entity, it)).join('');
      // Wire row checkbox changes
      tbody.querySelectorAll('.rst-select').forEach(cb => cb.addEventListener('change', updateSelectAll));
    }

    renderPager(pages);
    // Sync select-all state
    updateSelectAll();
  }

  function rowHtml(entity, it){
    const id = it.id;
    const cb = `<input type=\"checkbox\" class=\"rst-select\" data-id=\"${id}\">`;
    const act = `<button class=\"px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs\" onclick=\"window.rstRestore(${id})\">Restore</button>`;
    if (entity === 'appointments') {
      const name = safe(fullName(it));
      return `<tr class=\"border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700/40\" data-id=\"${id}\">`
        + `<td class=\"py-3 px-3\">${cb}</td>`
        + `<td class=\"py-3 px-3 text-gray-700 dark:text-gray-300\">#${safe(id)}</td>`
        + `<td class=\"py-3 px-3 text-gray-700 dark:text-gray-300\">${name}</td>`
        + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.email)}</td>`
        + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.phone)}</td>`
        + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.appointment_type)}</td>`
        + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.preferred_date||'')}</td>`
        + `<td class=\"py-3 px-3\"><span class=\"px-2 py-1 rounded text-xs font-medium bg-yellow-500 text-white dark:bg-yellow-600\">${safe(String(it.status||'').toUpperCase())}</span></td>`
        + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.deleted_at||'')}</td>`
        + `<td class=\"py-3 px-3 text-right\">${act}</td>`
        + `</tr>`;
    }
    // service_requests
    const service = safe(it.service_type);
    const urgency = (it.urgency||'').toLowerCase();
    const uCls = urgency==='emergency'?'bg-red-600 text-white':(urgency==='high'?'bg-orange-600 text-white':(urgency==='low'?'bg-gray-500 text-white':'bg-yellow-600 text-white'));
    return `<tr class=\"border-b border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700/40\" data-id=\"${id}\">`
      + `<td class=\"py-3 px-3\">${cb}</td>`
      + `<td class=\"py-3 px-3 text-gray-700 dark:text-gray-300\">#${safe(id)}</td>`
      + `<td class=\"py-3 px-3 text-gray-800 dark:text-white\">${service}</td>`
      + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.full_name)}</td>`
      + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.email)}</td>`
      + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.phone)}</td>`
      + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.preferred_date||'')}</td>`
      + `<td class=\"py-3 px-3\"><span class=\"px-2 py-1 rounded text-xs font-medium ${uCls}\">${safe(String(it.urgency||'').toUpperCase())}</span></td>`
      + `<td class=\"py-3 px-3\"><span class=\"px-2 py-1 rounded text-xs font-medium bg-blue-600 text-white dark:bg-blue-700\">${safe(String(it.status||'').toUpperCase())}</span></td>`
      + `<td class=\"py-3 px-3 text-gray-600 dark:text-gray-300\">${safe(it.deleted_at||'')}</td>`
      + `<td class=\"py-3 px-3 text-right\">${act}</td>`
      + `</tr>`;
  }

  function renderPager(pages){
    pager.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white';
    prev.disabled = page <= 1;
    prev.onclick = () => { page--; render(); };
    pager.appendChild(prev);
    for (let i=1;i<=pages;i++){
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i===page ? 'bg-primary text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white');
      b.onclick = () => { page = i; render(); };
      pager.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-white';
    next.disabled = page >= pages;
    next.onclick = () => { page++; render(); };
    pager.appendChild(next);
  }

  function updateTypeOptions(){
    // Populate service types filtered by selected module
    const allTypes = serviceMap || {};
    const mod = (moduleSel.value||'').toLowerCase();
    let allowed = null;
    if (mod && moduleGroups[mod]) {
      allowed = new Set(moduleGroups[mod]);
    }
    const options = Object.keys(allTypes)
      .filter(k => !allowed || allowed.has(k))
      .map(k => `<option value="${k}">${allTypes[k]}</option>`)
      .join('');
    typeSel.innerHTML = '<option value="">All</option>' + options;
  }

  async function loadTypes(){
    try {
      const res = await api({ action:'fetch_service_types' });
      if (res.success && res.types) { serviceMap = res.types; }
      updateTypeOptions();
    } catch(e){ console.error(e); }
  }

  function getVisibleCheckboxes(){
    return Array.from(tbody.querySelectorAll('.rst-select'));
  }
  function updateSelectAll(){
    const selAll = document.getElementById('rst-select-all');
    if (!selAll) return;
    const cbs = getVisibleCheckboxes();
    if (cbs.length === 0) { selAll.indeterminate = false; selAll.checked = false; return; }
    const checked = cbs.filter(cb => cb.checked).length;
    selAll.checked = checked === cbs.length;
    selAll.indeterminate = checked > 0 && checked < cbs.length;
  }
  function toggleSelectAll(checked){
    getVisibleCheckboxes().forEach(cb => { cb.checked = checked; });
    updateSelectAll();
  }

  async function load(){
    const entity = entitySel.value;
    const payload = { action:'list', entity };
    if (entity === 'service_requests') {
      const st = typeSel.value || '';
      if (st) payload.service_type = st;
    }
    const q = searchEl.value.trim(); if (q) payload.q = q;
    try {
      const res = await api(payload);
      if (res.success) {
        items = res.items || [];
        page = 1;
        render();
      } else {
        tbody.innerHTML = '<tr><td colspan="10" class="py-6 px-3 text-center text-gray-400">Failed to load.</td></tr>';
      }
    } catch(e){ console.error(e); tbody.innerHTML = '<tr><td colspan="10" class="py-6 px-3 text-center text-gray-400">Server error.</td></tr>'; }
  }

  async function restoreOne(id){
    const entity = entitySel.value;
    try {
      if (!confirm('Restore this record?')) return;
      const res = await api({ action:'restore', entity, id });
      if (res.success) { alert('Record restored successfully.'); await load(); }
      else alert(res.message || 'Restore failed');
    } catch(e){ console.error(e); alert('Server error'); }
  }
  window.rstRestore = restoreOne;

  async function restoreSelected(){
    const ids = Array.from(document.querySelectorAll('.rst-select:checked')).map(cb => parseInt(cb.getAttribute('data-id')||'0',10)).filter(Boolean);
    if (!ids.length) return alert('No rows selected.');
    const entity = entitySel.value;
    try {
      if (!confirm(`Restore ${ids.length} selected record(s)?`)) return;
      const res = await api({ action:'restore_bulk', entity, ids });
      if (res.success) { alert(`Restored ${res.restored ?? ids.length} record(s).`); await load(); }
      else alert(res.message || 'Restore failed');
    } catch(e){ console.error(e); alert('Server error'); }
  }

  // events
  [entitySel, typeSel, searchEl].forEach(el => {
    if (!el) return;
    const evt = el === searchEl ? 'input' : 'change';
    el.addEventListener(evt, ()=>{ if (el!==searchEl) load(); else render(); });
  });
  moduleSel?.addEventListener('change', ()=>{ updateTypeOptions(); load(); });
  refreshBtn?.addEventListener('click', (e)=>{ e?.preventDefault?.(); loadTypes().then(load); });
  restoreSelectedBtn?.addEventListener('click', restoreSelected);
  restoreAllBtn?.addEventListener('click', restoreAllFiltered);

  // init
  setColumns();
  loadTypes().then(load);
  entitySel.addEventListener('change', ()=>{ setColumns(); if (entitySel.value==='service_requests'){ updateTypeOptions(); } load(); });
})();
</script>
