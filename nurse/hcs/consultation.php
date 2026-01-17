<?php
// Nurse: view HCS service requests (read-only)
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
  error_log('Nurse fetch HCS service requests error: ' . $e->getMessage());
  $requests = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<section class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
  <div class="flex flex-col gap-3">
    <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">Health Center Services</span>
    <div>
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Consultation Requests</h2>
      <p class="text-gray-600 dark:text-gray-400">Monitor ongoing consultation, emergency, and preventive care requests.</p>
    </div>
    <div class="inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-5 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
      Total Requests: <span class="ml-2 text-base text-primary dark:text-primary-100"><?php echo number_format(count($requests)); ?></span>
    </div>
  </div>
  <div class="flex flex-wrap items-center gap-2">
    <button id="svc-export" type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Export CSV</button>
    <button id="svc-add" type="button" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add Walk-in</button>
  </div>
</section>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
  <div class="grid grid-cols-1 gap-3 md:grid-cols-4 md:gap-4 mb-4">
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
      <select id="svc-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
      <select id="svc-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
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
        <input id="svc-search" type="text" placeholder="Search name, email, phone, details..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 placeholder-gray-400 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:placeholder-gray-500">
        <svg class="pointer-events-none absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
    </div>
  </div>
  <div class="flex justify-end">
    <button type="button" id="svc-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-100 text-sm text-gray-700 dark:divide-slate-800 dark:text-gray-200" id="svc-table">
      <thead class="bg-gray-50 text-gray-500 dark:bg-slate-900/60 dark:text-gray-400">
        <tr>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 text-left text-xs font-semibold uppercase tracking-wide cursor-pointer sortable" data-key="service">Service</th>
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
      <tbody id="svc-tbody" class="divide-y divide-gray-100 dark:divide-slate-800">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-400 dark:text-gray-500">No service requests found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
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
              $serviceLabel = $typeLabels[$r['service_type']] ?? $r['service_type'];
            ?>
            <tr class="svc-row border-b border-transparent transition-colors hover:bg-gray-50 dark:hover:bg-slate-800/60"
                data-id="<?php echo h($r['id']); ?>"
                data-service="<?php echo h($serviceLabel); ?>"
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
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($serviceLabel); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
              </td>
              <td class="py-3 px-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
              <td class="py-3 px-3 text-right">
                <button type="button" class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick="openSvcModal(this.closest('tr'))">View</button>
                <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="openEditSvcModal(this.closest('tr'))">Edit</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="svc-pagination" class="mt-4 flex items-center justify-end gap-2 text-xs font-semibold text-gray-600 dark:text-gray-300"></div>
</section>

<!-- Create (Walk-in) Modal -->
<div id="svc-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[95%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 id="svc-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add Service Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeCrudModal()">✕</button>
    </div>
    <form id="svc-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</label>
        <select name="service_type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="medical-consultation">Medical Consultation</option>
          <option value="emergency-care">Emergency Care</option>
          <option value="preventive-care">Preventive Care</option>
        </select>
      </div>
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
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#svc-crud-modal.show{display:flex;}</style>
</div>
<!-- Details Modal -->
<div id="svc-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeSvcModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Service Request Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeSvcModal()">✕</button>
    </div>
    <div id="svc-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#svc-modal.show{display:flex;}</style>
</div>

<!-- Edit Modal -->
<div id="svc-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeEditSvcModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Service Request</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeEditSvcModal()">✕</button>
    </div>
    <form id="svc-edit-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="id" />
      <div class="md:col-span-2">
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
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service Details</label>
        <textarea name="service_details" rows="4" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeEditSvcModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#svc-edit-modal.show{display:flex;}</style>
</div>

