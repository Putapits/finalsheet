<?php
// Inspector Settings page
?>
<div class="mb-8">
  <div class="flex flex-col gap-2">
    <span class="text-sm font-semibold uppercase tracking-wide text-primary dark:text-primary-200">Inspector Settings</span>
    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Account Management</h2>
    <p class="text-gray-600 dark:text-gray-400">Update your profile details, contact information, and credentials.</p>
  </div>
</div>

<section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
  <!-- Profile -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
    <div class="mb-4 flex items-start justify-between">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Personal Information</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Keep your contact and demographic information current.</p>
      </div>
    </div>
    <div id="acct-prof-msg" class="hidden"></div>
    <form id="acct-profile-form" class="space-y-4">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">First Name</label>
          <input id="ap-first" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        </div>
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Last Name</label>
          <input id="ap-last" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        </div>
      </div>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Email</label>
          <input id="ap-email" type="email" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        </div>
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Phone</label>
          <input id="ap-phone" type="text" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        </div>
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</label>
        <textarea id="ap-address" rows="3" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"></textarea>
      </div>
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date of Birth</label>
          <input id="ap-dob" type="date" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
        </div>
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gender</label>
          <select id="ap-gender" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end">
        <button class="inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40" type="submit">Save Changes</button>
      </div>
    </form>
  </div>

  <!-- Password -->
  <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
    <div class="mb-4 flex items-start justify-between">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Change Password</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Choose a strong password to secure your inspector access.</p>
      </div>
    </div>
    <div id="acct-pass-msg" class="hidden"></div>
    <form id="acct-pass-form" class="space-y-4">
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current Password</label>
        <input id="ap-current" type="password" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">New Password</label>
        <input id="ap-new" type="password" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div>
        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Confirm New Password</label>
        <input id="ap-confirm" type="password" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-primary focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
      </div>
      <div class="flex justify-end">
        <button class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary/30 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100 dark:hover:bg-slate-700" type="submit">Update Password</button>
      </div>
    </form>
  </div>
</section>

<script>
(function(){
  function endpoint(){ const p = location.pathname.split('/').filter(Boolean); const root='/' + (p[0]||''); return root + '/api/account.php'; }
  const msgP = document.getElementById('acct-prof-msg');
  const msgPw = document.getElementById('acct-pass-msg');
  function setMsg(el, ok, text){
    if (!el) return;
    const base = 'mb-4 rounded-xl px-4 py-3 text-sm font-semibold shadow-sm flex items-start gap-3 '; 
    const styles = ok
      ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/20 dark:text-emerald-100'
      : 'border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/40 dark:bg-rose-500/20 dark:text-rose-100';
    el.className = base + styles;
    el.textContent = text;
    el.setAttribute('role','alert');
    el.classList.remove('hidden');
  }

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
