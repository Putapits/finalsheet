<?php
require_once '../include/database.php';
startSecureSession();
requireRole('doctor');
include 'doctorheader.php';
include 'doctorsidebar.php';
?>

<main id="main-content" class="transition-all duration-300 ease-in-out ml-64 pt-16">
  <div id="content-area" class="p-6 bg-dark-bg min-h-screen">
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    if (in_array($page, ['hcs', 'hss', 'int', 'dashboard', 'account'])) {
      if ($page === 'hcs') {
        $allowedViews = ['overview', 'appointment', 'consultation'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'appointment';
        include 'hcs/' . $view . '.php';
      } elseif ($page === 'hss') {
        $allowedViews = ['overview', 'disease', 'environmental'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'overview';
        include 'hss/' . $view . '.php';
      } elseif ($page === 'account') {
        $allowedViews = ['settings'];
        $view = isset($_GET['view']) && in_array($_GET['view'], $allowedViews) ? $_GET['view'] : 'settings';
        include 'account/' . $view . '.php';
      } else {
        $hcsConsultTypes = ['medical-consultation', 'emergency-care', 'preventive-care'];
        $hssTypes = ['disease-monitoring', 'environmental-monitoring'];
        function d_countPendingAppointments(PDO $db)
        {
          try {
            $s = $db->prepare("SELECT COUNT(*) FROM appointments WHERE status='pending' AND deleted_at IS NULL");
            $s->execute();
            return (int) $s->fetchColumn();
          } catch (Throwable $e) {
            return 0;
          }
        }
        function d_countPendingByTypes(PDO $db, array $types)
        {
          if (!$types)
            return [];
          try {
            $ph = implode(',', array_fill(0, count($types), '?'));
            $sql = "SELECT service_type, COUNT(*) c FROM service_requests WHERE deleted_at IS NULL AND status='pending' AND service_type IN ($ph) GROUP BY service_type";
            $st = $db->prepare($sql);
            $st->execute(array_values($types));
            $out = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
              $out[$r['service_type']] = (int) $r['c'];
            }
            return $out;
          } catch (Throwable $e) {
            return [];
          }
        }
        $hcsAppt = d_countPendingAppointments($db);
        $hcsConsult = d_countPendingByTypes($db, $hcsConsultTypes);
        $hssBy = d_countPendingByTypes($db, $hssTypes);
        $pendingModules = [
          [
            'title' => 'Health Center Services',
            'services' => [
              ['label' => 'Appointment', 'count' => $hcsAppt],
              ['label' => 'Consultations', 'count' => array_sum($hcsConsult)]
            ]
          ],
          [
            'title' => 'Health Surveillance System',
            'services' => [
              ['label' => 'Disease Monitoring', 'count' => (int) ($hssBy['disease-monitoring'] ?? 0)],
              ['label' => 'Environmental Monitoring', 'count' => (int) ($hssBy['environmental-monitoring'] ?? 0)]
            ]
          ],
        ];
        function d_timeAgo($ts)
        {
          $t = is_numeric($ts) ? (int) $ts : strtotime((string) $ts);
          if (!$t)
            return 'just now';
          $d = max(1, time() - $t);
          if ($d < 60)
            return $d . ' seconds ago';
          if ($d < 3600)
            return floor($d / 60) . ' minutes ago';
          if ($d < 86400)
            return floor($d / 3600) . ' hours ago';
          return floor($d / 86400) . ' days ago';
        }
        $recent = [];
        if (!empty($_SESSION['user_id'])) {
          try {
            $recent = $database->getAuditLogsPaginated(['user_id' => $_SESSION['user_id']], 5, 0);
          } catch (Throwable $e) {
            $recent = [];
          }
        }
        ?>
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-white">Doctor Dashboard</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          <?php foreach ($pendingModules as $mod): ?>
            <div class="bg-dark-card rounded-lg p-6 border border-gray-600">
              <p class="text-sm text-gray-400 mb-3"><?php echo htmlspecialchars($mod['title'], ENT_QUOTES, 'UTF-8'); ?></p>
              <div class="space-y-2">
                <?php foreach ($mod['services'] as $svc): ?>
                  <div class="flex items-center justify-between">
                    <span
                      class="text-gray-300 text-sm"><?php echo htmlspecialchars($svc['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="text-white font-bold text-xl"><?php echo number_format((int) $svc['count']); ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-dark-card rounded-lg p-6 border border-gray-600">
            <h3 class="text-lg font-semibold text-white mb-6">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <button onclick="location.href='doctor.php?page=hcs&view=appointment'"
                class="w-full text-left p-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                <div class="flex items-center"><svg class="w-5 h-5 text-white mr-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8" stroke-width="2" fill="none"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8"></path>
                  </svg><span class="text-white font-medium">HCS Appointments</span></div>
              </button>
              <button onclick="location.href='doctor.php?page=hcs&view=consultation'"
                class="w-full text-left p-3 bg-blue-600 hover:bg-green-700 rounded-lg transition-colors">
                <div class="flex items-center"><svg class="w-5 h-5 text-white mr-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8" stroke-width="2" fill="none"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m-4-4h8"></path>
                  </svg><span class="text-white font-medium">HCS Consultations</span></div>
              </button>
              <button onclick="location.href='doctor.php?page=hss&view=disease'"
                class="w-full text-left p-3 bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                <div class="flex items-center"><svg class="w-5 h-5 text-white mr-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg><span class="text-white font-medium">HSS Disease</span></div>
              </button>
              <button onclick="location.href='doctor.php?page=hss&view=environmental'"
                class="w-full text-left p-3 bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                <div class="flex items-center"><svg class="w-5 h-5 text-white mr-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg><span class="text-white font-medium">HSS Environmental</span></div>
              </button>
            </div>
          </div>
          <div class="bg-dark-card rounded-lg p-6 border border-gray-600">
            <h3 class="text-lg font-semibold text-white mb-6">Recent Activity</h3>
            <div class="space-y-4">
              <?php if (empty($recent)): ?>
                <div class="text-sm text-gray-400">No recent activity.</div>
              <?php else:
                foreach ($recent as $ra): ?>
                  <?php $fn = trim(((string) ($ra['first_name'] ?? '')) . ' ' . ((string) ($ra['last_name'] ?? '')));
                  $act = strtolower((string) ($ra['action'] ?? ''));
                  $dot = ($act === 'login' ? 'bg-green-500' : ($act === 'logout' ? 'bg-gray-500' : ($act === 'profile_update' ? 'bg-yellow-500' : ($act === 'password_change' ? 'bg-blue-500' : 'bg-indigo-500')))); ?>
                  <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 rounded-full mt-2 <?php echo $dot; ?>"></div>
                    <div>
                      <p class="text-sm text-white">
                        <?php echo htmlspecialchars((string) ($ra['action'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        <?php echo $fn ? ' - ' . htmlspecialchars($fn, ENT_QUOTES, 'UTF-8') : ''; ?>
                      </p>
                      <p class="text-xs text-gray-400"><?php echo d_timeAgo($ra['created_at'] ?? ''); ?></p>
                    </div>
                  </div>
                <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
        <?php
      }
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
  document.getElementById('sidebar-toggle').addEventListener('click', toggleSidebar);
  function toggleDropdown(id) {
    const dd = document.getElementById(id);
    const arrow = document.getElementById(id.replace('-dropdown', '-arrow'));
    if (dd) dd.classList.toggle('hidden');
    if (arrow) arrow.classList.toggle('rotate-180');
  }
</script>
</body>

</html>