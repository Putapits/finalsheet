<?php
if (!isset($db)) { require_once '../../include/database.php'; startSecureSession(); requireRole('admin'); }
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$messages = [];
$errors = [];

// Ensure optional columns
$hasStatus = false; $hasLastLogin = false;
try { $hasStatus = (bool)$db->query("SHOW COLUMNS FROM users LIKE 'status'")->fetch(PDO::FETCH_ASSOC); } catch (Throwable $e) {}
try { $hasLastLogin = (bool)$db->query("SHOW COLUMNS FROM users LIKE 'last_login'")->fetch(PDO::FETCH_ASSOC); } catch (Throwable $e) {}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['enable_status']) && $_POST['enable_status']==='1') {
  try {
    if (!$hasStatus) $db->exec("ALTER TABLE users ADD COLUMN status ENUM('active','blocked') NOT NULL DEFAULT 'active'");
    if (!$hasLastLogin) $db->exec("ALTER TABLE users ADD COLUMN last_login DATETIME NULL");
    $hasStatus = true; $hasLastLogin = true;
    $messages[] = 'User status and last_login columns are now enabled.';
  } catch (Throwable $e) { $errors[] = 'Schema update failed: ' . $e->getMessage(); }
}

// Filters and pagination
$q = trim((string)($_GET['q'] ?? ''));
$role = trim((string)($_GET['role'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$per = max(5, min(100, (int)($_GET['per'] ?? 20)));
$page = max(1, (int)($_GET['p'] ?? 1));
$offset = ($page-1)*$per;

$baseWhere = "WHERE role IN ('doctor','nurse','inspector')";
$params = [];
if ($q !== '') { $baseWhere .= " AND (CONCAT(first_name,' ',last_name) LIKE :q1 OR email LIKE :q2)"; $params[':q1'] = "%$q%"; $params[':q2'] = "%$q%"; }
if (in_array($role, ['doctor','nurse','inspector'], true)) { $baseWhere .= " AND role = :role"; $params[':role'] = $role; }
if ($hasStatus && in_array($status, ['active','blocked'], true)) { $baseWhere .= " AND IFNULL(status,'active') = :status"; $params[':status'] = $status; }

$countSql = "SELECT COUNT(*) c FROM users $baseWhere";
$stmt = $db->prepare($countSql); $stmt->execute($params); $total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total/$per)); if ($page > $totalPages) { $page = $totalPages; $offset = ($page-1)*$per; }

$selectStatus = $hasStatus ? "IFNULL(status,'active')" : "'active'";
$selectLastLogin = $hasLastLogin ? "last_login" : "NULL";
$listSql = "SELECT id, first_name, last_name, email, role, $selectStatus AS status, $selectLastLogin AS last_login FROM users $baseWhere ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($listSql);
foreach ($params as $k=>$v) { $stmt->bindValue($k, $v, PDO::PARAM_STR); }
$stmt->bindValue(':limit', $per, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Admin base for API
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($script, '/admin/');
$adminBase = ($pos !== false) ? substr($script, 0, $pos + 7) : '/admin/';
?>
<?php include __DIR__ . '/../adminheader.php'; include __DIR__ . '/../adminsidebar.php'; ?>
<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div class="p-6 bg-slate-50 min-h-screen">
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-slate-800">User Management</h2>
      <p class="text-slate-500 mt-1">Manage Doctors, Nurses, and Inspectors.</p>
    </div>

    <?php if (!$hasStatus): ?>
      <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
        <div class="font-semibold mb-1">Account Status column not found</div>
        <p class="text-sm">Enable <code>status</code> (active/blocked) and optional <code>last_login</code> columns on the users table.</p>
        <form method="post" class="mt-3">
          <input type="hidden" name="enable_status" value="1" />
          <button class="px-3 py-2 bg-amber-500 hover:bg-amber-400 text-white rounded-md text-xs" onclick="return confirm('Apply schema update on users table?')">Enable Columns</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
      <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3"><?php echo h(implode(' ', $messages)); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3"><?php echo h(implode(' ', $errors)); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm mb-4">
      <form method="get" class="grid grid-cols-1 md:grid-cols-12 gap-3">
        <div class="md:col-span-5">
          <label class="block text-xs text-slate-600 mb-1" for="q">Search</label>
          <input id="q" type="text" name="q" value="<?php echo h($q); ?>" placeholder="Search name or email" class="w-full bg-white text-slate-900 placeholder-slate-400 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1" for="role">Role</label>
          <select id="role" name="role" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All</option>
            <?php foreach(['doctor'=>'Doctor','nurse'=>'Nurse','inspector'=>'Inspector'] as $rk=>$rv): ?>
              <option value="<?php echo $rk; ?>" <?php echo $role===$rk?'selected':''; ?>><?php echo $rv; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1" for="status">Status</label>
          <select id="status" name="status" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All</option>
            <option value="active" <?php echo $status==='active'?'selected':''; ?>>Active</option>
            <option value="blocked" <?php echo $status==='blocked'?'selected':''; ?>>Blocked</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-600 mb-1" for="per">Per Page</label>
          <select id="per" name="per" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <?php foreach([10,20,50,100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo (int)$per===$opt?'selected':''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="md:col-span-2"></div>
      </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-100">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Full Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Role</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Last Login</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-100">
            <?php if (empty($rows)): ?>
              <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No users found.</td></tr>
            <?php else: foreach ($rows as $u): $statusBadge = ($u['status']==='blocked') ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'; ?>
              <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-slate-800 font-medium"><?php echo h(trim(($u['first_name']??'').' '.($u['last_name']??''))); ?></td>
                <td class="px-4 py-3 text-slate-700"><?php echo h($u['email']??''); ?></td>
                <td class="px-4 py-3 text-slate-700 capitalize"><?php echo h($u['role']??''); ?></td>
                <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs <?php echo $statusBadge; ?>"><?php echo ($u['status']==='blocked')?'Blocked':'Active'; ?></span></td>
                <td class="px-4 py-3 text-slate-500"><?php echo h($u['last_login'] ?? '-'); ?></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <button type="button" data-view-id="<?php echo (int)$u['id']; ?>" class="px-3 py-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-800">View</button>
                    <?php if (($u['status'] ?? 'active') === 'blocked'): ?>
                      <button type="button" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white" data-action="unblock" data-id="<?php echo (int)$u['id']; ?>">Unblock</button>
                    <?php else: ?>
                      <button type="button" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white" data-action="block" data-id="<?php echo (int)$u['id']; ?>">Block</button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
      <div class="flex items-center justify-between p-3 border-t border-slate-200 text-sm">
        <div class="text-slate-600">Showing <span class="text-slate-800 font-medium"><?php echo $total?($offset+1):0; ?></span> - <span class="text-slate-800 font-medium"><?php echo min($offset+$per, $total); ?></span> of <span class="text-slate-800 font-medium"><?php echo $total; ?></span></div>
        <div class="flex items-center gap-2">
          <?php $qs = $_GET; $qs['p'] = max(1, $page-1); $prevUrl = 'list.php?'.http_build_query($qs); $qs['p'] = min($totalPages, $page+1); $nextUrl = 'list.php?'.http_build_query($qs); ?>
          <a class="px-2 py-1 rounded <?php echo $page<=1?'bg-slate-100 text-slate-400 cursor-not-allowed':'bg-slate-200 hover:bg-slate-300 text-slate-800'; ?>" href="<?php echo $page<=1?'#':h($prevUrl); ?>">Prev</a>
          <?php for($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): $qs['p']=$i; $url='list.php?'.http_build_query($qs); $cls=$i===$page?'bg-primary text-white':'bg-slate-200 hover:bg-slate-300 text-slate-800'; ?>
            <a class="px-2 py-1 rounded <?php echo $cls; ?>" href="<?php echo h($url); ?>"><?php echo $i; ?></a>
          <?php endfor; ?>
          <a class="px-2 py-1 rounded <?php echo $page>=$totalPages?'bg-slate-100 text-slate-400 cursor-not-allowed':'bg-slate-200 hover:bg-slate-300 text-slate-800'; ?>" href="<?php echo $page>=$totalPages?'#':h($nextUrl); ?>">Next</a>
        </div>
      </div>
    </div>
  </div>
</main>
<div id="userModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div id="userModalCard" class="bg-white rounded-xl w-full max-w-2xl shadow-xl border border-slate-200 overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-slate-200">
      <h3 class="text-lg font-semibold text-slate-800">User Account</h3>
      <button id="userModalClose" class="p-2 text-slate-500 hover:text-slate-700">✕</button>
    </div>
    <div class="p-6 space-y-6">
      <section>
        <h4 class="text-sm font-semibold text-slate-700 mb-3">Personal Information</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-slate-500">Full Name</div>
            <div id="um-full" class="text-slate-800 font-medium">-</div>
          </div>
          <div>
            <div class="text-slate-500">Email</div>
            <div id="um-email" class="text-slate-800 font-medium break-words">-</div>
          </div>
          <div>
            <div class="text-slate-500">Phone</div>
            <div id="um-phone" class="text-slate-800 font-medium">-</div>
          </div>
          <div>
            <div class="text-slate-500">Address</div>
            <div id="um-address" class="text-slate-800 font-medium break-words">-</div>
          </div>
          <div>
            <div class="text-slate-500">Date of Birth</div>
            <div id="um-dob" class="text-slate-800 font-medium">-</div>
          </div>
          <div>
            <div class="text-slate-500">Gender</div>
            <div id="um-gender" class="text-slate-800 font-medium capitalize">-</div>
          </div>
        </div>
      </section>
      <section>
        <h4 class="text-sm font-semibold text-slate-700 mb-3">Account</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Role</div>
            <div id="um-role" class="text-slate-800 font-medium capitalize">-</div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Status</div>
            <span id="um-status" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">Active</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Verification</div>
            <div id="um-verif" class="text-slate-800 font-medium capitalize">-</div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Created</div>
            <div id="um-created" class="text-slate-800 font-medium">-</div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Last Update</div>
            <div id="um-updated" class="text-slate-800 font-medium">-</div>
          </div>
          <div class="flex items-center justify-between">
            <div class="text-slate-500">Last Login</div>
            <div id="um-lastlogin" class="text-slate-800 font-medium">-</div>
          </div>
        </div>
      </section>
    </div>
    <div class="flex items-center justify-end gap-2 p-4 border-t border-slate-200">
      <button id="userBlockBtn" class="px-3 py-2 rounded-lg text-white bg-rose-600 hover:bg-rose-500 hidden">Block</button>
      <button id="userModalClose2" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg">Close</button>
    </div>
  </div>
</div>
<script>
(function(){
  try { window.__adminBase = <?php echo json_encode($adminBase); ?>; } catch(_) {}
  var formQ = document.getElementById('q');
  var formRole = document.getElementById('role');
  var formStatus = document.getElementById('status');
  var formPer = document.getElementById('per');
  function submitFilters(){
    var p = new URLSearchParams();
    var qv = formQ ? formQ.value.trim() : '';
    if (qv) p.set('q', qv);
    var rv = formRole ? formRole.value : '';
    if (rv) p.set('role', rv);
    var sv = formStatus ? formStatus.value : '';
    if (sv) p.set('status', sv);
    var pv = formPer ? formPer.value : '20';
    if (pv) p.set('per', pv);
    p.set('p', '1');
    window.location.href = 'list.php?' + p.toString();
  }
  var t;
  if (formQ) formQ.addEventListener('input', function(){ clearTimeout(t); t = setTimeout(submitFilters, 400); });
  if (formRole) formRole.addEventListener('change', submitFilters);
  if (formStatus) formStatus.addEventListener('change', submitFilters);
  if (formPer) formPer.addEventListener('change', submitFilters);

  function openUserModal(u){
    var m = document.getElementById('userModal');
    var b = document.getElementById('userBlockBtn');
    document.getElementById('um-full').textContent = ((u.first_name||'') + ' ' + (u.last_name||'')).trim();
    document.getElementById('um-email').textContent = u.email||'-';
    document.getElementById('um-phone').textContent = u.phone||'-';
    document.getElementById('um-address').textContent = u.address||'-';
    document.getElementById('um-dob').textContent = u.date_of_birth||'-';
    document.getElementById('um-gender').textContent = u.gender||'-';
    document.getElementById('um-role').textContent = u.role||'-';
    document.getElementById('um-verif').textContent = u.verification_status||'-';
    document.getElementById('um-created').textContent = u.created_at||'-';
    document.getElementById('um-updated').textContent = u.updated_at||'-';
    document.getElementById('um-lastlogin').textContent = u.last_login||'-';
    var sEl = document.getElementById('um-status');
    var blocked = (u.status||'active') === 'blocked';
    sEl.textContent = blocked ? 'Blocked' : 'Active';
    sEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs ' + (blocked ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200');
    if (b){
      b.dataset.id = u.id;
      b.dataset.action = blocked ? 'unblock' : 'block';
      b.textContent = blocked ? 'Unblock' : 'Block';
      b.classList.remove('bg-rose-600','hover:bg-rose-500','bg-emerald-600','hover:bg-emerald-500','hidden');
      if (blocked) { b.classList.add('bg-emerald-600','hover:bg-emerald-500'); } else { b.classList.add('bg-rose-600','hover:bg-rose-500'); }
    }
    if (m){ m.classList.remove('hidden'); m.classList.add('flex'); }
  }
  function closeUserModal(){ var m=document.getElementById('userModal'); if(m){ m.classList.add('hidden'); m.classList.remove('flex'); } }
  document.getElementById('userModalClose')?.addEventListener('click', closeUserModal);
  document.getElementById('userModalClose2')?.addEventListener('click', closeUserModal);
  document.getElementById('userModal')?.addEventListener('click', function(e){ var card=document.getElementById('userModalCard'); if(!card.contains(e.target)) closeUserModal(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeUserModal(); });

  document.addEventListener('click', function(e){
    const btn = e.target.closest('button[data-action][data-id]');
    if (btn) {
      const act = btn.getAttribute('data-action');
      const id = btn.getAttribute('data-id');
      if (!confirm((act==='block'?'Block':'Unblock') + ' this user?')) return;
      const fd = new URLSearchParams(); fd.set('action', act); fd.set('id', id);
      fetch(__adminBase + 'api/users.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: fd.toString(), credentials:'same-origin' })
        .then(r=>r.json()).then(res=>{ if(res && res.ok){ location.reload(); } else { alert(res && res.error ? res.error : 'Operation failed'); } })
        .catch(()=>alert('Network error'));
      return;
    }
    const vbtn = e.target.closest('[data-view-id]');
    if (vbtn) {
      const id = vbtn.getAttribute('data-view-id');
      fetch(__adminBase + 'api/users.php?action=show&id=' + encodeURIComponent(id), { credentials: 'same-origin' })
        .then(r=>r.json()).then(res=>{ if(res && res.ok && res.user){ openUserModal(res.user); } else { alert('Unable to load user'); } })
        .catch(()=>alert('Network error'));
    }
  });
})();
</script>
