<?php
// Nurse Settings page
?>
<section class="mb-8 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
  <div class="flex flex-col gap-2">
    <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">My Account</span>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Account Settings</h2>
    <p class="text-gray-600 dark:text-gray-400">Update your profile details and keep your password secure.</p>
  </div>
  <div class="inline-flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-gray-200">
    <svg class="h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3 3" /></svg>
    <span id="acct-last-updated">Profile synced recently</span>
  </div>
</section>

<section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
  <!-- Profile -->
  <div class="xl:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="mb-6 flex items-start justify-between">
      <div>
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Personal Information</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Keep your contact and demographic details current.</p>
      </div>
      <button type="button" onclick="document.getElementById('acct-profile-form').requestSubmit();" class="hidden md:inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40">
        Save Changes
      </button>
    </div>
    <div id="acct-prof-msg" class="hidden rounded-xl border border-transparent bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm dark:bg-emerald-500/20 dark:text-emerald-100"></div>
    <form id="acct-profile-form" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">First Name</label>
          <input id="ap-first" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
        </div>
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Name</label>
          <input id="ap-last" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
          <input id="ap-email" type="email" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
        </div>
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
          <input id="ap-phone" type="text" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
        </div>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <textarea id="ap-address" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" rows="3"></textarea>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date of Birth</label>
          <input id="ap-dob" type="date" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
        </div>
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gender</label>
          <select id="ap-gender" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100">
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end">
        <button class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 md:hidden" type="submit">Save Changes</button>
      </div>
    </form>
  </div>

  <!-- Password -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Change Password</h3>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Use a strong password with at least 8 characters.</p>
    <div id="acct-pass-msg" class="hidden rounded-xl border border-transparent bg-emerald-50 p-4 text-sm font-medium text-emerald-700 shadow-sm dark:bg-emerald-500/20 dark:text-emerald-100 mt-4"></div>
    <form id="acct-pass-form" class="mt-6 space-y-5">
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Password</label>
        <input id="ap-current" type="password" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">New Password</label>
        <input id="ap-new" type="password" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Confirm New Password</label>
        <input id="ap-confirm" type="password" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm text-gray-700 shadow-sm transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100" />
      </div>
      <div class="flex justify-end">
        <button class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-slate-700 dark:hover:bg-slate-600" type="submit">Update Password</button>
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
