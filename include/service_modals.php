<?php
/**
 * Shared Service Modals
 * This file contains the HTML and JavaScript for service-related modals.
 * Included in: services.php, health_center_services.php, sanitation_permit.php, etc.
 */

// Pre-fill user data if logged in
if ($isLoggedIn && !isset($__u)) {
    $__u = $database->getUserById($_SESSION['user_id']);
}

$u_fullname = $isLoggedIn && isset($__u) ? (($__u['first_name'] ?? '') . ' ' . ($__u['last_name'] ?? '')) : '';
$u_email = $isLoggedIn && isset($__u) ? ($__u['email'] ?? '') : '';
$u_phone = $isLoggedIn && isset($__u) ? ($__u['phone'] ?? '') : '';
$u_address = $isLoggedIn && isset($__u) ? ($__u['address'] ?? '') : '';
?>

<style>
    .qc-dashed-border {
        border-bottom: 1px dashed #9ca3af;
    }

    .qc-input-focus:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    #serviceModal .rounded-sm {
        border-radius: 4px;
    }

    /* Ensure the modal is wide on desktop but responsive */
    @media (min-width: 1024px) {
        .max-w-4xl {
            max-width: 850px;
        }
    }
</style>

<!-- Health Center Info Modal -->
<div id="healthCenterInfoModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-[10003] flex items-center justify-center p-4">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl max-w-6xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Modal Header -->
        <div
            class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    Quezon City Health Centers
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Directory of health services, contact
                    information, and medical staff by district.</p>
            </div>
            <button onclick="closeHealthCenterModal()"
                class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-8 bg-white dark:bg-gray-900">
            <?php
            // Use dynamic path detection for health_centers_data.php
            $healthDataPath = file_exists(__DIR__ . '/health_centers_data.php')
                ? __DIR__ . '/health_centers_data.php'
                : dirname(__DIR__) . '/include/health_centers_data.php';

            if (file_exists($healthDataPath)) {
                include $healthDataPath;
                foreach ($health_centers_by_district as $district => $centers):
                    ?>
                    <div class="district-section">
                        <h3
                            class="text-xl font-bold text-blue-600 dark:text-blue-400 mb-4 border-b-2 border-blue-100 dark:border-blue-900 pb-2 flex items-center gap-2">
                            <span
                                class="bg-blue-600 text-white px-3 py-0.5 rounded-full text-sm"><?php echo htmlspecialchars($district); ?></span>
                        </h3>
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-sm text-left">
                                <thead
                                    class="bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">Health Center
                                        </th>
                                        <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">Contact Number
                                        </th>
                                        <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">Email Address
                                        </th>
                                        <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">Physician</th>
                                        <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">Address</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php foreach ($centers as $center): ?>
                                        <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/20 transition-colors">
                                            <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($center['name']); ?>
                                            </td>
                                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                                                <?php echo !empty($center['contact']) ? htmlspecialchars($center['contact']) : '<span class="text-gray-400 italic">Not available</span>'; ?>
                                            </td>
                                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                                                <?php if (!empty($center['email'])): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($center['email']); ?>"
                                                        class="text-blue-500 hover:underline"><?php echo htmlspecialchars($center['email']); ?></a>
                                                <?php else: ?>
                                                    <span class="text-gray-400 italic">Not available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                                                <?php echo htmlspecialchars($center['physician']); ?>
                                            </td>
                                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300 text-xs leading-relaxed">
                                                <?php echo htmlspecialchars($center['address']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php
                endforeach;
            } else {
                echo '<p class="text-red-500 text-center py-8">Error: Health center directory data not found.</p>';
            }
            ?>
        </div>

        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end">
            <button onclick="closeHealthCenterModal()"
                class="px-6 py-2 bg-gray-800 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-900 transition-colors">
                Close Directory
            </button>
        </div>
    </div>
</div>

<!-- Data Privacy Policy Modal -->
<div id="privacyPolicyModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden z-[10005] flex items-center justify-center p-4 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-sm max-w-2xl w-full shadow-2xl flex flex-col max-h-[85vh] animate-in fade-in zoom-in duration-300">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 uppercase tracking-tight">Data Privacy Policy
            </h3>
            <button onclick="closePrivacyModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div
            class="p-8 overflow-y-auto custom-scrollbar text-sm text-gray-700 dark:text-gray-300 leading-relaxed space-y-4">
            <p class="font-bold">DATA PRIVACY POLICY</p>
            <p class="font-bold">PRIVACY POLICY</p>
            <p>1. DATA USAGE OVERVIEW</p>
            <p>Protecting, securing, and maintaining the information processed and handled through the GoServe platform
                is one
                of our top priorities, and it should be yours too. This section describes our respective obligations
                when handling and storing information connected with the GoServe platform. The following terms used in
                this
                section relate to data provided to us by you or other end-users, or received or accessed by you through
                your use of the GoServe platform:</p>
            <p>'PERSONAL DATA' means any information, whether true or not, that is related to a person, that can be used
                to specifically identify that person or not, and is transmitted or accessible through the GoServe
                platform.
            </p>
            <p>'USER DATA' means any information that describes your business and its operations, your products or
                services. HSM-Services DATA' means details of the web system and mobile application transactions over
                HSM-Services infrastructure, information used in fraud detection and analysis, aggregated or anonymized
                information generated from data collected, and any other information created by or originating from the
                Services.</p>
            <p>The term 'DATA' used without a modifier means all Personal Data, User Data, and HSM-Services data.</p>
            <p>We process, analyze, and manage data to:</p>
            <p>a) Provide GoServe services to you, other end-users, and other users of the GoServe platform;</p>
            <p>b) Adhere to applicable laws and government regulations;</p>
            <p>c) Facilitate your transactions and requests effectively.</p>
            <p>By using this service, you agree to the collection and use of information in accordance with this policy
                as mandated by the <span class="font-bold">Data Privacy Act of 2012</span>.</p>
        </div>
        <div class="p-6 border-t border-gray-100 dark:border-gray-700">
            <button onclick="closePrivacyModal()"
                class="w-full bg-blue-600 text-white font-bold py-2 rounded-sm uppercase text-xs tracking-widest">Close</button>
        </div>
    </div>
</div>

<!-- Update Initial Details Modal -->
<div id="updateDetailsModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden z-[10005] flex items-center justify-center p-4 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-sm max-w-lg w-full shadow-2xl animate-in fade-in zoom-in duration-300">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Update Initial Details</h3>
        </div>
        <div class="p-8 space-y-6">
            <div class="relative group">
                <label
                    class="absolute -top-2 left-2 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-gray-400 uppercase tracking-tight z-10">Type</label>
                <select id="editAppType"
                    class="w-full p-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="new_prov">New Business w/ Provisional Sanitary Permit</option>
                    <option value="exist_no_sp">Existing Business w/o Sanitary Permit</option>
                    <option value="renewal">Renewal of Sanitary Permit for Existing Business</option>
                    <option value="amendment">Amendment of Business</option>
                    <option value="retirement">Retirement of Business</option>
                </select>
            </div>
            <div class="relative group">
                <label
                    class="absolute -top-2 left-2 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-gray-400 uppercase tracking-tight z-10">Industry</label>
                <select id="editIndustry"
                    class="w-full p-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="Food">Food</option>
                    <option value="Non-Food">Non-Food</option>
                </select>
            </div>
            <div class="relative group">
                <label
                    class="absolute -top-2 left-2 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-gray-400 uppercase tracking-tight z-10">Sub-Industry</label>
                <select id="editSubIndustry"
                    class="w-full p-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <!-- Dynamic -->
                </select>
            </div>
            <div class="relative group">
                <label
                    class="absolute -top-2 left-2 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-gray-400 uppercase tracking-tight z-10">Business
                    Line</label>
                <select id="editBusinessLine"
                    class="w-full p-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <!-- Dynamic -->
                </select>
            </div>
            <div class="relative group">
                <label
                    class="absolute -top-2 left-2 bg-white dark:bg-gray-800 px-1 text-[10px] font-bold text-gray-400 uppercase tracking-tight z-10">Occupation</label>
                <select id="editOccupation"
                    class="w-full p-3 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">Select Occupation</option>
                    <option value="Owner">Owner</option>
                    <option value="Manager">Manager</option>
                    <option value="Authorized Representative">Authorized Representative</option>
                </select>
            </div>
        </div>
        <div class="p-6 pt-0 flex gap-4">
            <button id="submitEditBtn"
                class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-sm uppercase text-xs tracking-widest hover:bg-blue-700 shadow-md">Submit</button>
            <button onclick="closeEditModal()"
                class="flex-1 bg-[#ff0055] text-white font-bold py-3 rounded-sm uppercase text-xs tracking-widest hover:bg-[#e6004c] shadow-md">Cancel</button>
        </div>
    </div>
</div>

