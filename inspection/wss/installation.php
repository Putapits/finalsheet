<?php
// Inspector WSS - Installation & Upgrades (CRUD)
$serviceType = 'installation-upgrade';
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type = ? ORDER BY created_at DESC");
  $stmt->execute([$serviceType]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('WSS installation fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$statusKeys = ['pending','in_progress','completed','cancelled'];
$statusCounts = array_fill_keys($statusKeys, 0);
foreach ($rows as $r) {
  $s = strtolower($r['status'] ?? 'pending');
  if (isset($statusCounts[$s])) $statusCounts[$s]++;
}
?>

<div class="mb-8">
  <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Installation &amp; Upgrades (Inspector)</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Manage every system installation and upgrade from intake to completion.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <div class="rounded-full border border-primary/30 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
        Total Requests: <span class="ml-1"><?php echo number_format(count($rows)); ?></span>
      </div>
      <button id="wssi2-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
      <button id="wssi2-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
      <button id="wssi2-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add New</button>
    </div>
  </div>
</div>

<section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
  <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-200">Pending</p>
    <span class="mt-2 block text-2xl font-bold text-amber-600 dark:text-amber-200"><?php echo number_format($statusCounts['pending']); ?></span>
  </div>
  <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-200">In Progress</p>
    <span class="mt-2 block text-2xl font-bold text-emerald-600 dark:text-emerald-200"><?php echo number_format($statusCounts['in_progress']); ?></span>
  </div>
  <div class="rounded-2xl border border-sky-100 bg-sky-50 p-4 shadow-sm dark:border-sky-500/30 dark:bg-sky-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-200">Completed</p>
    <span class="mt-2 block text-2xl font-bold text-sky-600 dark:text-sky-200"><?php echo number_format($statusCounts['completed']); ?></span>
  </div>
  <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4 shadow-sm dark:border-rose-500/30 dark:bg-rose-500/15">
    <p class="text-xs font-semibold uppercase tracking-wide text-rose-600 dark:text-rose-200">Cancelled</p>
    <span class="mt-2 block text-2xl font-bold text-rose-600 dark:text-rose-200"><?php echo number_format($statusCounts['cancelled']); ?></span>
  </div>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="wssi2-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="wssi2-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="wssi2-table">
      <thead>
        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
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
      <tbody id="wssi2-tbody">
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No installation or upgrade requests found.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <tr class="wssi2-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
              data-id="<?php echo h($r['id']); ?>"
              data-name="<?php echo h($r['full_name']); ?>"
              data-email="<?php echo h($r['email']); ?>"
              data-phone="<?php echo h($r['phone']); ?>"
              data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>"
              data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>"
              data-service="Installation & Upgrade"
              data-address="<?php echo h($r['address']); ?>"
              data-details="<?php echo h($r['service_details']); ?>">
            <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
            <td class="py-3 px-3">
              <?php $u = strtolower($r['urgency'] ?? 'medium');
                $urgencyClasses = [
                  'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
                  'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                ];
                $urgencyClass = $urgencyClasses[$u] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
              ?>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
            </td>
            <td class="py-3 px-3">
              <?php $s = strtolower($r['status'] ?? 'pending');
                $statusClasses = [
                  'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                  'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                ];
                $statusClass = $statusClasses[$s] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
              ?>
              <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
            </td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
            <td class="py-3 px-3 text-right">
              <button class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openViewModal(this.closest('tr'))">View</button>
              <button class="mr-2 inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60" onclick="openCrudModal('edit', this.closest('tr'))">Edit</button>
              <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="deleteRow(this.closest('tr'))">Delete</button>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div id="wssi2-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="wssi2-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Installation Request Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeViewModal()">✕</button>
    </div>
    <div id="wssi2-view-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#wssi2-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="wssi2-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="wssi2-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add Installation Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="wssi2-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Full Name</label>
        <input name="full_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
        <input name="email" type="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
        <input name="phone" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Details</label>
        <textarea name="service_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgency</label>
        <select name="urgency" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#wssi2-crud-modal.show{display:flex;}</style>
</div>

<script>
  const wssi2Search = document.getElementById('wssi2-search');
  const wssi2Body = document.getElementById('wssi2-tbody');
  const wssi2FilterStatus = document.getElementById('wssi2-filter-status');
  const wssi2Table = document.getElementById('wssi2-table');
  const wssi2Export = document.getElementById('wssi2-export');
  const wssi2Pagination = document.getElementById('wssi2-pagination');
  const wssi2Add = document.getElementById('wssi2-add');
  const wssi2Clear = document.getElementById('wssi2-clear');
  let wssi2SortKey = 'id';
  let wssi2SortAsc = false;
  let wssi2Page = 1;
  const wssi2PageSize = 10;

  function wssi2Rows(){ return Array.from(wssi2Body?.querySelectorAll('.wssi2-row') || []); }
  function applyWssi2Filters(){
    const q = (wssi2Search?.value || '').toLowerCase();
    const fs = (wssi2FilterStatus?.value || '').toLowerCase();
    let rows = wssi2Rows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const s = (tr.dataset.status || '').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!fs || s === fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => {
      const va = (a.dataset[wssi2SortKey] || '').toLowerCase();
      const vb = (b.dataset[wssi2SortKey] || '').toLowerCase();
      if (wssi2SortKey === 'id') {
        return (wssi2SortAsc ? 1 : -1) * ((parseInt(va, 10) || 0) - (parseInt(vb, 10) || 0));
      }
      if (va < vb) return wssi2SortAsc ? -1 : 1;
      if (va > vb) return wssi2SortAsc ? 1 : -1;
      return 0;
    });
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / wssi2PageSize));
    if (wssi2Page > pages) wssi2Page = pages;
    const start = (wssi2Page - 1) * wssi2PageSize;
    const end = start + wssi2PageSize;
    const visible = new Set(rows.slice(start, end));
    wssi2Rows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderWssi2Pagination(pages);
  }
  function renderWssi2Pagination(pages){
    if (!wssi2Pagination) return;
    wssi2Pagination.innerHTML = '';
    const baseBtn = () => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'px-3 py-1 rounded-lg border text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary/30 border-gray-300 bg-white text-gray-600 hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
      return btn;
    };
    const prev = baseBtn();
    prev.textContent = 'Prev';
    prev.disabled = wssi2Page <= 1;
    if (prev.disabled) {
      prev.classList.add('opacity-60','cursor-not-allowed');
    } else {
      prev.onclick = () => { wssi2Page--; applyWssi2Filters(); };
    }
    wssi2Pagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = baseBtn();
      b.textContent = i;
      if (i === wssi2Page) {
        b.className = 'px-3 py-1 rounded-lg bg-primary text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        b.onclick = () => { wssi2Page = i; applyWssi2Filters(); };
      }
      wssi2Pagination.appendChild(b);
    }
    const next = baseBtn();
    next.textContent = 'Next';
    next.disabled = wssi2Page >= pages;
    if (next.disabled) {
      next.classList.add('opacity-60','cursor-not-allowed');
    } else {
      next.onclick = () => { wssi2Page++; applyWssi2Filters(); };
    }
    wssi2Pagination.appendChild(next);
  }
  [wssi2Search, wssi2FilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applyWssi2Filters);
    if (el) el.addEventListener('change', applyWssi2Filters);
  });
  if (wssi2Clear) {
    wssi2Clear.addEventListener('click', () => {
      if (wssi2Search) wssi2Search.value = '';
      if (wssi2FilterStatus) wssi2FilterStatus.value = '';
      wssi2Page = 1;
      applyWssi2Filters();
    });
  }
  if (wssi2Table){
    wssi2Table.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (wssi2SortKey === key) { wssi2SortAsc = !wssi2SortAsc; } else { wssi2SortKey = key; wssi2SortAsc = true; }
        applyWssi2Filters();
      });
    });
  }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function wssi2ToCSV(){
    const headers = ['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created'];
    const rows = wssi2Rows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = [
        tr.dataset.id || '',
        tr.dataset.service || 'Installation & Upgrade',
        tr.dataset.name || '',
        tr.dataset.email || '',
        tr.dataset.phone || '',
        tr.dataset.preferred_date || '',
        tr.dataset.urgency || '',
        tr.dataset.status || '',
        tr.dataset.created || '',
      ].map(val => '"' + (val || '').replace(/"/g,'""') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (wssi2Export) wssi2Export.addEventListener('click', ()=>download('wss_installations.csv', wssi2ToCSV()));

  function viewField(label, value){
    const safe = (value ?? '').toString();
    return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60">
  <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>
  <div class="break-words text-sm text-gray-900 whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
</div>`;
  }
  function openViewModal(row){ const modal=document.getElementById('wssi2-view-modal'); const details=document.getElementById('wssi2-view-details'); if(!modal||!details||!row)return; details.innerHTML=`
    ${viewField('Request ID', '#'+(row.dataset.id||''))}
    ${viewField('Service', row.dataset.service||'Installation & Upgrade')}
    ${viewField('Status', (row.dataset.status||'').toUpperCase())}
    ${viewField('Urgency', (row.dataset.urgency||'').toUpperCase())}
    ${viewField('Created At', row.dataset.created||'')}
    ${viewField('Full Name', row.dataset.name||'')}
    ${viewField('Email', row.dataset.email||'')}
    ${viewField('Phone', row.dataset.phone||'')}
    ${viewField('Preferred Date', row.dataset.preferred_date||'')}
    ${viewField('Address', row.dataset.address||'')}
    ${viewField('Service Details', row.dataset.details||'')}
  `; modal.classList.add('show'); modal.classList.remove('hidden'); }
  function closeViewModal(){ const modal=document.getElementById('wssi2-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal=openViewModal; window.closeViewModal=closeViewModal;

  const wssi2CrudModal=document.getElementById('wssi2-crud-modal'); const wssi2CrudForm=document.getElementById('wssi2-crud-form'); const wssi2CrudTitle=document.getElementById('wssi2-crud-title');
  let wssi2CurrentRow=null;
  function statusBadgeClass(status){
    switch((status||'').toLowerCase()){
      case 'completed': return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100';
      case 'in_progress': return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100';
      case 'cancelled': return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100';
      default: return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200';
    }
  }
  function urgencyBadgeClass(urgency){
    switch((urgency||'').toLowerCase()){
      case 'emergency': return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100';
      case 'high': return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100';
      case 'low': return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
      default: return 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200';
    }
  }
  function fillFormFromRow(row){
    const f=wssi2CrudForm;
    f.elements['id'].value=row.dataset.id||'';
    f.elements['full_name'].value=row.dataset.name||'';
    f.elements['email'].value=row.dataset.email||'';
    f.elements['phone'].value=row.dataset.phone||'';
    f.elements['address'].value=row.dataset.address||'';
    f.elements['service_details'].value=row.dataset.details||'';
    f.elements['preferred_date'].value=row.dataset.preferred_date||'';
    f.elements['urgency'].value=(row.dataset.urgency||'medium').toLowerCase();
    f.elements['status'].value=(row.dataset.status||'pending').toLowerCase();
  }
  function openCrudModal(mode,row){
    if(!wssi2CrudModal)return;
    wssi2CrudForm.reset();
    wssi2CrudForm.elements['mode'].value=mode;
    wssi2CurrentRow=null;
    if(mode==='edit'&&row){
      wssi2CrudTitle.textContent='Edit Installation Request';
      fillFormFromRow(row);
      wssi2CurrentRow=row;
    } else {
      wssi2CrudTitle.textContent='Add Installation Request';
      wssi2CrudForm.elements['status'].value='pending';
      wssi2CrudForm.elements['urgency'].value='medium';
    }
    wssi2CrudModal.classList.add('show');
    wssi2CrudModal.classList.remove('hidden');
  }
  function closeCrudModal(){ if(!wssi2CrudModal)return; wssi2CrudModal.classList.remove('show'); wssi2CrudModal.classList.add('hidden'); }
  window.openCrudModal=openCrudModal; window.closeCrudModal=closeCrudModal;
  if(wssi2Add) wssi2Add.addEventListener('click',()=>openCrudModal('create'));

  async function apiCall(payload){ const res=await fetch('wss/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}); const text=await res.text(); if(!res.ok) throw new Error(text||('HTTP '+res.status)); return JSON.parse(text); }
  async function deleteRow(row){ if(!row) return; const id=parseInt(row.dataset.id||'0',10); if(!id) return alert('Invalid ID'); if(!confirm('Delete this request?')) return; try{ const result=await apiCall({action:'delete', id}); if(result.success){ row.remove(); applyWssi2Filters(); } else alert('Delete failed.'); }catch(e){ console.error(e); alert('Server error.'); } }
  window.deleteRow=deleteRow;

  wssi2CrudForm?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const mode=wssi2CrudForm.elements['mode'].value;
    const id=parseInt(wssi2CrudForm.elements['id'].value||'0',10);
    const payload={};
    if(mode==='edit'){
      if(!id) return alert('Invalid ID');
      payload.action='update';
      payload.id=id;
      ['full_name','email','phone','status','urgency','preferred_date','address','service_details'].forEach(k=>{
        const v=wssi2CrudForm.elements[k]?.value ?? null;
        if(v!==null) payload[k]=v;
      });
    } else {
      payload.action='create';
      payload.service_type='<?php echo $serviceType; ?>';
      payload.user_email=wssi2CrudForm.elements['user_email'].value;
      payload.full_name=wssi2CrudForm.elements['full_name'].value;
      payload.email=wssi2CrudForm.elements['email'].value;
      payload.phone=wssi2CrudForm.elements['phone'].value;
      payload.address=wssi2CrudForm.elements['address'].value;
      payload.service_details=wssi2CrudForm.elements['service_details'].value;
      payload.preferred_date=wssi2CrudForm.elements['preferred_date'].value;
      payload.urgency=wssi2CrudForm.elements['urgency'].value;
    }
    try{
      const result=await apiCall(payload);
      if(!result.success) throw new Error('Operation failed');
      if(mode==='edit'){
        if(wssi2CurrentRow){
          const row=wssi2CurrentRow;
          if(payload.full_name!==undefined){ row.dataset.name=payload.full_name; const cell=row.children[1]; if(cell) cell.innerText=payload.full_name; }
          if(payload.email!==undefined){ row.dataset.email=payload.email; const cell=row.children[2]; if(cell) cell.innerText=payload.email; }
          if(payload.phone!==undefined){ row.dataset.phone=payload.phone; const cell=row.children[3]; if(cell) cell.innerText=payload.phone; }
          if(payload.preferred_date!==undefined){ row.dataset.preferred_date=payload.preferred_date; const cell=row.children[4]; if(cell) cell.innerText=payload.preferred_date; }
          if(payload.urgency!==undefined){
            row.dataset.urgency=payload.urgency;
            const cell=row.children[5];
            if(cell){
              const span=cell.querySelector('span');
              if(span){
                span.textContent=(payload.urgency||'').toUpperCase();
                span.className=urgencyBadgeClass(payload.urgency);
              }
            }
          }
          if(payload.status!==undefined){
            row.dataset.status=payload.status;
            const cell=row.children[6];
            if(cell){
              const span=cell.querySelector('span');
              if(span){
                span.textContent=(payload.status||'').toUpperCase();
                span.className=statusBadgeClass(payload.status);
              }
            }
          }
          if(payload.address!==undefined) row.dataset.address=payload.address;
          if(payload.service_details!==undefined) row.dataset.details=payload.service_details;
        }
        wssi2CurrentRow=null;
        closeCrudModal();
        applyWssi2Filters();
        alert('Save Changes Successfully.');
      } else {
        alert('Saved successfully');
        location.reload();
      }
    }catch(err){
      console.error(err);
      alert('Error saving data.');
    }
  });

  // Ensure all fields are updated on edit (including full_name, email, phone)
  if (wssi2CrudForm && !wssi2CrudForm.dataset.overrideAllFields){
    wssi2CrudForm.dataset.overrideAllFields = '1';
    wssi2CrudForm.addEventListener('submit', async (e)=>{
      try {
        e.preventDefault();
        e.stopImmediatePropagation();
        const mode = wssi2CrudForm.elements['mode'].value;
        const id = parseInt(wssi2CrudForm.elements['id'].value||'0',10);
        const payload = {};
        if (mode === 'edit'){
          if (!id) return alert('Invalid ID');
          payload.action = 'update';
          payload.id = id;
          ['status','urgency','preferred_date','address','service_details','full_name','email','phone'].forEach(k=>{
            const v = wssi2CrudForm.elements[k]?.value ?? null;
            if (v !== null) payload[k] = v;
          });
        } else {
          // Fallback to create behavior
          payload.action = 'create';
          payload.service_type = '<?php echo $serviceType; ?>';
          payload.user_email = wssi2CrudForm.elements['user_email'].value;
          payload.full_name = wssi2CrudForm.elements['full_name'].value;
          payload.email = wssi2CrudForm.elements['email'].value;
          payload.phone = wssi2CrudForm.elements['phone'].value;
          payload.address = wssi2CrudForm.elements['address'].value;
          payload.service_details = wssi2CrudForm.elements['service_details'].value;
          payload.preferred_date = wssi2CrudForm.elements['preferred_date'].value;
          payload.urgency = wssi2CrudForm.elements['urgency'].value;
        }
        const result = await apiCall(payload);
        if (!result.success) throw new Error('Operation failed');
        if (mode === 'edit'){
          if (wssi2CurrentRow){
            const row = wssi2CurrentRow;
            if (payload.full_name !== undefined){ row.dataset.name = payload.full_name; const c=row.children[1]; if(c) c.innerText = payload.full_name; }
            if (payload.email !== undefined){ row.dataset.email = payload.email; const c=row.children[2]; if(c) c.innerText = payload.email; }
            if (payload.phone !== undefined){ row.dataset.phone = payload.phone; const c=row.children[3]; if(c) c.innerText = payload.phone; }
            if (payload.preferred_date !== undefined){ row.dataset.preferred_date = payload.preferred_date; const c=row.children[4]; if(c) c.innerText = payload.preferred_date; }
            if (payload.urgency !== undefined){ row.dataset.urgency = payload.urgency; const c=row.children[5]; if(c){ const s=c.querySelector('span'); if(s){ s.textContent=(payload.urgency||'').toUpperCase(); s.className='px-2 py-1 rounded text-xs font-medium '+urgencyBadgeClass(payload.urgency); } } }
            if (payload.status !== undefined){ row.dataset.status = payload.status; const c=row.children[6]; if(c){ const s=c.querySelector('span'); if(s){ s.textContent=(payload.status||'').toUpperCase(); s.className='px-2 py-1 rounded text-xs font-medium '+statusBadgeClass(payload.status); } } }
            if (payload.address !== undefined) row.dataset.address = payload.address;
            if (payload.service_details !== undefined) row.dataset.details = payload.service_details;
          }
          wssi2CurrentRow=null; closeCrudModal(); applyWssi2Filters(); alert('Save Changes Successfully.');
        } else { alert('Saved successfully'); location.reload(); }
      } catch(err){ console.error(err); alert('Error saving data.'); }
    }, true);
  }

  applyWssi2Filters();
</script>
