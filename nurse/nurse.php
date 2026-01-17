<?php
require_once '../include/database.php';
startSecureSession();
requireRole('nurse');
include 'nurseheader.php';
include 'nursesidebar.php';
?>

<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div id="content-area" class="p-6 bg-dark-bg min-h-screen">
    <?php
      $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
      if ($page === 'int') {
        $allowedViews = ['overview','vaccination','nutrition'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'overview';
        include 'int/' . $view . '.php';
      } elseif ($page === 'hcs') {
        $allowedViews = ['overview','appointment','consultation'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'overview';
        include 'hcs/' . $view . '.php';
      } elseif ($page === 'hss') {
        $allowedViews = ['overview','disease','environmental'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'overview';
        include 'hss/' . $view . '.php';
      } elseif ($page === 'account') {
        $allowedViews = ['settings'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'settings';
        include 'account/' . $view . '.php';
      } elseif ($page === 'dashboard') {
        $hcsConsultTypes = ['medical-consultation','emergency-care','preventive-care'];
        $intTypes = ['vaccination','nutrition-monitoring'];
        $hssTypes = ['disease-monitoring','environmental-monitoring'];
        function n_countPendingAppointments(PDO $db){ try{ $s=$db->prepare("SELECT COUNT(*) FROM appointments WHERE status='pending' AND deleted_at IS NULL"); $s->execute(); return (int)$s->fetchColumn(); } catch(Throwable $e){ return 0; } }
        function n_countPendingByTypes(PDO $db, array $types){ if(!$types)return[]; try{ $ph=implode(',',array_fill(0,count($types),'?')); $sql="SELECT service_type, COUNT(*) c FROM service_requests WHERE deleted_at IS NULL AND status='pending' AND service_type IN ($ph) GROUP BY service_type"; $st=$db->prepare($sql); $st->execute(array_values($types)); $out=[]; foreach($st->fetchAll(PDO::FETCH_ASSOC) as $r){ $out[$r['service_type']]=(int)$r['c']; } return $out; } catch(Throwable $e){ return []; } }
        $hcsAppt = n_countPendingAppointments($db);
        $hcsConsult = n_countPendingByTypes($db, $hcsConsultTypes);
        $intBy = n_countPendingByTypes($db, $intTypes);
        $hssBy = n_countPendingByTypes($db, $hssTypes);
        $pendingModules = [
          ['title'=>'Health Center Services','services'=>[
            ['label'=>'Appointment','count'=>$hcsAppt],
            ['label'=>'Consultations','count'=>array_sum($hcsConsult)]
          ]],
          ['title'=>'Immunization & Nutrition','services'=>[
            ['label'=>'Vaccination','count'=>(int)($intBy['vaccination']??0)],
            ['label'=>'Nutrition Monitoring','count'=>(int)($intBy['nutrition-monitoring']??0)]
          ]],
          ['title'=>'Health Surveillance System','services'=>[
            ['label'=>'Disease Monitoring','count'=>(int)($hssBy['disease-monitoring']??0)],
            ['label'=>'Environmental Monitoring','count'=>(int)($hssBy['environmental-monitoring']??0)]
          ]],
        ];
        $totalPending = 0;
        foreach ($pendingModules as $module) {
          foreach ($module['services'] as $svc) {
            $totalPending += (int)$svc['count'];
          }
        }
        function n_timeAgo($ts){ $t=is_numeric($ts)?(int)$ts:strtotime((string)$ts); if(!$t)return 'just now'; $d=max(1,time()-$t); if($d<60)return $d.' seconds ago'; if($d<3600)return floor($d/60).' minutes ago'; if($d<86400)return floor($d/3600).' hours ago'; return floor($d/86400).' days ago'; }
        $recent = [];
        if (!empty($_SESSION['user_id'])) { try { $recent = $database->getAuditLogsPaginated(['user_id'=>$_SESSION['user_id']], 5, 0); } catch (Throwable $e) { $recent = []; } }
        ?>
        <div class="mb-10 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Nurse Dashboard</h2>
            <p class="text-gray-600 dark:text-gray-400">Monitor pending requests, launch core actions, and review your latest activity.</p>
          </div>
          <div class="inline-flex items-center rounded-full border border-primary/30 bg-primary/10 px-5 py-2 text-sm font-semibold text-primary dark:border-primary/40 dark:bg-primary/15 dark:text-primary-200">
            Total Pending Tasks: <span class="ml-2 text-base"><?php echo number_format($totalPending); ?></span>
          </div>
        </div>

        <div class="mb-10 grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
          <?php foreach ($pendingModules as $mod): ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8'); ?></p>
              <div class="mt-4 space-y-4">
                <?php foreach ($mod['services'] as $svc): ?>
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-300"><?php echo htmlspecialchars($svc['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-semibold text-gray-800 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-100"><?php echo number_format((int)$svc['count']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Jump directly into high-impact workflows.</p>
            <div class="mt-6 grid grid-cols-1 gap-3 md:grid-cols-2">
              <?php
                $quickActions = [
                  ['href'=>'nurse.php?page=hcs&view=appointment','label'=>'HCS Appointments','color'=>'text-blue-600 dark:text-blue-300','icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                  ['href'=>'nurse.php?page=hcs&view=consultation','label'=>'HCS Consultations','color'=>'text-emerald-600 dark:text-emerald-300','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                  ['href'=>'nurse.php?page=int&view=vaccination','label'=>'INT Vaccination','color'=>'text-rose-600 dark:text-rose-300','icon'=>'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z'],
                  ['href'=>'nurse.php?page=int&view=nutrition','label'=>'INT Nutrition','color'=>'text-amber-600 dark:text-amber-300','icon'=>'M3 7h2a2 2 0 012 2v10a2 2 0 01-2 2H3m0-14V5a2 2 0 012-2h2a2 2 0 012 2v2m0 0h10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v2m-6 0v14m0 0h-4a2 2 0 01-2-2V9h8v12a2 2 0 01-2 2z'],
                  ['href'=>'nurse.php?page=hss&view=disease','label'=>'HSS Disease','color'=>'text-indigo-600 dark:text-indigo-300','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                  ['href'=>'nurse.php?page=hss&view=environmental','label'=>'HSS Environmental','color'=>'text-teal-600 dark:text-teal-300','icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
              ?>
              <?php foreach ($quickActions as $action): ?>
                <button type="button" onclick="location.href='<?php echo htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8'); ?>'" class="group flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-3 text-left text-sm font-semibold text-gray-700 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/50 hover:bg-primary/10 dark:border-slate-700 dark:bg-slate-900/60 dark:text-gray-200 dark:hover:border-primary/40 dark:hover:bg-primary/20">
                  <div class="flex items-center gap-3">
                    <span class="rounded-full bg-primary/10 p-2 text-primary dark:bg-primary/20 dark:text-primary-200">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $action['icon']; ?>"></path></svg>
                    </span>
                    <span><?php echo htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>
                  <svg class="h-4 w-4 text-gray-400 transition group-hover:text-primary dark:group-hover:text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-slate-700 dark:bg-slate-900/70">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Track logins, profile updates, and password changes.</p>
              </div>
              <span class="rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300">Last 5</span>
            </div>
            <div class="mt-6 space-y-4">
              <?php if (empty($recent)): ?>
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-gray-300">No recent activity.</div>
              <?php else: foreach ($recent as $ra): ?>
                <?php $fn = trim(((string)($ra['first_name'] ?? '')) . ' ' . ((string)($ra['last_name'] ?? ''))); $act = strtolower((string)($ra['action'] ?? '')); $dot = ($act==='login' ? 'bg-emerald-500' : ($act==='logout' ? 'bg-slate-500' : ($act==='profile_update' ? 'bg-amber-500' : ($act==='password_change' ? 'bg-blue-500' : 'bg-primary')))); ?>
                <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                  <span class="mt-1 h-2.5 w-2.5 rounded-full <?php echo $dot; ?>"></span>
                  <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100"><?php echo htmlspecialchars((string)($ra['action'] ?? ''), ENT_QUOTES, 'UTF-8'); ?><?php echo $fn ? ' · ' . htmlspecialchars($fn, ENT_QUOTES, 'UTF-8') : ''; ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo n_timeAgo($ra['created_at'] ?? ''); ?></p>
                  </div>
                </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <?php
      } else {
        echo '<div class="text-white">Invalid page.</div>';
      }
    ?>
  </div>
</main>

<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleButton = document.getElementById('sidebar-toggle');
    if (!sidebar || !mainContent || !toggleButton) return;
    sidebar.classList.toggle('sidebar-collapsed');
    if (sidebar.classList.contains('sidebar-collapsed')) {
      mainContent.classList.remove('ml-64');
      mainContent.classList.add('ml-16');
      toggleButton.classList.remove('ml-64');
      toggleButton.classList.add('ml-16');
    } else {
      mainContent.classList.remove('ml-16');
      mainContent.classList.add('ml-64');
      toggleButton.classList.remove('ml-16');
      toggleButton.classList.add('ml-64');
    }
  }
  const sbt = document.getElementById('sidebar-toggle');
  if (sbt) sbt.addEventListener('click', toggleSidebar);
  function toggleDropdown(id) {
    const dd = document.getElementById(id);
    const arrow = document.getElementById(id.replace('-dropdown','-arrow'));
    if (dd) dd.classList.toggle('hidden');
    if (arrow) arrow.classList.toggle('rotate-180');
  }
  document.addEventListener('DOMContentLoaded', function(){
    const sidebar = document.getElementById('sidebar');
    const main = document.getElementById('main-content');
    if (sidebar && main) {
      let node = main.previousElementSibling;
      while (node && node !== sidebar) {
        const prev = node.previousElementSibling;
        node.parentNode.removeChild(node);
        node = prev;
      }
    }
  });
</script>