<!-- Requirements List Modal (Pre-form Checklist) -->
<div id="requirementsModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden z-[10004] flex items-center justify-center p-4 backdrop-blur-sm">
    <div
        class="bg-white dark:bg-gray-800 rounded-sm max-w-lg w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Requirements List</h3>
        </div>
        <div class="p-8">
            <p class="text-xs text-gray-500 mb-6 leading-relaxed">
                Make sure that you already have the following requirements prepared for a smoother application process
            </p>
            <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Requirement</h4>
            <ul id="requirementsListContainer" class="space-y-0 text-xs text-gray-700 dark:text-gray-300">
                <!-- Requirements will be dynamically inserted here -->
            </ul>
        </div>
        <div class="p-6 pt-0">
            <button id="confirmRequirementsBtn"
                class="w-full border border-blue-600 text-blue-600 hover:bg-blue-50 font-bold py-2 px-4 rounded-sm uppercase text-xs tracking-widest transition-all">
                confirm
            </button>
        </div>
    </div>
</div>

<style>
    #requirementsListContainer li {
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    #requirementsListContainer li:last-child {
        border-bottom: none;
    }

    .dark #requirementsListContainer li {
        border-bottom-color: #374151;
    }
</style>
<div id="serviceModal"
    class="fixed inset-0 bg-black bg-opacity-60 hidden z-[10003] flex items-center justify-center p-4 backdrop-blur-sm">
    <div id="modalContainer"
        class="bg-white dark:bg-gray-800 rounded-sm max-w-4xl w-full max-h-[90vh] shadow-2xl flex flex-col animate-in fade-in zoom-in duration-300 border border-gray-200 dark:border-gray-700">

        <!-- QC Style Header (Fixed) -->
        <div id="modalHeaderSection" class="p-6 pb-2 border-b border-gray-50 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 uppercase tracking-tight">Application
                        Details</h2>
                    <button onclick="openEditModal()" class="text-blue-600 hover:text-blue-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                            </path>
                        </svg>
                    </button>
                </div>
                <!-- Close Button -->
                <button onclick="closeServiceForm()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Static Details for QC Style -->
            <div id="applicationStaticDetails"
                class="hidden grid grid-cols-1 md:grid-cols-[2.5fr_1fr_1.2fr_1.3fr] gap-4 mb-4 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-sm border border-dashed border-gray-200 dark:border-gray-600">
                <div class="space-y-0.5 min-w-0">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Type</p>
                    <p id="staticType" class="text-[11px] font-bold text-gray-800 dark:text-gray-200 break-words">---
                    </p>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Industry</p>
                    <p id="staticIndustry" class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">---
                    </p>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Sub-Industry</p>
                    <p id="staticSubIndustry" class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">
                        ---</p>
                </div>
                <div class="space-y-0.5 min-w-0">
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Business Line</p>
                    <p id="staticBusinessLine" class="text-[11px] font-bold text-gray-800 dark:text-gray-200 truncate">
                        ---</p>
                </div>
            </div>

            <h3 id="modalTitle" class="text-xl font-bold text-blue-600 dark:text-blue-400 mb-0.5">Sanitary Permit
                Application Form</h3>
            <p class="text-[10px] text-gray-500 font-medium">Fields with <span class="text-red-500">*</span> are
                required</p>
        </div>

        <!-- Scrollable Form Body -->
        <form id="serviceForm" class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <input type="hidden" name="service_type" id="serviceType">
            <input type="hidden" name="app_type" id="appTypeHidden">
            <input type="hidden" name="industry" id="industryHidden">
            <input type="hidden" name="sub_industry" id="subIndustryHidden">
            <input type="hidden" name="business_line" id="businessLineHidden">

            <div class="hidden">
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($u_fullname); ?>">
                <input type="email" name="email" value="<?php echo htmlspecialchars($u_email); ?>">
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($u_phone); ?>">
                <textarea name="address"><?php echo htmlspecialchars($u_address); ?></textarea>
            </div>

            <!-- Dynamic Fields -->
            <div id="dynamicFields" class="grid grid-cols-1 md:grid-cols-6 gap-x-6 gap-y-8"></div>



            <!-- CAPTCHA -->
            <div id="captchaSection"
                class="mt-8 pt-8 border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row items-center gap-6">
                <div
                    class="flex items-center gap-4 bg-white dark:bg-gray-200 p-2 rounded border border-gray-200 dark:border-gray-700 shadow-sm">
                    <canvas id="captchaCanvas" width="130" height="45" class="rounded"></canvas>
                    <button type="button" id="captchaReload"
                        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-100 rounded-full transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 w-full relative">
                    <input type="text" id="captchaInput" placeholder="ENTER CODE" required
                        class="w-full p-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all uppercase tracking-[0.2em] font-mono">
                    <p id="captchaError" class="absolute -bottom-5 left-0 text-red-500 text-[9px] hidden italic">
                        Verification failed. Please try again.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-12 mb-4 flex flex-col md:flex-row gap-4">
                <button type="button" onclick="closeServiceForm()"
                    class="md:w-1/3 order-2 md:order-1 border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 font-bold py-3 px-6 rounded-sm uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                    class="md:w-2/3 order-1 md:order-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-sm uppercase text-xs tracking-[0.1em] transition-all shadow-sm">
                    CONFIRM
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e5e7eb;
        border-radius: 10px;
    }

    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #374151;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #d1d5db;
    }
</style>

