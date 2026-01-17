<?php
// Doctor HSS Overview (CRUD) for disease-monitoring + environmental-monitoring
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
  error_log('Doctor HSS overview fetch error: ' . $e->getMessage());
  $rows = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
// summaries
$statusKeys = ['pending','in_progress','completed','cancelled'];
$byStatus = array_fill_keys($statusKeys, 0);
$byType = array_fill_keys($types, 0);
foreach ($rows as $r) {
  $s = strtolower($r['status'] ?? 'pending'); if (isset($byStatus[$s])) $byStatus[$s]++;
  $t = $r['service_type'] ?? ''; if (isset($byType[$t])) $byType[$t]++;
}
?>

<div class="mb-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Health Surveillance System</h2>
      <p class="mt-1 text-gray-600 dark:text-gray-400">Manage Disease &amp; Environmental Monitoring submissions.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button id="hss-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
      <button id="hss-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
      <button id="hss-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add New</button>
      <div class="text-sm text-gray-600 dark:text-gray-300">Total: <span class="font-semibold text-gray-900 dark:text-white"><?php echo count($rows); ?></span></div>
    </div>
  </div>
</div>

<section class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="grid grid-cols-2 gap-4 md:grid-cols-6">
    <div class="rounded-xl border border-gray-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
      <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</div>
      <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white"><?php echo (int)count($rows); ?></div>
    </div>
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/40 dark:bg-amber-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-100">Pending</div>
      <div class="mt-2 text-2xl font-semibold text-amber-700 dark:text-amber-100"><?php echo (int)$byStatus['pending']; ?></div>
    </div>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-500/40 dark:bg-emerald-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-100">In Progress</div>
      <div class="mt-2 text-2xl font-semibold text-emerald-700 dark:text-emerald-100"><?php echo (int)$byStatus['in_progress']; ?></div>
    </div>
    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-500/40 dark:bg-sky-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-100">Completed</div>
      <div class="mt-2 text-2xl font-semibold text-sky-700 dark:text-sky-100"><?php echo (int)$byStatus['completed']; ?></div>
    </div>
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-500/40 dark:bg-rose-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-100">Cancelled</div>
      <div class="mt-2 text-2xl font-semibold text-rose-700 dark:text-rose-100"><?php echo (int)$byStatus['cancelled']; ?></div>
    </div>
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/40 dark:bg-indigo-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-indigo-700 dark:text-indigo-100">Disease</div>
      <div class="mt-2 text-2xl font-semibold text-indigo-700 dark:text-indigo-100"><?php echo (int)$byType['disease-monitoring']; ?></div>
    </div>
  </div>
  <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-6">
    <div class="rounded-xl border border-violet-200 bg-violet-50 p-4 dark:border-violet-500/40 dark:bg-violet-500/20">
      <div class="text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-100">Environmental</div>
      <div class="mt-2 text-2xl font-semibold text-violet-700 dark:text-violet-100"><?php echo (int)$byType['environmental-monitoring']; ?></div>
    </div>
  </div>
</section>

