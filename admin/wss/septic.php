<div class="p-6">
  <div class="flex justify-between items-center mb-6">
    <div>
      <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Wastewater & Septic Requests</h2>
      <p class="text-sm text-gray-500 dark:text-gray-400">Manage septic registrations, desludging, and clearances.</p>
    </div>
    <div class="flex gap-2">
      <button onclick="loadWSSRequests()" class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>
  </div>

  <!-- Filters -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <select id="wss-filter-status" onchange="loadWSSRequests()"
      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 text-sm">
      <option value="all">All Statuses</option>
      <option value="pending">Pending</option>
      <option value="in_progress">In Progress</option>
      <option value="completed">Completed</option>
      <option value="cancelled">Cancelled</option>
    </select>
    <select id="wss-filter-payment" onchange="loadWSSRequests()"
      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 text-sm">
      <option value="all">Payment Status</option>
      <option value="unpaid">Unpaid</option>
      <option value="for_verification">For Verification</option>
      <option value="paid">Paid</option>
    </select>
    <select id="wss-filter-type" onchange="loadWSSRequests()"
      class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-2 text-sm">
      <option value="all">All Service Types</option>
      <option value="septic-registration">Septic Registration</option>
      <option value="maintenance-service">Desludging/Maintenance</option>
      <option value="system-inspection">System Inspection</option>
      <option value="wastewater-clearance">Clearance</option>
    </select>
    <div class="relative">
      <input type="text" id="wss-search" onkeyup="filterWSSRows()" placeholder="Search name/ref..."
        class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg pl-10 pr-4 py-2 text-sm">
      <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>
  </div>

  <!-- Data Table -->
  <div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead>
          <tr
            class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-bold uppercase text-xs tracking-wider">
            <th class="px-6 py-4">Ref #</th>
            <th class="px-6 py-4">Citizen</th>
            <th class="px-6 py-4">Service Type</th>
            <th class="px-6 py-4">Payment</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4">Date</th>
            <th class="px-6 py-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody id="wss-table-body" class="divide-y divide-gray-100 dark:divide-gray-700">
          <!-- Rows populated by JS -->
          <tr>
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading requests...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Review Modal -->
<div id="wss-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
  aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="closeWSSModal()"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div
      class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100 dark:border-gray-700">

      <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
            <h3 class="text-xl leading-6 font-bold text-gray-900 dark:text-white mb-1" id="wss-modal-title">
              Request Review
            </h3>
            <p class="text-sm text-gray-500 mb-6" id="wss-modal-subtitle">Review details and attachments.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Left: Details -->
              <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl space-y-3 max-h-96 overflow-y-auto"
                id="wss-modal-details">
                <!-- Details populated here -->
              </div>

              <!-- Right: Actions & Uploads -->
              <div class="space-y-6">
                <!-- Attachments Preview -->
                <div>
                  <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Attachments</h4>
                  <div id="wss-modal-files" class="grid grid-cols-2 gap-2">
                    <!-- File thumbnails -->
                    <p class="text-sm text-gray-400 col-span-2 italic">No attachments found.</p>
                  </div>
                </div>

                <!-- Payment Action -->
                <div id="wss-payment-action-box"
                  class="hidden p-4 border border-blue-100 dark:border-blue-900/30 bg-blue-50 dark:bg-blue-900/10 rounded-xl">
                  <div class="flex items-center gap-3 mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h4 class="text-sm font-bold text-blue-700 dark:text-blue-300">Payment Verification</h4>
                  </div>
                  <p class="text-xs text-blue-600/80 mb-3">Receipt uploaded. Verify amount and reference.</p>
                  <div class="flex gap-2">
                    <button onclick="updateWSSRequest('verify_payment')"
                      class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded-lg transition">Verify
                      Payment</button>
                    <button onclick="updateWSSRequest('reject_payment')"
                      class="flex-1 bg-white text-rose-600 border border-rose-200 hover:bg-rose-50 text-xs font-bold py-2 rounded-lg transition">Reject</button>
                  </div>
                </div>
                
                <!-- Inspector Assignment Action (Visible if Paid) -->
                <div id="wss-inspector-box" class="hidden p-4 border border-purple-100 bg-purple-50 dark:bg-purple-900/10 dark:border-purple-900/30 rounded-xl">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <h4 class="text-sm font-bold text-purple-700 dark:text-purple-300">Assign Inspector</h4>
                    </div>
                    <select id="wss-inspector-select" class="w-full text-xs border-purple-200 rounded-lg mb-2 p-2 dark:bg-gray-800 dark:border-gray-600">
                        <option value="">Select Inspector...</option>
                    </select>
                    <button onclick="updateWSSRequest('assign_inspector')" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold py-2 rounded-lg transition">Assign & Process</button>
                </div>

                <!-- Final Decision -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                  <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Decision Remarks</label>
                  <textarea id="wss-remarks" rows="3"
                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 focus:border-blue-500 mb-3"
                    placeholder="Enter findings, compliance notes, or rejection reason..."></textarea>

                  <div class="grid grid-cols-2 gap-3">
                    <button onclick="updateWSSRequest('completed')"
                      class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl text-sm shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02]">
                      Approve / Clear
                    </button>
                    <button onclick="updateWSSRequest('cancelled')"
                      class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 font-bold py-2.5 rounded-xl text-sm transition-all">
                      Reject
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" onclick="closeWSSModal()"
          class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-gray-200 text-base font-medium text-gray-700 hover:bg-gray-300 sm:ml-3 sm:w-auto sm:text-sm">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  let wssData = [];
  let currentReqId = null;
  let inspectorsLoaded = false;

  async function loadInspectors() {
      if (inspectorsLoaded) return;
      try {
          const res = await fetch('api/wss.php?action=list_inspectors');
          const json = await res.json();
          if (json.success) {
              const sel = document.getElementById('wss-inspector-select');
              sel.innerHTML = '<option value="">Select Inspector...</option>' + 
                  json.data.map(i => `<option value="${i.id}">${i.first_name} ${i.last_name}</option>`).join('');
              inspectorsLoaded = true;
          }
      } catch(e) { console.error('Failed to load inspectors', e); }
  }

  async function loadWSSRequests() {
    const status = document.getElementById('wss-filter-status').value;
    const payment = document.getElementById('wss-filter-payment').value;
    const type = document.getElementById('wss-filter-type').value;

    const tbody = document.getElementById('wss-table-body');
    tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading...</td></tr>';

    try {
      const res = await fetch(`api/wss.php?action=list_requests&status=${status}&payment_status=${payment}&type=${type}`);
      const json = await res.json();

      if (json.success) {
        wssData = json.data;
        renderWSSRows(wssData);
        // Preload inspectors
        loadInspectors();
      } else {
        tbody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Error: ${json.message}</td></tr>`;
      }
    } catch (e) {
      console.error(e);
      tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Failed to load data</td></tr>';
    }
  }

  function renderWSSRows(data) {
    const tbody = document.getElementById('wss-table-body');
    if (!data.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 italic">No requests found matching criteria.</td></tr>';
      return;
    }

    tbody.innerHTML = data.map(row => {
      const sClass = {
        'completed': 'bg-emerald-100 text-emerald-800',
        'pending': 'bg-amber-100 text-amber-800',
        'cancelled': 'bg-rose-100 text-rose-800',
        'in_progress': 'bg-blue-100 text-blue-800'
      }[row.status] || 'bg-gray-100 text-gray-800';

      const pClass = {
        'paid': 'text-emerald-600 font-bold',
        'for_verification': 'text-blue-600 font-bold animate-pulse',
        'unpaid': 'text-gray-400'
      }[row.payment_status] || 'text-gray-400';

      const typeLabel = row.service_type.replace(/-/g, ' ').toUpperCase();
      const inspectorLabel = row.inspector_name ? `<div class="text-xs text-purple-600 font-bold mt-1">👮 ${row.inspector_name}</div>` : '';

      return `
            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                <td class="px-6 py-4 font-mono text-xs text-gray-500">SR-${row.id}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="ml-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">${row.full_name}</div>
                            <div class="text-xs text-gray-500">${row.email}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-xs font-bold text-gray-600 dark:text-gray-300">
                    ${typeLabel}
                </td>
                <td class="px-6 py-4 text-xs ${pClass}">
                    ${row.payment_status.replace(/_/g, ' ').toUpperCase()}
                </td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${sClass}">
                        ${row.status.toUpperCase()}
                    </span>
                    ${inspectorLabel}
                </td>
                <td class="px-6 py-4 text-xs text-gray-500">${new Date(row.created_at).toLocaleDateString()}</td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                    <button onclick="openWSSModal(${row.id})" class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">Review</button>
                </td>
            </tr>
        `;
    }).join('');
  }

  function filterWSSRows() {
    const q = document.getElementById('wss-search').value.toLowerCase();
    const filtered = wssData.filter(d =>
      d.full_name.toLowerCase().includes(q) ||
      d.email.toLowerCase().includes(q) ||
      ('SR-' + d.id).toLowerCase().includes(q)
    );
    renderWSSRows(filtered);
  }

  function openWSSModal(id) {
    const req = wssData.find(r => r.id == id);
    if (!req) return;
    currentReqId = id;

    document.getElementById('wss-modal-title').innerText = req.service_type.replace(/-/g, ' ').toUpperCase();
    document.getElementById('wss-modal-subtitle').innerText = `Request from ${req.full_name} • ${new Date(req.created_at).toDateString()}`;
    document.getElementById('wss-remarks').value = req.status_remarks || '';

    // Show/Hide Payment Verification Box
    const payBox = document.getElementById('wss-payment-action-box');
    const inspBox = document.getElementById('wss-inspector-box');
    
    if (req.payment_status === 'for_verification') {
        payBox.classList.remove('hidden');
        inspBox.classList.add('hidden');
    } else if (req.payment_status === 'paid' && req.status !== 'completed' && req.status !== 'cancelled') {
        // If paid but not completed, allow assignment
        payBox.classList.add('hidden');
        inspBox.classList.remove('hidden');
        // Preset select if assigned
        document.getElementById('wss-inspector-select').value = req.assigned_inspector_id || '';
    } else {
        payBox.classList.add('hidden');
        inspBox.classList.add('hidden');
    }

    // Parse Details & Attachments
    const detContainer = document.getElementById('wss-modal-details');
    const fileContainer = document.getElementById('wss-modal-files');
    let detailsHtml = '';
    let filesHtml = '';

    // Main text
    const lines = (req.service_details || '').split('\n');
    lines.forEach(line => {
      if (!line.trim()) return;
      // Check for attachment pattern (assuming stored as "Label: uploads/...")
      // Regex to find "Label: ...uploads/..." or just paths
      if (line.includes('uploads/')) {
        const parts = line.split(':');
        const label = parts[0] || 'Attachment';
        const path = parts.slice(1).join(':').trim(); // in case path has colons (unlikely for relative)

        // Clean path
        const cleanPath = path.match(/uploads\/.*\.[a-zA-Z0-9]+/);
        if (cleanPath) {
          const fullUrl = '../' + cleanPath[0]; // Assuming admin is in admin/, uploads in root
          const ext = fullUrl.split('.').pop().toLowerCase();
          const icon = ['png', 'jpg', 'jpeg', 'gif'].includes(ext) ? fullUrl : 'img/file-icon.png';

          // Add to file grid
          filesHtml += `
                    <a href="${fullUrl}" target="_blank" class="block group relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                        ${['png', 'jpg', 'jpeg', 'gif'].includes(ext)
              ? `<img src="${fullUrl}" class="w-full h-full object-cover group-hover:scale-110 transition">`
              : `<div class="flex items-center justify-center h-full text-xs font-bold text-gray-500">${ext.toUpperCase()}</div>`
            }
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition">
                            <span class="text-white text-xs font-bold">View</span>
                        </div>
                        <div class="absolute bottom-0 inset-x-0 bg-white/90 p-1 text-[10px] truncate text-center">${label}</div>
                    </a>
                `;
          return; // Don't show in details text list
        }
      }
      // Normal Detail Line
      detailsHtml += `<div class="text-sm text-gray-700 dark:text-gray-300 border-b border-gray-100 dark:border-gray-700 pb-1 mb-1 last:border-0">${line}</div>`;
    });

    // Basic fields
    detailsHtml = `
        <div class="text-xs font-bold text-gray-400 uppercase mb-2">Core Information</div>
        <div class="grid grid-cols-2 gap-2 mb-4">
            <div><span class="text-xs text-gray-500">Phone:</span> <div class="text-sm font-medium">${req.phone}</div></div>
            <div><span class="text-xs text-gray-500">Urgency:</span> <div class="text-sm font-medium uppercase ${req.urgency === 'emergency' ? 'text-red-600' : ''}">${req.urgency}</div></div>
            <div class="col-span-2"><span class="text-xs text-gray-500">Address:</span> <div class="text-sm font-medium">${req.address}</div></div>
            ${req.preferred_date ? `<div class="col-span-2"><span class="text-xs text-gray-500">Preferred Date:</span> <div class="text-sm font-medium text-blue-600">${new Date(req.preferred_date).toDateString()}</div></div>` : ''}
        </div>
        <div class="text-xs font-bold text-gray-400 uppercase mb-2">Additional Data</div>
        ${detailsHtml || '<p class="text-gray-400 italic">No additional text details.</p>'}
    `;

    if (!filesHtml) filesHtml = '<p class="text-gray-400 col-span-2 italic text-xs py-4 text-center">No attachments found in this request.</p>';

    detContainer.innerHTML = detailsHtml;
    fileContainer.innerHTML = filesHtml;

    document.getElementById('wss-modal').classList.remove('hidden');
  }

  function closeWSSModal() {
    document.getElementById('wss-modal').classList.add('hidden');
    currentReqId = null;
  }

  async function updateWSSRequest(actionType) {
    if (!currentReqId) return;

    let payload = { id: currentReqId };
    const remarks = document.getElementById('wss-remarks').value;

    if (actionType === 'verify_payment') {
        payload.payment_action = 'verify';
    } else if (actionType === 'reject_payment') {
        payload.payment_action = 'reject';
    } else if (actionType === 'assign_inspector') {
        const inspId = document.getElementById('wss-inspector-select').value;
        if (!inspId) { alert('Please select an inspector first.'); return; }
        payload.inspector_id = inspId;
    } else if (actionType === 'completed' || actionType === 'cancelled') {
        payload.status = actionType;
        payload.remarks = remarks;
    }

    // Confirmation
    if (!confirm('Are you sure you want to proceed with this action?')) return;

    try {
      const res = await fetch('api/wss.php?action=review_request', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (json.success) {
        closeWSSModal();
        loadWSSRequests(); // refresh table
        // alert('Update successful'); // Optional, better to use toast if available
      } else {
        alert(json.message);
      }
    } catch (e) {
      alert('Action failed: ' + e);
    }
  }

  // Init
  loadWSSRequests();
</script>