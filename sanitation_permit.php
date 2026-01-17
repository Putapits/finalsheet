<?php
/**
 * Sanitation Permit & Inspection Page - Premium Redesign
 */
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Determine verification status and fetch applications
$isVerified = false;
$applications = [];
if ($isLoggedIn) {
    $__u = $database->getUserById($_SESSION['user_id']);
    $isVerified = $__u && (($__u['verification_status'] ?? '') === 'verified');
    $applications = $database->getUserSanitaryPermitApplications($_SESSION['user_id']);
}

// SEO Meta Tags
$pageTitle = "Sanitation Permit & Inspection | Quezon City E-Services";
$metaDescription = "Apply for business sanitation permits and request health inspections.";

include 'header.php';
?>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .dark .glass-card {
        background: rgba(30, 41, 59, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .step-node {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .step-node:hover {
        transform: scale(1.1);
        z-index: 10;
    }

    .premium-banner {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }
</style>

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <!-- Notice Bar -->
    <div class="bg-emerald-600 text-white py-3 px-4 text-xs text-center font-black uppercase tracking-[0.2em]">
        Mandatory Online Filing System Active
    </div>

    <!-- Hero Section -->
    <section class="premium-banner pt-32 pb-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('img/gsmbg.png')] opacity-20 mix-blend-overlay"></div>
        <div class="max-w-7xl mx-auto px-4 relative z-10 text-center space-y-6">
            <span
                class="inline-block px-4 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest text-white">Compliance
                & Safety</span>
            <h1 class="text-5xl md:text-6xl font-black text-white tracking-tighter">Sanitation <span
                    class="text-emerald-200">Permits</span></h1>
            <p class="text-lg text-emerald-50 max-w-2xl mx-auto font-medium">Streamlined digital processing for
                commercial sanitation compliance and regular health inspections.</p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 -mt-16 relative z-20 pb-20">
        <!-- Process Timeline -->
        <div class="glass-card rounded-[3rem] p-12 shadow-2xl mb-12 border-emerald-500/10">
            <h3
                class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-[0.3em] text-center mb-12">
                Application Lifecycle</h3>

            <div class="relative flex flex-wrap justify-between items-start gap-y-12">
                <div class="absolute top-10 left-0 w-full h-0.5 bg-gray-100 dark:bg-white/5 hidden md:block"></div>

                <?php
                $steps = [
                    ['1', 'Register', 'Account Setup'],
                    ['2', 'File', 'Unified Form'],
                    ['3', 'Upload', 'Documents'],
                    ['4', 'Pay', 'Online/Bank'],
                    ['5', 'Inspect', 'Site Visit'],
                    ['6', 'Issue', 'Final Permit']
                ];
                foreach ($steps as $s): ?>
                    <div class="relative flex flex-col items-center text-center w-full sm:w-1/3 md:w-auto px-4 group">
                        <div
                            class="step-node w-20 h-20 rounded-[2rem] bg-white dark:bg-gray-800 border-4 border-emerald-500 flex items-center justify-center font-black text-2xl text-emerald-600 shadow-xl group-hover:bg-emerald-500 group-hover:text-white transition-all ring-8 ring-transparent group-hover:ring-emerald-500/10">
                            <?php echo $s[0]; ?>
                        </div>
                        <span
                            class="mt-6 text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest"><?php echo $s[1]; ?></span>
                        <span class="text-[10px] font-bold text-gray-400 mt-1"><?php echo $s[2]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- Main Content -->
        <div class="mt-8">
            <!-- Applicant Details Card -->
            <?php if ($isLoggedIn): ?>
                <div
                    class="glass-card rounded-[2.5rem] p-10 shadow-2xl mb-12 border-emerald-500/10 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 blur-[100px] rounded-full"></div>
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-sm font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Applicant
                            Details</h3>
                        <div class="h-px flex-1 bg-gray-200 dark:bg-white/5 mx-6"></div>
                        <button class="text-emerald-600 dark:text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>
                    </div>
                    <div class="grid md:grid-cols-3 gap-y-8 gap-x-12">
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">Full Name
                                </p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($__u['last_name'] . ', ' . $__u['first_name']); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">E-Mail</p>
                                <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                    <?php echo htmlspecialchars($__u['email']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">Birth Date
                                </p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo !empty($__u['date_of_birth']) ? date('m/d/Y', strtotime($__u['date_of_birth'])) : '---'; ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">Contact
                                    Number</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">
                                    <?php echo htmlspecialchars($__u['phone'] ?? '---'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-tighter mb-1">Gender</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white capitalize">
                                    <?php echo htmlspecialchars($__u['gender'] ?? '---'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-12 gap-10">
                <!-- Left Column: History -->
                <div class="lg:col-span-8 space-y-10">
                    <!-- History Card -->
                    <div class="glass-card rounded-[3rem] p-10 shadow-2xl border-emerald-500/10 min-h-[500px]">
                        <div class="flex items-center justify-between mb-10">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Application
                                    History</h3>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Tap or Click
                                    an entry to revisit</p>
                            </div>
                            <div
                                class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-gray-100 dark:border-white/5">
                                        <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                            Application ID</th>
                                        <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                            Date</th>
                                        <th class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                            Status</th>
                                        <th
                                            class="pb-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    <?php if (empty($applications)): ?>
                                        <tr>
                                            <td colspan="4" class="py-20 text-center">
                                                <div class="flex flex-col items-center opacity-20">
                                                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                    <p class="font-black uppercase tracking-widest">No history yet</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($applications as $app): ?>
                                            <tr class="group hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors cursor-pointer"
                                                onclick="revisitApplication('<?php echo $app['id']; ?>', '<?php echo $app['current_step'] ?: 'form_filing'; ?>', '<?php echo $app['step_status']; ?>')">
                                                <td
                                                    class="py-6 text-sm font-black text-emerald-600 dark:text-emerald-400 underline">
                                                    <?php echo str_pad($app['id'], 7, '0', STR_PAD_LEFT); ?>
                                                </td>
                                                <td class="py-6 text-sm font-bold text-gray-900 dark:text-white">
                                                    <?php echo date('m/d/Y', strtotime($app['created_at'])); ?>
                                                </td>
                                                <td class="py-6">
                                                    <?php
                                                    $statusClass = 'bg-gray-100 text-gray-600';
                                                    $statusText = 'Draft';

                                                    if ($app['current_step'] === 'form_filing' && $app['step_status'] === 'completed') {
                                                        $statusText = 'Initiated';
                                                        $statusClass = 'bg-blue-100 text-blue-700';
                                                    } else if ($app['current_step'] === 'submission') {
                                                        if ($app['step_status'] === 'completed') {
                                                            $statusText = 'Under Review';
                                                            $statusClass = 'bg-indigo-100 text-indigo-700';
                                                        } else if ($app['step_status'] === 'submitted') {
                                                            $statusText = 'Documents Submitted';
                                                            $statusClass = 'bg-blue-100 text-blue-700 animate-pulse';
                                                        } else {
                                                            $statusText = 'Draft (Uploading)';
                                                            $statusClass = 'bg-amber-100 text-amber-700';
                                                        }
                                                    } else if ($app['current_step'] === 'payment') {
                                                        if ($app['step_status'] === 'completed') {
                                                            $statusText = 'Paid / Scheduled';
                                                            $statusClass = 'bg-emerald-100 text-emerald-700';
                                                        } else if ($app['step_status'] === 'submitted') {
                                                            $statusText = 'Verifying Payment';
                                                            $statusClass = 'bg-blue-100 text-blue-700 animate-pulse';
                                                        } else {
                                                            $statusText = 'Payment Required';
                                                            $statusClass = 'bg-rose-100 text-rose-700 animate-pulse';
                                                        }
                                                    } else if ($app['current_step'] === 'inspection') {
                                                        if ($app['step_status'] === 'completed') {
                                                            $statusText = 'Inspected';
                                                            $statusClass = 'bg-emerald-100 text-emerald-700';
                                                        } else {
                                                            $statusText = 'Inspection Ongoing';
                                                            $statusClass = 'bg-blue-100 text-blue-700';
                                                        }
                                                    } else if ($app['current_step'] === 'issuance') {
                                                        $statusText = 'Permit Issued';
                                                        $statusClass = 'bg-emerald-600 text-white';
                                                    }
                                                    ?>
                                                    <span
                                                        class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?php echo $statusClass; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="py-6 text-right">
                                                    <div class="flex items-center justify-end gap-3">
                                                        <?php if ($app['step_status'] !== 'completed'): ?>
                                                            <button
                                                                onclick="event.stopPropagation(); deleteApplication('<?php echo $app['id']; ?>')"
                                                                class="p-2 rounded-xl text-gray-300 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all"
                                                                title="Cancel Application">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </button>
                                                        <?php endif; ?>

                                                        <div
                                                            class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all transform group-hover:translate-x-0 translate-x-4 shadow-lg shadow-emerald-500/30">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="3" d="M9 5l7 7-7 7"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Secondary Requests -->
                    <div class="grid md:grid-cols-2 gap-8">
                        <div
                            class="glass-card p-8 rounded-[2.5rem] shadow-xl border-emerald-500/10 group hover:-translate-y-2 transition-all">
                            <div class="flex items-center gap-6">
                                <div
                                    class="w-14 h-14 bg-blue-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/20">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-gray-900 dark:text-white">Health Inspection</h4>
                                    <button onclick="openServiceForm('health-inspection')"
                                        class="text-[10px] font-black text-blue-600 dark:text-blue-400 uppercase tracking-widest hover:underline mt-1">Schedule
                                        Visit →</button>
                                </div>
                            </div>
                        </div>

                        <div
                            class="glass-card p-8 rounded-[2.5rem] shadow-xl border-emerald-500/10 group hover:-translate-y-2 transition-all">
                            <div class="flex items-center gap-6">
                                <div
                                    class="w-14 h-14 bg-purple-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-purple-500/20">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-black text-gray-900 dark:text-white">Health Certificate</h4>
                                    <button onclick="openServiceForm('health-certificate')"
                                        class="text-[10px] font-black text-purple-600 dark:text-purple-400 uppercase tracking-widest hover:underline mt-1">Apply
                                        Online →</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: New Application -->
                <div class="lg:col-span-4 relative">
                    <div class="sticky top-24 space-y-8">
                        <!-- New Application Card -->
                        <div
                            class="glass-card rounded-[3rem] p-10 shadow-2xl border-emerald-500/10 relative overflow-hidden">
                            <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/5 blur-[100px] rounded-full">
                            </div>

                            <div class="flex items-center gap-4 mb-10">
                                <div
                                    class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">New
                                        Application</h3>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Start
                                        a
                                        digital filing</p>
                                </div>
                            </div>

                            <div class="space-y-6 mb-10">
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Application
                                        Type</label>
                                    <select id="appType"
                                        class="w-full h-14 px-6 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-white/10 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-gray-900 dark:text-white">
                                        <option value="" class="dark:bg-gray-800">Choose Type...</option>
                                        <option value="new_prov" class="dark:bg-gray-800">New Business w/ Provisional SP
                                        </option>
                                        <option value="renewal" class="dark:bg-gray-800">Renewal of Existing Permit
                                        </option>
                                        <option value="amendment" class="dark:bg-gray-800">Business Amendment</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Industry
                                        Sector</label>
                                    <select id="appIndustry"
                                        class="w-full h-14 px-6 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-white/10 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all text-gray-900 dark:text-white">
                                        <option value="" class="dark:bg-gray-800">Choose Sector...</option>
                                        <option value="Food" class="dark:bg-gray-800">Food & Beverage</option>
                                        <option value="Non-Food" class="dark:bg-gray-800">Service & Retail</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Sub-Industry</label>
                                    <select id="appSubIndustry" disabled
                                        class="w-full h-14 px-6 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-white/10 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all disabled:opacity-50 text-gray-900 dark:text-white">
                                        <option value="" class="dark:bg-gray-800">Select Sector First</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Specific
                                        Line</label>
                                    <select id="appBusinessLine" disabled
                                        class="w-full h-14 px-6 bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-white/10 rounded-2xl text-sm font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all disabled:opacity-50 text-gray-900 dark:text-white">
                                        <option value="" class="dark:bg-gray-800">Select Sub-Industry</option>
                                    </select>
                                </div>
                            </div>

                            <?php if ($isLoggedIn && $isVerified): ?>
                                <button onclick="startApplying()"
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-emerald-500/30 transition-all transform active:scale-95 group uppercase tracking-widest text-xs">
                                    Apply for Sanitary Permit
                                </button>
                            <?php else: ?>
                                <div class="p-6 bg-amber-500/5 border border-amber-500/20 rounded-3xl text-center">
                                    <p class="text-sm font-bold text-amber-600 mb-4 uppercase tracking-wider">Verification
                                        Required</p>
                                    <a href="index.php"
                                        class="inline-block px-8 py-3 bg-amber-50 rounded-xl text-amber-700 font-black text-xs">Verify
                                        Profile</a>
                                </div>
                            <?php endif; ?>
                        </div>


                    </div>
                </div> <!-- End sticky wrapper -->
            </div>
        </div>

    </section>
</main>

<script>
    async function deleteApplication(appId) {
        if (!confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch('api/delete_application.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ application_id: appId })
            });
            const result = await response.json();
            if (result.success) {
                // Refresh the page to show updated history
                location.reload();
            } else {
                alert(result.message || 'Failed to delete application.');
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('An error occurred while deleting the application.');
        }
    }
    function startApplying() {
        const appType = document.getElementById('appType').value;
        const industry = document.getElementById('appIndustry').value;
        const subIndustry = document.getElementById('appSubIndustry').value;
        const businessLine = document.getElementById('appBusinessLine').value;

        if (!appType || !industry || !subIndustry || !businessLine) {
            alert('Selection Required: Please complete all business details for processing.');
            return;
        }

        openServiceForm('business-permit', {
            appType: appType,
            industry: industry,
            subIndustry: subIndustry,
            businessLine: businessLine
        });
    }

    function revisitApplication(appId, currentStep, status) {
        if (status === 'submitted' && !['inspection', 'issuance'].includes(currentStep)) {
            const msg = (currentStep === 'payment')
                ? 'Your payment is currently being verified by the administrator. Please wait for approval before proceeding to the inspection schedule.'
                : 'Your documents are currently under review. Please wait for administrator approval before proceeding to the payment step.';
            alert(msg);
            return;
        }
        let targetModal = 'business-permit';

        // Determine which modal to open based on the current step
        const stepMapping = {
            'form_filing': 'sanitation-submission',
            'submission': 'sanitation-submission',
            'payment': 'sanitation-payment',
            'inspection': 'sanitation-workflow', // sanitation-inspection
            'issuance': 'sanitation-workflow'    // sanitation-issuance
        };

        // If a step is completed, move to the next one
        if (status === 'completed') {
            if (currentStep === 'form_filing') targetModal = 'sanitation-submission';
            else if (currentStep === 'submission') targetModal = 'sanitation-payment';
            else if (currentStep === 'payment') targetModal = 'sanitation-workflow'; // inspection
            else if (currentStep === 'inspection') targetModal = 'sanitation-workflow'; // issuance
            else targetModal = 'sanitation-workflow';
        } else {
            // If pending or rejected, stay on the same modal
            if (currentStep === 'form_filing') targetModal = 'sanitation-submission';
            else if (currentStep === 'submission') targetModal = 'sanitation-submission';
            else if (currentStep === 'payment') targetModal = 'sanitation-payment';
            else if (currentStep === 'inspection') targetModal = 'sanitation-workflow';
            else targetModal = 'sanitation-workflow';
        }

        // Special handling for workflow-based modals
        const workflowSteps = {
            'inspection': 'sanitation-inspection',
            'issuance': 'sanitation-issuance'
        };

        let uiType = workflowSteps[currentStep] || targetModal;
        let metadata = { applicationId: appId };

        // Citizens see inspection results even when step is 'issuance'
        if (currentStep === 'issuance') {
            uiType = 'sanitation-inspection';
            metadata.readOnly = true;
        } else {
            if (status === 'completed' && currentStep === 'payment') uiType = 'sanitation-inspection';
            if (status === 'completed' && currentStep === 'inspection') uiType = 'sanitation-issuance';
        }

        openServiceForm(uiType, metadata);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const indSelect = document.getElementById('appIndustry');
        const subIndSelect = document.getElementById('appSubIndustry');
        const lineSelect = document.getElementById('appBusinessLine');

        indSelect?.addEventListener('change', function () {
            subIndSelect.innerHTML = '<option value="">Choose Sub-Sector...</option>';
            lineSelect.innerHTML = '<option value="">Select Sub-Industry</option>';
            lineSelect.disabled = true;

            if (this.value && typeof industryData !== 'undefined' && industryData[this.value]) {
                subIndSelect.disabled = false;
                Object.keys(industryData[this.value]).forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub; opt.textContent = sub;
                    subIndSelect.appendChild(opt);
                });
            } else { subIndSelect.disabled = true; }
        });

        subIndSelect?.addEventListener('change', function () {
            const ind = indSelect.value;
            lineSelect.innerHTML = '<option value="">Choose Business Line...</option>';

            if (ind && this.value && industryData[ind][this.value]) {
                lineSelect.disabled = false;
                industryData[ind][this.value].forEach(line => {
                    const opt = document.createElement('option');
                    opt.value = line; opt.textContent = line;
                    lineSelect.appendChild(opt);
                });
            } else { lineSelect.disabled = true; }
        });
    });
</script>

<?php
include 'include/service_modals.php';
include 'footer.php';
?>