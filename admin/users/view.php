<?php
if (!isset($db)) { require_once '../../include/database.php'; startSecureSession(); requireRole('admin'); }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: list.php'); exit; }

$stmt = $db->prepare("SELECT id, first_name, last_name, email, role, IFNULL(status,'active') AS status, phone, address, date_of_birth, gender, verification_status, created_at, updated_at, last_login FROM users WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { header('Location: list.php'); exit; }

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($script, '/admin/');
$adminBase = ($pos !== false) ? substr($script, 0, $pos + 7) : '/admin/';
?>
<?php include __DIR__ . '/../adminheader.php'; include __DIR__ . '/../adminsidebar.php'; ?>
<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-semibold text-slate-800">User Account</h2>
        <p class="text-slate-500 mt-1">Details and status for <?php echo h(($user['first_name']??'').' '.($user['last_name']??'')); ?>.</p>
      </div>
      <div class="flex items-center gap-2">
        <a href="list.php" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-sm">Back to List</a>
        <?php $isBlocked = ($user['status']==='blocked'); ?>
        <?php if ($isBlocked): ?>
          <button type="button" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm" data-action="unblock" data-id="<?php echo (int)$user['id']; ?>">Unblock</button>
        <?php else: ?>
          <button type="button" class="px-3 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm" data-action="block" data-id="<?php echo (int)$user['id']; ?>">Block</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
      <section class="md:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Personal Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-slate-500">Full Name</div>
            <div class="text-slate-800 font-medium"><?php echo h(($user['first_name']??'').' '.($user['last_name']??'')); ?></div>
          </div>
          <div>
            <div class="text-slate-500">Email / Username</div>
            <div class="text-slate-800 font-medium"><?php echo h($user['email'] ?? ''); ?></div>
          </div>
          <div>
            <div class="text-slate-500">Phone</div>
            <div class="text-slate-800 font-medium"><?php echo h($user['phone'] ?? '-'); ?></div>
          </div>
          <div>
            <div class="text-slate-500">Address</div>
            <div class="text-slate-800 font-medium break-words"><?php echo h($user['address'] ?? '-'); ?></div>
          </div>
          <div>
            <div class="text-slate-500">Date of Birth</div>
            <div class="text-slate-800 font-medium"><?php echo h($user['date_of_birth'] ?? '-'); ?></div>
          </div>
          <div>
            <div class="text-slate-500">Gender</div>
            <div class="text-slate-800 font-medium capitalize"><?php echo h($user['gender'] ?? '-'); ?></div>
          </div>
        </div>
      </section>

      <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Account</h3>
        <div class="space-y-3 text-sm">
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Role</div>
            <div class="text-slate-800 font-medium capitalize"><?php echo h($user['role'] ?? '-'); ?></div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Status</div>
            <?php $badge = ($user['status']==='blocked') ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'; ?>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs <?php echo $badge; ?>"><?php echo ($user['status']==='blocked')?'Blocked':'Active'; ?></span>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Verification</div>
            <div class="text-slate-800 font-medium capitalize"><?php echo h($user['verification_status'] ?? '-'); ?></div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Created</div>
            <div class="text-slate-800 font-medium"><?php echo h($user['created_at'] ?? '-'); ?></div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Last Update</div>
            <div class="text-slate-800 font-medium"><?php echo h($user['updated_at'] ?? '-'); ?></div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Last Login</div>
            <div class="text-slate-800 font-medium"><?php echo h($user['last_login'] ?? '-'); ?></div>
          </div>
        </div>
      </section>
    </div>
  </div>
</main>
<script>
(function(){
  document.addEventListener('click', function(e){
    const btn = e.target.closest('button[data-action][data-id]');
    if (!btn) return;
    const act = btn.getAttribute('data-action');
    const id = btn.getAttribute('data-id');
    if (!confirm((act==='block'?'Block':'Unblock') + ' this user?')) return;
    const fd = new URLSearchParams(); fd.set('action', act); fd.set('id', id);
    fetch('<?php echo $adminBase; ?>api/users.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: fd.toString(), credentials:'same-origin' })
      .then(r=>r.json()).then(res=>{ if(res && res.ok){ location.reload(); } else { alert(res && res.error ? res.error : 'Operation failed'); } })
      .catch(()=>alert('Network error'));
  });
})();
</script>
