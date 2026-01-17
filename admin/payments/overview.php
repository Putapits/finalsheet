<?php
// Unified Payments View for Admin
// Path: admin/payments/overview.php

require_once '../include/database.php';

// Prepare initial data (optional, or we can use AJAX for both)
// For simplicity and matching existing patterns, we'll use a mix.
?>
<div class="px-6 py-6" id="payments-module">
    <div
        class="mb-8 rounded-2xl p-8 border border-blue-100 shadow-lg bg-gradient-to-r from-blue-50 via-sky-50 to-indigo-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-slate-100">Unified Payments</h2>
                <p class="text-gray-600 dark:text-slate-300 mt-1 uppercase text-xs font-bold tracking-wider">Financial
                    Transactions Management</p>
            </div>
            <div class="flex items-center gap-3">
                <div
                    class="bg-white/50 dark:bg-slate-800/50 backdrop-blur-md p-1.5 rounded-xl border border-white dark:border-slate-700 flex shadow-inner">
                    <button onclick="switchService('spi')" id="btn-spi"
                        class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 service-btn active">
                        SPI Sanitation
                    </button>
                    <button onclick="switchService('wss')" id="btn-wss"
                        class="px-5 py-2.5 rounded-lg text-sm font-bold transition-all duration-300 service-btn">
                        WSS Wastewater
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div
        class="bg-white dark:bg-slate-900/50 rounded-2xl p-4 mb-6 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Search
                Records</label>
            <div class="relative">
                <input type="text" id="pay-search" placeholder="Search by name, OR#, or establishment..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 transition-all dark:text-white">
                <i class="fas fa-search absolute right-4 top-3.5 text-gray-400"></i>
            </div>
        </div>
        <div class="w-full md:w-48">
            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5 ml-1">Payment
                Status</label>
            <select id="pay-filter-status" onchange="filterPayments()"
                class="w-full bg-slate-50 dark:bg-slate-800 border-none rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                <option value="all">All Statuses</option>
                <option value="for_verification">For Verification</option>
                <option value="paid">Verified / Paid</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="flex items-end h-full mt-auto">
            <button onclick="fetchPayments()"
                class="p-3 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Data Table Container -->
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden min-h-[400px]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                <thead class="bg-slate-50 dark:bg-slate-900/40">
                    <tr id="table-header">
                        <!-- Header injected via JS -->
                    </tr>
                </thead>
                <tbody id="pay-table-body" class="divide-y divide-gray-100 dark:divide-gray-700">
                    <!-- Data injected via JS -->
                </tbody>
            </table>
        </div>
        <div id="no-data-msg" class="hidden py-20 text-center">
            <div class="text-gray-300 dark:text-slate-600 mb-4">
                <i class="fas fa-receipt text-6xl"></i>
            </div>
            <p class="text-gray-500 dark:text-slate-400 font-medium italic">No payment records match your criteria.</p>
        </div>
        <div id="loading-overlay" class="py-20 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent">
            </div>
            <p class="mt-4 text-gray-500 text-sm animate-pulse">Fetching transactions...</p>
        </div>
    </div>
</div>

