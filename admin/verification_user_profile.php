<?php
require_once '../include/database.php';
startSecureSession();
requireRole('admin');

// Small helper
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$message = '';
$message_type = '';

// Handle verify/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verId = isset($_POST['verification_id']) ? (int)$_POST['verification_id'] : 0;
    $action = $_POST['action'] ?? '';
    $note = trim($_POST['note'] ?? '');
    if ($verId > 0 && in_array($action, ['verify','reject'], true)) {
        $targetStatus = $action === 'verify' ? 'verified' : 'rejected';
        if ($database->updateUserVerificationStatus($verId, $targetStatus, $_SESSION['user_id'], $note ?: ($action === 'verify' ? 'Verified by admin' : 'Rejected by admin'))) {
            $message = $action === 'verify' ? 'Verification approved.' : 'Verification rejected.';
            $message_type = 'success';
        } else {
            $message = 'Failed to update verification status.';
            $message_type = 'error';
        }
    }
}

$statusFilter = isset($_GET['status']) && in_array($_GET['status'], ['pending','verified','rejected'], true) ? $_GET['status'] : null;
$verifications = $database->listUserVerifications($statusFilter);

include 'adminheader.php';
include 'adminsidebar.php';
?>

<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div id="content-area" class="p-6 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="mb-8 rounded-2xl p-8 border border-rose-100 shadow-lg bg-gradient-to-r from-rose-50 via-pink-50 to-peach-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">User Verification Review</h2>
      <p class="text-gray-600 dark:text-slate-300 mt-1">Review submitted IDs and verify or reject.</p>
    </div>

    <?php if (!empty($message)): ?>
      <?php $alertClass = $message_type === 'success'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/50 dark:bg-emerald-900/40 dark:text-emerald-200'
        : 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/50 dark:bg-rose-900/40 dark:text-rose-200'; ?>
      <div class="mb-5 px-4 py-3 rounded-xl border transition-all shadow-sm <?php echo $alertClass; ?>">
        <?php echo h($message); ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
      <div class="flex items-center gap-3">
        <label for="status" class="text-gray-700 dark:text-slate-200 text-sm font-medium">Status:</label>
        <select id="status" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-rose-500 dark:focus:ring-rose-400" onchange="onStatusChange(this.value)">
          <option value="" <?php echo $statusFilter===null ? 'selected' : ''; ?>>All</option>
          <option value="pending" <?php echo $statusFilter==='pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="verified" <?php echo $statusFilter==='verified' ? 'selected' : ''; ?>>Verified</option>
          <option value="rejected" <?php echo $statusFilter==='rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
      </div>
    </div>

    <div class="bg-white dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-700 dark:text-slate-200 border-b border-slate-200 dark:border-slate-700">
              <th class="py-3 px-3">#</th>
              <th class="py-3 px-3">Citizen</th>
              <th class="py-3 px-3">Email</th>
              <th class="py-3 px-3">Document</th>
              <th class="py-3 px-3">File</th>
              <th class="py-3 px-3">Status</th>
              <th class="py-3 px-3">Submitted</th>
              <th class="py-3 px-3">Reviewed</th>
              <th class="py-3 px-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($verifications)): ?>
              <tr><td colspan="9" class="py-6 px-3 text-center text-gray-500 dark:text-slate-400">No records found.</td></tr>
            <?php else: ?>
              <?php foreach ($verifications as $v): ?>
                <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-rose-50/60 dark:hover:bg-slate-800/60">
                  <td class="py-3 px-3 text-gray-600 dark:text-slate-300 font-medium">#<?php echo h($v['id']); ?></td>
                  <td class="py-3 px-3 text-gray-900 dark:text-slate-100 font-semibold"><?php echo h($v['first_name'] . ' ' . $v['last_name']); ?></td>
                  <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($v['email']); ?></td>
                  <td class="py-3 px-3 text-gray-600 dark:text-slate-300 capitalize"><?php echo h($v['document_type']); ?></td>
                  <td class="py-3 px-3 text-gray-600 dark:text-slate-300">
                    <?php if (!empty($v['file_path'])): ?>
                      <a class="text-rose-600 dark:text-rose-300 hover:underline" href="../<?php echo h($v['file_path']); ?>" target="_blank">View</a>
                      <span class="text-gray-400 dark:text-slate-500">·</span>
                      <a class="text-rose-600 dark:text-rose-300 hover:underline" href="../<?php echo h($v['file_path']); ?>" download>Download</a>
                    <?php else: ?>
                      <span class="text-gray-400 dark:text-slate-500">N/A</span>
                    <?php endif; ?>
                  </td>
                  <td class="py-3 px-3">
                    <?php $s = $v['status']; ?>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $s==='verified' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200' : ($s==='rejected' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200'); ?>"><?php echo h(strtoupper($s)); ?></span>
                  </td>
                  <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($v['created_at']); ?></td>
                  <td class="py-3 px-3 text-gray-600 dark:text-slate-300"><?php echo h($v['reviewed_at'] ?: '-'); ?></td>
                  <td class="py-3 px-3 text-right">
                    <?php if ($v['status'] === 'pending'): ?>
                      <div class="flex justify-end gap-2">
                        <form method="post" onsubmit="return confirm('Verify this submission?')">
                          <input type="hidden" name="verification_id" value="<?php echo (int)$v['id']; ?>">
                          <input type="hidden" name="note" value="Verified by admin">
                          <button name="action" value="verify" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-medium shadow-sm">Verify</button>
                        </form>
                        <form method="post" onsubmit="return confirm('Reject this submission?')">
                          <input type="hidden" name="verification_id" value="<?php echo (int)$v['id']; ?>">
                          <input type="hidden" name="note" value="Rejected by admin">
                          <button name="action" value="reject" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-medium shadow-sm">Reject</button>
                        </form>
                      </div>
                    <?php else: ?>
                      <span class="text-gray-400 dark:text-slate-500 text-sm">No actions</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<script>
function onStatusChange(val){
  const u = new URL(window.location.href);
  if (val) u.searchParams.set('status', val); else u.searchParams.delete('status');
  window.location.href = u.toString();
}
</script>

</body>
</html>
