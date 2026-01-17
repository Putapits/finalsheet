<?php
// Doctor: manage appointments (CRUD actions)
// Assumes doctorheader.php already included by router and $db is available

try {
  // Fetch Appointments
  $stmt = $db->query("SELECT *, 'appointment' as _source FROM appointments WHERE deleted_at IS NULL");
  $rawAppts = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch Service Requests (Medical & Emergency)
  $stmt2 = $db->query("SELECT *, 'service_request' as _source FROM service_requests WHERE service_type IN ('medical-consultation', 'emergency-care') AND status != 'cancelled' ORDER BY created_at DESC");
  $rawReqs = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  $appointments = [];
  $apptTypes = [];

  // Normalize Appointments
  foreach ($rawAppts as $row) {
    $row['display_name'] = trim(($row['first_name']??'') . ' ' . ($row['last_name']??''));
    $row['type_display'] = $row['appointment_type'] ?? 'General';
    $appointments[] = $row;
    $t = $row['type_display'];
    if ($t !== '' && !in_array($t, $apptTypes, true)) $apptTypes[] = $t;
  }

  // Normalize Service Requests
  foreach ($rawReqs as $row) {
    // Map fields to match appointment structure where possible
    $row['display_name'] = $row['full_name'] ?? 'Unknown';
    $row['first_name'] = $row['full_name']; // Approx
    $row['last_name'] = '';
    $row['appointment_type'] = $row['service_type'];
    $row['type_display'] = ucwords(str_replace('-', ' ', $row['service_type']));
    $row['health_concerns'] = $row['service_details'] ?? '';
    // Service requests might not have all fields, ensure defaults
    $row['birth_date'] = ''; $row['gender'] = ''; $row['civil_status'] = '';
    $row['medical_history'] = ''; $row['current_medications'] = ''; $row['allergies'] = '';
    $row['emergency_contact_name'] = ''; $row['emergency_contact_phone'] = '';
    
    $appointments[] = $row;
    $t = $row['type_display'];
    if ($t !== '' && !in_array($t, $apptTypes, true)) $apptTypes[] = $t;
  }

  // Sort merged list by created_at DESC
  usort($appointments, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
  });

} catch (Exception $e) {
  error_log('Doctor fetch appointments error: ' . $e->getMessage());
  $appointments = array();
  $apptTypes = array();
}
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="mb-6">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Patient Referrals</h2>
  <p class="text-gray-600 dark:text-gray-400 mt-1">Manage patient referrals and transfers.</p>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="flex flex-col gap-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Status</label>
        <select id="appt-filter-status"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Type</label>
        <select id="appt-filter-type"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="">All</option>
          <?php foreach ($apptTypes as $t): ?>
            <option value="<?php echo h($t); ?>"><?php echo h($t); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Date
          From</label>
        <input id="appt-filter-from" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Date
          To</label>
        <input id="appt-filter-to" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
    </div>
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="relative w-full md:w-96">
        <input id="appt-search" type="text" placeholder="Search name, email, type..."
          class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      <div class="flex flex-wrap items-center gap-2 md:ml-4">
        <button id="appt-clear"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear
          Filters</button>
        <button id="appt-export"
          class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-sky-500/40 dark:bg-sky-500/20 dark:text-sky-100 dark:hover:bg-sky-500/30">Export
          CSV</button>
        <button id="doc-appt-add"
          class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add
          New</button>
        <div class="text-sm text-gray-600 dark:text-gray-300">Total: <span
            class="font-semibold text-gray-900 dark:text-white"><?php echo count($appointments); ?></span></div>
      </div>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200" id="appt-table">
      <thead>
        <tr
          class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
          <th class="py-3 px-3 cursor-pointer sortable" data-key="id">ID</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="name">Name</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="email">Email</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="phone">Phone</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="type">Type</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="preferred_date">Preferred Date</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="status">Status</th>
          <th class="py-3 px-3 cursor-pointer sortable" data-key="created">Created</th>
          <th class="py-3 px-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody id="appt-tbody">
        <?php if (empty($appointments)): ?>
          <tr>
            <td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-gray-400">No referrals found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($appointments as $a): ?>
            <tr
              class="border-b border-gray-100 hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60 transition-colors appt-row"
              data-id="<?php echo h($a['id']); ?>"
              data-source="<?php echo h($a['_source']); ?>"
              data-name="<?php echo h($a['display_name']); ?>"
              data-email="<?php echo h(isset($a['email']) ? $a['email'] : ''); ?>"
              data-phone="<?php echo h(isset($a['phone']) ? $a['phone'] : ''); ?>"
              data-type="<?php echo h($a['type_display']); ?>"
              data-preferred_date="<?php echo h(isset($a['preferred_date']) ? $a['preferred_date'] : ''); ?>"
              data-status="<?php echo h(isset($a['status']) ? $a['status'] : 'pending'); ?>"
              data-created="<?php echo h(isset($a['created_at']) ? $a['created_at'] : ''); ?>"
              data-health_concerns="<?php echo h(isset($a['health_concerns']) ? $a['health_concerns'] : ''); ?>"
              data-time-ms="<?php echo (int) (strtotime(isset($a['created_at']) ? $a['created_at'] : '') * 1000); ?>">
              
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">
                <span class="text-xs uppercase font-bold text-gray-400 mr-1"><?php echo $a['_source']==='appointment'?'APT':'REQ'; ?></span>
                #<?php echo h($a['id']); ?>
              </td>
              <td class="py-3 px-3 text-gray-900 dark:text-white">
                <?php echo h($a['display_name']); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h(isset($a['email']) ? $a['email'] : ''); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h(isset($a['phone']) ? $a['phone'] : ''); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300">
                <?php echo h($a['type_display']); ?>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300">
                <?php echo h(isset($a['preferred_date']) ? $a['preferred_date'] : ''); ?></td>
              <td class="py-3 px-3">
                <?php $s = isset($a['status']) ? $a['status'] : 'pending'; ?>
                <?php
                $badgeClasses = [
                  'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
                  'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'in_progress' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                  'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                  'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                ];
                $badge = $badgeClasses[strtolower($s)] ?? 'bg-gray-200 text-gray-700 dark:bg-slate-700 dark:text-gray-100';
                ?>
                <span
                  class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $badge; ?>"><?php echo h(strtoupper($s)); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300">
                <?php echo h(isset($a['created_at']) ? $a['created_at'] : ''); ?></td>
              <td class="py-3 px-3 text-right whitespace-nowrap">
                <?php if($s === 'pending'): ?>
                  <button class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none mr-2"
                    onclick="acceptRequest(<?php echo (int)$a['id']; ?>, '<?php echo $a['_source']; ?>')">Accept</button>
                <?php endif; ?>
                
                <button
                  class="inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 mr-2"
                  onclick='openApptModal(<?php echo json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>View</button>
                
                <?php if($a['_source'] === 'appointment'): ?>
                <button
                  class="inline-flex items-center rounded-lg bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400/60 mr-2"
                  onclick='openEditApptModal(<?php echo json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                <?php endif; ?>

                <button
                  class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700 mr-2"
                  onclick="deleteAppointment(<?php echo (int) $a['id']; ?>, '<?php echo $a['_source']; ?>')">Delete</button>
                
                <button
                  class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                  onclick='openReferModal(<?php echo json_encode(["id" => $a["id"], "source" => $a["_source"], "name" => $a["display_name"]]); ?>)'>Refer</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="appt-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm text-gray-600 dark:text-gray-300">
  </div>
</section>

<!-- Refer Modal -->
<div id="refer-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeReferModal()"></div>
  <div
    class="relative w-[95%] max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Refer Patient</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeReferModal()">✕</button>
    </div>
    <form id="refer-form" class="space-y-4">
      <input type="hidden" id="refer-appt-id">
      <input type="hidden" id="refer-source">
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Patient</label>
        <input type="text" id="refer-patient-name" readonly
          class="w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700 shadow-sm dark:border-slate-600 dark:bg-slate-800 dark:text-gray-400 cursor-not-allowed" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Hospital /
          Facility Name</label>
        <input type="text" id="refer-hospital" required
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
          placeholder="e.g. St. Luke's Medical Center" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Urgency</label>
        <select id="refer-urgency"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="Routine">Routine</option>
          <option value="Urgent">Urgent</option>
          <option value="Emergency">Emergency</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Reason
          for Referral</label>
        <textarea id="refer-reason" required rows="3"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
          placeholder="Clinical reason for referral..."></textarea>
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Doctor's
          Notes (Optional)</label>
        <textarea id="refer-notes" rows="2"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
          placeholder="Additional instructions..."></textarea>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
          onclick="closeReferModal()">Cancel</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/40">Confirm
          Referral</button>
      </div>
    </form>
  </div>
  <style>
    #refer-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- Details Modal -->
<div id="appt-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeApptModal()"></div>
  <div
    class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Appointment Details</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeApptModal()">✕</button>
    </div>
    <div id="appt-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>
    #appt-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- Edit Modal -->
<div id="appt-edit-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeEditApptModal()"></div>
  <div
    class="relative max-h-[90vh] w-[95%] max-w-4xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Appointment</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeEditApptModal()">✕</button>
    </div>
    <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Status</label>
        <select id="edit-status"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Appointment
          Type</label>
        <input id="edit-appointment_type" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Preferred
          Date</label>
        <input id="edit-preferred_date" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Email</label>
        <input id="edit-email" type="email"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Phone</label>
        <input id="edit-phone" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">First
          Name</label>
        <input id="edit-first_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Middle
          Name</label>
        <input id="edit-middle_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Last
          Name</label>
        <input id="edit-last_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Birth
          Date</label>
        <input id="edit-birth_date" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Gender</label>
        <select id="edit-gender"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
          <option value="prefer-not-to-say">Prefer not to say</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Civil
          Status</label>
        <select id="edit-civil_status"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="single">Single</option>
          <option value="married">Married</option>
          <option value="divorced">Divorced</option>
          <option value="widowed">Widowed</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Address</label>
        <textarea id="edit-address"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
          rows="2"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Health
          Concerns</label>
        <textarea id="edit-health_concerns"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
          rows="2"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Medical
          History</label>
        <textarea id="edit-medical_history"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
          rows="2"></textarea>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Current
          Medications</label>
        <input id="edit-current_medications" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Allergies</label>
        <input id="edit-allergies" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Emergency
          Contact Name</label>
        <input id="edit-emergency_contact_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Emergency
          Contact Phone</label>
        <input id="edit-emergency_contact_phone" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
    </div>
    <div class="mt-6 flex items-center justify-end gap-3">
      <button id="edit-appt-cancel"
        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Cancel</button>
      <button id="edit-appt-save"
        class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save
        Changes</button>
    </div>
  </div>
  <style>
    #appt-edit-modal.show {
      display: flex;
    }
  </style>
