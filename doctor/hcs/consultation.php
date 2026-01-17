<?php
// Doctor: manage HCS service requests (consultation/emergency/preventive)
$types = ['medical-consultation', 'emergency-care', 'preventive-care'];
$typeLabels = [
  'medical-consultation' => 'Medical Consultation',
  'emergency-care' => 'Emergency Care',
  'preventive-care' => 'Preventive Care',
];
try {
  $stmt = $db->prepare("SELECT * FROM service_requests WHERE service_type IN (?,?,?) AND deleted_at IS NULL ORDER BY created_at DESC");
  $stmt->execute($types);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  error_log('Doctor fetch service requests error: ' . $e->getMessage());
  $requests = [];
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="mb-6">
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white">HCS Service Requests (Doctor)</h2>
      <p class="text-gray-600 dark:text-gray-400 mt-1">Update status or delete requests.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button id="svc-clear"
        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear
        Filters</button>
      <button id="svc-export"
        class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-sky-500/40 dark:bg-sky-500/20 dark:text-sky-100 dark:hover:bg-sky-500/30">Export
        CSV</button>
      <button id="svc-add"
        class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add
        New</button>
      <div class="text-sm text-gray-600 dark:text-gray-300">Total: <span
          class="font-semibold text-gray-900 dark:text-white"><?php echo count($requests); ?></span></div>
    </div>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="grid grid-cols-1 gap-4 md:grid-cols-4 mb-6">
    <div>
      <label
        class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Service</label>
      <select id="svc-filter-type"
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo h($t); ?>"><?php echo h($typeLabels[$t]); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label
        class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Status</label>
      <select id="svc-filter-status"
        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <div class="md:col-span-2">
      <label
        class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Search</label>
      <div class="relative">
        <input id="svc-search" type="text" placeholder="Search name, email, phone, details..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200" id="svc-table">
      <thead>
        <tr
          class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
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
      <tbody id="svc-tbody">
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="10" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No service requests found.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($requests as $r): ?>
            <tr
              class="border-b border-gray-100 hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60 transition-colors svc-row"
              data-id="<?php echo h($r['id']); ?>"
              data-service="<?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?>"
              data-name="<?php echo h($r['full_name']); ?>" data-email="<?php echo h($r['email']); ?>"
              data-phone="<?php echo h($r['phone']); ?>" data-preferred_date="<?php echo h($r['preferred_date'] ?? ''); ?>"
              data-urgency="<?php echo h($r['urgency'] ?? ''); ?>" data-status="<?php echo h($r['status']); ?>"
              data-created="<?php echo h($r['created_at']); ?>" data-type="<?php echo h($r['service_type']); ?>"
              data-address="<?php echo h($r['address']); ?>" data-details="<?php echo h($r['service_details']); ?>">
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($r['id']); ?></td>
              <td class="py-3 px-3 text-gray-900 dark:text-white">
                <?php echo h($typeLabels[$r['service_type']] ?? $r['service_type']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['full_name']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['email']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['phone']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <?php
                $u = strtolower($r['urgency'] ?? 'medium');
                $urgencyClasses = [
                  'emergency' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-100',
                  'low' => 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100',
                  'medium' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                ];
                $urgencyClass = $urgencyClasses[$u] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
                ?>
                <span
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $urgencyClass; ?>"><?php echo h(strtoupper($r['urgency'] ?? 'MEDIUM')); ?></span>
              </td>
              <td class="py-3 px-3">
                <?php
                $s = strtolower($r['status'] ?? 'pending');
                $statusClasses = [
                  'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                  'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                  'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                ];
                $statusClass = $statusClasses[$s] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
                ?>
                <span
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($r['status'] ?? 'PENDING')); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($r['created_at']); ?></td>
              <td class="py-3 px-3 text-right">
                <button
                  class="inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 mr-2"
                  onclick='openSvcModal(<?php echo json_encode([
                    "id" => $r["id"],
                    "service_type" => $r["service_type"],
                    "full_name" => $r["full_name"],
                    "email" => $r["email"],
                    "phone" => $r["phone"],
                    "address" => $r["address"],
                    "service_details" => $r["service_details"],
                    "preferred_date" => $r["preferred_date"],
                    "urgency" => $r["urgency"],
                    "status" => $r["status"],
                    "created_at" => $r["created_at"],
                  ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>View</button>
                <button
                  class="inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 mr-2"
                  onclick="openEditSvcModal(this.closest('tr'))">Edit</button>
                <button
                  class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                  onclick="deleteService(<?php echo (int) $r['id']; ?>)">Delete</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="svc-pagination" class="mt-6 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300">
  </div>
</section>

<!-- Details Modal -->
<div id="svc-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeSvcModal()"></div>
  <div
    class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Service Request Details</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeSvcModal()">✕</button>
    </div>
    <div id="svc-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>
    #svc-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- CRUD Modal (Create Walk-in) -->
<div id="svc-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeCrudModal()"></div>
  <div
    class="relative max-h-[90vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-4">
      <h3 id="svc-crud-title" class="text-xl font-semibold text-gray-900 dark:text-white">Add Service Request</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeCrudModal()">✕</button>
    </div>
    <form id="svc-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="mode" value="create" />
      <input type="hidden" name="id" />

      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Service</label>
        <select name="service_type"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="medical-consultation">Medical Consultation</option>
          <option value="emergency-care">Emergency Care</option>
          <option value="preventive-care">Preventive Care</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">User
          Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Full
          Name</label>
        <input name="full_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Email</label>
        <input name="email" type="email"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Phone</label>
        <input name="phone" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Address</label>
        <input name="address" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Service
          Details</label>
        <textarea name="service_details" rows="4"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Preferred
          Date</label>
        <input name="preferred_date" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Urgency</label>
        <select name="urgency"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
          <option value="emergency">Emergency</option>
        </select>
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
          onclick="closeCrudModal()">Cancel</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>
    #svc-crud-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- Consultation Modal -->
<div id="svc-edit-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeEditSvcModal()"></div>
  <div
    class="relative max-h-[90vh] w-[95%] max-w-4xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-700 pb-4">
      <div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
            </path>
          </svg>
          Conduct Consultation
        </h3>
        <p class="text-sm text-gray-500 mt-1">Record findings, diagnosis, and prescriptions to finalize the visit.</p>
      </div>
      <button
        class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeEditSvcModal()">✕</button>
    </div>

    <form id="svc-edit-form" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <input type="hidden" name="id" />

      <!-- Patient Info Column -->
      <div class="md:col-span-1 space-y-4 border-r border-gray-100 dark:border-gray-700 pr-0 md:pr-6">
        <h4 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Patient Information</h4>

        <div>
          <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Full
            Name</label>
          <input name="full_name" type="text"
            class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-sm font-bold text-gray-900 focus:ring-0 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
            readonly />
        </div>
        <div>
          <label
            class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Service
            Type</label>
          <input name="service" type="text"
            class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:ring-0 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300"
            readonly />
        </div>

        <!-- Collapsible Patient Details -->
        <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
          <label
            class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Contact &
            details</label>
          <div class="space-y-3">
            <input name="email" type="email" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600"
              readonly placeholder="Email" />
            <input name="phone" type="text" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600"
              readonly placeholder="Phone" />
            <input name="address" type="text" class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600"
              readonly placeholder="Address" />
            <input name="preferred_date" type="text"
              class="w-full rounded-lg border-gray-200 bg-gray-50 text-xs text-gray-600" readonly placeholder="Date" />
          </div>
        </div>

        <div class="pt-4">
          <label
            class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Initial
            Request / Symptoms</label>
          <textarea name="original_details" rows="6"
            class="w-full rounded-lg border-gray-200 bg-yellow-50 px-3 py-2 text-sm text-gray-800 focus:ring-0 dark:bg-yellow-900/10 dark:text-yellow-100 resize-none"
            readonly></textarea>
        </div>

        <div class="hidden">
          <!-- Hidden fields to keep structure working if needed -->
          <input name="urgency" type="hidden">
        </div>
      </div>

      <!-- Consultation Record Column -->
      <div class="md:col-span-2 space-y-5">
        <h4 class="text-xs font-black uppercase tracking-widest text-blue-600 mb-4">Visit Record & Findings</h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="col-span-2">
            <label
              class="block text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300 mb-2">Findings /
              Clinical Notes</label>
            <textarea name="findings" rows="4"
              placeholder="Record physical exam findings, observations, and clinical notes here..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white transition-shadow"></textarea>
          </div>

          <div class="col-span-2">
            <label
              class="block text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300 mb-2">Diagnosis</label>
            <input name="diagnosis" type="text" placeholder="Primary diagnosis..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
          </div>

          <div class="col-span-2">
            <label
              class="block text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300 mb-2">Services
              Provided / Procedures</label>
            <textarea name="services_provided" rows="2"
              placeholder="List procedures done (e.g., wound cleaning, injection, BP check)..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
          </div>

          <div class="col-span-2">
            <label
              class="block text-xs font-bold uppercase tracking-wide text-gray-700 dark:text-gray-300 mb-2">Prescriptions
              & Recommendations</label>
            <textarea name="prescriptions" rows="4" placeholder="Rx: Medicines, Dosage, Instructions..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white font-mono"></textarea>
          </div>
        </div>

        <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
          <div class="w-1/3">
            <label
              class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Status
              Update</label>
            <select name="status"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-bold text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
              <option value="in_progress">In Progress</option>
              <option value="completed" selected>Completed (Finalize)</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="flex gap-3">
            <button type="button"
              class="px-6 py-2.5 rounded-xl border border-gray-300 text-sm font-bold text-gray-600 hover:bg-gray-50 transition-colors"
              onclick="closeEditSvcModal()">Cancel</button>
            <button type="submit"
              class="px-6 py-2.5 rounded-xl bg-blue-600 text-sm font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Finalize Record
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <style>
    #svc-edit-modal.show {
      display: flex;
    }
  </style>
</div>

<script>
  const svcSearch = document.getElementById('svc-search');
  const svcBody = document.getElementById('svc-tbody');
  const svcFilterType = document.getElementById('svc-filter-type');
  const svcFilterStatus = document.getElementById('svc-filter-status');
  const svcTable = document.getElementById('svc-table');
  const svcExport = document.getElementById('svc-export');
  const svcPagination = document.getElementById('svc-pagination');
  const svcAdd = document.getElementById('svc-add');
  let svcSortKey = 'id';
  let svcSortAsc = false;
  let svcPage = 1;
  const svcPageSize = 10;

  function svcRows() { return Array.from(svcBody.querySelectorAll('.svc-row')); }

  function applySvcFilters() {
    const q = (svcSearch?.value || '').toLowerCase();
    const ft = (svcFilterType?.value || '').toLowerCase();
    const fs = (svcFilterStatus?.value || '').toLowerCase();
    if (!svcBody) return;
    let rows = svcRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const t = (tr.getAttribute('data-type') || '').toLowerCase();
      const s = (tr.getAttribute('data-status') || '').toLowerCase();
      tr.dataset._match = (text.includes(q) && (!ft || t === ft) && (!fs || s === fs)) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => {
      const ka = (a.dataset[svcSortKey] || '').toLowerCase();
      const kb = (b.dataset[svcSortKey] || '').toLowerCase();
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

  function renderSvcPagination(pages) {
    if (!svcPagination) return;
    svcPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = svcPage <= 1;
    prev.onclick = () => { svcPage--; applySvcFilters(); };
    svcPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i === svcPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { svcPage = i; applySvcFilters(); };
      svcPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = svcPage >= pages;
    next.onclick = () => { svcPage++; applySvcFilters(); };
    svcPagination.appendChild(next);
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
  const svcClearBtn = document.getElementById('svc-clear');
  if (svcClearBtn) {
    svcClearBtn.addEventListener('click', function () {
      if (svcSearch) svcSearch.value = '';
      if (svcFilterType) svcFilterType.value = '';
      if (svcFilterStatus) svcFilterStatus.value = '';
      svcPage = 1;
      applySvcFilters();
    });
  }

  function field(label, value) {
    const safe = (value ?? '').toString();
    return `<div class="bg-gray-700/40 border border-gray-600 rounded p-3">
      <div class="text-gray-400 text-xs mb-1">${label}</div>
      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
    </div>`;
  }
  function openSvcModal(data) {
    const modal = document.getElementById('svc-modal');
    const details = document.getElementById('svc-details');
    if (!modal || !details) return;
    const typeLabel = {
      'medical-consultation': 'Medical Consultation',
      'emergency-care': 'Emergency Care',
      'preventive-care': 'Preventive Care'
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
  function closeSvcModal() {
    const modal = document.getElementById('svc-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }

  // CRUD (Create) modal helpers
  const svcCrudModal = document.getElementById('svc-crud-modal');
  const svcCrudForm = document.getElementById('svc-crud-form');
  function openCrudModal() { if (!svcCrudModal) return; svcCrudForm?.reset(); svcCrudModal.classList.add('show'); svcCrudModal.classList.remove('hidden'); }
  function closeCrudModal() { if (!svcCrudModal) return; svcCrudModal.classList.remove('show'); svcCrudModal.classList.add('hidden'); }
  window.closeCrudModal = closeCrudModal;
  if (svcAdd) svcAdd.addEventListener('click', openCrudModal);

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function svcToCSV() {
    const headers = ['ID', 'Service', 'Full Name', 'Email', 'Phone', 'Preferred Date', 'Urgency', 'Status', 'Created'];
    const rows = svcRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0, 9).map(td => '"' + (td.innerText || '').replace(/"/g, '\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (svcExport) svcExport.addEventListener('click', () => download('hcs_service_requests.csv', svcToCSV()));

  // Edit modal logic
  const svcEditModal = document.getElementById('svc-edit-modal');
  const svcEditForm = document.getElementById('svc-edit-form');
  function statusBadgeClass(status) {
    switch ((status || '').toLowerCase()) {
      case 'completed': return 'bg-blue-600 text-white';
      case 'in_progress': return 'bg-green-600 text-white';
      case 'cancelled': return 'bg-red-600 text-white';
      default: return 'bg-yellow-600 text-white';
    }
  }
  function urgencyBadgeClass(urg) {
    switch ((urg || '').toLowerCase()) {
      case 'emergency': return 'bg-red-600 text-white';
      case 'high': return 'bg-orange-600 text-white';
      case 'low': return 'bg-gray-500 text-white';
      default: return 'bg-yellow-600 text-white';
    }
  }

  function openEditSvcModal(row) {
    if (!row || !svcEditModal || !svcEditForm) return;
    const f = svcEditForm.elements;
    f['id'].value = row.dataset.id || '';
    f['service'].value = row.dataset.service || '';
    f['status'].value = (row.dataset.status || 'in_progress').toLowerCase();
    f['full_name'].value = row.dataset.name || '';
    f['email'].value = row.dataset.email || '';
    f['phone'].value = row.dataset.phone || '';
    f['address'].value = row.dataset.address || '';
    f['preferred_date'].value = row.dataset.preferred_date || '';
    f['urgency'].value = (row.dataset.urgency || '').toUpperCase();
    
    // Populate original details
    f['original_details'].value = row.dataset.details || '';
    
    // Clear consultation fields for new entry
    if(f['findings']) f['findings'].value = '';
    if(f['diagnosis']) f['diagnosis'].value = '';
    if(f['services_provided']) f['services_provided'].value = '';
    if(f['prescriptions']) f['prescriptions'].value = '';

    svcEditModal.classList.add('show');
    svcEditModal.classList.remove('hidden');
  }
  function closeEditSvcModal() { if (!svcEditModal) return; svcEditModal.classList.remove('show'); svcEditModal.classList.add('hidden'); }
  window.openEditSvcModal = openEditSvcModal;
  window.closeEditSvcModal = closeEditSvcModal;

  async function apiHcs(payload) {
    const res = await fetch('hcs/api.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) });
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); }
    catch { throw new Error('Server returned non-JSON (' + res.status + ').'); }
    if (!res.ok || !json.success) throw new Error(json.message || 'Operation failed');
    return json;
  }

  // Handle create (walk-in)
  svcCrudForm && svcCrudForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    try {
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
      const result = await apiHcs(payload);
      if (!result.success) throw new Error('Operation failed');
      alert('Saved successfully');
      location.reload();
    } catch (err) { console.error(err); alert('Error saving data.'); }
  });

  svcEditForm && svcEditForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    const id = parseInt(svcEditForm.elements['id'].value || '0', 10);
    if (!id) { alert('Invalid data'); return; }
    
    // Construct final service_details with structured consultation record
    const f = svcEditForm.elements;
    const original = f['original_details'].value;
    const findings = f['findings'].value;
    const diagnosis = f['diagnosis'].value;
    const services = f['services_provided'].value;
    const prescriptions = f['prescriptions'].value;
    
    let combinedDetails = original;
    if (findings || diagnosis || services || prescriptions) {
        combinedDetails += "\n\n========================================\n";
        combinedDetails += "       MEDICAL CONSULTATION RECORD       \n";
        combinedDetails += "========================================\n";
        combinedDetails += "Date: " + new Date().toLocaleString() + "\n";
        if(diagnosis) combinedDetails += "DIAGNOSIS: " + diagnosis + "\n";
        if(findings) combinedDetails += "FINDINGS: " + findings + "\n";
        if(services) combinedDetails += "SERVICES PROVIDED: " + services + "\n";
        if(prescriptions) combinedDetails += "PRESCRIPTIONS/RX: \n" + prescriptions + "\n";
        combinedDetails += "========================================\n";
    }

    const payload = {
      action: 'update', id,
      status: (f['status'].value || '').toLowerCase(),
      full_name: f['full_name'].value,
      email: f['email'].value,
      phone: f['phone'].value,
      address: f['address'].value,
      preferred_date: f['preferred_date'].value,
      urgency: (f['urgency'].value || '').toLowerCase(),
      service_details: combinedDetails
    };
    try {
      const result = await apiHcs(payload);
      if (!result.success) throw new Error('Update failed');
      const row = svcRows().find(r => Number(r.dataset.id) === Number(id));
      if (row) {
        // dataset sync
        row.dataset.name = payload.full_name;
        row.dataset.email = payload.email;
        row.dataset.phone = payload.phone;
        row.dataset.address = payload.address;
        row.dataset.preferred_date = payload.preferred_date;
        row.dataset.urgency = payload.urgency;
        row.dataset.status = payload.status;
        row.dataset.details = payload.service_details;
        // cells: 0 ID,1 Service,2 Name,3 Email,4 Phone,5 PrefDate,6 Urgency(span),7 Status(span),8 Created,9 Actions
        const cells = row.children;
        if (cells[2]) cells[2].innerText = payload.full_name;
        if (cells[3]) cells[3].innerText = payload.email;
        if (cells[4]) cells[4].innerText = payload.phone;
        if (cells[5]) cells[5].innerText = payload.preferred_date;
        if (cells[6]) { const span = cells[6].querySelector('span'); if (span) { span.textContent = (payload.urgency || '').toUpperCase(); span.className = 'px-2 py-1 rounded text-xs font-medium ' + urgencyBadgeClass(payload.urgency); } }
        if (cells[7]) { const span = cells[7].querySelector('span'); if (span) { span.textContent = (payload.status || '').toUpperCase(); span.className = 'px-2 py-1 rounded text-xs font-medium ' + statusBadgeClass(payload.status); } }
      }
      closeEditSvcModal();
      applySvcFilters();
      alert('Medical Record Finalized Successfully');
    } catch (err) { alert('Failed to save: ' + err.message); }
  });
  async function deleteService(id) {
    if (!confirm('Delete this service request? This cannot be undone.')) return;
    try {
      const res = await fetch('api/delete_service_request.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ id }) });
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Delete failed');
      const row = svcRows().find(r => Number(r.dataset.id) === Number(id));
      if (row) row.remove();
      applySvcFilters();
    } catch (e) { alert('Failed to delete: ' + e.message); }
  }

  // init
  applySvcFilters();
</script>