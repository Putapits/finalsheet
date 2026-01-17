<?php
// Wastewater - Inspector Assignment
// Path: admin/wss/assignment.php

// Helper to filter by assignment status
$viewMode = $_GET['mode'] ?? 'unassigned'; // unassigned vs all
?>
<div class="px-6 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Inspector Assignment</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Allocate field inspectors to verified service requests.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <select id="wss-assign-filter" onchange="loadAssignmentTable()"
                class="rounded-lg border-gray-300 dark:border-gray-600 text-sm p-2 dark:bg-gray-700 dark:text-white">
                <option value="unassigned" selected>Unassigned (Paid)</option>
                <option value="assigned">Assigned Tasks</option>
                <option value="all">All Requests</option>
            </select>
            <button onclick="loadAssignmentTable()"
                class="p-2 text-gray-500 hover:text-blue-600 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Assignment Table -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ref ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Service
                            Details</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Location</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Preferred Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Assigned Inspector</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action
                        </th>
                    </tr>
                </thead>
                <tbody id="wss-assign-body"
                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Loading requests...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Assign Inspector Modal -->
<div id="wss-assign-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAssignModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Assign Inspector</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Select an available inspector for Request <span
                        id="assign-ref-id" class="font-mono font-bold text-blue-600">SR-000</span>.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Available
                            Inspectors</label>
                        <select id="assign-inspector-select"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                            <option value="">Loading inspectors...</option>
                        </select>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                        <p class="text-xs text-blue-700 dark:text-blue-300 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Assigning an inspector will automatically move this request to <b>In Progress</b>
                                status.</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/30 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" onclick="confirmAssignment()"
                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm Assignment
                </button>
                <button type="button" onclick="closeAssignModal()"
                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let wssRequests = [];
    let targetRequestId = null;
    let inspectorsList = [];

    async function loadInspectors() {
        try {
            const res = await fetch('api/wss.php?action=list_inspectors');
            const json = await res.json();
            if (json.success) {
                inspectorsList = json.data;
            }
        } catch (e) {
            console.error("Failed to load inspectors", e);
        }
    }

    async function loadAssignmentTable() {
        const filter = document.getElementById('wss-assign-filter').value;
        const tbody = document.getElementById('wss-assign-body');
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">Loading requests...</td></tr>';

        try {
            // Fetch All Requests then filter locally for complex logic
            // "Unassigned" means: assigned_inspector_id IS NULL AND payment_status = 'paid' (or just show all unassigned?)
            // Let's assume we want to assign Paid items generally.

            const res = await fetch('api/wss.php?action=list_requests'); // Fetch all
            const json = await res.json();

            if (json.success) {
                let data = json.data;

                // Apply Filters Client Side
                if (filter === 'unassigned') {
                    // Show items that need assignment: Any Unassigned (excluding closed cases)
                    data = data.filter(r => (!r.assigned_inspector_id && r.status !== 'completed' && r.status !== 'cancelled'));
                } else if (filter === 'assigned') {
                    data = data.filter(r => r.assigned_inspector_id);
                }

                wssRequests = data;
                renderAssignmentTable(data);
            } else {
                tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Error: ${json.message}</td></tr>`;
            }
        } catch (e) {
            console.error(e);
            tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Failed to load data.</td></tr>`;
        }
    }

    function renderAssignmentTable(data) {
        const tbody = document.getElementById('wss-assign-body');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No requests matching filter.</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(r => {
            const inspectorName = r.inspector_name ? `<span class="flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-300"><span class="text-xs">👮</span> ${r.inspector_name}</span>` : '<span class="text-gray-400 italic">Not Assigned</span>';

            let actionBtn = '';
            if (!r.assigned_inspector_id) {
                actionBtn = `<button onclick="openAssignModal(${r.id})" class="text-blue-600 hover:text-blue-900 font-bold text-xs uppercase bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-full transition-colors border border-blue-200">Assign Action</button>`;
            } else {
                actionBtn = `<button onclick="openAssignModal(${r.id})" class="text-gray-500 hover:text-gray-700 font-medium text-xs uppercase">Re-Assign</button>`;
            }

            // Status Badge logic
            const pStatus = r.payment_status || 'unpaid';
            let pBadge = '';
            if(pStatus === 'paid') pBadge = '<span class="text-[10px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded uppercase font-bold ml-2">PAID</span>';
            else if(pStatus === 'for_verification') pBadge = '<span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded uppercase font-bold ml-2 animate-pulse">VERIFY PAY</span>';
            else pBadge = '<span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded uppercase font-bold ml-2">UNPAID</span>';

            return `
        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-600 dark:text-gray-400">SR-${r.id}</td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="text-sm font-bold text-gray-900 dark:text-white capitalize">${r.service_type.replace(/-/g, ' ')}</div>
                    ${pBadge}
                </div>
                <div class="text-xs text-gray-500">${r.full_name}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-xs" title="${r.address}">${r.address}</div>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-700 dark:text-gray-300">${r.preferred_date || '<span class="text-gray-400">Anytime</span>'}</div>
            </td>
            <td class="px-6 py-4">
                ${inspectorName}
            </td>
            <td class="px-6 py-4 text-right">
                ${actionBtn}
            </td>
        </tr>
        `;
        }).join('');
    }

    function openAssignModal(id) {
        targetRequestId = id;
        const req = wssRequests.find(r => r.id == id);
        if (!req) return;

        document.getElementById('assign-ref-id').textContent = 'SR-' + id;

        // Populate Select
        const select = document.getElementById('assign-inspector-select');
        select.innerHTML = '<option value="">Select Inspector...</option>';

        inspectorsList.forEach(insp => {
            const isSel = (req.assigned_inspector_id == insp.id) ? 'selected' : '';
            select.innerHTML += `<option value="${insp.id}" ${isSel}>${insp.first_name} ${insp.last_name}</option>`;
        });

        document.getElementById('wss-assign-modal').classList.remove('hidden');
    }

    function closeAssignModal() {
        document.getElementById('wss-assign-modal').classList.add('hidden');
        targetRequestId = null;
    }

    async function confirmAssignment() {
        const inspId = document.getElementById('assign-inspector-select').value;
        if (!inspId) {
            alert('Please select an inspector.');
            return;
        }

        try {
            const res = await fetch('api/wss.php?action=review_request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: targetRequestId,
                    inspector_id: inspId
                })
            });
            const json = await res.json();
            if (json.success) {
                closeAssignModal();
                loadAssignmentTable(); // Refresh
                // alert('Inspector assigned successfully.');
            } else {
                alert(json.message);
            }
        } catch (e) {
            alert('Error: ' + e);
        }
    }

    // Init
    loadInspectors().then(() => {
        loadAssignmentTable();
    });

</script>