<script>
  const svcSearch = document.getElementById('svc-search');
  const svcBody = document.getElementById('svc-tbody');
  const svcFilterType = document.getElementById('svc-filter-type');
  const svcFilterStatus = document.getElementById('svc-filter-status');
  const svcTable = document.getElementById('svc-table');
  const svcExport = document.getElementById('svc-export');
  const svcAdd = document.getElementById('svc-add');
  const svcClear = document.getElementById('svc-clear');
  const svcCrudModal = document.getElementById('svc-crud-modal');
  const svcCrudForm = document.getElementById('svc-crud-form');
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
      const t = ((tr.getAttribute('data-type') || tr.getAttribute('data-service') || '')).toLowerCase();
      const s = (tr.getAttribute('data-status')||'').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t===ft) && (!fs || s===fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a,b)=>{
      const ka = (a.dataset[svcSortKey]||'').toLowerCase();
      const kb = (b.dataset[svcSortKey]||'').toLowerCase();
      if (ka < kb) return svcSortAsc ? -1 : 1;
      if (ka > kb) return svcSortAsc ? 1 : -1;
      return 0;
    });
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
    const makeBtn = (label, disabled, onClick, active = false) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = label;
      if (active) {
        btn.className = 'rounded-lg bg-primary px-3 py-1 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        btn.className = 'rounded-lg border border-gray-300 bg-white px-3 py-1 text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
        btn.disabled = disabled;
        if (!disabled) btn.onclick = onClick;
        if (disabled) btn.classList.add('opacity-60','cursor-not-allowed');
      }
      return btn;
    };
    svcPagination.appendChild(makeBtn('Prev', svcPage <= 1, () => { svcPage--; applySvcFilters(); }));
    for (let i=1;i<=pages;i++){
      svcPagination.appendChild(makeBtn(String(i), false, () => { svcPage = i; applySvcFilters(); }, i === svcPage));
    }
    svcPagination.appendChild(makeBtn('Next', svcPage >= pages, () => { svcPage++; applySvcFilters(); }));
  }

  if (svcTable) {
    svcTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (svcSortKey === key) { svcSortAsc = !svcSortAsc; } else { svcSortKey = key; svcSortAsc = true; }
        applySvcFilters();
      });
    });
  }

  [svcSearch, svcFilterType, svcFilterStatus].forEach(el => {
    if (el) el.addEventListener('input', applySvcFilters);
    if (el) el.addEventListener('change', applySvcFilters);
  });

  if (svcClear) {
    svcClear.addEventListener('click', () => {
      if (svcFilterType) svcFilterType.value = '';
      if (svcFilterStatus) svcFilterStatus.value = '';
      if (svcSearch) svcSearch.value = '';
      svcPage = 1;
      applySvcFilters();
    });
  }

  function openCrudModal(){ if(!svcCrudModal) return; svcCrudForm?.reset(); svcCrudModal.classList.add('show'); svcCrudModal.classList.remove('hidden'); }
  function closeCrudModal(){ if(!svcCrudModal) return; svcCrudModal.classList.remove('show'); svcCrudModal.classList.add('hidden'); }
  window.closeCrudModal = closeCrudModal;
  if (svcAdd) svcAdd.addEventListener('click', openCrudModal);

  async function apiCreate(payload){
    const res = await fetch('hcs/api.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)});
    const text = await res.text();
    if (!res.ok) throw new Error(text || ('HTTP '+res.status));
    return JSON.parse(text);
  }
  svcCrudForm && svcCrudForm.addEventListener('submit', async function(e){
    e.preventDefault();
    try{
      const f = svcCrudForm;
      const payload = {
        action: 'create',
        service_type: f.elements['service_type'].value,
        user_email: f.elements['user_email'].value,
        full_name: f.elements['full_name'].value,
        email: f.elements['email'].value,
        phone: f.elements['phone'].value,
        address: f.elements['address'].value,
        service_details: f.elements['service_details'].value,
        preferred_date: f.elements['preferred_date'].value,
        urgency: f.elements['urgency'].value
      };
      const result = await apiCreate(payload);
      if (!result.success) throw new Error('Operation failed');
      alert('Saved successfully');
      location.reload();
    } catch(err){ console.error(err); alert('Error saving data.'); }
  });

  function field(label, value) {
    const safe = (value ?? '').toString();
    const cls = 'rounded-lg border border-gray-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60';
    return `<div class="${cls}">
      <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">${label}</div>
      <div class="text-sm text-gray-900 break-words whitespace-pre-wrap dark:text-gray-100">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
    </div>`;
  }
  function openSvcModal(row) {
    const modal = document.getElementById('svc-modal');
    const details = document.getElementById('svc-details');
    if (!modal || !details || !row) return;
    const rawType = row.dataset.type || row.dataset.service || '';
    const typeLabel = {
      'medical-consultation':'Medical Consultation',
      'emergency-care':'Emergency Care',
      'preventive-care':'Preventive Care'
    }[rawType] || rawType;
    details.innerHTML = `
      ${field('Request ID', '#'+(row.dataset.id || ''))}
      ${field('Service', typeLabel)}
      ${field('Status', (row.dataset.status || '').toUpperCase())}
      ${field('Urgency', (row.dataset.urgency || '').toUpperCase())}
      ${field('Created At', row.dataset.created || '')}
      ${field('Full Name', row.dataset.name || '')}
      ${field('Email', row.dataset.email || '')}
      ${field('Phone', row.dataset.phone || '')}
      ${field('Preferred Date', row.dataset.preferred_date || '')}
      ${field('Address', row.dataset.address || '')}
      ${field('Service Details', row.dataset.details || '')}
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

  // Edit wiring
  const svcEditModal = document.getElementById('svc-edit-modal');
  const svcEditForm = document.getElementById('svc-edit-form');
  function statusBadgeClass(status){
    switch ((status||'').toLowerCase()){
      case 'completed': return 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100';
      case 'in_progress': return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100';
      case 'cancelled': return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100';
      default: return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200';
    }
  }
  function urgencyBadgeClass(urg){
    switch ((urg||'').toLowerCase()){
      case 'emergency': return 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100';
      case 'high': return 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100';
      case 'low': return 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
      default: return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200';
    }
  }
  function openEditSvcModal(row){
    if (!row || !svcEditModal || !svcEditForm) return;
    svcEditForm.elements['id'].value = row.dataset.id || '';
    svcEditForm.elements['full_name'].value = row.dataset.name || '';
    svcEditForm.elements['email'].value = row.dataset.email || '';
    svcEditForm.elements['phone'].value = row.dataset.phone || '';
    svcEditForm.elements['preferred_date'].value = row.dataset.preferred_date || '';
    svcEditForm.elements['urgency'].value = (row.dataset.urgency || 'medium').toLowerCase();
    svcEditForm.elements['status'].value = (row.dataset.status || 'pending').toLowerCase();
    svcEditForm.elements['address'].value = row.dataset.address || '';
    svcEditForm.elements['service_details'].value = row.dataset.details || '';
    svcEditModal.classList.add('show'); svcEditModal.classList.remove('hidden');
    // stash current row
    svcEditModal._row = row;
  }
  function closeEditSvcModal(){ if (!svcEditModal) return; svcEditModal.classList.remove('show'); svcEditModal.classList.add('hidden'); svcEditModal._row = null; }
  window.openEditSvcModal = openEditSvcModal; window.closeEditSvcModal = closeEditSvcModal;

  async function apiUpdateSvc(payload){
    const res = await fetch('hcs/api.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)});
    const text = await res.text(); if (!res.ok) throw new Error(text||('HTTP '+res.status)); return JSON.parse(text);
  }
  svcEditForm && svcEditForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = parseInt(svcEditForm.elements['id'].value||'0',10);
    if (!id) return alert('Invalid ID');
    const payload = {
      action: 'update', id,
      full_name: svcEditForm.elements['full_name'].value,
      email: svcEditForm.elements['email'].value,
      phone: svcEditForm.elements['phone'].value,
      preferred_date: svcEditForm.elements['preferred_date'].value,
      urgency: svcEditForm.elements['urgency'].value,
      status: svcEditForm.elements['status'].value,
      address: svcEditForm.elements['address'].value,
      service_details: svcEditForm.elements['service_details'].value
    };
    try{
      const result = await apiUpdateSvc(payload);
      if (!result.success) throw new Error('Update failed');
      const row = svcEditModal._row || document.querySelector(`.svc-row[data-id="${id}"]`);
      if (row){
        // dataset
        row.dataset.name = payload.full_name;
        row.dataset.email = payload.email;
        row.dataset.phone = payload.phone;
        row.dataset.preferred_date = payload.preferred_date;
        row.dataset.urgency = payload.urgency;
        row.dataset.status = payload.status;
        row.dataset.address = payload.address;
        row.dataset.details = payload.service_details;
        // cells: 0 ID,1 Service,2 Name,3 Email,4 Phone,5 PrefDate,6 Urgency(span),7 Status(span),8 Created,9 Actions
        const cells = row.children;
        if (cells[2]) cells[2].innerText = payload.full_name;
        if (cells[3]) cells[3].innerText = payload.email;
        if (cells[4]) cells[4].innerText = payload.phone;
        if (cells[5]) cells[5].innerText = payload.preferred_date;
        const badgeBase = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ';
        if (cells[6]){ const span = cells[6].querySelector('span'); if (span){ span.textContent = (payload.urgency||'').toUpperCase(); span.className = badgeBase + urgencyBadgeClass(payload.urgency); } }
        if (cells[7]){ const span = cells[7].querySelector('span'); if (span){ span.textContent = (payload.status||'').toUpperCase(); span.className = badgeBase + statusBadgeClass(payload.status); } }
      }
      closeEditSvcModal();
      applySvcFilters();
      alert('Save Changes Successfully.');
    } catch(err){ console.error(err); alert('Failed to save: '+err.message); }
  });

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

  // init
  applySvcFilters();
</script>
