<?php
if (!isset($db)) { require_once '../../include/database.php'; startSecureSession(); requireRole('admin'); }
$typeLabels = [
  'medical-consultation'=>'HCS: Consultation', 'emergency-care'=>'HCS: Emergency Care', 'preventive-care'=>'HCS: Preventive Care',
  'business-permit'=>'SPI: Business Permit', 'health-inspection'=>'SPI: Health Inspection',
  'vaccination'=>'INT: Vaccination', 'nutrition-monitoring'=>'INT: Nutrition Monitoring',
  'system-inspection'=>'WSS: System Inspection', 'maintenance-service'=>'WSS: Maintenance', 'installation-upgrade'=>'WSS: Installation & Upgrade',
  'disease-monitoring'=>'HSS: Disease Monitoring', 'environmental-monitoring'=>'HSS: Environmental Monitoring',
];
function n_timeago($ts){ $t=is_numeric($ts)?(int)$ts:strtotime((string)$ts); if(!$t)return 'just now'; $d=max(1,time()-$t); if($d<60)return $d.'s ago'; if($d<3600)return floor($d/60).'m ago'; if($d<86400)return floor($d/3600).'h ago'; return floor($d/86400).'d ago'; }
$moduleMap = [
  'hcs_appointment' => 'hcs',
  'medical-consultation' => 'hcs', 'emergency-care'=>'hcs', 'preventive-care'=>'hcs',
  'business-permit'=>'spi','health-inspection'=>'spi',
  'vaccination'=>'int','nutrition-monitoring'=>'int',
  'system-inspection'=>'wss','maintenance-service'=>'wss','installation-upgrade'=>'wss',
  'disease-monitoring'=>'hss','environmental-monitoring'=>'hss',
];
$routes = [
  'hcs_appointment' => 'DashboardOverview_new.php?page=hcs&view=appointment',
  'medical-consultation'=>'DashboardOverview_new.php?page=hcs&view=consultation', 'emergency-care'=>'DashboardOverview_new.php?page=hcs&view=consultation', 'preventive-care'=>'DashboardOverview_new.php?page=hcs&view=consultation',
  'business-permit'=>'DashboardOverview_new.php?page=spi&view=permits','health-inspection'=>'DashboardOverview_new.php?page=spi&view=sanitation',
  'vaccination'=>'DashboardOverview_new.php?page=int&view=immunization','nutrition-monitoring'=>'DashboardOverview_new.php?page=int&view=nutrition',
  'system-inspection'=>'DashboardOverview_new.php?page=wss&view=septic','maintenance-service'=>'DashboardOverview_new.php?page=wss&view=assessment','installation-upgrade'=>'DashboardOverview_new.php?page=wss&view=assessment',
  'disease-monitoring'=>'DashboardOverview_new.php?page=hss&view=disease','environmental-monitoring'=>'DashboardOverview_new.php?page=hss&view=environmental',
];
$q = trim((string)($_GET['q'] ?? ''));
$modFilter = trim((string)($_GET['module'] ?? ''));
// Extra filters
$serviceFilter = trim((string)($_GET['service'] ?? ''));
$kindFilter = trim((string)($_GET['kind'] ?? ''));// '' | 'hcs_appointment' | 'service_request'
$dateFrom = trim((string)($_GET['date_from'] ?? ''));// YYYY-MM-DD
$dateTo = trim((string)($_GET['date_to'] ?? ''));// YYYY-MM-DD
// Pagination
$per = (int)($_GET['per'] ?? 20); if ($per < 5) $per = 5; if ($per > 100) $per = 100;
$pageNum = (int)($_GET['p'] ?? 1); if ($pageNum < 1) $pageNum = 1;
$limit = 1000; // fetch cap before merging; pagination is applied after filtering
try { $apps = $db->query("SELECT id, 'hcs_appointment' AS kind, appointment_type AS subtype, CONCAT(first_name,' ',last_name) AS name, email, phone, created_at FROM appointments WHERE status='pending' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) { $apps = []; }
try { $reqs = $db->query("SELECT id, 'service_request' AS kind, service_type AS subtype, full_name AS name, email, phone, created_at FROM service_requests WHERE status='pending' AND deleted_at IS NULL ORDER BY created_at DESC LIMIT $limit")->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) { $reqs = []; }
$items = array_merge($apps, $reqs);
usort($items, function($a,$b){ return strcmp((string)($b['created_at']??''),(string)($a['created_at']??'')); });
// Text search
if ($q !== '') {
  $items = array_values(array_filter($items, function($r) use ($q){
    $hay = strtolower(($r['name']??'')." ".($r['subtype']??'')." ".($r['kind']??''));
    return strpos($hay, strtolower($q)) !== false;
  }));
}
// Module filter
if ($modFilter !== '' && in_array($modFilter, ['hcs','spi','int','wss','hss'])) {
  $items = array_values(array_filter($items, function($r) use ($moduleMap, $modFilter){
    $key = (string)($r['kind'] === 'hcs_appointment' ? 'hcs_appointment' : ($r['subtype'] ?? ''));
    return ($moduleMap[$key] ?? '') === $modFilter;
  }));
}
// Kind filter
if ($kindFilter === 'hcs_appointment' || $kindFilter === 'service_request') {
  $items = array_values(array_filter($items, function($r) use ($kindFilter){ return ($r['kind'] ?? '') === $kindFilter; }));
}
// Service filter (including appointments)
if ($serviceFilter !== '') {
  if ($serviceFilter === 'hcs_appointment') {
    $items = array_values(array_filter($items, function($r){ return ($r['kind'] ?? '') === 'hcs_appointment'; }));
  } else {
    $items = array_values(array_filter($items, function($r) use ($serviceFilter){ return (string)($r['subtype'] ?? '') === $serviceFilter; }));
  }
}
// Date range filter (created_at)
if ($dateFrom !== '' || $dateTo !== '') {
  $fromTs = $dateFrom ? strtotime($dateFrom.' 00:00:00') : null;
  $toTs = $dateTo ? strtotime($dateTo.' 23:59:59') : null;
  $items = array_values(array_filter($items, function($r) use ($fromTs, $toTs){
    $t = strtotime((string)($r['created_at'] ?? '')) ?: 0;
    if ($fromTs && $t < $fromTs) return false;
    if ($toTs && $t > $toTs) return false;
    return true;
  }));
}
// Pagination vars after filtering
$totalItems = count($items);
$totalPages = max(1, (int)ceil($totalItems / $per));
if ($pageNum > $totalPages) $pageNum = $totalPages;
$offset = ($pageNum - 1) * $per;
$pageItems = array_slice($items, $offset, $per);
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($script, '/admin/');
$adminBase = ($pos !== false) ? substr($script, 0, $pos + 7) : '/admin/';
?>
<?php
  $pendingTotal = $totalItems;
  $countAppt = 0; $countReq = 0;
  foreach ($items as $r) { if (($r['kind'] ?? '') === 'hcs_appointment') { $countAppt++; } else { $countReq++; } }
  $baseQs = [
    'page'=>'notifications','view'=>'list',
    'q'=>$q,'module'=>$modFilter,'service'=>$serviceFilter,'kind'=>$kindFilter,
    'date_from'=>$dateFrom,'date_to'=>$dateTo,'per'=>$per
  ];
  $buildFilterUrl = function($overrides = []) use ($adminBase, $baseQs) {
    $qs = array_merge($baseQs, $overrides);
    return htmlspecialchars($adminBase.'DashboardOverview_new.php?'.http_build_query($qs), ENT_QUOTES, 'UTF-8');
  };
  $modColors = [
    'hcs' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
    'spi' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
    'int' => 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-200',
    'wss' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
    'hss' => 'bg-fuchsia-50 text-fuchsia-700 ring-1 ring-fuchsia-200',
  ];
?>
<div class="mb-6">
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h2 class="text-2xl font-semibold text-slate-800">Notifications</h2>
      <p class="text-slate-500 mt-1">New pending submissions from citizens across modules.</p>
    </div>
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-700 ring-1 ring-blue-200 text-sm">
        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
        <?php echo (int)$pendingTotal; ?> pending
      </span>
      <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 text-sm">
        <?php echo (int)$countAppt; ?> appointments
      </span>
      <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-amber-200 text-sm">
        <?php echo (int)$countReq; ?> requests
      </span>
    </div>
  </div>
  <div class="mt-3 flex flex-wrap items-center gap-2">
    <a href="<?php echo $buildFilterUrl(['module'=>'']); ?>" class="px-3 py-1.5 rounded-full text-xs <?php echo $modFilter===''?'bg-blue-100 text-blue-700 ring-1 ring-blue-200':'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>">All</a>
    <?php foreach (['hcs'=>'HCS','spi'=>'SPI','int'=>'INT','wss'=>'WSS','hss'=>'HSS'] as $k=>$v): $active = $modFilter===$k; ?>
      <a href="<?php echo $buildFilterUrl(['module'=>$k]); ?>" class="px-3 py-1.5 rounded-full text-xs <?php echo $active?($modColors[$k]??'bg-blue-100 text-blue-700 ring-1 ring-blue-200'):'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?>"><?php echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); ?></a>
    <?php endforeach; ?>
  </div>
</div>
<div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm mb-4">
  <form method="get" action="<?php echo htmlspecialchars($adminBase.'DashboardOverview_new.php', ENT_QUOTES, 'UTF-8'); ?>" class="grid grid-cols-1 md:grid-cols-12 gap-3">
    <input type="hidden" name="page" value="notifications" />
    <input type="hidden" name="view" value="list" />
    <div class="md:col-span-5">
      <label class="block text-xs text-slate-600 mb-1" for="notif_search">Search</label>
      <input id="notif_search" aria-label="Search by name or service type" type="text" name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-white text-slate-900 placeholder-slate-400 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Search name or service..." />
    </div>
    <div class="md:col-span-3">
      <label class="block text-xs text-slate-600 mb-1" for="notif_service">Service</label>
      <select id="notif_service" name="service" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <option value="">All</option>
        <option value="hcs_appointment" <?php echo $serviceFilter==='hcs_appointment'?'selected':''; ?>>HCS: Appointment</option>
        <?php foreach ($typeLabels as $k=>$label): ?>
          <option value="<?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $serviceFilter===$k?'selected':''; ?>><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-600 mb-1" for="notif_kind">Kind</label>
      <select id="notif_kind" name="kind" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <option value="">All</option>
        <option value="hcs_appointment" <?php echo $kindFilter==='hcs_appointment'?'selected':''; ?>>HCS Appointment</option>
        <option value="service_request" <?php echo $kindFilter==='service_request'?'selected':''; ?>>Service Request</option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-600 mb-1" for="notif_module">Module</label>
      <select id="notif_module" name="module" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <option value="">All</option>
        <?php foreach (['hcs'=>'HCS','spi'=>'SPI','int'=>'INT','wss'=>'WSS','hss'=>'HSS'] as $k=>$v): ?>
          <option value="<?php echo $k; ?>" <?php echo $modFilter===$k?'selected':''; ?>><?php echo $v; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-600 mb-1" for="notif_from">From</label>
      <input id="notif_from" aria-label="Filter from date" type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
    </div>
    <div>
      <label class="block text-xs text-slate-600 mb-1" for="notif_to">To</label>
      <input id="notif_to" aria-label="Filter to date" type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
    </div>
    <div>
      <label class="block text-xs text-slate-600 mb-1" for="notif_per">Per Page</label>
      <select id="notif_per" name="per" class="w-full bg-white text-slate-900 rounded-lg px-3 py-2 border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <?php foreach ([10,20,50,100] as $opt): ?>
          <option value="<?php echo $opt; ?>" <?php echo (int)$per===$opt?'selected':''; ?>><?php echo $opt; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="md:col-span-2 flex items-end gap-2">
      <button type="submit" class="px-3 py-2 bg-primary text-white rounded-lg hover:opacity-90 text-sm">Apply</button>
      <a href="<?php echo htmlspecialchars($adminBase.'DashboardOverview_new.php?page=notifications&view=list', ENT_QUOTES, 'UTF-8'); ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs border border-slate-300">Reset</a>
    </div>
  </form>
</div>
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-100 sticky top-0 z-10">
        <tr>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Module</th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Service</th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Citizen</th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Submitted</th>
          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Action</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-slate-100">
        <?php if (empty($pageItems)): ?>
          <tr>
            <td colspan="5" class="px-4 py-10 text-center text-slate-500 text-sm">No pending submissions for the selected filters.</td>
          </tr>
        <?php else: foreach ($pageItems as $it): ?>
          <?php
            $kind = (string)($it['kind'] ?? '');
            $sub = (string)($it['subtype'] ?? '');
            $title = ($kind === 'hcs_appointment') ? 'HCS: Appointment' : ($typeLabels[$sub] ?? ucfirst(str_replace('-', ' ', $sub)));
            $mod = $moduleMap[$kind === 'hcs_appointment' ? 'hcs_appointment' : $sub] ?? '';
            $name = trim((string)($it['name'] ?? ''));
            $when = (string)($it['created_at'] ?? '');
            $urlKey = ($kind === 'hcs_appointment') ? 'hcs_appointment' : $sub;
            $url = $routes[$urlKey] ?? 'DashboardOverview_new.php';
            $badgeCls = $modColors[$mod] ?? 'bg-slate-100 text-slate-700 ring-1 ring-slate-200';
          ?>
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 text-sm">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full uppercase tracking-wide text-[11px] <?php echo $badgeCls; ?>"><?php echo htmlspecialchars($mod ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
            </td>
            <td class="px-4 py-3 text-sm text-slate-800">
              <div class="flex items-center gap-2">
                <span class="font-medium truncate max-w-[26ch]" title="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="hidden md:inline-flex px-2 py-0.5 rounded-full text-[11px] uppercase tracking-wide <?php echo $kind==='hcs_appointment'?'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200':'bg-amber-50 text-amber-700 ring-1 ring-amber-200'; ?>"><?php echo $kind==='hcs_appointment'?'Appointment':'Request'; ?></span>
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-slate-700">
              <span class="truncate max-w-[26ch]" title="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>
            </td>
            <td class="px-4 py-3 text-sm text-slate-500" title="<?php echo htmlspecialchars($when, ENT_QUOTES, 'UTF-8'); ?>"><?php echo n_timeago($when); ?></td>
            <td class="px-4 py-3 text-sm">
              <a aria-label="Open submission" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white" data-notif-kind="<?php echo htmlspecialchars($kind, ENT_QUOTES, 'UTF-8'); ?>" data-notif-id="<?php echo (int)($it['id'] ?? 0); ?>" href="<?php echo htmlspecialchars($adminBase.$url, ENT_QUOTES, 'UTF-8'); ?>">Open</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <div class="flex items-center justify-between p-3 border-t border-slate-200 text-sm">
    <div class="text-slate-600">Showing
      <span class="text-slate-800 font-medium"><?php echo $totalItems ? ($offset+1) : 0; ?></span> -
      <span class="text-slate-800 font-medium"><?php echo min($offset + $per, $totalItems); ?></span>
      of <span class="text-slate-800 font-medium"><?php echo $totalItems; ?></span>
    </div>
    <div class="flex items-center gap-2">
      <?php
        $qs = [
          'page'=>'notifications','view'=>'list',
          'q'=>$q,'module'=>$modFilter,'service'=>$serviceFilter,'kind'=>$kindFilter,
          'date_from'=>$dateFrom,'date_to'=>$dateTo,'per'=>$per
        ];
        $makeUrl = function($p) use ($adminBase, $qs){ $qs['p']=(int)$p; return htmlspecialchars($adminBase.'DashboardOverview_new.php?'.http_build_query($qs), ENT_QUOTES, 'UTF-8'); };
      ?>
      <a aria-label="Previous page" class="px-2 py-1 rounded <?php echo $pageNum<=1?'bg-slate-100 text-slate-400 cursor-not-allowed':'bg-slate-200 hover:bg-slate-300 text-slate-800'; ?>" href="<?php echo $pageNum<=1?'#':$makeUrl($pageNum-1); ?>">Prev</a>
      <?php
        $start = max(1, $pageNum-2); $end = min($totalPages, $pageNum+2);
        if ($start > 1) { echo '<span class="px-2 text-slate-400">...</span>'; }
        for($i=$start;$i<=$end;$i++){
          $cls = $i===$pageNum ? 'bg-primary text-white' : 'bg-slate-200 hover:bg-slate-300 text-slate-800';
          echo '<a aria-label="Page '.$i.'" class="px-2 py-1 rounded '.$cls.'" href="'.$makeUrl($i).'">'.$i.'</a>';
        }
        if ($end < $totalPages) { echo '<span class="px-2 text-slate-400">...</span>'; }
      ?>
      <a aria-label="Next page" class="px-2 py-1 rounded <?php echo $pageNum>=$totalPages?'bg-slate-100 text-slate-400 cursor-not-allowed':'bg-slate-200 hover:bg-slate-300 text-slate-800'; ?>" href="<?php echo $pageNum>=$totalPages?'#':$makeUrl($pageNum+1); ?>">Next</a>
    </div>
  </div>
</div>
<script>
(function(){
  try {
    window.__adminBase = window.__adminBase || <?php echo json_encode($adminBase, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
  } catch (_) {}
  document.addEventListener('click', function(e){
    var a = e.target.closest('a[data-notif-kind][data-notif-id]');
    if (!a) return;
    try {
      var kind = a.getAttribute('data-notif-kind');
      var id = a.getAttribute('data-notif-id');
      var fd = new FormData(); fd.append('action','read'); fd.append('kind',kind); fd.append('id',id);
      if (navigator.sendBeacon) {
        navigator.sendBeacon(window.__adminBase + 'api/notifications.php', fd);
      } else {
        fetch(window.__adminBase + 'api/notifications.php', { method:'POST', body: fd, keepalive: true });
      }
      var badge = document.getElementById('notif-badge');
      var total = document.getElementById('notif-total');
      var cur = badge ? parseInt(badge.textContent || '0', 10) : 0;
      if (cur > 0 && badge) { badge.textContent = (cur-1); if (cur-1<=0) { badge.remove(); } }
      if (total) { var num = parseInt(total.textContent, 10); if (!isNaN(num) && num>0) total.textContent = (num-1) + ' total'; }
    } catch (err) { /* ignore */ }
  });
})();
</script>
