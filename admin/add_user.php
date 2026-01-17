<?php
require_once '../include/database.php';
startSecureSession();
requireRole('admin');

// Helper: escape
function h($v)
{
  return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

// Check if 'inspector' role is supported by DB enum
$inspector_supported = false;
try {
  $col = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
  if ($col && isset($col['Type'])) {
    $type = $col['Type']; // e.g. enum('admin','doctor','nurse','citizen')
    $inspector_supported = (stripos($type, "'inspector'") !== false);
  }
} catch (Throwable $e) {
  error_log('Role enum check failed: ' . $e->getMessage());
}

$errors = [];
$success = null;

// Optional: enable inspector role via one-click schema update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enable_inspector']) && $_POST['enable_inspector'] === '1') {
  try {
    $db->exec("ALTER TABLE users MODIFY role ENUM('admin','doctor','nurse','citizen','inspector','health_worker') DEFAULT 'citizen'");
    $inspector_supported = true;
    $success = "Inspector role has been enabled in the database.";
  } catch (Throwable $e) {
    $errors[] = 'Failed to enable Inspector role: ' . $e->getMessage();
  }
}

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['enable_inspector']))) {
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm'] ?? '';
  $role = strtolower(trim($_POST['role'] ?? ''));

  // Allowed roles in UI
  $allowed_roles = ['doctor', 'nurse', 'health_worker'];
  // Include 'inspector' if supported or chosen, we will validate below
  if ($inspector_supported) {
    $allowed_roles[] = 'inspector';
  }

  if ($first_name === '')
    $errors[] = 'First name is required.';
  if ($last_name === '')
    $errors[] = 'Last name is required.';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = 'Valid email is required.';
  if (strlen($password) < 6)
    $errors[] = 'Password must be at least 6 characters.';
  if ($password !== $confirm)
    $errors[] = 'Passwords do not match.';

  // If trying to set inspector but schema not enabled
  if ($role === 'inspector' && !$inspector_supported) {
    $errors[] = "Inspector role isn't enabled in the database yet. Click 'Enable Inspector Role' above or ask to add it.";
  }

  if (!in_array($role, array_merge($allowed_roles, ['inspector']), true)) {
    $errors[] = 'Invalid role selected.';
  }

  // Check duplicate email
  if (empty($errors)) {
    try {
      if ($database->emailExists($email)) {
        $errors[] = 'Email already exists.';
      }
    } catch (Throwable $e) {
      $errors[] = 'Failed to validate email uniqueness.';
    }
  }

  // Create user
  if (empty($errors)) {
    try {
      $newId = $database->registerUser($first_name, $last_name, $email, $password, $role);
      if ($newId) {
        $success = 'User created successfully with ID #' . (int) $newId . '.';
        // Clear form
        $first_name = $last_name = $email = '';
        $role = '';
      } else {
        $errors[] = 'Failed to create user. Please check server logs.';
      }
    } catch (Throwable $e) {
      $errors[] = 'Server error while creating user: ' . $e->getMessage();
    }
  }
}

include 'adminheader.php';
include 'adminsidebar.php';
?>

<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div class="p-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div
      class="mb-8 rounded-2xl p-8 border border-emerald-100 shadow-lg bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Add User (Doctor / Nurse / Inspector)</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Create staff accounts for the Health & Sanitation system.</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div
        class="mb-5 rounded-2xl border border-rose-200 bg-rose-50/80 text-rose-700 dark:border-rose-500/50 dark:bg-rose-900/40 dark:text-rose-200 px-4 py-3 shadow-sm">
        <div class="font-semibold mb-2">There were some problems:</div>
        <ul class="list-disc ml-5 space-y-1 text-sm">
          <?php foreach ($errors as $e): ?>
            <li><?php echo h($e); ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div
        class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50/80 text-emerald-700 dark:border-emerald-500/50 dark:bg-emerald-900/40 dark:text-emerald-200 px-4 py-3 shadow-sm">
        <div class="font-semibold"><?php echo h($success); ?></div>
      </div>
    <?php endif; ?>

    <?php if (!$inspector_supported): ?>
      <div
        class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/80 text-amber-700 dark:border-amber-500/50 dark:bg-amber-900/40 dark:text-amber-200 px-4 py-4 shadow-sm">
        <div class="font-semibold mb-1">Inspector role not enabled</div>
        <p class="text-sm">Your database is missing the <strong>inspector</strong> role in the users table enum. You can
          enable it now:</p>
        <form method="post" class="mt-3 flex flex-wrap gap-3 items-center">
          <input type="hidden" name="enable_inspector" value="1" />
          <button class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-white rounded-lg text-xs font-medium shadow-sm"
            onclick="return confirm('Apply DB schema update to enable Inspector role?')">Enable Inspector Role</button>
          <span class="text-xs text-amber-700 dark:text-amber-200">This runs: ALTER TABLE users MODIFY role
            ENUM('admin','doctor','nurse','citizen','inspector','health_worker')</span>
        </form>
      </div>
    <?php endif; ?>

    <section
      class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md max-w-2xl">
      <form method="post" class="grid grid-cols-1 gap-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">First Name</label>
            <input name="first_name" type="text" required value="<?php echo h($_POST['first_name'] ?? ''); ?>"
              class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Last Name</label>
            <input name="last_name" type="text" required value="<?php echo h($_POST['last_name'] ?? ''); ?>"
              class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Email</label>
          <input name="email" type="email" required value="<?php echo h($_POST['email'] ?? ''); ?>"
            class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark;text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Password</label>
            <input name="password" type="password" required
              class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Confirm Password</label>
            <input name="confirm" type="password" required
              class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400" />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Role</label>
          <select name="role" required
            class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:focus:ring-emerald-400">
            <option value="">Select role</option>
            <option value="doctor" <?php echo (($_POST['role'] ?? '') === 'doctor') ? 'selected' : ''; ?>>Doctor</option>
            <option value="nurse" <?php echo (($_POST['role'] ?? '') === 'nurse') ? 'selected' : ''; ?>>Nurse</option>
            <option value="inspector" <?php echo (($_POST['role'] ?? '') === 'inspector') ? 'selected' : ''; ?> <?php echo !$inspector_supported ? 'disabled' : ''; ?>>Inspector</option>
            <option value="health_worker" <?php echo (($_POST['role'] ?? '') === 'health_worker') ? 'selected' : ''; ?>>Health
              Worker</option>
          </select>
          <?php if (!$inspector_supported): ?>
            <p class="text-xs text-amber-600 dark:text-amber-200 mt-2">Enable Inspector role above to select it here.</p>
          <?php endif; ?>
        </div>
        <div class="pt-2">
          <button
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium shadow-sm">Create
            User</button>
        </div>
      </form>
    </section>
  </div>
</main>

<!-- Uses global toggleSidebar/toggleDropdown from adminheader.php -->