<script>
    // Configuration for dynamic fields per service type
    const serviceConfig = {
        'medical-consultation': {
            title: 'Medical Consultation Request',
            fields: [
                { type: 'select', name: 'consultation_type', label: 'Type of Consultation', options: ['General Practice', 'Specialist Referral', 'Health Assessment', 'Medical Certificate'], required: true, width: '1/2' },
                { type: 'select', name: 'consultation_urgency', label: 'Urgency Level', options: ['Routine', 'Urgent', 'Emergency'], required: true, width: '1/2' },
                { type: 'date', name: 'preferred_date', label: 'Preferred Date', required: true, width: 'full' }
            ]
        },
        'emergency-care': {
            title: 'Emergency Care Request',
            fields: [
                { type: 'select', name: 'emergency_type', label: 'Type of Emergency', options: ['Medical Emergency', 'Accident', 'Trauma', 'Critical Care'], required: true, width: '1/2' },
                { type: 'text', name: 'symptoms', label: 'Symptoms/Condition', required: true, width: '1/2' },
                { type: 'textarea', name: 'location_address', label: 'Exact Location / Address', placeholder: 'Provide the exact location where the emergency is occurring...', required: true, width: 'full' },
                { type: 'text', name: 'contact_number', label: 'Contact Number', placeholder: 'Enter active phone number', required: true, width: '1/2' },
                { type: 'date', name: 'preferred_date', label: 'Preferred Date', required: true, width: '1/2' }
            ]
        },

        'business-permit': {
            title: 'Sanitary Permit Application Form',
            fields: [
                { type: 'text', name: 'establishment_name', label: 'Establishment Name', placeholder: 'Enter Establishment Name', required: true, width: 'full' },
                { type: 'text', name: 'establishment_address', label: 'Establishment Address', placeholder: 'Enter Complete Address', required: true, width: 'full' },
                { type: 'text', name: 'owner_name', label: 'Name of Owner', placeholder: 'Enter Full Name of Owner', required: true, width: 'full' },
                { type: 'text', name: 'mayor_permit', label: "Mayor's Permit No.", placeholder: "Enter Mayor's Permit Number", required: true, width: 'full' },
                { type: 'number', name: 'total_employees', label: 'Total Number of Employees', required: false, width: '1/3' },
                { type: 'number', name: 'employees_with_health_cert', label: 'Employees w/ Health Certificate', required: false, width: '1/3' },
                { type: 'number', name: 'employees_without_health_cert', label: 'Employees w/o Health Certificate', required: false, width: '1/3' },
                { type: 'number', name: 'ppe_personnel', label: 'No. of Personnel using PPEs', required: false, width: '1/3' }
            ]
        },
        'health-inspection': {
            title: 'Sanitary Inspection Request',
            fields: [
                { type: 'text', name: 'facility_name', label: 'Establishment/Facility Name', required: true },
                { type: 'select', name: 'inspection_type', label: 'Inspection Type', options: ['Pre-opening Inspection', 'Routine Annual Inspection', 'Re-inspection (Follow-up)', 'Complaint-based Inspection'], required: true },
                { type: 'text', name: 'previous_permit_no', label: 'Previous Sanitary Permit No. (if renewal)', required: false },
                { type: 'date', name: 'preferred_inspection_date', label: 'Preferred Inspection Date', required: true }
            ]
        },
        'vaccination': {
            title: 'Vaccination Appointment',
            fields: [
                { type: 'select', name: 'vaccine_type', label: 'Vaccine Type', options: ['Childhood Immunization', 'Adult Vaccination', 'Travel Vaccination', 'COVID-19'], required: true },
                { type: 'number', name: 'age', label: 'Age', required: true }
            ]
        },
        'nutrition-monitoring': {
            title: 'Nutrition Assessment Request',
            fields: [
                { type: 'select', name: 'assessment_type', label: 'Assessment Type', options: ['Nutritional Status', 'Growth Monitoring', 'Dietary Counseling', 'BMI Check'], required: true },
                { type: 'number', name: 'age', label: 'Age', required: true }
            ]
        },
        'system-inspection': {
            title: 'System Inspection Request',
            fields: [
                { type: 'select', name: 'inspection_scope', label: 'Service Category', options: ['Standard Inspection (Free)', 'Re-inspection / Special (Paid)'], required: true, width: 'full' },
                { type: 'select', name: 'system_type', label: 'System Type', options: ['Septic Tank', 'Drainage System', 'Wastewater Treatment', 'Grease Trap'], required: true, width: '1/2' },
                { type: 'select', name: 'urgency', label: 'Urgency', options: ['Routine', 'Urgent'], required: true, width: '1/2' },
                { type: 'text', name: 'property_address', label: 'Property Address', required: true, width: 'full' },
                { type: 'date', name: 'preferred_date', label: 'Preferred Schedule', required: true, width: '1/2' },
                { type: 'file', name: 'attachment', label: 'Upload Property Layout / Photos', required: false, width: '1/2' },
                { type: 'file', name: 'payment_receipt', label: 'Proof of Payment (if applicable)', required: false, width: 'full' }
            ]
        },
        'septic-registration': {
            title: 'Septic Tank Registration Form',
            fields: [
                { type: 'select', name: 'property_type', label: 'Property Type', options: ['Residential', 'Commercial', 'Industrial', 'Government'], required: true, width: '1/2' },
                { type: 'select', name: 'tank_type', label: 'Septic Tank Type', options: ['Individual', 'Shared/Communal', 'STP Connection'], required: true, width: '1/2' },
                { type: 'number', name: 'capacity_m3', label: 'Tank Capacity (m³)', placeholder: 'e.g. 5.0', required: true, width: '1/2' },
                { type: 'date', name: 'last_desludging', label: 'Last Desludging Date (if any)', required: false, width: '1/2' },
                { type: 'text', name: 'property_location', label: 'Complete Property Location', placeholder: 'Street, Brgy, District', required: true, width: 'full' },
                { type: 'file', name: 'attachment', label: 'System Photo / Layout', required: false, width: 'full' }
            ]
        },
        'maintenance-service': {
            title: 'Maintenance & Desludging Request',
            fields: [
                { type: 'select', name: 'customer_type', label: 'Service Category', options: ['Residential (Free - Scheduled)', 'Commercial / Rush (Paid)'], required: true, width: 'full' },
                { type: 'select', name: 'maintenance_type', label: 'Service Type', options: ['Desludging Service', 'Routine Maintenance', 'Emergency Repair', 'Pump Servicing'], required: true, width: '1/2' },
                { type: 'select', name: 'urgency', label: 'Urgency', options: ['Routine', 'Urgent', 'Emergency'], required: true, width: '1/2' },
                { type: 'text', name: 'property_address', label: 'Property Address', required: true, width: 'full' },
                { type: 'date', name: 'preferred_date', label: 'Preferred Schedule', required: true, width: '1/2' },
                { type: 'file', name: 'payment_receipt', label: 'Proof of Payment (if Paid)', required: false, width: '1/2' },
                { type: 'textarea', name: 'service_details', label: 'Additional Details / Accessibility Notes', required: false, width: 'full' }
            ]
        },
        'wastewater-clearance': {
            title: 'Wastewater Clearance/Certificate',
            fields: [
                { type: 'select', name: 'clearance_category', label: 'Application Category', options: ['Residential (Free)', 'Business / Commercial (Paid)'], required: true, width: 'full' },
                { type: 'select', name: 'clearance_type', label: 'Clearance Type', options: ['Operational Clearance', 'Discharge Permit', 'Compliance Certificate', 'Desludging Certificate'], required: true, width: 'full' },
                { type: 'text', name: 'reference_number', label: 'Reference No. (Inspection/Registration ID)', placeholder: 'Optional', required: false, width: '1/2' },
                { type: 'text', name: 'property_address', label: 'Property Address', required: true, width: '1/2' },
                { type: 'file', name: 'attachment', label: 'Upload Required Documents', required: false, width: '1/2' },
                { type: 'file', name: 'payment_receipt', label: 'Proof of Payment (if Business)', required: false, width: '1/2' },
                { type: 'textarea', name: 'purpose', label: 'Purpose of Request', required: true, width: 'full' }
            ]
        },
        'installation-upgrade': {
            title: 'Installation/Upgrade Request',
            fields: [
                { type: 'select', name: 'installation_type', label: 'Service Type', options: ['New Installation', 'System Upgrade', 'Technology Integration', 'Capacity Expansion'], required: true },
                { type: 'text', name: 'property_address', label: 'Property Address', required: true }
            ]
        },
        'disease-monitoring': {
            title: 'Disease Case Filing (PIDSR Standard)',
            fields: [
                { type: 'text', name: 'patient_name', label: 'Patient Full Name', required: true, width: 'full', staffOnly: true },
                { type: 'text', name: 'patient_phone', label: 'Patient Phone (Optional)', width: '1/2', staffOnly: true },
                { type: 'text', name: 'patient_address', label: 'Patient Residential Address', width: '1/2', staffOnly: true },
                { type: 'select', name: 'classification', label: 'Case Classification', options: ['Suspected', 'Probable', 'Confirmed'], required: true, width: '1/2' },
                { type: 'select', name: 'disease_condition', label: 'Disease / Condition', options: ['COVID-19', 'Dengue', 'Measles', 'Typhoid', 'Chikungunya', 'Cholera', 'Tuberculosis', 'Others'], required: true, width: '1/2' },
                { type: 'date', name: 'onset_date', label: 'Date of Onset', required: true, width: '1/2' },
                { type: 'text', name: 'location_barangay', label: 'Location (Barangay)', placeholder: 'e.g. San Jose, Dist. I', required: true, width: '1/2' },
                { type: 'textarea', name: 'symptoms_history', label: 'Symptoms & Exposure History', placeholder: 'Describe symptoms, travel history, or known contacts...', required: true, width: 'full' }
            ]
        },
        'environmental-monitoring': {
            title: 'Environmental Health Assessment',
            fields: [
                { type: 'select', name: 'assessment_type', label: 'Assessment Type', options: ['Air Quality Monitoring', 'Water Safety Test', 'Hazardous Waste Inspection', 'Pollution Risk Mapping'], required: true, width: 'full' },
                { type: 'text', name: 'target_location', label: 'Incident/Target Location', required: true, width: 'full' },
                { type: 'textarea', name: 'incident_description', label: 'Incident / Assessment Details', required: true, width: 'full' }
            ]
        },
        'health-certificate': {
            title: 'Personal Health Certificate Application',
            fields: [
                { type: 'text', name: 'employee_name', label: 'Employee Full Name', required: true },
                { type: 'text', name: 'position', label: 'Position/Job Title', required: true },
                { type: 'select', name: 'category', label: 'Work Category', options: ['Food Handler', 'Service Worker', 'Beauty/Barber', 'Hospitality', 'Other'], required: true },
                { type: 'text', name: 'medical_exam_date', label: 'Medical Exam Date (YYYY-MM-DD)', required: true },
                { type: 'text', name: 'clinic_name', label: 'Name of Accredited Clinic', required: true }
            ]
        },
        'child-registration': {
            title: 'Child / Dependent Registration',
            fields: [
                { type: 'text', name: 'child_name', label: "Child's Full Name", required: true, width: 'full' },
                { type: 'date', name: 'date_of_birth', label: 'Date of Birth', required: true, width: '1/3' },
                { type: 'select', name: 'gender', label: 'Gender', options: ['Male', 'Female'], required: true, width: '1/3' },
                { type: 'text', name: 'birth_place', label: 'Place of Birth', required: true, width: '1/3' },
                { type: 'text', name: 'parent_id', label: 'Parent ID (Staff Only)', placeholder: 'User ID or Email of Parent', required: false, width: '1/2', staffOnly: true },
                { type: 'text', name: 'parent_name', label: 'Parent/Guardian Name', required: true, width: '1/2' },
                { type: 'text', name: 'relationship', label: 'Relationship', required: true, width: '1/3' },
                { type: 'text', name: 'contact_number', label: 'Contact Number', required: true, width: '2/3' }
            ]
        },
        'wss-payment': {
            title: 'Wastewater Service Payment',
            fields: [
                { type: 'readonly', name: 'request_id', label: 'Request Reference #' },
                { type: 'number', name: 'amount', label: 'Amount Paid (PHP)', required: true },
                { type: 'date', name: 'payment_date', label: 'Payment Date', required: true },
                { type: 'select', name: 'payment_method', label: 'Payment Method', options: ['Gcash', 'Cash', 'Bank Transfer'], required: true },
                { type: 'text', name: 'or_number', label: 'Reference / OR Number', required: true },
                { type: 'file', name: 'payment_receipt', label: 'Upload Proof of Payment', required: true }
            ]
        }

        ,

        'nutrition-update': {
            title: 'Update Nutrition Record (Health Worker Only)',
            fields: [
                { type: 'text', name: 'child_id', label: 'Child ID', required: true, width: 'full' },
                { type: 'number', name: 'height', label: 'Height (cm)', required: true, width: '1/3' },
                { type: 'number', name: 'weight', label: 'Weight (kg)', required: true, width: '1/3' },
                { type: 'select', name: 'nutritional_status', label: 'Status', options: ['Normal', 'Underweight', 'Overweight', 'Stunted', 'Wasted'], required: true, width: '1/3' },
                { type: 'date', name: 'assessment_date', label: 'Date of Assessment', required: true, width: '1/2' },
                { type: 'date', name: 'next_visit', label: 'Next Visit Date', required: false, width: '1/2' },
                { type: 'textarea', name: 'remarks', label: 'Remarks / Observations', required: false, width: 'full' }
            ]
        },
        'immunization-record': {
            title: 'Add Immunization Record',
            headerAction: '<button type="button" onclick="showVaccineSchedule()" class="text-xs font-bold text-purple-600 hover:text-purple-800 underline">📋 View Vaccine Schedule</button>',
            fields: [
                { type: 'hidden', name: 'child_id' },
                {
                    type: 'select',
                    name: 'vaccine_name',
                    label: 'Vaccine Type',
                    options: [
                        'BCG (Tuberculosis)',
                        'Hepatitis B (HepB)',
                        'Pentavalent (DPT-HepB-Hib)',
                        'Oral Polio Vaccine (OPV)',
                        'Inactivated Polio Vaccine (IPV)',
                        'Pneumococcal Conjugate Vaccine (PCV)',
                        'Measles-Rubella (MR)',
                        'MMR (Measles-Mumps-Rubella)',
                        'Tetanus-Diphtheria (Td)',
                        'HPV (Human Papillomavirus)',
                        'Tetanus Toxoid (TT)',
                        'Other (specify in remarks)'
                    ],
                    required: true,
                    width: '2/3'
                },
                { type: 'number', name: 'dose_number', label: 'Dose No.', required: true, width: '1/3' },
                { type: 'text', name: 'batch_number', label: 'Batch No.', required: false, width: '1/2' },
                { type: 'date', name: 'date_administered', label: 'Date Administered', required: true, width: '1/2' },
                { type: 'date', name: 'date_due', label: 'Next Dose Due', required: false, width: '1/2' },
                { type: 'select', name: 'status', label: 'Status', options: ['Administered', 'Scheduled', 'Overdue'], required: true, width: '1/2' },
                { type: 'textarea', name: 'remarks', label: 'Remarks / Notes', required: false, width: 'full', placeholder: 'Any adverse reactions, special notes, or custom vaccine name if "Other" selected' }
            ]
        },
        'edit-immunization': {
            title: 'Edit Immunization Record',
            headerAction: '<button type="button" onclick="showVaccineSchedule()" class="text-xs font-bold text-purple-600 hover:text-purple-800 underline">📋 View Vaccine Schedule</button>',
            fields: [
                { type: 'hidden', name: 'record_id' },
                { type: 'text', name: 'vaccine_name', label: 'Vaccine Name', required: true, width: '2/3' },
                { type: 'number', name: 'dose_number', label: 'Dose No.', required: true, width: '1/3' },
                { type: 'text', name: 'batch_number', label: 'Batch No.', required: false, width: '1/2' },
                { type: 'date', name: 'date_administered', label: 'Date Administered', required: true, width: '1/2' },
                { type: 'date', name: 'date_due', label: 'Next Dose Due', required: false, width: '1/2' },
                { type: 'select', name: 'status', label: 'Status', options: ['Administered', 'Scheduled', 'Overdue'], required: true, width: '1/2' },
                { type: 'textarea', name: 'remarks', label: 'Remarks', required: false, width: 'full' }
            ]
        },
        'edit-nutrition': {
            title: 'Edit Nutrition Record',
            fields: [
                { type: 'hidden', name: 'record_id' },
                { type: 'number', name: 'weight', label: 'Weight (kg)', required: true, width: '1/2' },
                { type: 'number', name: 'height', label: 'Height (cm)', required: true, width: '1/2' },
                { type: 'select', name: 'nutritional_status', label: 'Status', options: ['Normal', 'Underweight', 'Overweight', 'Stunted', 'Wasted'], required: true, width: 'full' },
                { type: 'date', name: 'assessment_date', label: 'Date', required: true, width: '1/2' },
                { type: 'date', name: 'next_visit', label: 'Next Visit', required: false, width: '1/2' },
                { type: 'textarea', name: 'remarks', label: 'Remarks', required: false, width: 'full' }
            ]
        },
        'edit-dependent': {
            title: 'Edit Family Member Info',
            fields: [
                { type: 'hidden', name: 'record_id' },
                { type: 'text', name: 'first_name', label: 'First Name', required: true, width: '1/2' },
                { type: 'text', name: 'last_name', label: 'Last Name', required: true, width: '1/2' },
                { type: 'date', name: 'date_of_birth', label: 'Date of Birth', required: true, width: '1/2' },
                { type: 'text', name: 'place_of_birth', label: 'Place of Birth', required: true, width: '1/2' },
                { type: 'select', name: 'gender', label: 'Gender', options: ['male', 'female', 'other'], required: true, width: '1/2' },
                { type: 'text', name: 'relationship', label: 'Relationship', required: true, width: '1/2' },
                { type: 'select', name: 'fic_status', label: 'Status', options: ['active', 'inactive'], required: true, width: 'full' }
            ]
        }
    };

    // Sanitation Workflow Step Forms (Steps 2–6)
    serviceConfig['sanitation-form-filing'] = {
        title: 'Sanitary Permit - Form Filing',
        fields: [
            { type: 'number', name: 'application_id', label: 'Application ID (if existing)', required: false },
            { type: 'text', name: 'notes', label: 'Notes / Remarks', required: false }
        ]
    };
    serviceConfig['sanitation-submission'] = {
        title: 'Sanitary Permit - Submission of Requirements',
        fields: [
            { type: 'number', name: 'application_id', label: 'Application ID', required: true },
            { type: 'file', name: 'business_permit_image', label: 'Business Permit (Current Year)', required: true },
            { type: 'file', name: 'permit_receipt_image', label: 'Business Permit Official Receipt (Present Year)', required: true },
            { type: 'file', name: 'health_certificates_image', label: 'Health Certificates', required: true },
            { type: 'file', name: 'occupancy_permit_image', label: 'Health Occupancy Permit Receipt', required: false },
            { type: 'file', name: 'water_analysis_image', label: 'Microbiological Water Analysis Report', required: true },
            { type: 'file', name: 'pest_control_image', label: 'Pest Control Service Report', required: true }
        ]
    };
    serviceConfig['sanitation-payment'] = {
        title: 'Sanitary Permit - Payment Details',
        fields: [
            { type: 'number', name: 'application_id', label: 'Application ID', required: true },
            { type: 'text', name: 'or_number', label: 'Official Receipt No.', required: true },
            { type: 'number', name: 'amount', label: 'Amount Paid', required: true },
            { type: 'date', name: 'payment_date', label: 'Payment Date', required: true },
            { type: 'select', name: 'payment_method', label: 'Payment Method', options: ['Gcash', 'Paymaya', 'Bank Transfer', 'Over-the-counter', 'Other Online'], required: true },
            { type: 'file', name: 'payment_receipt_image', label: 'Upload/Attach Proof of Payment (Official Receipt)', required: true }
        ]
    };
    serviceConfig['sanitation-inspection'] = {
        title: 'Sanitary Permit - Inspection Schedule & Result',
        fields: [
            { type: 'number', name: 'application_id', label: 'Application ID', required: true },
            { type: 'date', name: 'preferred_date', label: 'Preferred Inspection Date', required: false },
            { type: 'date', name: 'scheduled_date', label: 'Scheduled Inspection Date', required: false },
            { type: 'text', name: 'inspector_name', label: 'Inspector Name', required: false },
            { type: 'select', name: 'result', label: 'Inspection Result', options: ['Pass', 'Fail', 'Conditional'], required: false },
            { type: 'text', name: 'findings', label: 'Findings / Observations', required: false }
        ]
    };
    serviceConfig['sanitation-issuance'] = {
        title: 'Sanitary Permit - Issuance',
        fields: [
            { type: 'number', name: 'application_id', label: 'Application ID', required: true },
            { type: 'text', name: 'permit_no', label: 'Sanitary Permit No.', required: true },
            { type: 'date', name: 'issued_date', label: 'Issued Date', required: true },
            { type: 'date', name: 'expiry_date', label: 'Expiry Date', required: false },
            { type: 'text', name: 'remarks', label: 'Remarks', required: false }
        ]
    };
    const serviceModal = document.getElementById('serviceModal');
    const serviceForm = document.getElementById('serviceForm');
    const captchaCanvas = document.getElementById('captchaCanvas');
    const captchaInput = document.getElementById('captchaInput');
    const captchaError = document.getElementById('captchaError');
    const captchaReload = document.getElementById('captchaReload');
    const modalTitle = document.getElementById('modalTitle');
    const serviceTypeInput = document.getElementById('serviceType');

    let captchaValue = '';

    function generateCaptcha() {
        if (!captchaCanvas) return;

        const ctx = captchaCanvas.getContext('2d');
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        captchaValue = Array.from({ length: 6 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, captchaCanvas.width, captchaCanvas.height);

        const gradient = ctx.createLinearGradient(0, 0, captchaCanvas.width, captchaCanvas.height);
        gradient.addColorStop(0, '#1d4ed8');
        gradient.addColorStop(1, '#fb923c');
        ctx.fillStyle = '#f8fafc';
        ctx.fillRect(0, 0, captchaCanvas.width, captchaCanvas.height);

        ctx.font = 'bold 30px "Courier New", monospace';
        ctx.setTransform(1, 0, Math.random() * 0.2 - 0.1, 1, 0, 0);
        ctx.fillStyle = '#1e3a8a'; // Solid dark blue for better contrast
        ctx.textBaseline = 'middle';
        ctx.fillText(captchaValue, 10, captchaCanvas.height / 2 + 2);

        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.strokeStyle = 'rgba(30, 64, 175, 0.2)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        for (let i = 0; i < 5; i++) {
            ctx.moveTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
            ctx.lineTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
        }
        ctx.stroke();
    }

    // HSM-Services Dependent Dropdown Data
    window.industryData = {
        'Food': {
            'FOOD MANUFACTURING WITH WATER PROVISION': ['Canning', 'Bottling', 'Preserving'],
            'FOOD WITH WATER PROVISION & FOOD PREPARATION': ['Restaurant', 'Fast Food', 'Canteen'],
            'FOOD WITH WATER PROVISION & WITHOUT FOOD PREPARATION': ['Grocery', 'Convenience Store', 'REPACKER OF LOCALLY MANUFACTURED FOOD'],
            'IMPORTER': ['Food Importer'],
            'KTV / NIGHT CLUB / COCKTAIL LOUNGE': ['MUSIC LOUNGE', 'BAR', 'NIGHT CLUB'],
            'PHARMACY': ['Retail Pharmacy', 'Wholesale Pharmacy'],
            'RETAILER': ['Food Retailer'],
            'WATER REFILLING STATION & ICE PLANTS': ['Water Refilling', 'Ice Plant'],
            'WHOLESALER': ['Food Wholesaler']
        },
        'Non-Food': {
            'COMMERCIAL ESTABLISHMENTS': ['Office', 'Bank', 'Retail Shop'],
            'INDUSTRIAL FACILITIES': ['Warehouse', 'Factory'],
            'PUBLIC PLACES': ['Park', 'Plaza', 'Public Office']
        }
    };

    function openHealthCenterModal() {
        const modal = document.getElementById('healthCenterInfoModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeHealthCenterModal() {
        const modal = document.getElementById('healthCenterInfoModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // Requirements configuration based on service types and business lines
    const requirementsConfig = {
        'default': [
            'Business Permit (Current Year)',
            'Business Permit Official Receipt (Present Year)',
            'Health Certificates',
            'Health Occupancy Permit Receipt',
            'Microbiological Water Analysis Report',
            'Pest Control Service Report'
        ],
        'MUSIC LOUNGE': [
            'Business Permit (Current Year)',
            'Business Permit Official Receipt (Present Year)',
            'Health Certificates',
            'Health Occupancy Permit Receipt (for New Applicants and Newly Renovated Establishments)',
            'MICROBIOLOGICAL WATER ANALYSIS (Every Other Month)',
            'Pest Control Service Report from FDA accredited service provider (Monthly)',
            'YELLOW CARD (If with employed Entertainers)'
        ],
        'Restaurant': [
            'Business Permit (Current Year)',
            'Business Permit Official Receipt (Present Year)',
            'Health Certificates',
            'MICROBIOLOGICAL WATER ANALYSIS (Every six (6) Months)',
            'Pest Control Service Report from FDA accredited service provider (Monthly)'
        ],
        'REPACKER OF LOCALLY MANUFACTURED FOOD': [
            'Business Permit (Current Year)',
            'Business Permit Official Receipt (Present Year)',
            'Health Certificates',
            'Health Occupancy Permit Receipt (for New Applicants and Newly Renovated Establishments)',
            'MICROBIOLOGICAL WATER ANALYSIS (Every six (6) Months)',
            'Pest Control Service Report from FDA accredited service provider (Monthly)'
        ],
        'MUSIC LOUNGE': [
            'Business Permit (Current Year)',
            'Business Permit Official Receipt (Present Year)',
            'Health Certificates',
            'Health Occupancy Permit Receipt (for New Applicants and Newly Renovated Establishments)',
            'MICROBIOLOGICAL WATER ANALYSIS (Every six (6) Months)',
            'Pest Control Service Report from FDA accredited service provider (Monthly)',
            'YELLOW CARD (If with employed Entertainers)'
        ]
    };

    function openPrivacyModal() {
        document.getElementById('privacyPolicyModal')?.classList.remove('hidden');
    }

    function closePrivacyModal() {
        document.getElementById('privacyPolicyModal')?.classList.add('hidden');
    }

    let currentMetadata = {};

    function openEditModal() {
        const modal = document.getElementById('updateDetailsModal');
        if (!modal) return;

        // Populate with current values
        document.getElementById('editAppType').value = currentMetadata.appType || '';
        document.getElementById('editIndustry').value = currentMetadata.industry || '';

        // Trigger industry change to populate sub-industry
        const industryEvent = new Event('change');
        document.getElementById('editIndustry').dispatchEvent(industryEvent);

        // Map sub-industry and business line after population delay (if needed)
        setTimeout(() => {
            document.getElementById('editSubIndustry').value = currentMetadata.subIndustry || '';
            const subIndustryEvent = new Event('change');
            document.getElementById('editSubIndustry').dispatchEvent(subIndustryEvent);

            setTimeout(() => {
                document.getElementById('editBusinessLine').value = currentMetadata.businessLine || '';
            }, 50);
        }, 50);

        // Pre-fill application_id if provided via metadata (for Steps 2–6)
        if (typeof metadata !== 'undefined' && metadata && metadata.applicationId) {
            const form = document.getElementById('serviceForm');
            if (form) {
                const appIdInput = form.querySelector('input[name="application_id"]');
                if (appIdInput) { appIdInput.value = metadata.applicationId; appIdInput.readOnly = true; }
            }
        }
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('updateDetailsModal')?.classList.add('hidden');
    }

    // Logic for Update Details Submit
    document.addEventListener('DOMContentLoaded', () => {
        const submitEditBtn = document.getElementById('submitEditBtn');
        if (submitEditBtn) {
            submitEditBtn.onclick = () => {
                const newMetadata = {
                    appType: document.getElementById('editAppType').value,
                    industry: document.getElementById('editIndustry').value,
                    subIndustry: document.getElementById('editSubIndustry').value,
                    businessLine: document.getElementById('editBusinessLine').value,
                    occupation: document.getElementById('editOccupation').value
                };

                currentMetadata = newMetadata;
                closeEditModal();

                // Refresh main form with new metadata
                continueToServiceForm('business-permit', newMetadata);
            };
        }

        // Dependent dropdowns for Edit Modal (mirroring main page logic)
        const industrySelect = document.getElementById('editIndustry');
        const subIndustrySelect = document.getElementById('editSubIndustry');
        const businessLineSelect = document.getElementById('editBusinessLine');

        industrySelect?.addEventListener('change', function () {
            const industry = this.value;
            subIndustrySelect.innerHTML = '<option value="">Select Option</option>';
            businessLineSelect.innerHTML = '<option value="">Select Option</option>';

            if (industry && industryData[industry]) {
                Object.keys(industryData[industry]).forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub;
                    opt.textContent = sub;
                    subIndustrySelect.appendChild(opt);
                });
            }
        });

        subIndustrySelect?.addEventListener('change', function () {
            const industry = industrySelect.value;
            const subIndustry = this.value;
            businessLineSelect.innerHTML = '<option value="">Select Option</option>';

            if (industry && subIndustry && industryData[industry][subIndustry]) {
                industryData[industry][subIndustry].forEach(line => {
                    const opt = document.createElement('option');
                    opt.value = line;
                    opt.textContent = line;
                    businessLineSelect.appendChild(opt);
                });
            }
        });
    });

    function showRequirementsList(serviceType, businessLine, callback) {
        const modal = document.getElementById('requirementsModal');
        const list = document.getElementById('requirementsListContainer');
        const confirmBtn = document.getElementById('confirmRequirementsBtn');

        if (!modal || !list || !confirmBtn) return;

        const requirements = requirementsConfig[businessLine] || requirementsConfig['default'];

        list.innerHTML = '';
        requirements.forEach(req => {
            const li = document.createElement('li');
            li.textContent = req;
            li.className = "text-xs font-medium";
            list.appendChild(li);
        });

        // Pre-fill application_id if provided via metadata (for Steps 2–6)
        if (typeof metadata !== 'undefined' && metadata && metadata.applicationId) {
            const form = document.getElementById('serviceForm');
            if (form) {
                const appIdInput = form.querySelector('input[name="application_id"]');
                if (appIdInput) { appIdInput.value = metadata.applicationId; appIdInput.readOnly = true; }
            }
        }
        modal.classList.remove('hidden');

        confirmBtn.onclick = function () {
            modal.classList.add('hidden');
            if (callback) callback();
        };
    }

    function openServiceForm(serviceType, metadata = {}) {
        console.log('Opening Service Form:', serviceType, metadata);
        currentMetadata = metadata; // Save globally for editing
        // Special flow for business permit: show requirements first
        if (serviceType === 'business-permit') {
            const businessLine = metadata.businessLine || '';
            showRequirementsList(serviceType, businessLine, () => {
                // This is the callback that runs after "Confirm" is clicked
                continueToServiceForm(serviceType, metadata);
            });
            return;
        }

        continueToServiceForm(serviceType, metadata);
    }

    function continueToServiceForm(serviceType, metadata = {}) {
        const config = serviceConfig[serviceType];
        if (!config) return;

        const modal = document.getElementById('serviceModal');
        const form = document.getElementById('serviceForm');
        const title = document.getElementById('modalTitle');
        const typeInput = document.getElementById('serviceType');
        const headerSection = document.getElementById('modalHeaderSection');
        const staticDetails = document.getElementById('applicationStaticDetails');
        const submitBtn = document.getElementById('submitBtn');
        const privacyConsent = document.getElementById('privacyConsent');

        if (!modal || !form || !title || !typeInput) {
            console.warn('Service modal elements missing.');
            return;
        }

        form.reset();
        if (captchaError) captchaError.classList.add('hidden');
        if (captchaInput) captchaInput.value = '';

        // Set title and add header action if present
        title.innerHTML = config.title + (config.headerAction ? ` <span class="ml-3">${config.headerAction}</span>` : '');
        typeInput.value = serviceType;

        // Visual adjustment for QC style
        if (serviceType === 'business-permit') {
            headerSection?.classList.remove('hidden');
            staticDetails?.classList.remove('hidden');

            // Populate static details from metadata
            const typeLabelMap = {
                'new_prov': 'New Business w/ Provisional Sanitary Permit',
                'exist_no_sp': 'Existing Business w/o Sanitary Permit',
                'renewal': 'Renewal of Sanitary Permit for Existing Business',
                'amendment': 'Amendment of Business',
                'retirement': 'Retirement of Business'
            };

            if (document.getElementById('staticType')) document.getElementById('staticType').textContent = typeLabelMap[metadata.appType] || metadata.appType || '---';
            if (document.getElementById('staticIndustry')) document.getElementById('staticIndustry').textContent = metadata.industry || '---';
            if (document.getElementById('staticSubIndustry')) document.getElementById('staticSubIndustry').textContent = metadata.subIndustry || '---';
            if (document.getElementById('staticBusinessLine')) document.getElementById('staticBusinessLine').textContent = metadata.businessLine || '---';
            // Populate hidden metadata inputs so backend receives them
            const ah = document.getElementById('appTypeHidden'); if (ah) ah.value = metadata.appType || '';
            const ih = document.getElementById('industryHidden'); if (ih) ih.value = metadata.industry || '';
            const sh = document.getElementById('subIndustryHidden'); if (sh) sh.value = metadata.subIndustry || '';
            const bh = document.getElementById('businessLineHidden'); if (bh) bh.value = metadata.businessLine || '';

        } else {
            staticDetails?.classList.add('hidden');
        }

        // Handle CAPTCHA visibility
        const captchaSection = document.getElementById('captchaSection');
        if (captchaSection) {
            // Hide captcha for editing and viewing
            if (serviceType.includes('edit-') || metadata.readOnly) {
                captchaSection.classList.add('hidden');
                if (captchaInput) captchaInput.required = false;
            } else {
                captchaSection.classList.remove('hidden');
                if (captchaInput) captchaInput.required = true;
                generateCaptcha();
            }
        }

        // Handle Submit Button visibility and state
        if (submitBtn) {
            if (metadata.readOnly) {
                submitBtn.classList.add('hidden');
            } else {
                submitBtn.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.className = 'md:w-2/3 order-1 md:order-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-sm uppercase text-xs tracking-[0.1em] transition-all shadow-sm';
            }
        }

        // Generate dynamic fields with grid support
        const dynamicFields = document.getElementById('dynamicFields');
        dynamicFields.innerHTML = '';

        config.fields.forEach(field => {
            if (field.staffOnly && !['admin', 'health_worker', 'nurse', 'doctor'].includes(window.userRole)) return;

            // Handle hidden fields separately
            if (field.type === 'hidden') {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = field.name;
                hiddenInput.value = metadata[field.name] || '';
                dynamicFields.appendChild(hiddenInput);
                return;
            }

            const div = document.createElement('div');
            // Support various widths in 6-column grid
            let span = 'md:col-span-2'; // Default (1/3)
            if (field.width === 'full') span = 'md:col-span-6';
            else if (field.width === '1/2') span = 'md:col-span-3';
            else if (field.width === '2/3') span = 'md:col-span-4';
            else if (field.width === '1/3') span = 'md:col-span-2';

            div.className = `${span} group`;

            let fieldHTML = `<label class="block text-xs font-bold text-gray-500 uppercase tracking-tight mb-1 group-focus-within:text-blue-600 transition-colors">${field.label}${field.required ? ' *' : ''}</label>`;

            const baseClass = "w-full p-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-gray-400";

            // Check for pre-filled value from metadata
            const preValue = metadata[field.name] || '';
            const isReadOnly = (['child_id', 'record_id', 'application_id'].includes(field.name) && preValue !== '') ? 'readonly' : '';
            const readOnlyStyle = isReadOnly ? 'bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed opacity-75' : '';

            if (field.type === 'select') {
                fieldHTML += `<select name="${field.name}" ${field.required ? 'required' : ''} class="${baseClass} ${readOnlyStyle}" ${(isReadOnly || metadata.readOnly) ? 'style="pointer-events: none;"' : ''} ${metadata.readOnly ? 'disabled' : ''}>`;
                fieldHTML += '<option value="">Select ' + field.label + '</option>';
                field.options?.forEach(option => {
                    const selected = (String(option) === String(preValue)) ? 'selected' : '';
                    fieldHTML += `<option value="${option}" ${selected}>${option}</option>`;
                });
                fieldHTML += '</select>';
            } else if (field.type === 'textarea') {
                fieldHTML += `<textarea name="${field.name}" ${field.required ? 'required' : ''} placeholder="${field.placeholder || ''}" class="${baseClass} h-24 ${readOnlyStyle}" ${isReadOnly ? 'readonly' : ''} ${metadata.readOnly ? 'disabled' : ''}>${preValue}</textarea>`;
            } else if (field.type === 'date') {
                // Add date restrictions to prevent invalid years
                const today = new Date().toISOString().split('T')[0];
                const minDate = '1900-01-01';
                // For administered dates, max is today; for due/next dates, allow up to 10 years ahead
                const isFutureDate = ['date_due', 'next_visit', 'next_visit_date'].includes(field.name);
                const maxDate = isFutureDate ? new Date(new Date().setFullYear(new Date().getFullYear() + 10)).toISOString().split('T')[0] : today;
                fieldHTML += `<input type="date" name="${field.name}" value="${preValue}" min="${minDate}" max="${maxDate}" ${field.required ? 'required' : ''} class="${baseClass} ${readOnlyStyle}" ${isReadOnly ? 'readonly' : ''} ${metadata.readOnly ? 'disabled' : ''}>`;
            } else {
                fieldHTML += `<input type="${field.type}" name="${field.name}" value="${preValue}" ${field.required ? 'required' : ''} placeholder="${field.placeholder || ''}" class="${baseClass} ${readOnlyStyle}" ${isReadOnly ? 'readonly' : ''} ${metadata.readOnly ? 'disabled' : ''}>`;
            }

            div.innerHTML = fieldHTML;
            dynamicFields.appendChild(div);
        });

        // Add real-time date validation to prevent invalid years
        setTimeout(() => {
            const dateInputs = dynamicFields.querySelectorAll('input[type="date"]');
            dateInputs.forEach(input => {
                input.addEventListener('input', function (e) {
                    const value = this.value;
                    if (value) {
                        const parts = value.split('-');
                        if (parts.length >= 1) {
                            const year = parseInt(parts[0], 10);
                            // Check if year is valid (between 1900 and 2036)
                            if (year < 1900 || year > 2036 || isNaN(year)) {
                                this.value = '';
                                alert('Please enter a valid year between 1900 and 2036.');
                            }
                        }
                    }
                });

                // Also validate on blur (when leaving the field)
                input.addEventListener('blur', function (e) {
                    const value = this.value;
                    if (value) {
                        const year = parseInt(value.split('-')[0], 10);
                        if (year < 1900 || year > 2036 || isNaN(year)) {
                            this.value = '';
                            alert('Invalid date. Year must be between 1900 and 2036.');
                        }
                    }
                });
            });
        }, 100);

        // Pre-fill application_id if provided via metadata (for Steps 2–6)
        if (typeof metadata !== 'undefined' && metadata && metadata.applicationId) {
            const form = document.getElementById('serviceForm');
            if (form) {
                const appIdInput = form.querySelector('input[name="application_id"]');
                if (appIdInput) { appIdInput.value = metadata.applicationId; appIdInput.readOnly = true; }
            }

            // Fetch and display existing documents for sanitation-submission
            if (serviceType === 'sanitation-submission') {
                loadExistingDocuments(metadata.applicationId);
            }
        }
        // Inspector-only step: disable citizen editing for Inspection
        try {
            if (serviceType === 'sanitation-inspection') {
                const isInspectorPortal = window.location.pathname.includes('/inspection/');
                const formEl = document.getElementById('serviceForm');
                const submitBtnEl = document.getElementById('submitBtn');

                if (formEl && !isInspectorPortal) {
                    if (metadata.readOnly) {
                        formEl.querySelectorAll('input, select, textarea').forEach(el => {
                            el.disabled = true;
                            el.readOnly = true;
                            el.classList.add('opacity-50', 'bg-gray-100', 'dark:bg-gray-800/50');
                        });
                        formEl.querySelector('.citizen-inspection-note')?.remove();
                        if (submitBtnEl) submitBtnEl.classList.add('hidden');
                    } else {
                        formEl.querySelectorAll('input, select, textarea').forEach(el => {
                            // Citizens can suggest a Preferred Date, everything else is for Inspector (except captcha)
                            if (el && el.name !== 'application_id' && el.name !== 'preferred_date' && el.id !== 'captchaInput') {
                                el.disabled = true;
                                el.readOnly = true;
                                el.classList.add('opacity-50', 'bg-gray-100', 'dark:bg-gray-800/50');
                            }
                        });

                        if (submitBtnEl) {
                            submitBtnEl.textContent = 'Submit Suggestion';
                            submitBtnEl.disabled = false;
                            submitBtnEl.classList.remove('bg-[#d1d5db]', 'text-[#9ca3af]', 'cursor-not-allowed');
                            submitBtnEl.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'text-white', 'shadow-lg', 'shadow-emerald-500/20');
                        }

                        // Add a clearer note for the citizen
                        const existingNote = formEl.querySelector('.citizen-inspection-note');
                        if (!existingNote) {
                            const note = document.createElement('div');
                            note.className = 'citizen-inspection-note mt-8 p-6 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-100 text-xs font-semibold rounded-2xl border border-blue-200 dark:border-blue-800/50 leading-relaxed flex gap-4 items-start';
                            note.innerHTML = `
                                <div class="w-8 h-8 rounded-full bg-blue-600 flex-shrink-0 flex items-center justify-center text-white shadow-md">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="uppercase tracking-widest text-[10px] opacity-70 mb-1 block">Information for Applicants</span>
                                    Use this form to suggest your <strong>Preferred Inspection Date</strong>. The actual Scheduled Date, assigned Inspector, and final results will be filled in by the Health Office after the official visit.
                                </div>`;
                            // Insert before the buttons
                            const buttonContainer = formEl.querySelector('.flex-col.md\\:flex-row.gap-4');
                            if (buttonContainer) {
                                formEl.insertBefore(note, buttonContainer);
                            } else {
                                formEl.appendChild(note);
                            }
                        }
                    }
                }
            } else {
                // Clear any existing notes for other service types
                formEl.querySelector('.citizen-inspection-note')?.remove();
            }
        } catch (e) { console.error('Inspection check error:', e); }

        // Start Auto-fill for Inspection Results
        if (serviceType === 'sanitation-inspection' && metadata.applicationId) {
            const isCitizenPortal = window.location.pathname.includes('/citizen/');
            const assignEndpoint = isCitizenPortal ? '../api/permit_assignment.php' : 'api/permit_assignment.php';
            fetch(assignEndpoint + '?application_id=' + encodeURIComponent(metadata.applicationId))
                .then(r => r.json())
                .then(j => {
                    if (j && j.success) {
                        const form = document.querySelector('#serviceForm');
                        if (!form) return;
                        const details = j.details || {};
                        const mapping = {
                            'inspector_name': j.inspector?.name || details.assigned_inspector_name || details.inspector_name,
                            'preferred_date': details.preferred_date,
                            'scheduled_date': details.scheduled_date,
                            'findings': details.findings,
                            'result': details.result
                        };
                        Object.keys(mapping).forEach(name => {
                            const val = mapping[name];
                            if (val) {
                                const el = form.querySelector(`[name="${name}"]`);
                                if (el) {
                                    if (name === 'result' && el.tagName === 'SELECT') {
                                        const norm = val.charAt(0).toUpperCase() + val.slice(1).toLowerCase();
                                        el.value = (norm === 'Passed') ? 'Pass' : (norm === 'Failed' ? 'Fail' : norm);
                                    } else {
                                        el.value = val;
                                    }
                                }
                            }
                        });
                    }
                }).catch(e => console.error('Auto-fill fetch error:', e));
        }

        modal.classList.remove('hidden');
        generateCaptcha();
    }

    function closeServiceForm() {
        const modal = document.getElementById('serviceModal');
        if (modal) modal.classList.add('hidden');
        document.getElementById('serviceForm')?.reset();
    }

    if (captchaReload) {
        captchaReload.addEventListener('click', function () {
            generateCaptcha();
            captchaError?.classList.add('hidden');
            if (captchaInput) {
                captchaInput.value = '';
                captchaInput.focus();
            }
        });
    }

    if (serviceForm) {
        serviceForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (captchaInput && captchaError && captchaCanvas) {
                const captchaEntered = captchaInput.value.trim().toUpperCase();
                if (captchaEntered !== captchaValue) {
                    captchaError.classList.remove('hidden');
                    captchaInput.value = '';
                    captchaInput.focus();
                    generateCaptcha();
                    return;
                }
            }

            const formEl = this; const formData = new FormData(formEl);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;

            // Disable submit button and show loading
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';

            // Convert FormData to JSON
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            // Map sanitation UI service types to workflow_step + sanitation-workflow type
            const stepMap = {
                'sanitation-form-filing': 'form_filing',
                'sanitation-submission': 'submission',
                'sanitation-payment': 'payment',
                'sanitation-inspection': 'inspection',
                'sanitation-issuance': 'issuance'
            };
            // Read file inputs to base64 for workflow steps and set remarks
            const getFileBase64 = (file) => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(((reader.result || '').toString().split(',')[1]) || '');
                reader.onerror = () => reject(new Error('File read error'));
                reader.readAsDataURL(file);
            });

            // Generic File Handling for ALL Forms
            const fileInputs = formEl.querySelectorAll('input[type="file"]');
            for (const fin of fileInputs) {
                const f = formData.get(fin.name);
                if (f && typeof f === 'object' && 'size' in f && f.size > 0) {
                    data[fin.name + '_name'] = f.name;
                    data[fin.name + '_type'] = f.type;
                    data[fin.name + '_base64'] = await getFileBase64(f);
                }
            }

            if (stepMap[data.service_type]) {
                data.workflow_step = stepMap[data.service_type];
                data.service_type = 'sanitation-workflow';
                // Guard: only inspectors submit the inspection step
                const __isInspectorPortal = window.location.pathname.includes('/inspection/');
                if (data.workflow_step === 'inspection' && !__isInspectorPortal) {
                    alert('Inspection is handled by an assigned inspector. This step is not available here.');
                    submitButton.disabled = false; submitButton.textContent = originalText;
                    return;
                }

                if (data.workflow_step === 'submission') {
                    const names = ['business_permit_image', 'permit_receipt_image', 'health_certificates_image', 'occupancy_permit_image', 'water_analysis_image', 'pest_control_image'];
                    let allOk = true;
                    for (const n of names) {
                        if (!data[n + '_base64']) allOk = false;
                    }
                    data.remarks = allOk ? 'PASSED' : 'FAILED';
                    data.status = 'submitted';
                }
                if (data.workflow_step === 'payment') {
                    const required = ['or_number', 'amount', 'payment_date', 'payment_method'];
                    const ok = required.every(k => (data[k] !== undefined && String(data[k]).trim() !== ''));

                    data.remarks = ok ? 'PASSED' : 'FAILED';
                    data.status = 'submitted';
                }
            }

            try {
                // Resolve endpoint based on current path (works both on main site and inside citizen portal)
                const isCitizenPortal = window.location.pathname.includes('/citizen/');
                const endpoint = isCitizenPortal ? '../process_service_request.php' : 'process_service_request.php';

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                // If server returned HTML (e.g., login redirect), throw a readable error
                const text = await response.text();
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${text.slice(0, 120)}`);
                }
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                    throw new Error('Server returned HTML instead of JSON. Are you logged in?');
                }

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('JSON Parse Error. Server returned:', text);
                    throw new Error('Server returned invalid JSON response.');
                }

                if (result.success) {
                    // Custom Alert for Wastewater & Septic Services
                    const wastewaterServices = ['system-inspection', 'septic-registration', 'maintenance-service', 'wastewater-clearance'];
                    if (wastewaterServices.includes(data.service_type)) {
                        Swal.fire({
                            title: 'Request Submitted!',
                            text: 'Your Wastewater / Septic service request has been received. Our team will review your submission and contact you for the next steps.',
                            icon: 'success',
                            confirmButtonText: 'Great!',
                            customClass: {
                                container: 'my-swal'
                            }
                        }).then(() => {
                            if (window.location.pathname.includes('wastewater_septic.php')) {
                                window.location.reload();
                            }
                        });
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                        closeServiceForm();
                        return;
                    }
                    // Auto-advance sanitation steps
                    const appId = result.application_id || data.application_id || null;
                    const nextByStep = {
                        form_filing: 'sanitation-submission',
                        submission: null, // Stop for administrative review
                        payment: null,    // Stop for administrative payment verification
                        inspection: 'sanitation-issuance',
                        issuance: null
                    };
                    if (data.service_type === 'sanitation-workflow') {
                        const nextUi = nextByStep[data.workflow_step] || null;
                        if (nextUi && appId) { openServiceForm(nextUi, { applicationId: appId }); return; }
                    }
                    // After initial application creation, jump to Submission step
                    if (serviceTypeInput && serviceTypeInput.value === 'business-permit' && appId) {
                        openServiceForm('sanitation-submission', { applicationId: appId });
                        return;
                    }
                    if (data.workflow_step === 'submission') {
                        alert('Requirements submitted successfully! Your documents are now under review. You will be notified once you can proceed to payment.');
                    } else if (data.workflow_step === 'payment') {
                        alert('Payment details submitted successfully! Your proof of payment is now being verified by the administrator. You will be notified once it is approved and you can proceed to the inspection schedule.');
                    } else {
                        alert('Successfully submitted form.');
                    }
                    closeServiceForm();
                } else {
                    if (result.debug) {
                        console.error('Service submit debug:', result.debug);
                        alert('âŒ ' + result.message + '\n\nDetails: ' + JSON.stringify(result.debug));
                    } else {
                        alert(result.message || 'Submission failed.');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while submitting your request: ' + error.message);
            } finally {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                if (captchaInput) {
                    captchaInput.value = '';
                }
            }
        });
    }

    // Close on ESC key
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeHealthCenterModal();
            closeServiceForm();
        }
    });

    // Close modal when clicking outside
    if (serviceModal) {
        serviceModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeServiceForm();
            }
        });
    }

    async function deleteMedicalRecord(serviceType, recordId) {
        if (!confirm('Are you sure you want to PERMANENTLY delete this medical record? This action cannot be undone.')) return;

        try {
            const isCitizenPortal = window.location.pathname.includes('/citizen/');
            const endpoint = isCitizenPortal ? '../process_service_request.php' : 'process_service_request.php';

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_type: serviceType, record_id: recordId })
            });

            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message || 'Failed to delete record.');
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred while deleting the record.');
        }
    }

    // Load and display existing documents for sanitation submission
    async function loadExistingDocuments(applicationId) {
        if (!applicationId) return;

        const dynamicFields = document.getElementById('dynamicFields');
        if (!dynamicFields) return;

        // Create container for existing documents with loading state
        let docsContainer = document.getElementById('existingDocsContainer');
        if (!docsContainer) {
            docsContainer = document.createElement('div');
            docsContainer.id = 'existingDocsContainer';
            docsContainer.className = 'md:col-span-6 mb-6 p-5 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 transition-all';
            docsContainer.innerHTML = `
                <h4 class="text-sm font-black text-blue-800 dark:text-blue-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Checking for uploaded documents...
                </h4>
            `;
            // Insert at the beginning
            dynamicFields.insertBefore(docsContainer, dynamicFields.firstChild);
        }

        try {
            console.log('Fetching documents for App ID:', applicationId);
            const response = await fetch(`/hash-master/api/get_application_documents.php?application_id=${applicationId}`);
            const data = await response.json();
            console.log('API Response:', data);

            if (!data.success) {
                console.error('API Error:', data.message);
                docsContainer.remove();
                return;
            }

            if (!data.documents || data.documents.length === 0) {
                docsContainer.innerHTML = `
                    <h4 class="text-sm font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        No documents uploaded yet
                    </h4>
                    <p class="text-[10px] text-gray-400 mt-1 ml-7">Upload your requirements below to proceed.</p>
                `;
                return;
            }

            docsContainer.innerHTML = `
                <h4 class="text-sm font-black text-blue-800 dark:text-blue-300 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    📁 Your Uploaded Documents
                </h4>
                <div id="uploadedDocsList" class="space-y-2"></div>
                <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-3 italic">To replace a document, upload a new file in the field below.</p>
            `;

            const docsList = document.getElementById('uploadedDocsList');

            // Group by document type (show latest only)
            const docsByType = {};
            data.documents.forEach(doc => {
                if (!docsByType[doc.document_type]) {
                    docsByType[doc.document_type] = doc;
                }
            });

            // Display documents
            Object.values(docsByType).forEach(doc => {
                const statusStyles = {
                    'pending': 'bg-amber-100 text-amber-700',
                    'verified': 'bg-emerald-100 text-emerald-700',
                    'rejected': 'bg-red-100 text-red-700'
                };
                const statusClass = statusStyles[doc.status] || statusStyles['pending'];

                const docDiv = document.createElement('div');
                docDiv.className = 'flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700';
                docDiv.innerHTML = `
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-gray-900 dark:text-white truncate">${doc.label}</p>
                            <p class="text-[10px] text-gray-500">Uploaded: ${new Date(doc.uploaded_at).toLocaleDateString()}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase ${statusClass}">${doc.status}</span>
                        <a href="${doc.url}" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold uppercase rounded-lg transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View
                        </a>
                    </div>
                `;
                docsList.appendChild(docDiv);
            });

        } catch (err) {
            console.error('Error loading documents:', err);
            if (docsContainer) docsContainer.remove();
        }
    }

    // Similarly for health center modal
    const healthModal = document.getElementById('healthCenterInfoModal');
    if (healthModal) {
        healthModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeHealthCenterModal();
            }
        });
    }

    // Vaccine Schedule Modal
    function showVaccineSchedule() {
        const modalHTML = `
            <div id="vaccineScheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[20005] p-4">
                <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                    <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-5 flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-black">📋 Philippine DOH Vaccine Schedule</h2>
                            <p class="text-xs opacity-90 mt-1">National Immunization Program Reference</p>
                        </div>
                        <button onclick="this.closest('#vaccineScheduleModal').remove()" class="text-white hover:bg-white/20 rounded-lg p-2 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">
                        <!-- Infants and Young Children -->
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-800">
                            <h3 class="text-lg font-black text-blue-900 dark:text-blue-300 mb-4 flex items-center gap-2">
                                <span class="text-2xl">🧒</span> Infants and Young Children
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b-2 border-blue-300 dark:border-blue-700">
                                            <th class="text-left py-3 px-4 font-black text-blue-900 dark:text-blue-200 uppercase text-xs">Vaccine</th>
                                            <th class="text-left py-3 px-4 font-black text-blue-900 dark:text-blue-200 uppercase text-xs">Protects Against</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-blue-200 dark:divide-blue-800">
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">BCG</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Tuberculosis (TB)</td>
                                        </tr>
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Hepatitis B (HepB)</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Hepatitis B</td>
                                        </tr>
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Pentavalent Vaccine</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Diphtheria, Tetanus, Pertussis, Hepatitis B, Haemophilus influenzae type B</td>
                                        </tr>
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">OPV / IPV</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Polio</td>
                                        </tr>
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">PCV</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Pneumonia, meningitis</td>
                                        </tr>
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Measles-Rubella (MR)</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Measles and Rubella</td>
                                        </tr>
                                        <tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">MMR</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Measles, Mumps, Rubella (in some LGUs)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Older Children -->
                        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-2xl p-6 border border-emerald-200 dark:border-emerald-800">
                            <h3 class="text-lg font-black text-emerald-900 dark:text-emerald-300 mb-4 flex items-center gap-2">
                                <span class="text-2xl">👧👦</span> Older Children & Adolescents
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b-2 border-emerald-300 dark:border-emerald-700">
                                            <th class="text-left py-3 px-4 font-black text-emerald-900 dark:text-emerald-200 uppercase text-xs">Vaccine</th>
                                            <th class="text-left py-3 px-4 font-black text-emerald-900 dark:text-emerald-200 uppercase text-xs">Protects Against</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-emerald-200 dark:divide-emerald-800">
                                        <tr class="hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Tetanus-Diphtheria (Td)</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Tetanus and Diphtheria</td>
                                        </tr>
                                        <tr class="hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">HPV</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Cervical cancer and related diseases (usually for school-aged girls)</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pregnant Women -->
                        <div class="bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-900/20 dark:to-rose-900/20 rounded-2xl p-6 border border-pink-200 dark:border-pink-800">
                            <h3 class="text-lg font-black text-pink-900 dark:text-pink-300 mb-4 flex items-center gap-2">
                                <span class="text-2xl">🤰</span> Pregnant Women
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b-2 border-pink-300 dark:border-pink-700">
                                            <th class="text-left py-3 px-4 font-black text-pink-900 dark:text-pink-200 uppercase text-xs">Vaccine</th>
                                            <th class="text-left py-3 px-4 font-black text-pink-900 dark:text-pink-200 uppercase text-xs">Protects Against</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-pink-200 dark:divide-pink-800">
                                        <tr class="hover:bg-pink-100 dark:hover:bg-pink-900/40 transition-colors">
                                            <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">Tetanus Toxoid (TT / Td)</td>
                                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">Maternal and neonatal tetanus</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Footer Note -->
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-4 border-l-4 border-purple-500">
                            <p class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                <span class="text-purple-600 dark:text-purple-400">ℹ️ Note:</span> This schedule represents the standard Philippine DOH National Immunization Program. 
                                Always consult with healthcare providers for specific timing and additional vaccines.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
</script>