<!-- Consolidated Payment Modal -->
<div id="paymentModal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[9999] flex items-center justify-center p-4">
    <div
        class="bg-white dark:bg-slate-900 rounded-3xl max-w-4xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col sm:flex-row max-h-[90vh] relative">
        <!-- Close Button (X) -->
        <button onclick="closePaymentModal()"
            class="absolute top-6 right-6 z-50 p-2 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full transition-all text-gray-400 hover:text-gray-900 dark:hover:text-white shadow-sm border border-slate-200 dark:border-slate-700">
            <i class="fas fa-times text-xl"></i>
        </button>
        <!-- Left: Image Preview -->
        <div
            class="w-full sm:w-1/2 bg-slate-100 dark:bg-slate-950 flex flex-col p-6 border-r border-slate-100 dark:border-slate-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Proof of Payment</h3>
            <div class="flex-1 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 flex items-center justify-center relative group overflow-hidden shadow-inner cursor-zoom-in"
                onclick="window.open(document.getElementById('modal-receipt-img').src, '_blank')">
                <img id="modal-receipt-img" src=""
                    class="max-w-full max-h-[500px] object-contain transition-transform group-hover:scale-105">
                <div
                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                    <span
                        class="bg-white/20 backdrop-blur-md text-white px-4 py-2 rounded-full text-xs font-bold border border-white/30">Click
                        to View Full Size</span>
                </div>
                <div id="no-receipt-msg" class="hidden text-gray-400 italic text-center p-8">
                    <i class="fas fa-image-slash text-4xl mb-4 opacity-20"></i>
                    <p>No receipt image attached to this transaction.</p>
                </div>
            </div>
        </div>

        <!-- Right: Details & Actions -->
        <div class="w-full sm:w-1/2 p-8 flex flex-col">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <span id="modal-service-tag"
                        class="px-3 py-1 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-full text-[10px] font-black uppercase tracking-widest mb-2 inline-block">SPI
                        SANITATION</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white" id="modal-ref-id">SR-0000</h3>
                </div>
            </div>

            <div class="space-y-6 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Applicant</p>
                        <p id="modal-applicant" class="font-bold text-gray-900 dark:text-white"></p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Establishment
                        </p>
                        <p id="modal-establishment" class="font-bold text-gray-900 dark:text-white">--</p>
                    </div>
                    <div class="col-span-2">
                        <div
                            class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-700 grid grid-cols-2 gap-4">
                            <div>
                                <p
                                    class="text-[10px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest mb-1">
                                    Amount Paid</p>
                                <p id="modal-amount" class="text-xl font-black text-gray-900 dark:text-white">₱0.00</p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-1">
                                    OR / Reference</p>
                                <p id="modal-or" class="text-xl font-black text-gray-900 dark:text-white">N/A</p>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                <span class="font-bold uppercase opacity-50 block mb-1">Method</span>
                                <span id="modal-method">--</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 text-right">
                                <span class="font-bold uppercase opacity-50 block mb-1">Date Paid</span>
                                <span id="modal-date">--</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="modal-status-box"
                    class="p-4 rounded-2xl text-center text-sm font-black uppercase tracking-widest">
                    --
                </div>
            </div>

            <div id="modal-actions" class="mt-8 space-y-3">
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="updateStatus('rejected')"
                        class="py-3 px-4 border-2 border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 font-bold rounded-2xl transition-all">
                        Reject Payment
                    </button>
                    <button onclick="updateStatus('verified')"
                        class="py-3 px-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/30 transition-all">
                        Verify & Accept
                    </button>
                </div>
            </div>
            <div id="modal-verified-msg"
                class="hidden mt-8 text-center p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 animate-in slide-in-from-bottom-2 duration-300">
                <i class="fas fa-check-circle text-2xl mb-2 text-emerald-500"></i>
                <p class="font-black">TRANSACTION VERIFIED</p>
                <p class="text-xs font-semibold opacity-70">Payment has been officially recorded.</p>
            </div>

            <button onclick="closePaymentModal()"
                class="mt-4 w-full py-3 px-4 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 font-bold rounded-2xl transition-all">
                Close
            </button>
        </div>
    </div>
</div>

