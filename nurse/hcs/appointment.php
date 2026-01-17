<?php
// Nurse: view HCS appointments (read-only)
// Assumes nurseheader.php already included by router and $db is available

try {
  $stmt = $db->query("SELECT * FROM appointments ORDER BY created_at DESC");
  $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $apptTypes = [];
  foreach ($appointments as $row) {
    $t = trim((string)($row['appointment_type'] ?? ''));
    if ($t !== '' && !in_array($t, $apptTypes, true)) $apptTypes[] = $t;
  }
  /*
  function napptStatusBadgeClass(s){ s=(s||'').toLowerCase(); return s==='confirmed'?'bg-green-600 text-white':(s==='completed'?'bg-blue-600 text-white':(s==='cancelled'?'bg-red-600 text-white':'bg-yellow-600 text-white')); }
  function openEditApptModal(row){
    if (!row || !napptEditModal || !napptEditForm) return;
    const d=row.dataset; napptEditForm.elements['id'].value=d.id||'';
    napptEditForm.elements['status'].value=(d.status||'pending').toLowerCase();
    napptEditForm.elements['appointment_type'].value=d.type||'';
    napptEditForm.elements['preferred_date'].value=d.preferred_date||'';
    napptEditForm.elements['email'].value=d.email||'';
    napptEditForm.elements['phone'].value=d.phone||'';
    napptEditForm.elements['first_name'].value=d.first_name||'';
    napptEditForm.elements['middle_name'].value=d.middle_name||'';
    napptEditForm.elements['last_name'].value=d.last_name||'';
    napptEditForm.elements['birth_date'].value=d.birth_date||'';
    napptEditForm.elements['gender'].value=(d.gender||'male').toLowerCase();
    napptEditForm.elements['civil_status'].value=(d.civil_status||'single').toLowerCase();
    napptEditForm.elements['address'].value=d.address||'';
    napptEditForm.elements['health_concerns'].value=d.health_concerns||'';
    napptEditForm.elements['medical_history'].value=d.medical_history||'';
    napptEditForm.elements['current_medications'].value=d.current_medications||'';
    napptEditForm.elements['allergies'].value=d.allergies||'';
    napptEditForm.elements['emergency_contact_name'].value=d.emergency_contact_name||'';
    napptEditForm.elements['emergency_contact_phone'].value=d.emergency_contact_phone||'';
    napptEditModal._row=row;
    napptEditModal.classList.add('show'); napptEditModal.classList.remove('hidden');
  }
  function closeNurseApptEdit(){ if(!napptEditModal) return; napptEditModal.classList.remove('show'); napptEditModal.classList.add('hidden'); napptEditModal._row=null; }
  window.openEditApptModal = openEditApptModal; window.closeNurseApptEdit = closeNurseApptEdit;

  napptEditForm && napptEditForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id=parseInt(napptEditForm.elements['id'].value||'0',10); if(!id) return alert('Invalid ID');
    const payload={ action:'update', id };
    // Collect all inputs
    ['status','appointment_type','preferred_date','email','phone','first_name','middle_name','last_name','birth_date','gender','civil_status','address','health_concerns','medical_history','current_medications','allergies','emergency_contact_name','emergency_contact_phone'].forEach(k=>{ const el=napptEditForm.elements[k]; if(el) payload[k]=el.value; });
    try{
      const res=await fetch('hcs/appt_api.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify(payload)});
      const text=await res.text();
      let json; try{ json=JSON.parse(text); }catch{ throw new Error('Server returned non-JSON ('+res.status+').'); }
      if(!res.ok || !json.success) throw new Error(json.message||('HTTP '+res.status));
      const row=napptEditModal._row || document.querySelector(`.appt-row[data-id="${id}"]`);
      if(row){
        // dataset sync
        row.dataset.status=payload.status; row.dataset.type=payload.appointment_type; row.dataset.preferred_date=payload.preferred_date; row.dataset.email=payload.email; row.dataset.phone=payload.phone; row.dataset.first_name=payload.first_name; row.dataset.middle_name=payload.middle_name; row.dataset.last_name=payload.last_name; row.dataset.birth_date=payload.birth_date; row.dataset.gender=payload.gender; row.dataset.civil_status=payload.civil_status; row.dataset.address=payload.address; row.dataset.health_concerns=payload.health_concerns; row.dataset.medical_history=payload.medical_history; row.dataset.current_medications=payload.current_medications; row.dataset.allergies=payload.allergies; row.dataset.emergency_contact_name=payload.emergency_contact_name; row.dataset.emergency_contact_phone=payload.emergency_contact_phone;
        const fullName=[payload.first_name,payload.middle_name,payload.last_name].filter(Boolean).join(' ').trim(); row.dataset.name=fullName;
        // cells: 0 ID,1 Name,2 Email,3 Phone,4 Type,5 Pref Date,6 Status(span),7 Created, 8 Actions
        const cells=row.children; if(cells[1]&&fullName) cells[1].innerText=fullName; if(cells[2]) cells[2].innerText=payload.email; if(cells[3]) cells[3].innerText=payload.phone; if(cells[4]) cells[4].innerText=payload.appointment_type; if(cells[5]) cells[5].innerText=payload.preferred_date; if(cells[6]){ const span=cells[6].querySelector('span'); if(span){ const s=payload.status; span.textContent=(s||'').toUpperCase(); span.className='px-2 py-1 rounded text-xs font-medium '+napptStatusBadgeClass(s); }}
      }
      closeNurseApptEdit(); apptApply(); alert('Save Changes Successfully.');
    }catch(err){ console.error(err); alert('Failed to save: '+err.message); }
  });
  */
} catch (Exception $e) {
  error_log('Nurse fetch appointments error: ' . $e->getMessage());
  $appointments = [];
  $apptTypes = [];
}
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>

