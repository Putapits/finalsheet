<?php
// Admin - Contact Messages (Read-only)
// This file is intended to be included by admin/DashboardOverview_new.php
// It assumes $database/$db are available. Fallback includes for direct access.
if (!isset($database)) {
    require_once '../../include/database.php';
    startSecureSession();
    requireRole('admin');
}

// Fetch all messages initially; client-side filters/pagination will handle UI
try {
    $messages = $database->getContactMessages();
} catch (Throwable $e) {
    error_log('Admin contact messages fetch error: ' . $e->getMessage());
    $messages = [];
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8 rounded-2xl p-8 border border-sky-100 shadow-lg bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <div class="flex items-center justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Contact Messages</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Read-only list of messages submitted via the public contact form.</p>
    </div>
    <div class="flex items-center gap-3">
      <div class="text-sm text-gray-600 dark:text-slate-300">Total: <span class="text-gray-900 dark:text-slate-100 font-semibold"><?php echo count($messages); ?></span></div>
      <button id="cm-export" class="px-3 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-medium shadow-sm">Export CSV</button>
    </div>
  </div>
</div>

<section class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
  <div class="flex flex-col md:flex-row gap-3 md:items-end md:justify-between mb-5">
    <div class="relative w-full md:w-80">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Search</label>
      <input id="cm-search" type="text" placeholder="Search..." class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 placeholder-gray-500 dark:placeholder-slate-400 rounded-lg px-3 py-2 pr-10 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:focus:ring-sky-400">
      <svg class="absolute right-3 top-9 h-5 w-5 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>
    <div class="w-full md:w-56">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Status</label>
      <select id="cm-status" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:focus:ring-sky-400">
        <option value="">All</option>
        <option value="new">New</option>
        <option value="read">Read</option>
        <option value="archived">Archived</option>
      </select>
    </div>
    <div class="w-full md:w-56">
      <label class="block text-xs text-gray-700 dark:text-slate-200 mb-1 font-medium">Sort</label>
      <select id="cm-sort" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:focus:ring-sky-400">
        <option value="created_desc">Newest</option>
        <option value="created_asc">Oldest</option>
        <option value="name_az">Name (A→Z)</option>
        <option value="name_za">Name (Z→A)</option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-transparent mb-1">Clear</label>
      <button id="cm-clear" class="px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-sm text-gray-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">Clear</button>
    </div>
  </div>

  <div id="cm-list" class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
      <thead class="bg-slate-50 dark:bg-slate-900/60">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">#</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Submitted</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Name</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Email</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Phone</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Subject</th>
          <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
          <th class="px-4 py-2 text-right text-xs font-semibold text-gray-600 dark:text-slate-300 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-slate-900/50 divide-y divide-slate-200 dark:divide-slate-700" id="cm-tbody">
        <?php foreach ($messages as $i => $m): ?>
        <tr class="cm-row" data-created="<?php echo h(date('c', strtotime($m['created_at']))); ?>" data-status="<?php echo h($m['status']); ?>" data-name="<?php echo h(strtolower($m['first_name'].' '.$m['last_name'])); ?>">
          <td class="px-4 py-2 text-sm text-muted dark:text-gray-300"><?php echo $m['id']; ?></td>
          <td class="px-4 py-2 text-sm text-muted dark:text-gray-300"><?php echo date('M j, Y g:i A', strtotime($m['created_at'])); ?></td>
          <td class="px-4 py-2 text-sm text-default dark:text-white"><?php echo h($m['first_name'].' '.$m['last_name']); ?></td>
          <td class="px-4 py-2 text-sm text-secondary dark:text-blue-300"><?php echo h($m['email']); ?></td>
          <td class="px-4 py-2 text-sm text-muted dark:text-gray-300"><?php echo h($m['phone']); ?></td>
          <td class="px-4 py-2 text-sm text-default dark:text-gray-200"><?php echo h($m['subject']); ?></td>
          <td class="px-4 py-2 text-sm">
            <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
              switch($m['status']){
                case 'new': echo 'bg-primary/15 text-primary dark:bg-green-900 dark:text-green-200'; break;
                case 'read': echo 'bg-secondary/15 text-secondary dark:bg-blue-900 dark:text-blue-200'; break;
                case 'archived': echo 'bg-muted/20 text-muted dark:bg-gray-700 dark:text-gray-300'; break;
              }
            ?>"><?php echo ucfirst($m['status']); ?></span>
          </td>
          <td class="px-4 py-2 text-sm text-right">
            <button class="px-3 py-1 bg-primary hover:bg-primary-dark text-white rounded-lg text-xs" onclick='cmView(<?php echo json_encode(["id"=>$m["id"],"first_name"=>$m["first_name"],"last_name"=>$m["last_name"],"email"=>$m["email"],"phone"=>$m["phone"],"subject"=>$m["subject"],"message"=>$m["message"],"created_at"=>$m["created_at"]], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'>View</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div id="cm-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<script>
(function(){
  const q = document.getElementById('cm-search');
  const st = document.getElementById('cm-status');
  const so = document.getElementById('cm-sort');
  const cl = document.getElementById('cm-clear');
  const tbody = document.getElementById('cm-tbody');
  const pag = document.getElementById('cm-pagination');
  const rows = Array.from(document.querySelectorAll('#cm-tbody .cm-row'));
  let page = 1; const pageSize = 10;

  function filterSort(){
    const qv = (q?.value || '').toLowerCase();
    const sv = (st?.value || '').toLowerCase();
    const sort = (so?.value || 'created_desc');

    let vis = rows.filter(tr => {
      const text = tr.innerText.toLowerCase();
      const s = (tr.dataset.status || '').toLowerCase();
      const name = (tr.dataset.name || '');
      const match = (!qv || text.includes(qv)) && (!sv || s === sv);
      tr.style.display = match ? '' : 'none';
      return match;
    });

    vis.sort((a,b)=>{
      switch(sort){
        case 'created_asc': return new Date(a.dataset.created)-new Date(b.dataset.created);
        case 'created_desc': return new Date(b.dataset.created)-new Date(a.dataset.created);
        case 'name_az': return (a.dataset.name||'').localeCompare(b.dataset.name||'');
        case 'name_za': return (b.dataset.name||'').localeCompare(a.dataset.name||'');
        default: return 0;
      }
    });

    // Re-append in sorted order
    vis.forEach(tr => tbody.appendChild(tr));

    // Paginate
    const total = vis.length; const pages = Math.max(1, Math.ceil(total/pageSize));
    if (page > pages) page = pages;
    const start = (page-1)*pageSize; const slice = new Set(vis.slice(start, start+pageSize));
    rows.forEach(tr => tr.style.display = 'none');
    slice.forEach(tr => tr.style.display = '');

    // Render pagination
    if (pag){
      pag.innerHTML = '';
      const mkBtn = (label, handler, disabled=false, active=false) => {
        const b = document.createElement('button'); b.textContent=label;
        b.className = 'px-2 py-1 rounded-lg transition-colors ' + (active ? 'bg-primary text-white' : 'bg-background dark:bg-gray-700 hover:bg-primary/10 dark:hover:bg-gray-600 text-default dark:text-white border border-subtle dark:border-transparent');
        b.disabled = !!disabled; if (disabled) b.classList.add('opacity-50','cursor-not-allowed');
        b.addEventListener('click', handler); return b;
      };
      pag.appendChild(mkBtn('Prev', ()=>{ if (page>1){ page--; filterSort(); } }, page<=1));
      for(let i=1;i<=pages;i++) pag.appendChild(mkBtn(String(i), ()=>{ page=i; filterSort(); }, false, i===page));
      pag.appendChild(mkBtn('Next', ()=>{ if (page<pages){ page++; filterSort(); } }, page>=pages));
    }
  }

  [q, st, so].forEach(el=>{ if(el){ const ev = (el.tagName==='INPUT') ? 'input' : 'change'; el.addEventListener(ev, ()=>{ page=1; filterSort(); }); } });
  if (cl){ cl.addEventListener('click', (e)=>{ e.preventDefault(); if(q) q.value=''; if(st) st.value=''; if(so) so.value='created_desc'; page=1; filterSort(); }); }

  filterSort();

  // CSV export
  const exp = document.getElementById('cm-export');
  if (exp){
    exp.addEventListener('click', ()=>{
      const headers = ['ID','Submitted','First Name','Last Name','Email','Phone','Subject','Message','Status'];
      const rowsCsv = [headers.join(',')];
      Array.from(document.querySelectorAll('#cm-tbody .cm-row')).forEach(tr=>{
        const tds = tr.querySelectorAll('td');
        const id = tds[0].innerText.trim();
        const submitted = tds[1].innerText.trim();
        const name = tds[2].innerText.trim().split(' ');
        const first = name.shift()||''; const last = name.join(' ');
        const email = tds[3].innerText.trim();
        const phone = tds[4].innerText.trim();
        const subject = tds[5].innerText.replace(/\n/g,' ').trim();
        const status = tds[6].innerText.trim();
        const message = (<?php echo json_encode(array_column($messages, 'message', 'id')); ?>)[id] || '';
        const esc = v => '"'+String(v).replace(/"/g,'""')+'"';
        rowsCsv.push([id,submitted,first,last,email,phone,subject,message,status].map(esc).join(','));
      });
      const blob = new Blob([rowsCsv.join('\n')], {type:'text/csv;charset=utf-8;'});
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href=url; a.download='contact_messages.csv'; a.click(); URL.revokeObjectURL(url);
    });
  }
})();

function cmView(m){
  const html = `
  <div id="cmModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
    <div class="bg-dark-card border border-gray-700 rounded-lg p-6 w-full max-w-2xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-white">Message from ${escapeHtml(m.first_name)} ${escapeHtml(m.last_name)}</h3>
        <button onclick="document.getElementById('cmModal').remove()" class="text-gray-300 hover:text-white">✕</button>
      </div>
      <div class="space-y-2 text-sm">
        <div><span class="text-gray-400">Submitted:</span> <span class="text-white">${new Date(m.created_at).toLocaleString()}</span></div>
        <div><span class="text-gray-400">Email:</span> <span class="text-blue-300">${escapeHtml(m.email)}</span></div>
        <div><span class="text-gray-400">Phone:</span> <span class="text-white">${escapeHtml(m.phone)}</span></div>
        <div><span class="text-gray-400">Subject:</span> <span class="text-white">${escapeHtml(m.subject)}</span></div>
        <div class="mt-3">
          <div class="text-gray-400 mb-1">Message:</div>
          <div class="bg-gray-800 text-gray-100 p-3 rounded whitespace-pre-wrap">${escapeHtml(m.message)}</div>
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-2">
        <button onclick="copyToClipboard(${JSON.stringify(String(m.message))})" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm">Copy</button>
        <button onclick="document.getElementById('cmModal').remove()" class="px-3 py-2 bg-primary hover:bg-blue-700 text-white rounded text-sm">Close</button>
      </div>
    </div>
  </div>`;
  document.body.insertAdjacentHTML('beforeend', html);
}

function escapeHtml(s){ return String(s).replace(/[&<>"']/g,(c)=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[c])); }
function copyToClipboard(text){ navigator.clipboard.writeText(text).then(()=>{ alert('Copied to clipboard'); }); }
</script>