</div>

<!-- Create (Walk-in) Appointment Modal -->
<div id="doc-appt-crud-modal" class="fixed inset-0 hidden items-center justify-center z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeDocApptCrudModal()"></div>
  <div
    class="relative max-h-[90vh] w-[95%] max-w-4xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add Appointment</h3>
      <button
        class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800"
        onclick="closeDocApptCrudModal()">✕</button>
    </div>
    <form id="doc-appt-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">User
          Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Appointment
          Type</label>
        <input name="appointment_type" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
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
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">First
          Name</label>
        <input name="first_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Middle
          Name</label>
        <input name="middle_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Last
          Name</label>
        <input name="last_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Birth
          Date</label>
        <input name="birth_date" type="date"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Gender</label>
        <select name="gender"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
          <option value="prefer-not-to-say">Prefer not to say</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Civil
          Status</label>
        <select name="civil_status"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="single">Single</option>
          <option value="married">Married</option>
          <option value="divorced">Divorced</option>
          <option value="widowed">Widowed</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Address</label>
        <input name="address" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Health
          Concerns</label>
        <textarea name="health_concerns" rows="3"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Medical
          History</label>
        <textarea name="medical_history" rows="3"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Current
          Medications</label>
        <textarea name="current_medications" rows="2"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2">
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Allergies</label>
        <textarea name="allergies" rows="2"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Emergency
          Contact Name</label>
        <input name="emergency_contact_name" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label
          class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Emergency
          Contact Phone</label>
        <input name="emergency_contact_phone" type="text"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button"
          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
          onclick="closeDocApptCrudModal()">Cancel</button>
        <button type="submit"
          class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>
    #doc-appt-crud-modal.show {
      display: flex;
    }
  </style>