<div class="mb-8 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
  <div>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">HCS Appointments</h2>
    <p class="text-gray-600 dark:text-gray-400">Review, filter, and manage walk-in appointments captured by the nurse station.</p>
  </div>
  <div class="inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-5 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
    Total Records: <span class="ml-2 text-base"><?php echo number_format(count($appointments)); ?></span>
  </div>
</div>

<section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
  <div class="flex flex-col gap-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select id="appt-filter-status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</label>
        <select id="appt-filter-type" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="">All</option>
          <?php foreach ($apptTypes as $t): ?>
            <option value="<?php echo h($t); ?>"><?php echo h($t); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date From</label>
        <input id="appt-filter-from" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date To</label>
        <input id="appt-filter-to" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
    </div>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div class="relative w-full lg:w-[420px]">
        <input id="appt-search" type="text" placeholder="Search name, email, type..." class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2 pr-10 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        <svg class="pointer-events-none absolute right-3 top-2.5 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button id="appt-clear" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-600 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700">Clear Filters</button>
        <button id="appt-export" class="inline-flex items-center rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100 dark:hover:bg-emerald-500/30">Export CSV</button>
        <button id="appt-add" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Add Walk-in</button>
      </div>
    </div>
  </div>

  <div class="mt-6 overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-200" id="appt-table">
      <thead>
        <tr class="border-b border-gray-200 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-slate-700 dark:text-gray-400">
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
            <td colspan="9" class="py-6 px-3 text-center text-gray-400">No appointments found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($appointments as $a): ?>
            <?php
              $status = strtolower($a['status'] ?? 'pending');
              $statusClasses = [
                'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-100',
                'completed' => 'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-100',
                'cancelled' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-100',
                'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
              ];
              $statusClass = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-600/40 dark:text-slate-100';
              $fullName = trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''));
            ?>
            <tr class="appt-row border-b border-gray-100 transition-colors hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/60"
                data-id="<?php echo h($a['id']); ?>"
                data-name="<?php echo h($fullName); ?>"
                data-email="<?php echo h($a['email'] ?? ''); ?>"
                data-phone="<?php echo h($a['phone'] ?? ''); ?>"
                data-type="<?php echo h($a['appointment_type'] ?? ''); ?>"
                data-preferred_date="<?php echo h($a['preferred_date'] ?? ''); ?>"
                data-status="<?php echo h($a['status'] ?? 'pending'); ?>"
                data-created="<?php echo h($a['created_at'] ?? ''); ?>"
                data-first_name="<?php echo h($a['first_name'] ?? ''); ?>"
                data-middle_name="<?php echo h($a['middle_name'] ?? ''); ?>"
                data-last_name="<?php echo h($a['last_name'] ?? ''); ?>"
                data-birth_date="<?php echo h($a['birth_date'] ?? ''); ?>"
                data-gender="<?php echo h($a['gender'] ?? ''); ?>"
                data-civil_status="<?php echo h($a['civil_status'] ?? ''); ?>"
                data-address="<?php echo h($a['address'] ?? ''); ?>"
                data-health_concerns="<?php echo h($a['health_concerns'] ?? ''); ?>"
                data-medical_history="<?php echo h($a['medical_history'] ?? ''); ?>"
                data-current_medications="<?php echo h($a['current_medications'] ?? ''); ?>"
                data-allergies="<?php echo h($a['allergies'] ?? ''); ?>"
                data-emergency_contact_name="<?php echo h($a['emergency_contact_name'] ?? ''); ?>"
                data-emergency_contact_phone="<?php echo h($a['emergency_contact_phone'] ?? ''); ?>"
                data-time-ms="<?php echo (int) (strtotime($a['created_at'] ?? '') * 1000); ?>">
              <td class="py-3 px-3 font-medium text-gray-700 dark:text-gray-200">#<?php echo h($a['id']); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($fullName); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($a['email'] ?? ''); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($a['phone'] ?? ''); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($a['appointment_type'] ?? ''); ?></td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($a['preferred_date'] ?? ''); ?></td>
              <td class="py-3 px-3">
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo $statusClass; ?>"><?php echo h(strtoupper($status)); ?></span>
              </td>
              <td class="py-3 px-3 text-gray-600 dark:text-gray-300"><?php echo h($a['created_at'] ?? ''); ?></td>
              <td class="py-3 px-3 text-right">
                <?php $jsonData = json_encode([
                  'id'=>$a['id'],
                  'first_name'=>$a['first_name'],
                  'middle_name'=>$a['middle_name'],
                  'last_name'=>$a['last_name'],
                  'email'=>$a['email'],
                  'phone'=>$a['phone'],
                  'birth_date'=>$a['birth_date'],
                  'gender'=>$a['gender'],
                  'civil_status'=>$a['civil_status'],
                  'address'=>$a['address'],
                  'appointment_type'=>$a['appointment_type'],
                  'preferred_date'=>$a['preferred_date'],
                  'health_concerns'=>$a['health_concerns'],
                  'medical_history'=>$a['medical_history'],
                  'current_medications'=>$a['current_medications'],
                  'allergies'=>$a['allergies'],
                  'emergency_contact_name'=>$a['emergency_contact_name'],
                  'emergency_contact_phone'=>$a['emergency_contact_phone'],
                  'status'=>$a['status'],
                  'created_at'=>$a['created_at'],
                ], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>
                <button type="button" class="mr-2 inline-flex items-center rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" onclick='openApptModal(<?php echo $jsonData; ?>)'>View</button>
                <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="openEditApptModal(this.closest('tr'))">Edit</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div id="appt-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
</section>

<!-- Details Modal -->
<div id="appt-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeApptModal()"></div>
  <div class="relative max-h-[85vh] w-[92%] max-w-3xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Appointment Details</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeApptModal()">✕</button>
    </div>
    <div id="appt-details" class="grid grid-cols-1 gap-4 text-sm text-gray-700 md:grid-cols-2 dark:text-gray-200"></div>
  </div>
  <style>#appt-modal.show{display:flex;}</style>
</div>

<!-- Edit Appointment Modal -->
<div id="nappt-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeNurseApptEdit()"></div>
  <div class="relative max-h-[90vh] w-[95%] max-w-4xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Edit Appointment</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeNurseApptEdit()">✕</button>
    </div>
    <form id="nappt-edit-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <input type="hidden" name="id" />
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="confirmed">Confirmed</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
          <option value="pending">Pending</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Appointment Type</label>
        <input name="appointment_type" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
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
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">First Name</label>
        <input name="first_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Middle Name</label>
        <input name="middle_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Name</label>
        <input name="last_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Birth Date</label>
        <input name="birth_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gender</label>
        <select name="gender" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
          <option value="prefer-not-to-say">Prefer not to say</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Civil Status</label>
        <select name="civil_status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="single">Single</option>
          <option value="married">Married</option>
          <option value="divorced">Divorced</option>
          <option value="widowed">Widowed</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <textarea name="address" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" rows="2"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Health Concerns</label>
        <textarea name="health_concerns" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" rows="2"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Medical History</label>
        <textarea name="medical_history" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" rows="2"></textarea>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Medications</label>
        <input name="current_medications" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Allergies</label>
        <input name="allergies" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Emergency Contact Name</label>
        <input name="emergency_contact_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Emergency Contact Phone</label>
        <input name="emergency_contact_phone" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeNurseApptEdit()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#nappt-edit-modal.show{display:flex;}</style>
</div>
<!-- Create (Walk-in) Appointment Modal -->
<div id="appt-crud-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60" onclick="closeApptCrudModal()"></div>
  <div class="relative max-h-[90vh] w-[95%] max-w-4xl overflow-y-auto rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-4 flex items-center justify-between">
      <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Add Appointment</h3>
      <button class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-slate-800" onclick="closeApptCrudModal()">✕</button>
    </div>
    <form id="appt-crud-form" class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">User Email (for create)</label>
        <input name="user_email" type="email" placeholder="citizen@example.com" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Appointment Type</label>
        <input name="appointment_type" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Preferred Date</label>
        <input name="preferred_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
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
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">First Name</label>
        <input name="first_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Middle Name</label>
        <input name="middle_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Name</label>
        <input name="last_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Birth Date</label>
        <input name="birth_date" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gender</label>
        <select name="gender" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
          <option value="prefer-not-to-say">Prefer not to say</option>
        </select>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Civil Status</label>
        <select name="civil_status" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
          <option value="single">Single</option>
          <option value="married">Married</option>
          <option value="divorced">Divorced</option>
          <option value="widowed">Widowed</option>
        </select>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <input name="address" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Health Concerns</label>
        <textarea name="health_concerns" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Medical History</label>
        <textarea name="medical_history" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Medications</label>
        <textarea name="current_medications" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Allergies</label>
        <textarea name="allergies" rows="2" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Emergency Contact Name</label>
        <input name="emergency_contact_name" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Emergency Contact Phone</label>
        <input name="emergency_contact_phone" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="md:col-span-2 flex justify-end gap-2 pt-2">
        <button type="button" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700" onclick="closeApptCrudModal()">Cancel</button>
        <button type="submit" class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">Save</button>
      </div>
    </form>
  </div>
  <style>#appt-crud-modal.show{display:flex;}</style>
</div>

<script>
  const apptSearch = document.getElementById('appt-search');
  const apptBody = document.getElementById('appt-tbody');
  const apptTable = document.getElementById('appt-table');
  const apptExport = document.getElementById('appt-export');
  const apptAdd = document.getElementById('appt-add');
  const apptClear = document.getElementById('appt-clear');
  const apptCrudModal = document.getElementById('appt-crud-modal');
  const apptCrudForm = document.getElementById('appt-crud-form');
  const napptEditModal = document.getElementById('nappt-edit-modal');
  const napptEditForm = document.getElementById('nappt-edit-form');
  const apptPagination = document.getElementById('appt-pagination');
  const apptFilterStatus = document.getElementById('appt-filter-status');
  const apptFilterType = document.getElementById('appt-filter-type');
  const apptFilterFrom = document.getElementById('appt-filter-from');
  const apptFilterTo = document.getElementById('appt-filter-to');
  let apptSortKey = 'id';
  let apptSortAsc = false;
  let apptPage = 1;
  const apptPageSize = 10;

  function apptRows(){ return Array.from(apptBody.querySelectorAll('.appt-row')); }

  function apptApply(){
    const q = (apptSearch?.value || '').toLowerCase();
    const fs = (apptFilterStatus?.value || '').toLowerCase();
    const ft = (apptFilterType?.value || '').toLowerCase();
    const df = apptFilterFrom?.value ? new Date(apptFilterFrom.value).setHours(0,0,0,0) : null;
    const dt = apptFilterTo?.value ? new Date(apptFilterTo.value).setHours(23,59,59,999) : null;
    let rows = apptRows();
    rows.forEach(tr => {
      const text = tr.innerText.toLowerCase();
      const st = (tr.getAttribute('data-status')||'').toLowerCase();
      const tp = (tr.getAttribute('data-type')||'').toLowerCase();
      const tms = Number(tr.getAttribute('data-time-ms')||'0');
      const okSearch = text.includes(q);
      const okStatus = !fs || st === fs;
      const okType = !ft || tp === ft;
      const okFrom = !df || tms >= df;
      const okTo = !dt || tms <= dt;
      tr.dataset._match = (okSearch && okStatus && okType && okFrom && okTo) ? '1' : '0';
    });
    rows = rows.filter(tr => tr.dataset._match === '1');
    rows.sort((a,b)=>{
      const ka = (a.dataset[apptSortKey]||'').toLowerCase();
      const kb = (b.dataset[apptSortKey]||'').toLowerCase();
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

  function renderApptPagination(pages){
    if (!apptPagination) return;
    apptPagination.innerHTML = '';
    const makeButton = (label, disabled, onClick, active=false) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = label;
      if (active) {
        btn.className = 'rounded-lg bg-primary px-3 py-1 text-xs font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/40';
      } else {
        btn.className = 'rounded-lg border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-600 transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700';
        btn.disabled = disabled;
        if (!disabled) btn.onclick = onClick;
        if (disabled) btn.classList.add('opacity-60','cursor-not-allowed');
      }
      return btn;
    };
    apptPagination.appendChild(makeButton('Prev', apptPage <= 1, () => { apptPage--; apptApply(); }));
    for (let i=1;i<=pages;i++){
      apptPagination.appendChild(makeButton(String(i), false, () => { apptPage = i; apptApply(); }, i===apptPage));
    }
    apptPagination.appendChild(makeButton('Next', apptPage >= pages, () => { apptPage++; apptApply(); }));
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
  [apptFilterStatus, apptFilterType, apptFilterFrom, apptFilterTo].forEach(el=>{ if (el){ el.addEventListener('change', ()=>{ apptPage=1; apptApply(); }); el.addEventListener('input', ()=>{ apptPage=1; apptApply(); }); }});

  function download(filename, text){
    const a = document.createElement('a');
    a.setAttribute('href','data:text/csv;charset=utf-8,' + encodeURIComponent(text));
    a.setAttribute('download', filename);
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
  }
  function apptToCSV(){
    const headers = ['ID','Name','Email','Phone','Type','Preferred Date','Status','Created'];
    const rows = apptRows().filter(tr => tr.style.display !== 'none');
    const lines = [headers.join(',')];
    rows.forEach(tr => {
      const cells = Array.from(tr.children).slice(0,8).map(td => '"' + (td.innerText||'').replace(/"/g,'\"') + '"');
      lines.push(cells.join(','));
    });
    return lines.join('\n');
  }
  if (apptExport) apptExport.addEventListener('click', () => download('hcs_appointments.csv', apptToCSV()));
  if (apptAdd) apptAdd.addEventListener('click', ()=>{ if(!apptCrudModal) return; apptCrudForm?.reset(); apptCrudModal.classList.add('show'); apptCrudModal.classList.remove('hidden'); });

  function closeApptCrudModal(){ if(!apptCrudModal) return; apptCrudModal.classList.remove('show'); apptCrudModal.classList.add('hidden'); }
  window.closeApptCrudModal = closeApptCrudModal;
  // Edit support
  function napptStatusBadgeClass(s){ s=(s||'').toLowerCase(); return s==='confirmed'?'bg-green-600 text-white':(s==='completed'?'bg-blue-600 text-white':(s==='cancelled'?'bg-red-600 text-white':'bg-yellow-600 text-white')); }
  function openEditApptModal(row){
    if (!row || !napptEditModal || !napptEditForm) return;
    const d=row.dataset; napptEditForm.elements['id'].value=d.id||'';
    napptEditForm.elements['status'].value=(d.status||'pending').toLowerCase();
    napptEditForm.elements['appointment_type'].value=d.type||'';
    napptEditForm.elements['preferred_date'].value=d.preferred_date||'';
    napptEditForm.elements['email'].value=d.email||'';
    napptEditForm.elements['phone'].value=d.phone||'';
    napptEditForm.elements['first_name'].value=d.first_name||'';
    napptEditForm.elements['middle_name'].value=d.middle_name||'';
    napptEditForm.elements['last_name'].value=d.last_name||'';
    napptEditForm.elements['birth_date'].value=d.birth_date||'';
    napptEditForm.elements['gender'].value=(d.gender||'male').toLowerCase();
    napptEditForm.elements['civil_status'].value=(d.civil_status||'single').toLowerCase();
    napptEditForm.elements['address'].value=d.address||'';
    napptEditForm.elements['health_concerns'].value=d.health_concerns||'';
    napptEditForm.elements['medical_history'].value=d.medical_history||'';
    napptEditForm.elements['current_medications'].value=d.current_medications||'';
    napptEditForm.elements['allergies'].value=d.allergies||'';
    napptEditForm.elements['emergency_contact_name'].value=d.emergency_contact_name||'';
    napptEditForm.elements['emergency_contact_phone'].value=d.emergency_contact_phone||'';
    napptEditModal._row=row;
    napptEditModal.classList.add('show'); napptEditModal.classList.remove('hidden');
  }
  function closeNurseApptEdit(){ if(!napptEditModal) return; napptEditModal.classList.remove('show'); napptEditModal.classList.add('hidden'); napptEditModal._row=null; }
  window.openEditApptModal = openEditApptModal; window.closeNurseApptEdit = closeNurseApptEdit;
  napptEditForm && napptEditForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id=parseInt(napptEditForm.elements['id'].value||'0',10); if(!id) return alert('Invalid ID');
    const payload={ action:'update', id };
    ['status','appointment_type','preferred_date','email','phone','first_name','middle_name','last_name','birth_date','gender','civil_status','address','health_concerns','medical_history','current_medications','allergies','emergency_contact_name','emergency_contact_phone'].forEach(k=>{ const el=napptEditForm.elements[k]; if(el) payload[k]=el.value; });
    try{
      const res=await fetch('hcs/appt_api.php',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},credentials:'same-origin',body:JSON.stringify(payload)});
      const text=await res.text();
      let json; try{ json=JSON.parse(text); }catch{ throw new Error('Server returned non-JSON ('+res.status+').'); }
      if(!res.ok || !json.success) throw new Error(json.message||('HTTP '+res.status));
      const row=napptEditModal._row || document.querySelector(`.appt-row[data-id="${id}"]`);
      if(row){
        row.dataset.status=payload.status; row.dataset.type=payload.appointment_type; row.dataset.preferred_date=payload.preferred_date; row.dataset.email=payload.email; row.dataset.phone=payload.phone; row.dataset.first_name=payload.first_name; row.dataset.middle_name=payload.middle_name; row.dataset.last_name=payload.last_name; row.dataset.birth_date=payload.birth_date; row.dataset.gender=payload.gender; row.dataset.civil_status=payload.civil_status; row.dataset.address=payload.address; row.dataset.health_concerns=payload.health_concerns; row.dataset.medical_history=payload.medical_history; row.dataset.current_medications=payload.current_medications; row.dataset.allergies=payload.allergies; row.dataset.emergency_contact_name=payload.emergency_contact_name; row.dataset.emergency_contact_phone=payload.emergency_contact_phone;
        const fullName=[payload.first_name,payload.middle_name,payload.last_name].filter(Boolean).join(' ').trim(); row.dataset.name=fullName;
        const cells=row.children; if(cells[1]&&fullName) cells[1].innerText=fullName; if(cells[2]) cells[2].innerText=payload.email; if(cells[3]) cells[3].innerText=payload.phone; if(cells[4]) cells[4].innerText=payload.appointment_type; if(cells[5]) cells[5].innerText=payload.preferred_date; if(cells[6]){ const span=cells[6].querySelector('span'); if(span){ const s=payload.status; span.textContent=(s||'').toUpperCase(); span.className='px-2 py-1 rounded text-xs font-medium '+napptStatusBadgeClass(s); }}
      }
      closeNurseApptEdit(); apptApply(); alert('Save Changes Successfully.');
    }catch(err){ console.error(err); alert('Failed to save: '+err.message); }
  });
  // Create (walk-in) submission
  apptCrudForm && apptCrudForm.addEventListener('submit', async function(e){
    e.preventDefault();
    try{
      const f = apptCrudForm;
      const payload = {
        action: 'create',
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
      const res = await fetch('hcs/appt_api.php', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json'}, credentials:'same-origin', body: JSON.stringify(payload) });
      const text = await res.text();
      let json; try { json = JSON.parse(text); } catch { throw new Error('Server returned non-JSON ('+res.status+').'); }
      if (!res.ok || !json.success) throw new Error(json.message || 'Operation failed');
      alert('Saved successfully');
      location.reload();
    }catch(err){ console.error(err); alert('Error saving data: '+err.message); }
  });

  function field(label, value){
    const safe = (value ?? '').toString();
    return `<div class="rounded-lg border border-gray-200 bg-slate-50 p-3 ${document.documentElement.classList.contains('dark') ? 'dark:border-slate-700 dark:bg-slate-800/60' : ''}">
      <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 ${document.documentElement.classList.contains('dark') ? 'dark:text-gray-400' : ''} mb-1">${label}</div>
      <div class="text-sm text-gray-900 break-words whitespace-pre-wrap ${document.documentElement.classList.contains('dark') ? 'dark:text-gray-100' : ''}">${safe.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
    </div>`;
  }
  function openApptModal(data){
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
  function closeApptModal(){
    const modal = document.getElementById('appt-modal');
    if (!modal) return;
    modal.classList.remove('show');
    modal.classList.add('hidden');
  }

  // init
  apptApply();
</script>