<style>
    .service-btn.active {
        background: #3b82f6;
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .service-btn:not(.active) {
        color: #64748b;
    }

    .sidebar-link.active {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-right: 4px solid #3b82f6;
    }
</style>

<script>
    let currentService = 'spi';
    let allPayments = [];
    let currentItem = null;

    function switchService(service) {
        currentService = service;
        document.querySelectorAll('.service-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('btn-' + service).classList.add('active');

        // Update labels and fetch data
        document.getElementById('modal-service-tag').textContent = service === 'spi' ? 'SPI SANITATION' : 'WSS WASTEWATER';
        fetchPayments();
    }

    async function fetchPayments() {
        toggleLoading(true);
        const tbody = document.getElementById('pay-table-body');
        const header = document.getElementById('table-header');

        // Set Headers
        if (currentService === 'spi') {
            header.innerHTML = `
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Establishment / Owner</th>
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">OR# / Amount</th>
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
        `;
        } else {
            header.innerHTML = `
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Ref ID</th>
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Applicant / Service</th>
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount / Method</th>
            <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
            <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
        `;
        }

        try {
            let data = [];
            let res, json;
            if (currentService === 'spi') {
                res = await fetch('api/permits.php?action=list_all_steps&step=payment');
                json = await res.json();
            } else {
                res = await fetch('api/wss.php?action=list_requests&payment_status=all');
                json = await res.json();
            }

            if (json.success) {
                data = json.data || [];
            } else {
                console.error('API Error:', json.message);
                tbody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-red-400 font-bold">API Error: ${json.message || 'Unknown error'}</td></tr>`;
                toggleLoading(false);
                return;
            }

            allPayments = data;
            filterPayments();
        } catch (e) {
            console.error('Fetch Error:', e);
            tbody.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-red-400 font-bold">Failed to connect to the server. Please try refreshing.</td></tr>`;
        } finally {
            toggleLoading(false);
        }
    }

    function filterPayments() {
        const search = document.getElementById('pay-search').value.toLowerCase();
        const status = document.getElementById('pay-filter-status').value;
        const tbody = document.getElementById('pay-table-body');

        const filtered = allPayments.filter(p => {
            // Status mapping
            let pStatus = '';
            if (currentService === 'spi') {
                pStatus = p.status === 'completed' ? 'paid' : (p.status === 'rejected' ? 'rejected' : 'for_verification');
            } else {
                pStatus = p.payment_status; // paid, for_verification, rejected, unpaid
            }

            const matchesStatus = status === 'all' || pStatus === status;

            let searchText = '';
            if (currentService === 'spi') {
                const details = JSON.parse(p.details || '{}');
                searchText = `${p.id} ${p.establishment_name || ''} ${p.first_name || ''} ${p.last_name || ''} ${details.or_number || ''}`.toLowerCase();
            } else {
                searchText = `${p.id} ${p.full_name || p.citizen_name || ''} ${p.service_details || ''} ${p.service_type || ''}`.toLowerCase();
            }

            const matchesSearch = searchText.includes(search);

            return matchesStatus && matchesSearch;
        });

        renderTable(filtered);
    }

    function renderTable(data) {
        const tbody = document.getElementById('pay-table-body');
        const noData = document.getElementById('no-data-msg');

        if (data.length === 0) {
            tbody.innerHTML = '';
            noData.classList.remove('hidden');
            return;
        }

        noData.classList.add('hidden');

        tbody.innerHTML = data.map(p => {
            if (currentService === 'spi') {
                const details = JSON.parse(p.details || '{}');
                const status = p.status === 'completed' ? 'paid' : (p.status === 'rejected' ? 'rejected' : 'for_verification');
                return renderRow(p.id, p.establishment_name, `${p.first_name} ${p.last_name}`, details.or_number, details.amount, status, p);
            } else {
                // WSS Row
                const details = parseWSSDetails(p.service_details);
                const name = p.full_name || p.citizen_name || 'Unknown Applicant';
                return renderRow(p.id, name, (p.service_type || '').replace(/-/g, ' '), details.or_number, details.amount, p.payment_status, p);
            }
        }).join('');
    }


    function renderRow(id, title, subtitle, or, amount, status, raw) {
        const statusMeta = {
            'paid': { label: 'VERIFIED', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' },
            'for_verification': { label: 'FOR REVIEW', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 animate-pulse' },
            'rejected': { label: 'REJECTED', class: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300' },
            'unpaid': { label: 'UNPAID', class: 'bg-gray-100 text-gray-500' }
        };
        const meta = statusMeta[status] || { label: status, class: 'bg-gray-100 text-gray-800' };

        return `
        <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-all duration-200 cursor-pointer group" onclick='openModal(${id})'>
            <td class="px-6 py-5 whitespace-nowrap text-xs font-mono font-black text-blue-600">SR-${id}</td>
            <td class="px-6 py-5">
                <div class="text-sm font-black text-gray-900 dark:text-white capitalize">${title}</div>
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">${subtitle}</div>
            </td>
            <td class="px-6 py-5">
                <div class="text-xs font-black text-gray-900 dark:text-white">₱${parseFloat(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</div>
                <div class="text-[10px] font-mono text-gray-400 mt-1 uppercase">OR: ${or || 'N/A'}</div>
            </td>
            <td class="px-6 py-5">
                <span class="px-3 py-1 text-[10px] font-black rounded-full uppercase tracking-tighter ${meta.class}">
                    ${meta.label}
                </span>
            </td>
            <td class="px-6 py-5 text-right">
                <button class="px-4 py-2 bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-600 hover:text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-2 ml-auto shadow-sm border border-blue-100 dark:border-blue-800/50">
                    <i class="fas fa-eye text-xs"></i>
                    <span>View Details</span>
                </button>
            </td>
        </tr>
    `;
    }

    function parseWSSDetails(detailsText) {
        const details = { amount: '0', or_number: 'N/A', method: 'N/A', date: 'N/A', image: '' };
        if (!detailsText) return details;

        const lines = detailsText.split('\n');
        lines.forEach(l => {
            const parts = l.split(':');
            if (parts.length >= 2) {
                const key = parts[0].trim().toLowerCase();
                const val = parts.slice(1).join(':').trim();
                if (key.includes('amount')) details.amount = val.replace(/[^0-9.]/g, '');
                if (key.includes('number') || key.includes('reference')) details.or_number = val;
                if (key.includes('method')) details.method = val;
                if (key.includes('date')) details.date = val;
                if (val.includes('uploads/')) details.image = val;
            }
        });
        return details;
    }

    function toggleLoading(isLoading) {
        document.getElementById('loading-overlay').classList.toggle('hidden', !isLoading);
        document.getElementById('pay-table-body').parentElement.classList.toggle('opacity-30', isLoading);
    }

    function openModal(id) {
        const item = allPayments.find(p => p.id == id);
        if (!item) return;
        currentItem = item;

        const modal = document.getElementById('paymentModal');
        const receiptImg = document.getElementById('modal-receipt-img');
        const noReceipt = document.getElementById('no-receipt-msg');

        // Values
        let applicant = '', establishment = '', amount = '0', or = 'N/A', method = 'N/A', date = 'N/A', image = '', status = '';

        if (currentService === 'spi') {
            applicant = `${item.first_name} ${item.last_name}`;
            establishment = item.establishment_name;
            const d = JSON.parse(item.details || '{}');
            amount = d.amount;
            or = d.or_number;
            method = d.payment_method;
            date = d.payment_date;
            image = d.payment_receipt_image;
            status = item.status === 'completed' ? 'paid' : (item.status === 'rejected' ? 'rejected' : 'for_verification');
        } else {
            applicant = item.full_name;
            establishment = item.service_type.replace(/-/g, ' ');
            const d = parseWSSDetails(item.service_details);
            amount = d.amount;
            or = d.or_number;
            method = d.method;
            date = d.date;
            image = d.image;
            status = item.payment_status;
        }

        document.getElementById('modal-ref-id').textContent = 'SR-' + id;
        document.getElementById('modal-applicant').textContent = applicant;
        document.getElementById('modal-establishment').textContent = establishment;
        document.getElementById('modal-amount').textContent = '₱' + parseFloat(amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
        document.getElementById('modal-or').textContent = or || 'N/A';
        document.getElementById('modal-method').textContent = method || 'N/A';
        document.getElementById('modal-date').textContent = date || 'N/A';

        if (image) {
            const ext = image.split('.').pop().toLowerCase();
            const fullPath = '../' + image;
            
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                receiptImg.src = fullPath;
                receiptImg.classList.remove('hidden');
                noReceipt.classList.add('hidden');
                // Remove any existing iframe
                const existingFrame = document.getElementById('modal-receipt-frame');
                if (existingFrame) existingFrame.remove();
            } else if (ext === 'pdf') {
                receiptImg.classList.add('hidden');
                noReceipt.classList.add('hidden');
                
                let frame = document.getElementById('modal-receipt-frame');
                if (!frame) {
                    frame = document.createElement('iframe');
                    frame.id = 'modal-receipt-frame';
                    frame.className = 'w-full h-full min-h-[400px] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-inner';
                    receiptImg.parentElement.appendChild(frame);
                }
                frame.src = fullPath;
            } else {
                // For other files (.md, .pdf, etc that might be links)
                receiptImg.classList.add('hidden');
                noReceipt.innerHTML = `
                    <div class="text-center p-8 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                        <i class="fas fa-file-alt text-4xl mb-4 text-blue-500 opacity-50"></i>
                        <p class="text-gray-600 dark:text-gray-400 font-bold mb-4 uppercase tracking-wider text-xs">${ext.toUpperCase()} Document Attached</p>
                        <a href="${fullPath}" target="_blank" class="inline-block px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-blue-500/20 transition-all">
                            Open File in New Tab
                        </a>
                    </div>
                `;
                noReceipt.classList.remove('hidden');
                const existingFrame = document.getElementById('modal-receipt-frame');
                if (existingFrame) existingFrame.remove();
            }
        } else {
            receiptImg.classList.add('hidden');
            noReceipt.innerHTML = `
                <i class="fas fa-image-slash text-4xl mb-4 opacity-20"></i>
                <p>No receipt image attached to this transaction.</p>
            `;
            noReceipt.classList.remove('hidden');
            const existingFrame = document.getElementById('modal-receipt-frame');
            if (existingFrame) existingFrame.remove();
        }

        // Status UI
        const statusBox = document.getElementById('modal-status-box');
        const actions = document.getElementById('modal-actions');
        const verifiedMsg = document.getElementById('modal-verified-msg');

        if (status === 'for_verification') {
            statusBox.textContent = 'STATUS: PENDING VERIFICATION';
            statusBox.className = 'p-4 rounded-2xl text-center text-sm font-black uppercase tracking-widest bg-blue-50 text-blue-600 animate-pulse';
            actions.classList.remove('hidden');
            verifiedMsg.classList.add('hidden');
        } else {
            if (status === 'paid') {
                statusBox.textContent = 'STATUS: VERIFIED';
                statusBox.className = 'p-4 rounded-2xl text-center text-sm font-black uppercase tracking-widest bg-emerald-50 text-emerald-600';
                verifiedMsg.classList.remove('hidden');
                verifiedMsg.querySelector('p').textContent = 'TRANSACTION VERIFIED';
                verifiedMsg.className = 'mt-8 text-center p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100';
            } else {
                statusBox.textContent = 'STATUS: REJECTED';
                statusBox.className = 'p-4 rounded-2xl text-center text-sm font-black uppercase tracking-widest bg-rose-50 text-rose-600';
                verifiedMsg.classList.remove('hidden');
                verifiedMsg.querySelector('p').textContent = 'PAYMENT REJECTED';
                verifiedMsg.className = 'mt-8 text-center p-4 bg-rose-50 text-rose-700 rounded-2xl border border-rose-100';
            }
            actions.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        currentItem = null;
    }

    async function updateStatus(decision) {
        if (!currentItem) return;
        const confirmMsg = `Are you sure you want to ${decision === 'verified' ? 'ACCEPT' : 'REJECT'} this payment?`;
        if (!confirm(confirmMsg)) return;

        try {
            let res;
            if (currentService === 'spi') {
                const status = decision === 'verified' ? 'completed' : 'rejected';
                res = await fetch('api/permits.php?action=update_step_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ step_id: currentItem.id, status: status })
                });
            } else {
                const action = decision === 'verified' ? 'verify' : 'reject';
                res = await fetch('api/wss.php?action=review_request', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: currentItem.id, payment_action: action })
                });
            }

            const json = await res.json();
            if (json.success) {
                closePaymentModal();
                fetchPayments();
            } else {
                alert(json.message || 'Error updating payment.');
            }
        } catch (e) {
            alert('Server connection error. Please try again.');
        }
    }

    // Global search listener
    document.getElementById('pay-search').addEventListener('input', filterPayments);

    // Handle pre-selected service from URL
    const urlParams = new URLSearchParams(window.location.search);
    const preSelected = urlParams.get('service');
    if (preSelected && (preSelected === 'spi' || preSelected === 'wss')) {
        switchService(preSelected);
    } else {
        switchService('spi');
    }
</script>