<div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
  <button onclick="location.href='doctor.php?page=hss&view=disease'" class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-left transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">
    <div class="flex items-center">
      <svg class="mr-3 h-5 w-5 text-emerald-600 dark:text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      <span class="text-base font-semibold text-gray-900 dark:text-white">Disease Monitoring</span>
    </div>
  </button>
  <button onclick="location.href='doctor.php?page=hss&view=environmental'" class="w-full rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-left transition hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-200 dark:border-sky-500/40 dark:bg-sky-500/20 dark:text-sky-100 dark:hover:bg-sky-500/30">
    <div class="flex items-center">
      <svg class="mr-3 h-5 w-5 text-sky-600 dark:text-sky-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span class="text-base font-semibold text-gray-900 dark:text-white">Environmental Monitoring</span>
    </div>
  </button>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
      <select id="hss-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="hss-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Search</label>
      <div class="relative">
        <input id="hss-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="hss-table">
      <thead>
        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
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
      <tbody id="hss-tbody">
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No HSS submissions found.</td>
          </tr>
        <?php else: foreach ($rows as $r): ?>
          <tr class="hss-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
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
            <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
            <td class="py-3 px-3 text-gray-900 dark:text-white"><?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
            <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
            <td class="py-3 px-3">
              <?php $u = strtolower($r['urgency'] ?? 'medium');
                $urgencyClasses = [
                  'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
                  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                  'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
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
  <div id="hss-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300"></div>
</section>

<!-- View Modal -->
<div id="hss-view-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>
  <div class="relative modal-panel bg-dark-card border border-gray-600 rounded-lg max-w-3xl w-[92%] p-6 max-h-[85vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-white">HSS Submission Details</h3>
      <button class="text-gray-400 hover:text-white" onclick="closeViewModal()">✕</button>
    </div>
    <div id="hss-view-details" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm"></div>
  </div>
  <style>#hss-view-modal.show{display:flex;}</style>
</div>

<!-- CRUD Modal -->
<div id="hss-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative modal-panel bg-dark-card border border-gray-600 rounded-lg max-w-3xl w-[92%] p-6 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 id="hss-crud-title" class="text-xl font-semibold text-white">Add HSS Submission</h3>
      <button class="text-gray-400 hover:text-white" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="hss-crud-form" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div class="md:col-span-2">
        <label class="block text-xs text-gray-400 mb-1">Service Type</label>
        <select name="service_type" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600">
          <option value="disease-monitoring">Disease Monitoring</option>
          <option value="environmental-monitoring">Environmental Monitoring</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Full Name</label>
        <input name="full_name" type="text" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Email</label>
        <input name="email" type="email" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Phone</label>
        <input name="phone" type="text" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-400 mb-1">Address</label>
        <input name="address" type="text" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-400 mb-1">Details</label>
        <textarea name="service_details" rows="4" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600"></textarea>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600" />
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Urgency</label>
        <select name="urgency" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1">Status</label>
        <select name="status" class="w-full bg-gray-700 text-white rounded px-3 py-2 border border-gray-600">
          <option value="pending">Pending</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="px-3 py-2 border border-gray-600 text-gray-300 rounded" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="px-3 py-2 bg-primary text-white rounded">Save</button>
      </div>
    </form>
  </div>
  <style>#hss-crud-modal.show{display:flex;}</style>
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
  const hsSearch = document.getElementById('hss-search');
  const hsBody = document.getElementById('hss-tbody');
  const hsFilterType = document.getElementById('hss-filter-type');
  const hsFilterStatus = document.getElementById('hss-filter-status');
  const hsTable = document.getElementById('hss-table');
  const hsExport = document.getElementById('hss-export');
  const hsPagination = document.getElementById('hss-pagination');
  const hsAdd = document.getElementById('hss-add');
  let hsSortKey='id', hsSortAsc=false, hsPage=1; const hsPageSize=10;

  function hsRows(){ return Array.from(hsBody?.querySelectorAll('.hss-row')||[]); }
  function applyHsFilters(){
    const q=(hsSearch?.value||'').toLowerCase(); const ft=(hsFilterType?.value||'').toLowerCase(); const fs=(hsFilterStatus?.value||'').toLowerCase();
    let rows=hsRows();
    rows.forEach(tr=>{ const text=tr.innerText.toLowerCase(); const t=(tr.dataset.type||'').toLowerCase(); const s=(tr.dataset.status||'').toLowerCase(); tr.dataset._match=(text.includes(q)&&(!ft||t===ft)&&(!fs||s===fs))?'1':'0'; });
    rows=rows.filter(tr=>tr.dataset._match==='1');
    rows.sort((a,b)=>{ const ka=(a.dataset[hsSortKey]||'').toLowerCase(); const kb=(b.dataset[hsSortKey]||'').toLowerCase(); if(ka<kb)return hsSortAsc?-1:1; if(ka>kb)return hsSortAsc?1:-1; return 0; });
    const total=rows.length; const pages=Math.max(1, Math.ceil(total/hsPageSize)); if(hsPage>pages) hsPage=pages; const start=(hsPage-1)*hsPageSize; const visible=new Set(rows.slice(start,start+hsPageSize));
    hsRows().forEach(tr=>tr.style.display='none'); visible.forEach(tr=>tr.style.display='');
    renderHsPagination(pages);
  }
  function renderHsPagination(pages){ if(!hsPagination)return; hsPagination.innerHTML=''; const prev=document.createElement('button'); prev.textContent='Prev'; prev.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; prev.disabled=hsPage<=1; prev.onclick=()=>{hsPage--;applyHsFilters();}; hsPagination.appendChild(prev); for(let i=1;i<=pages;i++){ const b=document.createElement('button'); b.textContent=i; b.className='px-2 py-1 rounded '+(i===hsPage?'bg-primary text-white':'bg-gray-700 hover:bg-gray-600 text-white'); b.onclick=()=>{hsPage=i;applyHsFilters();}; hsPagination.appendChild(b);} const next=document.createElement('button'); next.textContent='Next'; next.className='px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded'; next.disabled=hsPage>=pages; next.onclick=()=>{hsPage++;applyHsFilters();}; hsPagination.appendChild(next); }
  [hsSearch, hsFilterType, hsFilterStatus].forEach(el=>{ if(el) el.addEventListener('input', applyHsFilters); if(el) el.addEventListener('change', applyHsFilters); });
  const hssClearBtn = document.getElementById('hss-clear');
  if (hssClearBtn){ hssClearBtn.addEventListener('click', function(){ if(hsSearch) hsSearch.value=''; if(hsFilterType) hsFilterType.value=''; if(hsFilterStatus) hsFilterStatus.value=''; hsPage=1; applyHsFilters(); }); }
  if (hsTable){ hsTable.querySelectorAll('th.sortable').forEach(th=>{ th.addEventListener('click',()=>{ const key=th.getAttribute('data-key'); if(hsSortKey===key){hsSortAsc=!hsSortAsc;} else {hsSortKey=key; hsSortAsc=true;} applyHsFilters(); }); }); }
  function download(filename, text){ const a=document.createElement('a'); a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(text); a.download=filename; document.body.appendChild(a); a.click(); document.body.removeChild(a); }
  function hsToCSV(){ const headers=['ID','Service','Full Name','Email','Phone','Preferred Date','Urgency','Status','Created']; const rows=hsRows().filter(tr=>tr.style.display!=='none'); const lines=[headers.join(',')]; rows.forEach(tr=>{ const cells=Array.from(tr.children).slice(0,9).map(td=>'"'+(td.innerText||'').replace(/"/g,'\\"')+'"'); lines.push(cells.join(',')); }); return lines.join('\n'); }
  if (hsExport) hsExport.addEventListener('click', ()=>download('hss_submissions.csv', hsToCSV()));

  function viewField(label, value){ const safe=(value??'').toString(); return `<div class=\"modal-field border rounded p-3\">\n  <div class=\"text-gray-400 text-xs mb-1\">${label}</div>\n  <div class=\"text-white break-words whitespace-pre-wrap\">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n</div>`; }
  function openViewModal(row){ const modal=document.getElementById('hss-view-modal'); const details=document.getElementById('hss-view-details'); if(!modal||!details||!row)return; const typeMap={'disease-monitoring':'Disease Monitoring','environmental-monitoring':'Environmental Monitoring'}; const typeLabel=typeMap[row.dataset.type]||row.dataset.type; details.innerHTML=`
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
  function closeViewModal(){ const modal=document.getElementById('hss-view-modal'); if(!modal)return; modal.classList.remove('show'); modal.classList.add('hidden'); }
  window.openViewModal=openViewModal; window.closeViewModal=closeViewModal;

  const hsCrudModal=document.getElementById('hss-crud-modal'); const hsCrudForm=document.getElementById('hss-crud-form'); const hsCrudTitle=document.getElementById('hss-crud-title');
  function fillFormFromRow(row){ const f=hsCrudForm; f.elements['id'].value=row.dataset.id||''; f.elements['service_type'].value=row.dataset.type||'disease-monitoring'; f.elements['full_name'].value=row.dataset.name||''; f.elements['email'].value=row.dataset.email||''; f.elements['phone'].value=row.dataset.phone||''; f.elements['address'].value=row.dataset.address||''; f.elements['service_details'].value=row.dataset.details||''; f.elements['preferred_date'].value=row.dataset.preferred_date||''; f.elements['urgency'].value=(row.dataset.urgency||'medium').toLowerCase(); f.elements['status'].value=(row.dataset.status||'pending').toLowerCase(); }
  function openCrudModal(mode,row){ if(!hsCrudModal)return; hsCrudForm.reset(); hsCrudForm.elements['mode'].value=mode; if(mode==='edit'&&row){ hsCrudTitle.textContent='Edit HSS Submission'; fillFormFromRow(row);} else { hsCrudTitle.textContent='Add HSS Submission'; hsCrudForm.elements['status'].value='pending'; hsCrudForm.elements['urgency'].value='medium'; } hsCrudModal.classList.add('show'); hsCrudModal.classList.remove('hidden'); }
  function closeCrudModal(){ if(!hsCrudModal)return; hsCrudModal.classList.remove('show'); hsCrudModal.classList.add('hidden'); }
  window.openCrudModal=openCrudModal; window.closeCrudModal=closeCrudModal; if(hsAdd) hsAdd.addEventListener('click',()=>openCrudModal('create'));

  async function apiCall(payload){ const res=await fetch('hss/api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}); const text=await res.text(); if(!res.ok) throw new Error(text||('HTTP '+res.status)); return JSON.parse(text); }
  async function deleteRow(row){ if(!row) return; const id=parseInt(row.dataset.id||'0',10); if(!id) return alert('Invalid ID'); if(!confirm('Delete this submission?')) return; try{ const result=await apiCall({action:'delete', id}); if(result.success){ row.remove(); applyHsFilters(); } else alert('Delete failed.'); }catch(e){ console.error(e); alert('Server error.'); } }
  window.deleteRow=deleteRow;

  hsCrudForm?.addEventListener('submit', async (e)=>{ e.preventDefault(); const mode=hsCrudForm.elements['mode'].value; const id=parseInt(hsCrudForm.elements['id'].value||'0',10); const payload={}; if(mode==='edit'){ if(!id) return alert('Invalid ID'); payload.action='update'; payload.id=id; ['status','urgency','preferred_date','address','service_details'].forEach(k=>{ const v=hsCrudForm.elements[k]?.value ?? null; if(v!==null) payload[k]=v; }); } else { payload.action='create'; payload.service_type=hsCrudForm.elements['service_type'].value; payload.user_email=hsCrudForm.elements['user_email'].value; payload.full_name=hsCrudForm.elements['full_name'].value; payload.email=hsCrudForm.elements['email'].value; payload.phone=hsCrudForm.elements['phone'].value; payload.address=hsCrudForm.elements['address'].value; payload.service_details=hsCrudForm.elements['service_details'].value; payload.preferred_date=hsCrudForm.elements['preferred_date'].value; payload.urgency=hsCrudForm.elements['urgency'].value; } try{ const result=await apiCall(payload); if(!result.success) throw new Error('Operation failed'); alert('Saved successfully'); location.reload(); } catch(err){ console.error(err); alert('Error saving data.'); } });

  applyHsFilters();
</script>
