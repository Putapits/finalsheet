<?php
// Start session and check login status
require_once '../include/database.php';
startSecureSession();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../index.php');
    exit();
}

// Ensure this page is always served inside the citizen portal layout for styling
if (basename($_SERVER['SCRIPT_NAME']) === 'profile.php') {
    header('Location: citizen.php?page=profile');
    exit();
}

// Handle cancellation requests
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_appointment'])) {
        $appointment_id = (int) $_POST['appointment_id'];
        if ($database->cancelAppointment($appointment_id, $_SESSION['user_id'])) {
            $message = 'Appointment cancelled successfully.';
            $message_type = 'success';
        } else {
            // Determine current status for a clearer message
            try {
                $stmt = $db->prepare('SELECT status FROM appointments WHERE id = :id AND user_id = :uid');
                $stmt->execute([':id' => $appointment_id, ':uid' => $_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $st = $row['status'] ?? '';
                if ($st === 'confirmed') {
                    $message = "You can't cancel this appointment because it has been confirmed by the doctor.";
                } elseif ($st === 'completed') {
                    $message = "You can't cancel a completed appointment.";
                } elseif ($st === 'cancelled') {
                    $message = 'This appointment is already cancelled.';
                } elseif ($st === 'pending') {
                    // Should have succeeded; generic fallback
                    $message = 'Failed to cancel appointment. Please try again.';
                } else {
                    $message = 'Unable to cancel this appointment at this time.';
                }
            } catch (Throwable $e) {
                $message = 'Unable to cancel this appointment at this time.';
            }
            $message_type = 'error';
        }
    } elseif (isset($_POST['cancel_service'])) {
        $service_id = (int) $_POST['service_id'];
        if ($database->cancelServiceRequest($service_id, $_SESSION['user_id'])) {
            $message = 'Service request cancelled successfully.';
            $message_type = 'success';
        } else {
            // Determine current status for a clearer message
            try {
                $stmt = $db->prepare('SELECT status FROM service_requests WHERE id = :id AND user_id = :uid');
                $stmt->execute([':id' => $service_id, ':uid' => $_SESSION['user_id']]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $st = $row['status'] ?? '';
                if ($st === 'in_progress') {
                    $message = "You can't cancel this request because it is already in progress.";
                } elseif ($st === 'completed') {
                    $message = "You can't cancel a completed request.";
                } elseif ($st === 'cancelled') {
                    $message = 'This request is already cancelled.';
                } elseif ($st === 'pending') {
                    // Should have succeeded; generic fallback
                    $message = 'Failed to cancel service request. Please try again.';
                } else {
                    $message = 'Unable to cancel this service request at this time.';
                }
            } catch (Throwable $e) {
                $message = 'Unable to cancel this service request at this time.';
            }
            $message_type = 'error';
        }
    }
}

// Get user appointments and service requests
$appointments = $database->getUserAppointments($_SESSION['user_id']);
$serviceRequests = $database->getUserServiceRequests($_SESSION['user_id']);
// Fetch verification status from DB
$__user = $database->getUserById($_SESSION['user_id']);
$verification_status = $database->isUserVerified($_SESSION['user_id']) ? 'verified' : ($__user['verification_status'] ?? 'unverified');

// Use real database-backed user data
$user_data = [
    'id' => (int) ($_SESSION['user_id']),
    'first_name' => $__user['first_name'] ?? ($_SESSION['first_name'] ?? 'User'),
    'last_name' => $__user['last_name'] ?? ($_SESSION['last_name'] ?? 'Name'),
    'email' => $__user['email'] ?? ($_SESSION['email'] ?? 'user@example.com'),
    'phone' => $__user['phone'] ?? '',
    'address' => $__user['address'] ?? '',
    // Keep raw YYYY-MM-DD for inputs; format at display-time
    'date_of_birth' => isset($__user['date_of_birth']) ? ($__user['date_of_birth'] ?: '') : '',
    // Normalize to lowercase to match DB enum
    'gender' => isset($__user['gender']) ? strtolower($__user['gender']) : '',
    'profile_picture' => $__user['profile_picture'] ?? null,
    'verification_status' => $verification_status,
    'documents' => []
];

// Compute profile image source path (web path)
$profile_img_src = null;
if (!empty($user_data['profile_picture'])) {
    $pp = $user_data['profile_picture'];
    if (strpos($pp, 'http://') === 0 || strpos($pp, 'https://') === 0 || strpos($pp, '../') === 0) {
        $profile_img_src = $pp;
    } else {
        $profile_img_src = '../' . ltrim($pp, '/');
    }
}
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">My Profile & Activity</h1>
            <p class="mt-2 text-lg text-gray-500 dark:text-gray-400">Track your interactions and keep your information
                up to date.</p>
        </div>

        <!-- Flash Messages (from uploads) -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <div
                class="mb-6 p-4 rounded-md <?php echo ($_SESSION['flash_type'] ?? 'success') === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
                <?php echo htmlspecialchars($_SESSION['flash_message']);
                unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>

        <!-- Display Messages (page-local) -->
        <?php if (!empty($message)): ?>
            <div
                class="mb-6 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Account Verification Card -->
        <div class="mb-12">
            <div class="glass-card rounded-[2rem] shadow-xl overflow-hidden border-0">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Account Verification</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enhance your account security and
                            access all services.</p>
                    </div>
                    <div>
                        <?php
                        $vsBadge = '';
                        if ($verification_status === 'verified') {
                            $vsBadge = '<span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Verified</span>';
                        } elseif ($verification_status === 'pending') {
                            $vsBadge = '<span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Pending</span>';
                        } elseif ($verification_status === 'rejected') {
                            $vsBadge = '<span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Rejected</span>';
                        } else {
                            $vsBadge = '<span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Unverified</span>';
                        }
                        echo $vsBadge;
                        ?>
                    </div>
                </div>
                <div class="p-6">
                    <?php if ($verification_status === 'verified'): ?>
                        <div class="text-sm text-green-700 dark:text-green-300">Your account is verified. You can submit
                            appointments and service requests.</div>
                    <?php elseif ($verification_status === 'pending'): ?>
                        <div class="text-sm text-yellow-700 dark:text-yellow-300">Your verification is under review. You’ll
                            be notified once it’s approved.</div>
                    <?php else: ?>
                        <?php if ($verification_status === 'rejected'): ?>
                            <div class="mb-4 text-sm text-red-700 dark:text-red-300">Your previous submission was rejected.
                                Please upload a clearer photo or a different valid ID.</div>
                        <?php else: ?>
                            <div class="mb-4 text-sm text-gray-700 dark:text-gray-300">Please upload a government-issued ID.
                                Accepted formats: JPG, PNG, or PDF (max 5MB).</div>
                        <?php endif; ?>
                        <form method="POST" action="upload_verification.php" enctype="multipart/form-data"
                            class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Document
                                    Type</label>
                                <select name="document_type" required
                                    class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                    <option value="">Select a document</option>
                                    <option value="national_id">National ID</option>
                                    <option value="drivers_license">Driver's License</option>
                                    <option value="passport">Passport</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload
                                    File</label>
                                <input type="file" name="document_file" accept="image/*,.pdf" required
                                    class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                    class="bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">Submit
                                    for Verification</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Tab Navigation -->
        <div class="mb-10 p-1.5 bg-gray-100 dark:bg-white/5 rounded-3xl inline-flex w-auto">
            <button onclick="showTab('appointments')" id="appointments-tab"
                class="tab-button active px-8 py-3 rounded-2xl font-bold text-sm transition-all">
                Appointments
            </button>
            <button onclick="showTab('services')" id="services-tab"
                class="tab-button px-8 py-3 rounded-2xl font-bold text-sm transition-all text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Services
            </button>
            <button onclick="showTab('profile')" id="profile-tab"
                class="tab-button px-8 py-3 rounded-2xl font-bold text-sm transition-all text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Settings
            </button>
        </div>

        <style>
            .tab-button.active {
                background: white;
                color: #4f46e5;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            }

            .dark .tab-button.active {
                background: rgba(255, 255, 255, 0.1);
                color: white;
            }
        </style>

        <!-- Appointments Tab -->
        <div id="appointments-content" class="tab-content transition-all duration-300">
            <div class="glass-card rounded-[2rem] shadow-xl border-0">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Appointment History</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track your upcoming and past medical
                        consultations.</p>
                </div>
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <div class="relative w-full md:w-80">
                            <input id="appt-search" type="text"
                                placeholder="Search appointments (type, name, phone, etc.)"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-primary">
                            <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="w-full md:w-60">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Status</label>
                            <select id="appt-status-filter"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <?php if (empty($appointments)): ?>
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No appointments yet</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">You haven't booked any appointments yet.</p>
                            <a href="../website.php#appointment"
                                class="bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                Book Your First Appointment
                            </a>
                        </div>
                    <?php else: ?>
                        <div id="appt-list" class="space-y-6">
                            <?php foreach ($appointments as $appointment): ?>
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md transition-shadow appt-card"
                                    data-status="<?php echo htmlspecialchars($appointment['status']); ?>">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($appointment['appointment_type']); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Appointment #<?php echo $appointment['id']; ?>
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                        <?php
                                        switch ($appointment['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                break;
                                            case 'confirmed':
                                                echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                break;
                                            case 'completed':
                                                echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                                break;
                                        }
                                        ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient Name:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Date:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo date('F j, Y', strtotime($appointment['preferred_date'])); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Contact:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($appointment['phone']); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Submitted:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo date('M j, Y g:i A', strtotime($appointment['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Health Concerns:
                                        </p>
                                        <p
                                            class="text-gray-900 dark:text-white text-sm bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <?php echo htmlspecialchars($appointment['health_concerns']); ?>
                                        </p>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <button onclick="viewAppointmentDetails(<?php echo $appointment['id']; ?>)"
                                            class="text-primary hover:text-blue-700 font-medium text-sm">
                                            View Full Details
                                        </button>
                                        <?php if ($appointment['status'] === 'pending'): ?>
                                            <form method="POST" class="inline"
                                                onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                                                <input type="hidden" name="appointment_id"
                                                    value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" name="cancel_appointment"
                                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded text-sm transition-colors">
                                                    Cancel Appointment
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="appt-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Service Requests Tab -->
        <div id="services-content" class="tab-content transition-all duration-300" style="display: none;">
            <div class="glass-card rounded-[2rem] shadow-xl border-0">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Applied Services</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Status and details of your sanitation and
                        health service requests.</p>
                </div>
                <div class="p-8">
                    <?php $serviceTypes = [];
                    if (!empty($serviceRequests)) {
                        foreach ($serviceRequests as $__svc) {
                            $tt = $__svc['service_type'];
                            if ($tt !== null && !in_array($tt, $serviceTypes, true)) {
                                $serviceTypes[] = $tt;
                            }
                        }
                    } ?>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <div class="relative w-full md:w-80">
                            <input id="svc-search" type="text"
                                placeholder="Search service requests (type, name, phone, details...)"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-primary">
                            <svg class="absolute right-3 top-2.5 h-5 w-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="w-full md:w-60">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Status</label>
                            <select id="svc-status-filter"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="w-full md:w-60">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Type</label>
                            <select id="svc-type-filter"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                                <option value="">All Types</option>
                                <?php foreach ($serviceTypes as $__t): ?>
                                    <option value="<?php echo htmlspecialchars($__t); ?>">
                                        <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $__t))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-full md:w-60">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Urgency</label>
                            <select id="svc-urgency-filter"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                                <option value="">All</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Submitted From</label>
                            <input id="svc-date-from" type="date"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                        </div>
                        <div class="w-full md:w-56">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Submitted To</label>
                            <input id="svc-date-to" type="date"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                        </div>
                        <div class="w-full md:w-60">
                            <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Sort By</label>
                            <select id="svc-sort"
                                class="w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-3 py-2 border border-gray-200 dark:border-gray-600">
                                <option value="created_desc">Newest (Submitted)</option>
                                <option value="created_asc">Oldest (Submitted)</option>
                                <option value="urgency_desc">Urgency (High→Low)</option>
                                <option value="urgency_asc">Urgency (Low→High)</option>
                                <option value="status_az">Status (A→Z)</option>
                                <option value="status_za">Status (Z→A)</option>
                                <option value="type_az">Type (A→Z)</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-xs text-transparent mb-1">Clear</label>
                            <button id="svc-clear"
                                class="w-full md:w-auto px-3 py-2 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Clear
                                Filters</button>
                        </div>
                    </div>
                    <?php if (empty($serviceRequests)): ?>
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No service requests yet</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">You haven't submitted any service requests yet.
                            </p>
                            <a href="citizen.php?page=services"
                                class="bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                Request a Service
                            </a>
                        </div>
                    <?php else: ?>
                        <div id="svc-list" class="space-y-6">
                            <?php foreach ($serviceRequests as $service): ?>
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-6 hover:shadow-md transition-shadow svc-card"
                                    data-status="<?php echo htmlspecialchars(strtolower($service['status'])); ?>"
                                    data-type="<?php echo htmlspecialchars(strtolower($service['service_type'])); ?>"
                                    data-urgency="<?php echo htmlspecialchars(strtolower($service['urgency'])); ?>"
                                    data-created="<?php echo htmlspecialchars(date('c', strtotime($service['created_at']))); ?>">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $service['service_type']))); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Request #<?php echo $service['id']; ?>
                                            </p>
                                        </div>
                                        <div class="flex flex-col items-end space-y-2">
                                            <span class="px-3 py-1 rounded-full text-xs font-medium
                                            <?php
                                            switch ($service['status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                                                    break;
                                                case 'in_progress':
                                                    echo 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
                                                    break;
                                                case 'completed':
                                                    echo 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                                    break;
                                            }
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $service['status'])); ?>
                                            </span>
                                            <span class="px-2 py-1 rounded text-xs font-medium
                                            <?php
                                            switch ($service['urgency']) {
                                                case 'low':
                                                    echo 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                                    break;
                                                case 'medium':
                                                    echo 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
                                                    break;
                                                case 'high':
                                                    echo 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                                                    break;
                                                case 'emergency':
                                                    echo 'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100';
                                                    break;
                                            }
                                            ?>">
                                                <?php echo ucfirst($service['urgency']); ?> Priority
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Requester:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($service['full_name']); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Contact:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo htmlspecialchars($service['phone']); ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Date:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo $service['preferred_date'] ? date('F j, Y', strtotime($service['preferred_date'])) : 'Not specified'; ?>
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Submitted:</p>
                                            <p class="text-gray-900 dark:text-white">
                                                <?php echo date('M j, Y g:i A', strtotime($service['created_at'])); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Details:
                                        </p>
                                        <p
                                            class="text-gray-900 dark:text-white text-sm bg-gray-50 dark:bg-gray-700 p-3 rounded">
                                            <?php echo htmlspecialchars($service['service_details']); ?>
                                        </p>
                                    </div>

                                    <?php if (!empty($service['address'])): ?>
                                        <div class="mb-4">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Address:
                                            </p>
                                            <p class="text-gray-900 dark:text-white text-sm">
                                                <?php echo htmlspecialchars($service['address']); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex justify-between items-center">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Last updated: <?php echo date('M j, Y g:i A', strtotime($service['updated_at'])); ?>
                                        </div>
                                        <?php if ($service['status'] === 'pending'): ?>
                                            <form method="POST" class="inline"
                                                onsubmit="return confirm('Are you sure you want to cancel this service request?')">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <button type="submit" name="cancel_service"
                                                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded text-sm transition-colors">
                                                    Cancel Request
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="svc-pagination" class="mt-4 flex items-center justify-end gap-2 text-sm"></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Settings Tab -->
        <div id="profile-content" class="tab-content transition-all duration-300" style="display: none;">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profile Card -->
                <div class="lg:col-span-1">
                    <div class="glass-card rounded-[2rem] shadow-xl border-0 p-8">
                        <!-- Profile Picture -->
                        <div class="text-center mb-6">
                            <div class="relative inline-block">
                                <div id="profile-picture-container"
                                    class="w-32 h-32 bg-primary rounded-full flex items-center justify-center mx-auto mb-4 overflow-hidden">
                                    <?php if ($profile_img_src): ?>
                                        <img src="<?php echo htmlspecialchars($profile_img_src); ?>" alt="Profile"
                                            class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span
                                            class="text-4xl text-white font-bold"><?php echo strtoupper(substr($user_data['first_name'], 0, 1) . substr($user_data['last_name'], 0, 1)); ?></span>
                                    <?php endif; ?>
                                </div>
                                <button onclick="document.getElementById('profile-picture-input').click()"
                                    class="absolute bottom-0 right-0 bg-primary hover:bg-blue-700 text-white rounded-full p-2 shadow-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                                <form id="profile-picture-form" method="POST" action="upload_profile_picture.php"
                                    enctype="multipart/form-data">
                                    <input type="file" id="profile-picture-input" name="profile_picture"
                                        accept="image/*" class="hidden" onchange="uploadProfilePicture(this)">
                                </form>
                            </div>
                            <h2 id="profile-name" class="text-xl font-bold text-gray-900 dark:text-white">
                                <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>
                            </h2>
                            <p id="profile-email" class="text-gray-600 dark:text-gray-400">
                                <?php echo htmlspecialchars($user_data['email']); ?>
                            </p>
                        </div>

                        <!-- Verification Status -->
                        <div class="mb-6">
                            <div
                                class="flex items-center justify-between p-3 rounded-lg <?php echo $verification_status === 'verified' ? 'bg-green-100 dark:bg-green-900' : 'bg-yellow-100 dark:bg-yellow-900'; ?>">
                                <div class="flex items-center">
                                    <?php if ($verification_status === 'verified'): ?>
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-2" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-green-800 dark:text-green-200 font-medium">Verified Account</span>
                                    <?php else: ?>
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-yellow-800 dark:text-yellow-200 font-medium">Pending
                                            Verification</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="space-y-3">
                            <button onclick="openEditModal()"
                                class="w-full bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                Edit Profile
                            </button>
                            <button onclick="openDocumentModal()"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                Upload Documents
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Personal Information -->
                    <div class="glass-card rounded-[2rem] shadow-xl border-0">
                        <div class="p-8 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Account Details</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First
                                        Name</label>
                                    <p id="pi-first" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo htmlspecialchars($user_data['first_name']); ?>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last
                                        Name</label>
                                    <p id="pi-last" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo htmlspecialchars($user_data['last_name']); ?>
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <p id="pi-email" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo htmlspecialchars($user_data['email']); ?>
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                                    <p id="pi-phone" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo ($user_data['phone'] !== '' ? htmlspecialchars($user_data['phone']) : 'N/A'); ?>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date
                                        of Birth</label>
                                    <p id="pi-dob" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo ($user_data['date_of_birth'] ? date('F j, Y', strtotime($user_data['date_of_birth'])) : 'N/A'); ?>
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Gender</label>
                                    <p id="pi-gender" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo ($user_data['gender'] ? ucfirst(htmlspecialchars($user_data['gender'])) : 'N/A'); ?>
                                    </p>
                                </div>
                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                                    <p id="pi-address" class="text-gray-900 dark:text-white font-medium">
                                        <?php echo ($user_data['address'] !== '' ? htmlspecialchars($user_data['address']) : 'N/A'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Verification -->
                    <div class="glass-card rounded-[2rem] shadow-xl border-0">
                        <div class="p-8 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Stored Documents</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and view your uploaded
                                identification files.</p>
                        </div>
                        <div class="p-6">
                            <div id="documents-list" class="space-y-4">
                                <?php if (empty($user_data['documents'])): ?>
                                    <div class="text-center py-8">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">No documents uploaded yet</p>
                                        <button onclick="openDocumentModal()"
                                            class="mt-4 bg-primary hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                            Upload First Document
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h2>
                    <button onclick="closeEditModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="editProfileForm" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First
                                Name</label>
                            <input type="text" name="first_name" value="<?php echo $user_data['first_name']; ?>"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last
                                Name</label>
                            <input type="text" name="last_name" value="<?php echo $user_data['last_name']; ?>"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                            <input type="email" name="email" value="<?php echo $user_data['email']; ?>"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label>
                            <input type="tel" name="phone" value="<?php echo $user_data['phone']; ?>"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date of
                                Birth</label>
                            <input type="date" name="date_of_birth" value="<?php echo $user_data['date_of_birth']; ?>"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Gender</label>
                            <select name="gender"
                                class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="male" <?php echo $user_data['gender'] === 'male' ? 'selected' : ''; ?>>Male
                                </option>
                                <option value="female" <?php echo $user_data['gender'] === 'female' ? 'selected' : ''; ?>>
                                    Female</option>
                                <option value="other" <?php echo $user_data['gender'] === 'other' ? 'selected' : ''; ?>>
                                    Other</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label>
                        <textarea name="address" rows="3"
                            class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"><?php echo $user_data['address']; ?></textarea>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeEditModal()"
                            class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary hover:bg-blue-700 text-white rounded-lg">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Document Upload Modal -->
    <div id="documentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Upload Document</h2>
                    <button onclick="closeDocumentModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="documentUploadForm" class="space-y-6" method="POST" action="upload_verification.php"
                    enctype="multipart/form-data">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Document
                            Type</label>
                        <select name="document_type" required
                            class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Select document type</option>
                            <option value="id">Government ID</option>
                            <option value="passport">Passport</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="proof_of_address">Proof of Address</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Upload
                            File</label>
                        <div
                            class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-primary transition-colors">
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required class="hidden"
                                id="document-input">
                            <label for="document-input" class="cursor-pointer">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                    </path>
                                </svg>
                                <p class="text-gray-600 dark:text-gray-400">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">PDF, JPG, PNG up to 10MB</p>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="button" onclick="closeDocumentModal()"
                            class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-primary hover:bg-blue-700 text-white rounded-lg">
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Profile picture upload
        function uploadProfilePicture(input) {
            const file = input.files && input.files[0];
            if (!file) return;
            const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (!allowed.includes(file.type)) {
                alert('Please upload an image (JPG, PNG, GIF, or WEBP).');
                input.value = '';
                return;
            }
            if (file.size > maxSize) {
                alert('Image too large. Maximum size is 5MB.');
                input.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function (e) {
                const container = document.getElementById('profile-picture-container');
                container.innerHTML = `<img src="${e.target.result}" alt="Profile" class="w-full h-full object-cover">`;
            };
            reader.readAsDataURL(file);
            // Submit to server for persistence
            document.getElementById('profile-picture-form').submit();
        }

        // Modal functions
        function openEditModal() {
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openDocumentModal() {
            document.getElementById('documentModal').classList.remove('hidden');
        }

        function closeDocumentModal() {
            document.getElementById('documentModal').classList.add('hidden');
        }

        // Form submissions
        document.getElementById('editProfileForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const form = e.currentTarget;
            const payload = {
                first_name: form.elements['first_name'].value.trim(),
                last_name: form.elements['last_name'].value.trim(),
                email: form.elements['email'].value.trim(),
                phone: form.elements['phone'].value.trim(),
                date_of_birth: form.elements['date_of_birth'].value,
                gender: form.elements['gender'].value,
                address: form.elements['address'].value.trim(),
            };

            try {
                const res = await fetch('update_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || !json.success) {
                    throw new Error(json.message || ('HTTP ' + res.status));
                }
                // Update visible personal info
                const cap = s => s ? (s.charAt(0).toUpperCase() + s.slice(1)) : '';
                const fullName = `${payload.first_name} ${payload.last_name}`.trim();
                const dobPretty = payload.date_of_birth ? new Date(payload.date_of_birth + 'T00:00:00').toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
                (document.getElementById('profile-name') || {}).textContent = fullName;
                (document.getElementById('profile-email') || {}).textContent = payload.email;
                (document.getElementById('pi-first') || {}).textContent = payload.first_name || 'N/A';
                (document.getElementById('pi-last') || {}).textContent = payload.last_name || 'N/A';
                (document.getElementById('pi-email') || {}).textContent = payload.email || 'N/A';
                (document.getElementById('pi-phone') || {}).textContent = payload.phone || 'N/A';
                (document.getElementById('pi-dob') || {}).textContent = dobPretty;
                (document.getElementById('pi-gender') || {}).textContent = payload.gender ? cap(payload.gender) : 'N/A';
                (document.getElementById('pi-address') || {}).textContent = payload.address || 'N/A';

                alert('Profile updated successfully!');
                closeEditModal();
            } catch (err) {
                alert('Failed to update profile: ' + (err && err.message ? err.message : 'Unknown error'));
            }
        });

        document.getElementById('documentUploadForm').addEventListener('submit', function (e) {
            const typeSelect = this.querySelector('select[name="document_type"]');
            const fileInput = document.getElementById('document-input');
            if (!typeSelect.value || !fileInput.files.length) {
                e.preventDefault();
                alert('Please select a document type and choose a file.');
                return;
            }
            // allow default submit to backend
        });

        // Close modals when clicking outside
        document.getElementById('editModal').addEventListener('click', function (e) {
            if (e.target === this) closeEditModal();
        });

        document.getElementById('documentModal').addEventListener('click', function (e) {
            if (e.target === this) closeDocumentModal();
        });

        // Tab functionality
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.style.display = 'none';
            });

            // Remove active class from all tab buttons
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.classList.remove('active');
                button.classList.remove('border-primary', 'text-primary');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });

            // Show selected tab content
            document.getElementById(tabName + '-content').style.display = 'block';

            // Add active class to selected tab button
            const activeButton = document.getElementById(tabName + '-tab');
            activeButton.classList.add('active');
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            activeButton.classList.add('border-primary', 'text-primary');
        }

        // Initialize tabs
        document.addEventListener('DOMContentLoaded', function () {
            // Set initial tab styles
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                if (!button.classList.contains('active')) {
                    button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                } else {
                    button.classList.add('border-primary', 'text-primary');
                }
            });
        });

        // View appointment details function
        function viewAppointmentDetails(appointmentId) {
            // Find the appointment data
            const appointments = <?php echo json_encode($appointments); ?>;
            const appointment = appointments.find(apt => apt.id == appointmentId);

            if (!appointment) return;

            // Create modal content
            const modalContent = `
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" id="appointmentDetailsModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Appointment Details</h2>
                        <button onclick="closeAppointmentDetails()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Full Name:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.first_name} ${appointment.middle_name || ''} ${appointment.last_name}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Email:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.email}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.phone}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth:</label>
                                        <p class="text-gray-900 dark:text-white">${new Date(appointment.birth_date).toLocaleDateString()}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Gender:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.gender}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Civil Status:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.civil_status}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Emergency Contact</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.emergency_contact_name}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.emergency_contact_phone}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Appointment Information</h3>
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type:</label>
                                        <p class="text-gray-900 dark:text-white">${appointment.appointment_type}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Date:</label>
                                        <p class="text-gray-900 dark:text-white">${new Date(appointment.preferred_date).toLocaleDateString()}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                                        <p class="text-gray-900 dark:text-white capitalize">${appointment.status}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Address</h3>
                                <p class="text-gray-900 dark:text-white">${appointment.address}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Health Concerns</h3>
                            <p class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 p-3 rounded">${appointment.health_concerns}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Medical History</h3>
                            <p class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 p-3 rounded">${appointment.medical_history}</p>
                        </div>
                        
                        ${appointment.current_medications ? `
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Current Medications</h3>
                            <p class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 p-3 rounded">${appointment.current_medications}</p>
                        </div>
                        ` : ''}
                        
                        ${appointment.allergies ? `
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Known Allergies</h3>
                            <p class="text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 p-3 rounded">${appointment.allergies}</p>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button onclick="closeAppointmentDetails()" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalContent);
        }

        function closeAppointmentDetails() {
            const modal = document.getElementById('appointmentDetailsModal');
            if (modal) {
                modal.remove();
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target && e.target.id === 'appointmentDetailsModal') {
                closeAppointmentDetails();
            }
        });

        // Filtering & search for Appointments
        (function () {
            const apptSearch = document.getElementById('appt-search');
            const apptStatus = document.getElementById('appt-status-filter');
            const apptList = document.getElementById('appt-list');
            const apptPagEl = document.getElementById('appt-pagination');
            let apptPage = 1; const apptPageSize = 5;
            function apptCards() { return Array.from(document.querySelectorAll('#appt-list .appt-card')); }

            function renderApptPagination(pages) {
                if (!apptPagEl) return;
                apptPagEl.innerHTML = '';
                const mkBtn = (label, onClick, disabled = false, active = false) => {
                    const b = document.createElement('button');
                    b.textContent = label;
                    b.className = 'px-2 py-1 rounded ' + (active ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
                    b.disabled = !!disabled;
                    if (disabled) b.classList.add('opacity-50', 'cursor-not-allowed');
                    b.addEventListener('click', onClick);
                    return b;
                };
                const prev = mkBtn('Prev', () => { if (apptPage > 1) { apptPage--; filterAppts(); } }, apptPage <= 1);
                apptPagEl.appendChild(prev);
                for (let i = 1; i <= pages; i++) {
                    apptPagEl.appendChild(mkBtn(String(i), () => { apptPage = i; filterAppts(); }, false, i === apptPage));
                }
                const next = mkBtn('Next', () => { if (apptPage < pages) { apptPage++; filterAppts(); } }, apptPage >= pages);
                apptPagEl.appendChild(next);
            }

            function filterAppts() {
                const q = (apptSearch?.value || '').toLowerCase();
                const s = (apptStatus?.value || '').toLowerCase();
                const cards = apptCards();
                const matches = [];
                cards.forEach(card => {
                    const text = card.innerText.toLowerCase();
                    const status = (card.getAttribute('data-status') || '').toLowerCase();
                    const match = (!q || text.includes(q)) && (!s || status === s);
                    card.dataset._match = match ? '1' : '0';
                    if (match) matches.push(card);
                });
                const total = matches.length; const pages = Math.max(1, Math.ceil(total / apptPageSize));
                if (apptPage > pages) apptPage = pages;
                const start = (apptPage - 1) * apptPageSize; const pageItems = matches.slice(start, start + apptPageSize);
                cards.forEach(card => card.style.display = 'none');
                pageItems.forEach(card => card.style.display = '');
                renderApptPagination(pages);
            }

            if (apptSearch) apptSearch.addEventListener('input', () => { apptPage = 1; filterAppts(); });
            if (apptStatus) apptStatus.addEventListener('change', () => { apptPage = 1; filterAppts(); });
            filterAppts();
        })();

        // Filtering & search for Service Requests (enhanced)
        (function () {
            const svcSearch = document.getElementById('svc-search');
            const svcStatus = document.getElementById('svc-status-filter');
            const svcType = document.getElementById('svc-type-filter');
            const svcUrgency = document.getElementById('svc-urgency-filter');
            const svcFrom = document.getElementById('svc-date-from');
            const svcTo = document.getElementById('svc-date-to');
            const svcSort = document.getElementById('svc-sort');
            const svcClear = document.getElementById('svc-clear');
            const svcList = document.getElementById('svc-list');
            const svcPagEl = document.getElementById('svc-pagination');
            let svcPage = 1; const svcPageSize = 5;

            function svcCards() { return Array.from(document.querySelectorAll('#services-content .svc-card')); }

            function urgencyRank(u) {
                switch ((u || '').toLowerCase()) {
                    case 'emergency': return 3;
                    case 'high': return 2;
                    case 'medium': return 1;
                    case 'low': return 0;
                    default: return -1;
                }
            }

            function renderSvcPagination(pages) {
                if (!svcPagEl) return;
                svcPagEl.innerHTML = '';
                const mkBtn = (label, onClick, disabled = false, active = false) => {
                    const b = document.createElement('button');
                    b.textContent = label;
                    b.className = 'px-2 py-1 rounded ' + (active ? 'bg-primary text-white' : 'bg-gray-700 hover:bg-gray-600 text-white');
                    b.disabled = !!disabled;
                    if (disabled) b.classList.add('opacity-50', 'cursor-not-allowed');
                    b.addEventListener('click', onClick);
                    return b;
                };
                const prev = mkBtn('Prev', () => { if (svcPage > 1) { svcPage--; filterAndSort(); } }, svcPage <= 1);
                svcPagEl.appendChild(prev);
                for (let i = 1; i <= pages; i++) {
                    svcPagEl.appendChild(mkBtn(String(i), () => { svcPage = i; filterAndSort(); }, false, i === svcPage));
                }
                const next = mkBtn('Next', () => { if (svcPage < pages) { svcPage++; filterAndSort(); } }, svcPage >= pages);
                svcPagEl.appendChild(next);
            }

            function filterAndSort() {
                const q = (svcSearch?.value || '').toLowerCase();
                const st = (svcStatus?.value || '').toLowerCase();
                const tp = (svcType?.value || '').toLowerCase();
                const ug = (svcUrgency?.value || '').toLowerCase();
                const df = svcFrom?.value ? new Date(svcFrom.value + 'T00:00:00Z').getTime() : null;
                const dt = svcTo?.value ? new Date(svcTo.value + 'T23:59:59Z').getTime() : null;
                const sort = (svcSort?.value || 'created_desc');

                const cards = svcCards();
                const visible = [];
                cards.forEach(card => {
                    const text = card.innerText.toLowerCase();
                    const status = (card.dataset.status || '');
                    const type = (card.dataset.type || '');
                    const urg = (card.dataset.urgency || '');
                    const createdISO = card.dataset.created || '';
                    const createdTs = createdISO ? new Date(createdISO).getTime() : 0;

                    let ok = true;
                    if (q && !text.includes(q)) ok = false;
                    if (ok && st && status !== st) ok = false;
                    if (ok && tp && type !== tp) ok = false;
                    if (ok && ug && urg !== ug) ok = false;
                    if (ok && df !== null && createdTs < df) ok = false;
                    if (ok && dt !== null && createdTs > dt) ok = false;

                    card.style.display = ok ? '' : 'none';
                    if (ok) visible.push(card);
                });

                // Sort visible
                visible.sort((a, b) => {
                    switch (sort) {
                        case 'created_asc': return (new Date(a.dataset.created) - new Date(b.dataset.created));
                        case 'created_desc': return (new Date(b.dataset.created) - new Date(a.dataset.created));
                        case 'urgency_desc': return urgencyRank(b.dataset.urgency) - urgencyRank(a.dataset.urgency);
                        case 'urgency_asc': return urgencyRank(a.dataset.urgency) - urgencyRank(b.dataset.urgency);
                        case 'status_az': return (a.dataset.status || '').localeCompare(b.dataset.status || '');
                        case 'status_za': return (b.dataset.status || '').localeCompare(a.dataset.status || '');
                        case 'type_az': return (a.dataset.type || '').localeCompare(b.dataset.type || '');
                        default: return 0;
                    }
                });

                // Re-append in sorted order and paginate
                if (svcList) {
                    visible.forEach(card => svcList.appendChild(card));
                }
                const total = visible.length; const pages = Math.max(1, Math.ceil(total / svcPageSize));
                if (svcPage > pages) svcPage = pages;
                const start = (svcPage - 1) * svcPageSize; const pageItems = new Set(visible.slice(start, start + svcPageSize));
                svcCards().forEach(card => card.style.display = 'none');
                pageItems.forEach(card => card.style.display = '');
                renderSvcPagination(pages);
            }

            [svcSearch, svcStatus, svcType, svcUrgency, svcFrom, svcTo, svcSort].forEach(el => {
                if (el) {
                    const ev = (el.tagName === 'INPUT') ? 'input' : 'change';
                    el.addEventListener(ev, () => { svcPage = 1; filterAndSort(); });
                }
            });

            if (svcClear) {
                svcClear.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (svcSearch) svcSearch.value = '';
                    if (svcStatus) svcStatus.value = '';
                    if (svcType) svcType.value = '';
                    if (svcUrgency) svcUrgency.value = '';
                    if (svcFrom) svcFrom.value = '';
                    if (svcTo) svcTo.value = '';
                    if (svcSort) svcSort.value = 'created_desc';
                    svcPage = 1; filterAndSort();
                });
            }

            filterAndSort();
        })();
    </script>

    <style>
        .tab-button.active {
            border-color: #4a90e2;
            color: #4a90e2;
        }
    </style>