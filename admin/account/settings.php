<?php
// Admin Settings page (Personal Info + Change Password)
// Assumes $database is available from DashboardOverview_new.php
?>
<div class="mb-8 rounded-2xl p-8 border border-violet-100 shadow-lg bg-gradient-to-r from-violet-50 via-purple-50 to-fuchsia-50 dark:border-slate-700/70 dark:bg-gradient-to-r dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:shadow-[0_15px_35px_-15px_rgba(15,23,42,0.8)]">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-slate-100">Account Settings</h2>
  <p class="text-gray-600 dark:text-slate-300 mt-1">Manage your personal information and password.</p>
</div>

<section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <!-- Profile -->
  <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Personal Information</h3>
    <div id="acct-prof-msg" class="hidden p-3 rounded-xl mb-4"></div>
    <form id="acct-profile-form" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">First Name</label>
          <input id="ap-first" type="text" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Last Name</label>
          <input id="ap-last" type="text" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Email</label>
          <input id="ap-email" type="email" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Phone</label>
          <input id="ap-phone" type="text" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Address</label>
        <textarea id="ap-address" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" rows="3"></textarea>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Date of Birth</label>
          <input id="ap-dob" type="date" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Gender</label>
          <select id="ap-gender" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark;border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400">
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end">
        <button class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-lg text-sm font-medium shadow-sm" type="submit">Save Changes</button>
      </div>
    </form>
  </div>

  <!-- Password -->
  <div class="bg-white dark:bg-slate-900/50 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 shadow-md">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100 mb-4">Change Password</h3>
    <div id="acct-pass-msg" class="hidden p-3 rounded-xl mb-4"></div>
    <form id="acct-pass-form" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Current Password</label>
        <input id="ap-current" type="password" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">New Password</label>
        <input id="ap-new" type="password" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-slate-200 mb-1">Confirm New Password</label>
        <input id="ap-confirm" type="password" class="w-full bg-white dark:bg-slate-900/50 text-gray-900 dark:text-slate-100 rounded-lg px-3 py-2 border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:focus:ring-violet-400" />
      </div>
      <div class="flex justify-end">
        <button class="px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-lg text-sm font-medium shadow-sm" type="submit">Update Password</button>
      </div>
    </form>
  </div>
</section>

<script>
(function(){
  function endpoint(){ const p = location.pathname.split('/').filter(Boolean); const root='/' + (p[0]||''); return root + '/api/account.php'; }
  const msgP = document.getElementById('acct-prof-msg');
  const msgPw = document.getElementById('acct-pass-msg');
  function setMsg(el, ok, text){ el.className = 'p-3 rounded mb-4 ' + (ok? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'); el.textContent = text; el.classList.remove('hidden'); }

  // Load profile
  fetch(endpoint() + '?action=me', { credentials:'same-origin' }).then(r=>r.json()).then(j=>{
    if (!j.success) throw new Error(j.message||'Failed to load');
    const d = j.data||{};
    document.getElementById('ap-first').value = d.first_name||'';
    document.getElementById('ap-last').value = d.last_name||'';
    document.getElementById('ap-email').value = d.email||'';
    document.getElementById('ap-phone').value = d.phone||'';
    document.getElementById('ap-address').value = d.address||'';
    document.getElementById('ap-dob').value = d.date_of_birth||'';
    document.getElementById('ap-gender').value = d.gender||'';
  }).catch(e=>{ if (msgP) setMsg(msgP, false, e.message||'Failed to load profile'); });

  // Save profile
  const formP = document.getElementById('acct-profile-form');
  formP.addEventListener('submit', function(e){ e.preventDefault();
    const payload = {
      action: 'update_profile',
      first_name: document.getElementById('ap-first').value.trim(),
      last_name: document.getElementById('ap-last').value.trim(),
      email: document.getElementById('ap-email').value.trim(),
      phone: document.getElementById('ap-phone').value.trim(),
      address: document.getElementById('ap-address').value.trim(),
      date_of_birth: document.getElementById('ap-dob').value,
      gender: document.getElementById('ap-gender').value
    };
    fetch(endpoint(), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload), credentials: 'same-origin' })
      .then(r=>r.json()).then(j=>{ if(!j.success) throw new Error(j.message||'Failed'); setMsg(msgP,true,j.message||'Profile saved'); })
      .catch(e=> setMsg(msgP,false,e.message||'Failed to save'));
  });

  // Change password
  const formPw = document.getElementById('acct-pass-form');
  formPw.addEventListener('submit', function(e){ e.preventDefault();
    const payload = { action:'change_password', current_password: document.getElementById('ap-current').value, new_password: document.getElementById('ap-new').value, confirm_password: document.getElementById('ap-confirm').value };
    fetch(endpoint(), { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload), credentials: 'same-origin' })
      .then(r=>r.json()).then(j=>{ if(!j.success) throw new Error(j.message||'Failed'); setMsg(msgPw,true,j.message||'Password updated'); formPw.reset(); })
      .catch(e=> setMsg(msgPw,false,e.message||'Failed to update password'));
  });
})();
</script>