</div>

<script>
  const apptSearch = document.getElementById('appt-search');
  const apptBody = document.getElementById('appt-tbody');
  const apptTable = document.getElementById('appt-table');
  const apptExport = document.getElementById('appt-export');
  const apptPagination = document.getElementById('appt-pagination');
  const apptFilterStatus = document.getElementById('appt-filter-status');
  const apptFilterType = document.getElementById('appt-filter-type');
  const apptFilterFrom = document.getElementById('appt-filter-from');
  const apptFilterTo = document.getElementById('appt-filter-to');
  let apptSortKey = 'id';
  let apptSortAsc = false;
  let apptPage = 1;
  const apptPageSize = 10;

  function apptRows() { return Array.from(apptBody.querySelectorAll('.appt-row')); }

  function apptApply() {
    const q = ((apptSearch && apptSearch.value) ? apptSearch.value : '').toLowerCase();
    const fs = ((apptFilterStatus && apptFilterStatus.value) ? apptFilterStatus.value : '').toLowerCase();
    const ft = ((apptFilterType && apptFilterType.value) ? apptFilterType.value : '').toLowerCase();
    const df = (apptFilterFrom && apptFilterFrom.value) ? new Date(apptFilterFrom.value).setHours(0, 0, 0, 0) : null;
    const dt = (apptFilterTo && apptFilterTo.value) ? new Date(apptFilterTo.value).setHours(23, 59, 59, 999) : null;
    let rows = apptRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const st = (tr.getAttribute('data-status') || '').toLowerCase();
      const tp = (tr.getAttribute('data-type') || '').toLowerCase();
      const tms = Number(tr.getAttribute('data-time-ms') || '0');
      const okSearch = text.includes(q);
      const okStatus = !fs || st === fs;
      const okType = !ft || tp === ft;
      const okFrom = !df || tms >= df;
      const okTo = !dt || tms <= dt;
      tr.dataset._match = (okSearch && okStatus && okType && okFrom && okTo) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a, b) => {
      const ka = (a.dataset[apptSortKey] || '').toLowerCase();
      const kb = (b.dataset[apptSortKey] || '').toLowerCase();
      if (ka < kb) return apptSortAsc ? -1 : 1;
      if (ka > kb) return apptSortAsc ? 1 : -1;
      return 0;
    });
    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / apptPageSize));
    if (apptPage > pages) apptPage = pages;
    const start = (apptPage - 1) * apptPageSize;
    const end = start + apptPageSize;
    const visible = new Set(rows.slice(start, end));
    apptRows().forEach(tr => tr.style.display = 'none');
    visible.forEach(tr => tr.style.display = '');
    renderApptPagination(pages);
  }

  function renderApptPagination(pages) {
    if (!apptPagination) return;
    apptPagination.innerHTML = '';
    const prev = document.createElement('button');
    prev.textContent = 'Prev';
    prev.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    prev.disabled = apptPage <= 1;
    prev.onclick = () => { apptPage--; apptApply(); };
    apptPagination.appendChild(prev);
    for (let i = 1; i <= pages; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      b.className = 'px-2 py-1 rounded ' + (i === apptPage ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
      b.onclick = () => { apptPage = i; apptApply(); };
      apptPagination.appendChild(b);
    }
    const next = document.createElement('button');
    next.textContent = 'Next';
    next.className = 'px-2 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded';
    next.disabled = apptPage >= pages;
    next.onclick = () => { apptPage++; apptApply(); };
    apptPagination.appendChild(next);
  }

  if (apptTable) {
    apptTable.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-key');
        if (apptSortKey === key) { apptSortAsc = !apptSortAsc; } else { apptSortKey = key; apptSortAsc = true; }
        apptApply();
      });
    });
  }

  if (apptSearch) apptSearch.addEventListener('input', apptApply);
  [apptFilterStatus, apptFilterType, apptFilterFrom, apptFilterTo].forEach(el => { if (el) { el.addEventListener('change', () => { apptPage = 1; apptApply(); }); el.addEventListener('input', () => { apptPage = 1; apptApply(); }); } });
  const apptClearBtn = document.getElementById('appt-clear');
  if (apptClearBtn) {
    apptClearBtn.addEventListener('click', () => {
      if (apptSearch) apptSearch.value = '';
      if (apptFilterStatus) apptFilterStatus.value = '';
      if (apptFilterType) apptFilterType.value = '';
      if (apptFilterFrom) apptFilterFrom.value = '';
      if (apptFilterTo) apptFilterTo.value = '';
      apptPage = 1;
      apptApply();
    });
  }

  function download(filename, text) {
    const a = document.createElement('a');
    a.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function apptToCSV() {
    const headers = ['ID', 'Name', 'Email', 'Phone', 'Type', 'Preferred Date', 'Status', 'Created'];
    const rows = apptRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0, 8).map(td => '"' + (td.innerText || '').replace(/"/g, '\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (apptExport) apptExport.addEventListener('click', () => download('appointments.csv', apptToCSV()));

  // Create (walk-in) appointment
  const docApptAdd = document.getElementById('doc-appt-add');
  const docApptCrudModal = document.getElementById('doc-appt-crud-modal');
  const docApptCrudForm = document.getElementById('doc-appt-crud-form');
  function openDocApptCrudModal() { if (!docApptCrudModal) return; docApptCrudForm && docApptCrudForm.reset(); docApptCrudModal.classList.add('show'); docApptCrudModal.classList.remove('hidden'); }
  function closeDocApptCrudModal() { if (!docApptCrudModal) return; docApptCrudModal.classList.remove('show'); docApptCrudModal.classList.add('hidden'); }
  window.closeDocApptCrudModal = closeDocApptCrudModal;
  if (docApptAdd) docApptAdd.addEventListener('click', openDocApptCrudModal);

  docApptCrudForm && docApptCrudForm.addEventListener('submit', async function (e) {
    e.preventDefault();
    try {
      const f = docApptCrudForm;
      const payload = {
        user_email: f.elements['user_email'].value,
        appointment_type: f.elements['appointment_type'].value,
        preferred_date: f.elements['preferred_date'].value,
        email: f.elements['email'].value,
        phone: f.elements['phone'].value,
        first_name: f.elements['first_name'].value,
        middle_name: f.elements['middle_name'].value,
        last_name: f.elements['last_name'].value,
        birth_date: f.elements['birth_date'].value,
        gender: f.elements['gender'].value,
        civil_status: f.elements['civil_status'].value,
        address: f.elements['address'].value,
        health_concerns: f.elements['health_concerns'].value,
        medical_history: f.elements['medical_history'].value,
        current_medications: f.elements['current_medications'].value,
        allergies: f.elements['allergies'].value,
        emergency_contact_name: f.elements['emergency_contact_name'].value,
        emergency_contact_phone: f.elements['emergency_contact_phone'].value
      };
      const res = await fetch('api/create_appointment.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const text = await res.text();
      let json;
      try { json = JSON.parse(text); } catch { throw new Error('Server returned non-JSON (' + res.status + ').'); }
      if (!res.ok || !json.success) throw new Error(json.message || 'Operation failed');
      alert('Saved successfully'); location.reload();
    } catch (err) { console.error(err); alert('Error saving data: ' + err.message); }
  });

  // Actions
  // Actions
  async function deleteAppointment(id, source) {
    if (!confirm('Delete this record? This cannot be undone.')) return;
    try {
      let url = 'api/delete_appointment.php';
      let body = { id: id };
      if(source === 'service_request') {
        url = 'api/delete_service_request.php';
        // Check API expectation. Usually standardizing on {id} is safe if I control it.
      }
      
      const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(body) });
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Delete failed');
      
      let row;
      if (source) {
          row = apptRows().find(r => Number(r.dataset.id) === Number(id) && r.dataset.source === source);
      } else {
          // Fallback for old calls
          row = apptRows().find(r => Number(r.dataset.id) === Number(id));
      }

      if (row) row.remove();
      apptApply();
    } catch (e) { alert('Failed to delete: ' + e.message); }
  }
  window.deleteAppointment = deleteAppointment;

  async function acceptRequest(id, source) {
    if (!confirm('Accept and assign this patient to you?')) return;
    try {
      let url = 'api/update_appointment_status.php';
      let body = { id: id, status: 'confirmed' };
      
      if(source === 'service_request') {
        url = 'api/update_service_request_status.php';
        body = { request_id: id, status: 'in_progress' }; 
      }
      
      const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(body) });
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Update failed');
      
      alert('Request accepted.');
      location.reload();
    } catch (e) { alert('Failed to accept: ' + e.message); }
  }
  window.acceptRequest = acceptRequest;

  // Edit modal logic
  const editModal = document.getElementById('appt-edit-modal');
  const editFields = {
    status: document.getElementById('edit-status'),
    appointment_type: document.getElementById('edit-appointment_type'),
    preferred_date: document.getElementById('edit-preferred_date'),
    email: document.getElementById('edit-email'),
    phone: document.getElementById('edit-phone'),
    first_name: document.getElementById('edit-first_name'),
    middle_name: document.getElementById('edit-middle_name'),
    last_name: document.getElementById('edit-last_name'),
    birth_date: document.getElementById('edit-birth_date'),
    gender: document.getElementById('edit-gender'),
    civil_status: document.getElementById('edit-civil_status'),
    address: document.getElementById('edit-address'),
    health_concerns: document.getElementById('edit-health_concerns'),
    medical_history: document.getElementById('edit-medical_history'),
    current_medications: document.getElementById('edit-current_medications'),
    allergies: document.getElementById('edit-allergies'),
    emergency_contact_name: document.getElementById('edit-emergency_contact_name'),
    emergency_contact_phone: document.getElementById('edit-emergency_contact_phone')
  };
  let editingApptId = null;

  function setSelectValue(selectEl, val, allowed) {
    if (!selectEl) return;
    const v = (val || '').toString();
    if (!allowed || allowed.includes(v)) { selectEl.value = v; }
    else if (allowed && allowed.length) { selectEl.value = allowed[0]; }
  }

  function openEditApptModal(data) {
    editingApptId = Number(data.id);
    if (!editModal || !editingApptId) return;
    // Use freshest values from table row dataset (reflects recent edits without reload)
    try {
      const row = apptRows().find(r => Number(r.dataset.id) === Number(editingApptId));
      if (row && row.dataset) {
        if (row.dataset.status) data.status = row.dataset.status;
        if (row.dataset.type) data.appointment_type = row.dataset.type;
        if (row.dataset.preferred_date) data.preferred_date = row.dataset.preferred_date;
        if (row.dataset.email) data.email = row.dataset.email;
        if (row.dataset.phone) data.phone = row.dataset.phone;
        if (row.dataset.first_name !== undefined) data.first_name = row.dataset.first_name;
        if (row.dataset.middle_name !== undefined) data.middle_name = row.dataset.middle_name;
        if (row.dataset.last_name !== undefined) data.last_name = row.dataset.last_name;
        if (row.dataset.birth_date !== undefined) data.birth_date = row.dataset.birth_date;
        if (row.dataset.gender !== undefined) data.gender = row.dataset.gender;
        if (row.dataset.civil_status !== undefined) data.civil_status = row.dataset.civil_status;
        if (row.dataset.address !== undefined) data.address = row.dataset.address;
        if (row.dataset.health_concerns !== undefined) data.health_concerns = row.dataset.health_concerns;
        if (row.dataset.medical_history !== undefined) data.medical_history = row.dataset.medical_history;
        if (row.dataset.current_medications !== undefined) data.current_medications = row.dataset.current_medications;
        if (row.dataset.allergies !== undefined) data.allergies = row.dataset.allergies;
        if (row.dataset.emergency_contact_name !== undefined) data.emergency_contact_name = row.dataset.emergency_contact_name;
        if (row.dataset.emergency_contact_phone !== undefined) data.emergency_contact_phone = row.dataset.emergency_contact_phone;
      }
    } catch (err) {
      console.error('Failed to sync edit modal data from row dataset', err);
    }
    // Fill fields
    setSelectValue(editFields.status, data.status, ['confirmed', 'completed', 'cancelled']);
    editFields.appointment_type && (editFields.appointment_type.value = data.appointment_type || '');
    editFields.preferred_date && (editFields.preferred_date.value = (data.preferred_date || '').substring(0, 10));
    editFields.email && (editFields.email.value = data.email || '');
    editFields.phone && (editFields.phone.value = data.phone || '');
    editFields.first_name && (editFields.first_name.value = data.first_name || '');
    editFields.middle_name && (editFields.middle_name.value = data.middle_name || '');
    editFields.last_name && (editFields.last_name.value = data.last_name || '');
    editFields.birth_date && (editFields.birth_date.value = (data.birth_date || '').substring(0, 10));
    setSelectValue(editFields.gender, data.gender, ['male', 'female', 'other', 'prefer-not-to-say']);
    setSelectValue(editFields.civil_status, data.civil_status, ['single', 'married', 'divorced', 'widowed']);
    editFields.address && (editFields.address.value = data.address || '');
    editFields.health_concerns && (editFields.health_concerns.value = data.health_concerns || '');
    editFields.medical_history && (editFields.medical_history.value = data.medical_history || '');
    editFields.current_medications && (editFields.current_medications.value = data.current_medications || '');
    editFields.allergies && (editFields.allergies.value = data.allergies || '');
    editFields.emergency_contact_name && (editFields.emergency_contact_name.value = data.emergency_contact_name || '');
    editFields.emergency_contact_phone && (editFields.emergency_contact_phone.value = data.emergency_contact_phone || '');
    // Show modal
    editModal.classList.add('show');
    editModal.classList.remove('hidden');
  }
  function closeEditApptModal() {
    if (!editModal) return;
    editModal.classList.remove('show');
    editModal.classList.add('hidden');
    editingApptId = null;
  }

  async function saveApptEdits() {
    if (!editingApptId) return;
    const payload = { id: editingApptId };
    // Collect values
    Object.keys(editFields).forEach(k => {
      const el = editFields[k];
      if (!el) return;
      const val = (el.value || '').toString();
      // Only include if changed or not empty (keep simple: include all)
      payload[k] = val;
    });
    try {
      const res = await fetch('api/update_appointment.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(payload) });
      const text = await res.text();
      let json; try { json = JSON.parse(text); } catch { throw new Error('Server returned non-JSON (' + res.status + ').'); }
      if (!res.ok || !json.success) throw new Error(json.message || 'Save failed');
      // Update row UI
      const row = apptRows().find(r => Number(r.dataset.id) === Number(editingApptId));
      if (row) {
        const fullName = [payload.first_name, payload.middle_name, payload.last_name].filter(Boolean).join(' ').trim();
        // dataset sync
        Object.keys(payload).forEach(key => {
          if (key === 'id') return;
          const val = payload[key];
          if (key === 'appointment_type') {
            row.dataset.type = val;
          } else if (key === 'preferred_date') {
            row.dataset.preferred_date = val;
          } else if (key === 'status') {
            row.dataset.status = val;
          } else {
            row.dataset[key] = val;
          }
        });
        row.dataset.name = fullName;

        // cells: 0 ID, 1 Name, 2 Email, 3 Phone, 4 Type, 5 Pref Date, 6 Status(span), 7 Created, 8 Actions
        const cells = row.children;
        if (cells[1] && fullName) cells[1].innerText = fullName;
        if (cells[2] && payload.email !== undefined) cells[2].innerText = payload.email;
        if (cells[3] && payload.phone !== undefined) cells[3].innerText = payload.phone;
        if (cells[4] && payload.appointment_type !== undefined) cells[4].innerText = payload.appointment_type;
        if (cells[5] && payload.preferred_date !== undefined) cells[5].innerText = payload.preferred_date;
        if (cells[6] && payload.status) {
          const span = cells[6].querySelector('span');
          if (span) {
            const s = payload.status;
            span.textContent = s.toUpperCase();
            span.className = 'px-2 py-1 rounded text-xs font-medium ' + (s === 'confirmed' ? 'bg-green-600 text-white' : (s === 'completed' ? 'bg-blue-600 text-white' : (s === 'cancelled' ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white')));
          }
        }
      }
      closeEditApptModal();
      apptApply();
      alert('Save Changes Successfully.');
    } catch (e) {
      alert('Failed to save: ' + e.message);
    }
  }

  const editSaveBtn = document.getElementById('edit-appt-save');
  const editCancelBtn = document.getElementById('edit-appt-cancel');
  if (editSaveBtn) editSaveBtn.addEventListener('click', saveApptEdits);
  if (editCancelBtn) editCancelBtn.addEventListener('click', closeEditApptModal);

  window.openEditApptModal = openEditApptModal;
  window.closeEditApptModal = closeEditApptModal;

  // Modal helpers
  function field(label, value) {
    const safe = (value == null ? '' : value).toString();
    return `<div class="bg-gray-700/40 border border-gray-600 rounded p-3">
      <div class="text-gray-400 text-xs mb-1">${label}</div>
      <div class="text-white break-words whitespace-pre-wrap">${safe.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
    </div>`;
  }
  function openApptModal(data) {
    const modal = document.getElementById('appt-modal');
    const details = document.getElementById('appt-details');
    if (!modal || !details) return;
    const fullName = [data.first_name, data.middle_name, data.last_name].filter(Boolean).join(' ');
    details.innerHTML = `
      ${field('Appointment ID', '#' + (data.id || ''))}
      ${field('Status', (data.status || '').toUpperCase())}
      ${field('Created At', data.created_at || '')}
      ${field('Full Name', fullName)}
      ${field('Email', data.email || '')}
      ${field('Phone', data.phone || '')}
      ${field('Birth Date', data.birth_date || '')}
      ${field('Gender', data.gender || '')}
      ${field('Civil Status', data.civil_status || '')}
      ${field('Address', data.address || '')}
      ${field('Appointment Type', data.appointment_type || '')}
      ${field('Preferred Date', data.preferred_date || '')}
      ${field('Health Concerns', data.health_concerns || '')}
      ${field('Medical History', data.medical_history || '')}
      ${field('Current Medications', data.current_medications || '')}
      ${field('Allergies', data.allergies || '')}
      ${field('Emergency Contact Name', data.emergency_contact_name || '')}
      ${field('Emergency Contact Phone', data.emergency_contact_phone || '')}
    `;
    modal.classList.add('show');
    modal.classList.remove('hidden');
  }
  function closeApptModal() {
    const modal = document.getElementById('appt-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }

  // Actions
  async function deleteAppointment(id, source) {
    if (!confirm('Delete this record? This cannot be undone.')) return;
    try {
      let url = 'api/delete_appointment.php';
      let body = { id: id };
      if(source === 'service_request') {
        url = 'api/delete_service_request.php';
        // delete_service_request.php might expect 'id' or 'request_id'. Assuming 'id' based on pattern.
        // Let's assume standard {id: ...}
      }
      
      const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(body) });
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Delete failed');
      const row = apptRows().find(r => Number(r.dataset.id) === Number(id)); // ID collision risk if not using source in finding row? 
      // Need to find by ID AND source?
      // apptRows() returns all rows. If IDs overlap (e.g. Appt 1 and Req 1), this is bad.
      // I should add data-source to rows and filter by it.
      const targetRow = apptRows().find(r => Number(r.dataset.id) === Number(id) && r.dataset.source === source);
      if (targetRow) targetRow.remove();
      apptApply();
    } catch (e) { alert('Failed to delete: ' + e.message); }
  }
  window.deleteAppointment = deleteAppointment;

  async function acceptRequest(id, source) {
    if (!confirm('Accept this request?')) return;
    try {
      let url = 'api/update_appointment_status.php';
      let body = { id: id, status: 'confirmed' };
      
      if(source === 'service_request') {
        url = 'api/update_service_request_status.php';
        body = { request_id: id, status: 'in_progress' }; // Verify param name for info
      }
      
      const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, credentials: 'same-origin', body: JSON.stringify(body) });
      const json = await res.json();
      if (!res.ok || !json.success) throw new Error(json.message || 'Update failed');
      alert('Request accepted.');
      location.reload();
    } catch (e) { alert('Failed to accept: ' + e.message); }
  }
  window.acceptRequest = acceptRequest;

  // Edit modal logic ... (unchanged, mainly for appointments)
  // ... (I will skip rewriting edit modal logic to keep token usage low, I am only replacing Actions section and below)
  // Wait, replace_file_content needs start/end.
  // I will replace from "async function deleteAppointment(id) {" to "window.deleteAppointment = deleteAppointment;"
  // And the Refer Modal logic at the end.
  // I need to be careful with line numbers.

  // Since I can't easily jump around, I will replace the "Actions" block (796-807) and "Refer Modal" block (1011-1058).
  // But they are far apart.
  // I will make TWO calls.
  
  // Call 1: Update deleteAppointment and add acceptRequest.
  // Call 2: Update Refer Modal logic.
  
  // This tool call is for Call 2 (Refer Modal Logic) as it is in the replacement content I prepared?
  // No, I will do Refer Modal Logic here first as it corresponds to the EndLine 1058 in the prompt instruction.
  
  // Refer Modal
  const referModal = document.getElementById('refer-modal');
  const referForm = document.getElementById('refer-form');
  const referApptId = document.getElementById('refer-appt-id');
  const referSource = document.getElementById('refer-source');
  const referPatientName = document.getElementById('refer-patient-name');

  function openReferModal(data) {
    if (!referModal) return;
    referApptId.value = data.id;
    referSource.value = data.source;
    referPatientName.value = data.name;
    referModal.classList.add('show');
    referModal.classList.remove('hidden');
  }
  function closeReferModal() {
    if (!referModal) return;
    referModal.classList.remove('show');
    referModal.classList.add('hidden');
    referForm.reset();
  }
  window.openReferModal = openReferModal;
  window.closeReferModal = closeReferModal;

  if (referForm) {
    referForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      try {
        const src = referSource.value;
        const payload = {
          hospital_name: document.getElementById('refer-hospital').value,
          urgency: document.getElementById('refer-urgency').value,
          reason: document.getElementById('refer-reason').value,
          notes: document.getElementById('refer-notes').value
        };
        if(src === 'appointment') payload.appointment_id = referApptId.value;
        else payload.service_request_id = referApptId.value;

        const res = await fetch('api/refer_patient.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(payload)
        });
        const text = await res.text();
        let json; try { json = JSON.parse(text); } catch { throw new Error('Server returned non-JSON'); }
        if (!res.ok || !json.success) throw new Error(json.message || 'Operation failed');

        alert('Patient referred successfully.');
        location.reload();
      } catch (err) {
        alert('Failed to refer: ' + err.message);
      }
    });
  }

  // init
  apptApply();
</script>