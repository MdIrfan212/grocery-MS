<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<?php
// home.php (enhanced) — expects variables from dashboard.php

// Load discount data if available
$discountsFile = dirname(__DIR__) . "/data/discounts.json";
$discounts = file_exists($discountsFile) ? load_json($discountsFile) : [];

// time of day greeting
$hour = (int) date('G');
if ($hour < 12) $greet = "Good Morning";
elseif ($hour < 18) $greet = "Good Afternoon";
else $greet = "Good Evening";

// Personalized greeting based on role
$roleGreetings = [
    'admin' => 'System Administrator',
    'manager' => 'Store Manager',
    'employee' => 'Team Member',
    'customer' => 'Valued Customer'
];
$roleTitle = $roleGreetings[$role] ?? 'User';

// today / last 7 days summary
$today = new DateTimeImmutable();
$todayStr = $today->format('Y-m-d');

$ordersToday = array_filter($orders, fn($o)=> str_starts_with($o['date'] ?? '', $todayStr));
$revenueToday = array_sum(array_map(fn($o)=> (int)$o['total'], $ordersToday));

$weekAgo = $today->sub(new DateInterval('P7D'));
$ordersWeek = array_filter($orders, function($o) use ($weekAgo){
  if (empty($o['date'])) return false;
  $d = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $o['date']);
  return $d && $d >= $weekAgo;
});
$ordersWeekCount = count($ordersWeek);
$revenueWeek = array_sum(array_map(fn($o)=> (int)$o['total'], $ordersWeek));

$totalProducts = count($products);
$lowStockCount = count($lowStock);

// to-do / reminders
$unpaidOrders = array_filter($orders, fn($o)=> empty($o['paid']) || $o['paid'] == false);
$unpaidCount = count($unpaidOrders);

// Calculate total unpaid amount
$totalUnpaidAmount = 0;
foreach ($unpaidOrders as $order) {
  $totalUnpaidAmount += (float)$order['total'];
}

// category overview (counts)
$categoryCounts = [];
foreach ($products as $p) {
  $c = $p['category'] ?? 'Other';
  if (!isset($categoryCounts[$c])) $categoryCounts[$c] = 0;
  $categoryCounts[$c]++;
}

// top customer
$customerCounts = [];
foreach ($orders as $o) {
  $b = $o['by'] ?? 'Guest';
  if (!isset($customerCounts[$b])) $customerCounts[$b] = 0;
  $customerCounts[$b] += (int)$o['qty'];
}
arsort($customerCounts);
$topCustomer = null;
if (!empty($customerCounts)) {
  $first = array_key_first($customerCounts);
  $topCustomer = ['name'=>$first,'qty'=>$customerCounts[$first]];
}

// trending category by qty sold
$categorySales = [];
foreach ($orders as $o) {
  $pid = $o['product_id'] ?? '';
  $qty = (int)$o['qty'];
  if (!$pid) continue;
  // find product
  foreach ($products as $p) {
    if ($p['id'] === $pid) {
      $cat = $p['category'] ?? 'Other';
      if (!isset($categorySales[$cat])) $categorySales[$cat] = 0;
      $categorySales[$cat] += $qty;
      break;
    }
  }
}
arsort($categorySales);
$trendingCategory = null;
if (!empty($categorySales)) {
  $tc = array_key_first($categorySales);
  $trendingCategory = ['category'=>$tc,'qty'=>$categorySales[$tc]];
}

// store health meter
if ($lowStockCount >= 8) {
    $health = ['status'=>'Critical','emoji'=>'🔴', 'level'=>'high'];
} elseif ($lowStockCount >= 1) {
    $health = ['status'=>'Warning','emoji'=>'🟡', 'level'=>'medium'];
} else {
    $health = ['status'=>'Healthy','emoji'=>'🟢', 'level'=>'low'];
}

// upcoming expiry — only if products have 'expiry' field (YYYY-MM-DD)
$nearExpiry = [];
foreach ($products as $p) {
  if (!empty($p['expiry'])) {
    $exp = DateTimeImmutable::createFromFormat('Y-m-d', $p['expiry']);
    if ($exp) {
      $diff = $exp->diff(new DateTimeImmutable());
      $days = (int)$diff->format('%r%a');
      if ($days >= 0 && $days <= 14) { // within 14 days
        $nearExpiry[] = ['name'=>$p['name'],'expiry'=>$exp->format('Y-m-d'), 'days'=>$days];
      }
    }
  }
}

// Get recent orders (last 5)
$recentOrders = array_slice(array_reverse($orders), 0, 5);

// tips
$tips = [
  "Tip: Display seasonal fruits at the entrance for impulse buys.",
  "Tip: Bundle rice + curry sauce to increase average order value.",
  "Tip: Restock fast-moving items before weekends.",
  "Tip: Use small discounts to clear low-stock items quickly.",
  "Tip: Label fresh produce with 'New' tags to attract customers."
];
$tip = $tips[array_rand($tips)];

// Calculate inventory value
$inventoryValue = 0;
foreach ($products as $p) {
    $inventoryValue += (float)$p['price'] * (int)$p['stock'];
}

// Count active discounts
$activeDiscounts = array_filter($discounts, function($discount) {
    if (isset($discount['valid_until']) && !empty($discount['valid_until'])) {
        return strtotime($discount['valid_until']) >= time();
    }
    return true;
});
$activeDiscountsCount = count($activeDiscounts);
?>
<!-- Banner -->
<div class="banner" style="background-image:url('../images/products/banner.jpg')">
  <div class="banner-text">
    <h3><?= htmlspecialchars($greet) ?>, <?= htmlspecialchars($name) ?> 👋</h3>
    <p>Welcome back, <?= $roleTitle ?>! Here's your store overview.</p>
    <div style="display:flex;align-items:center;gap:10px;margin-top:8px;flex-wrap:wrap;align-items:center;">
      <div id="datetime" style="background:rgba(255,255,255,0.2);padding:6px 12px;border-radius:20px;font-size:0.9em;">
        <?= date('l, F j, Y') ?> | <span id="current-time"><?= date('h:i A') ?></span>
      </div>
      <a class="btn ghost" href="dashboard.php?page=products"  style="margin: top: 0">Products</a>
      <?php if (can_manage()): ?><a class="btn" href="dashboard.php?page=products#add"  style="margin: top: 0">Add Product</a><?php endif; ?>
      <a class="btn ghost" href="dashboard.php?page=stock"  style="margin-top:0">Stock</a>
      <a class="btn ghost" href="dashboard.php?page=orders" style="margin-top:0">Orders</a>
      <?php if (can_manage()): ?><a class="btn ghost" href="dashboard.php?page=manager" style="margin-top:0">Manager</a><?php endif; ?>
    </div>
  </div>

  <div class="banner-quick">
    <div class="quick-card">
        <div style="font-weight:800;font-size:1.5em;"><?= $totalProducts ?></div>
        <div class="muted">Products</div>
    </div>
    <div class="quick-card">
        <div style="font-weight:800;font-size:1.5em;color:<?= $lowStockCount > 0 ? '#ff9800' : '#4caf50' ?>;"><?= $lowStockCount ?></div>
        <div class="muted">Low Stock</div>
    </div>
    <div class="quick-card">
        <div style="font-weight:800;font-size:1.5em;"><?= count($ordersToday) ?></div>
        <div class="muted">Orders Today</div>
    </div>
    <div class="quick-card">
        <div style="font-weight:800;font-size:1.5em;">৳<?= $revenueToday ?></div>
        <div class="muted">Revenue Today</div>
    </div>
    <?php if (can_manage()): ?>
    <div class="quick-card">
        <div style="font-weight:800;font-size:1.5em;color:#9c27b0;"><?= $activeDiscountsCount ?></div>
        <div class="muted">Active Discounts</div>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="page" style="margin-top:20px">
  <div style="display:grid;grid-template-columns:1fr 360px;gap:16px">
    <div>
      <!-- Quick Stats -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 16px;">
        <div class="panel" style="text-align: center;">
          <div style="font-size: 2em; color: #4caf50;">৳<?= number_format($inventoryValue, 2) ?></div>
          <div class="muted">Inventory Value</div>
        </div>
        <div class="panel" style="text-align: center;">
          <div style="font-size: 2em; color: #2196f3;"><?= count($orders) ?></div>
          <div class="muted">Total Orders</div>
        </div>
        <div class="panel" style="text-align: center;">
          <div style="font-size: 2em; color: #ff9800;"><?= count($categories) ?></div>
          <div class="muted">Categories</div>
        </div>
        <?php if (can_manage()): ?>
        <div class="panel" style="text-align: center;">
          <div style="font-size: 2em; color: #9c27b0;"><?= $activeDiscountsCount ?></div>
          <div class="muted">Active Discounts</div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Manager reminders / To-do -->
      <div class="panel">
        <h3>Reminders / To-do</h3>
        <ul style="list-style:none;padding-left:0">
          <li>• Low stock items: <strong><?= $lowStockCount ?></strong></li>
          <li>• Unpaid orders: <strong><?= $unpaidCount ?></strong> (৳<?= number_format($totalUnpaidAmount, 2) ?>)</li>
          <li>• Orders this week: <strong><?= $ordersWeekCount ?></strong></li>
          <?php if (!empty($nearExpiry)): ?>
            <li>• Products nearing expiry: <strong><?= count($nearExpiry) ?></strong></li>
          <?php endif; ?>
          <?php if (can_manage() && $activeDiscountsCount > 0): ?>
            <li>• Active discount codes: <strong><?= $activeDiscountsCount ?></strong></li>
          <?php endif; ?>
        </ul>
        <?php if (can_manage()): ?>
          <div style="margin-top:8px">
            <a class="btn" href="dashboard.php?page=stock">Review Stock</a>
            <a class="btn ghost" href="dashboard.php?page=orders" style="margin-left:8px">Process Orders</a>
            <a class="btn ghost" href="dashboard.php?page=manager" style="margin-left:8px">Manager Tools</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Category overview -->
      <div class="panel" style="margin-top:12px">
        <h3>Category Overview</h3>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <?php foreach ($categoryCounts as $c => $cnt): ?>
            <div class="card" style="min-width:120px;text-align:center;position:relative;">
              <div style="font-weight:800;font-size:1.2em;"><?= htmlspecialchars($cnt) ?></div>
              <div style="color:#666"><?= htmlspecialchars($c) ?></div>
              <div style="height:4px;background:#f0f0f0;border-radius:2px;margin-top:8px;overflow:hidden;">
                <div style="height:100%;width:<?= min(100, ($cnt / max(1, $totalProducts)) * 100) ?>%;background:#4caf50;"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Weekly summary -->
      <div class="panel" style="margin-top:12px">
        <h3>Weekly Performance</h3>
        <p>This week: <strong><?= $ordersWeekCount ?></strong> orders • Revenue: <strong>৳<?= $revenueWeek ?></strong></p>
        <?php if ($trendingCategory): ?>
          <p>Trending: <strong><?= htmlspecialchars($trendingCategory['category']) ?></strong> (<?= (int)$trendingCategory['qty'] ?> sold)</p>
        <?php endif; ?>
        <?php if ($topCustomer): ?>
          <p>Top customer: <strong><?= htmlspecialchars($topCustomer['name']) ?></strong> (<?= (int)$topCustomer['qty'] ?> items)</p>
        <?php endif; ?>
        
        <?php if (count($ordersWeek) > 0): ?>
          <div style="margin-top: 12px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
              <span>Daily Average:</span>
              <strong>৳<?= number_format($revenueWeek / 7, 2) ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Order Value Average:</span>
              <strong>৳<?= number_format($revenueWeek / max(1, $ordersWeekCount), 2) ?></strong>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Recent Orders -->
      <?php if (!empty($recentOrders)): ?>
      <div class="panel" style="margin-top:12px">
        <h3>Recent Orders</h3>
        <div style="max-height: 300px; overflow-y: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="border-bottom: 1px solid #eee;">
                <th style="text-align: left; padding: 8px;">Product</th>
                <th style="text-align: right; padding: 8px;">Qty</th>
                <th style="text-align: right; padding: 8px;">Total</th>
                <th style="text-align: left; padding: 8px;">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $order): ?>
              <tr style="border-bottom: 1px solid #f5f5f5;">
                <td style="padding: 8px;"><?= htmlspecialchars($order['product_name']) ?></td>
                <td style="text-align: right; padding: 8px;"><?= (int)$order['qty'] ?></td>
                <td style="text-align: right; padding: 8px;">৳<?= (int)$order['total'] ?></td>
                <td style="padding: 8px;">
                  <span style="padding: 2px 6px; border-radius: 4px; font-size: 0.8em; background: <?= $order['paid'] ? '#4caf50' : '#ff9800' ?>; color: white;">
                    <?= $order['paid'] ? 'Paid' : 'Unpaid' ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Top sellers + recent orders (keep as before) -->
      <div class="panel" style="margin-top:12px">
        <h3>Top Sellers</h3>
        <?php
          // calculate top sellers by qty
          $sales = [];
          foreach ($orders as $o) {
            $pid = $o['product_id'] ?? '';
            if (!$pid) continue;
            if (!isset($sales[$pid])) $sales[$pid] = 0;
            $sales[$pid] += (int)$o['qty'];
          }
          arsort($sales);
          $topList = array_slice(array_keys($sales),0,6);
        ?>
        <?php if (empty($topList)): ?><p>No sales yet.</p>
        <?php else: ?><div style="display:flex;gap:10px;flex-wrap:wrap">
          <?php foreach ($topList as $pid): 
            $prod = null;
            foreach ($products as $p) if ($p['id'] === $pid) { $prod = $p; break; }
            if (!$prod) continue;
          ?>
            <div class="topseller-card">
              <img src="<?= imgPath($prod['image']) ?>" alt="">
              <div class="ts-name"><?= htmlspecialchars($prod['name']) ?></div>
              <div class="ts-sales"><?= $sales[$pid] ?> sold</div>
            </div>
          <?php endforeach; ?>
        </div><?php endif; ?>
      </div>

    </div>

    <aside>
      <!-- Quick order -->
      <div class="panel">
        <h3>Quick Order</h3>
        <?php if (empty($products)): ?><p>No products yet.</p>
        <?php else: ?>
          <div style="display:flex;gap:10px;align-items:center">
            <div style="width:80px;height:80px;border-radius:10px;background:#f7f7f7;display:flex;align-items:center;justify-content:center;overflow:hidden">
              <img id="quickPreview" src="<?= imgPath($products[0]['image'] ?? 'placeholder.png') ?>" style="width:100%;height:100%;object-fit:cover">
            </div>
            <form method="post" style="flex:1">
              <input type="hidden" name="action" value="create_order">
              <label style="display:block;margin-bottom:6px">Product</label>
              <select name="product_id" id="quickProduct" onchange="updatePreview()" required>
                <?php foreach ($products as $p): ?>
                  <option value="<?= htmlspecialchars($p['id']) ?>" data-img="<?= htmlspecialchars($p['image']) ?>">
                    <?= htmlspecialchars($p['name']) ?> — ৳<?= (int)$p['price'] ?> (<?= (int)$p['stock'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <div style="display:flex;gap:8px;margin-top:8px">
                <input type="number" name="qty" value="1" min="1" class="qty" style="flex:1">
                <button class="btn" style="min-width:90px">Order</button>
              </div>
            </form>
          </div>
        <?php endif; ?>
      </div>

      <!-- Store health -->
      <div class="panel" style="margin-top:12px">
        <h3>Store Health</h3>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
          <span style="font-size: 2em;"><?= $health['emoji'] ?></span>
          <div>
            <div style="font-weight: bold; font-size: 1.2em;"><?= htmlspecialchars($health['status']) ?></div>
            <div class="muted">Low stock items: <?= $lowStockCount ?></div>
          </div>
        </div>
        <div style="height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
          <div style="height: 100%; width: <?= 
            $health['level'] === 'high' ? '90%' : 
            ($health['level'] === 'medium' ? '60%' : '30%') 
          ?>; background: <?= 
            $health['level'] === 'high' ? '#f44336' : 
            ($health['level'] === 'medium' ? '#ff9800' : '#4caf50') 
          ?>;"></div>
        </div>
      </div>

      <!-- Payment Summary -->
      <div class="panel" style="margin-top:12px">
        <h3>Payment Summary</h3>
        <div style="display: grid; gap: 8px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <span>Total Orders:</span>
            <strong><?= count($orders) ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; color: #4caf50;">
            <span>Paid Orders:</span>
            <strong><?= count($orders) - $unpaidCount ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; color: #f44336;">
            <span>Unpaid Orders:</span>
            <strong><?= $unpaidCount ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; color: #ff9800;">
            <span>Unpaid Amount:</span>
            <strong>৳<?= number_format($totalUnpaidAmount, 2) ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; align-items: center; color: #2196f3;">
            <span>Total Revenue:</span>
            <strong>৳<?= number_format(array_sum(array_map(fn($o) => $o['total'], $orders)), 2) ?></strong>
          </div>
        </div>
        
        <?php if ($unpaidCount > 0): ?>
          <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #eee;">
            <a href="dashboard.php?page=orders&filter=unpaid" class="btn" style="width: 100%; text-align: center; padding: 8px;">
              View Unpaid Orders
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Active Discounts (Manager Only) -->
      <?php if (can_manage() && !empty($activeDiscounts)): ?>
      <div class="panel" style="margin-top:12px">
        <h3>Active Discounts</h3>
        <div style="max-height: 200px; overflow-y: auto;">
          <?php foreach ($activeDiscounts as $discount): ?>
            <div style="padding: 8px; border-bottom: 1px solid #f0f0f0;">
              <div style="font-weight: bold;"><?= htmlspecialchars($discount['code'] ?? 'N/A') ?></div>
              <div style="display: flex; justify-content: space-between; font-size: 0.9em;">
                <span>
                  <?= htmlspecialchars($discount['value'] ?? 0) ?>
                  <?= ($discount['type'] ?? 'percentage') === 'percentage' ? '%' : '৳' ?>
                </span>
                <span>Valid until: <?= htmlspecialchars($discount['valid_until'] ?? 'No expiry') ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div style="margin-top: 10px;">
          <a href="dashboard.php?page=manager" class="btn ghost" style="width: 100%; text-align: center; padding: 8px;">
            Manage Discounts
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Low stock -->
      <div class="panel" style="margin-top:12px">
        <h3>Low Stock Alert</h3>
        <?php if (empty($lowStock)): ?><p>All stocked well ✅</p>
        <?php else: ?>
        <ul style="list-style:none;padding-left:0">
          <?php foreach ($lowStock as $l): 
            $severity = (int)$l['stock'] == 0 ? 'high' : ((int)$l['stock'] <= 2 ? 'medium' : 'low');
            $colors = [
              'high' => ['bg' => '#ffebee', 'border' => '#f44336'],
              'medium' => ['bg' => '#fff8e1', 'border' => '#ffc107'],
              'low' => ['bg' => '#e8f5e9', 'border' => '#4caf50']
            ];
          ?>
            <li style="display:flex;align-items:center;gap:10px;padding:8px;border-radius:8px;margin-bottom:8px;background:<?= $colors[$severity]['bg'] ?>;border-left:4px solid <?= $colors[$severity]['border'] ?>">
              <img src="<?= imgPath($l['image']) ?>" style="width:42px;height:42px;object-fit:cover;border-radius:6px">
              <div style="flex:1">
                <div style="font-weight:800"><?= htmlspecialchars($l['name']) ?></div>
                <div class="muted">Stock: <?= (int)$l['stock'] ?></div>
              </div>
              <?php if (can_manage()): ?>
                <form method="post" style="display:flex;gap:6px;align-items:center">
                  <input type="hidden" name="action" value="restock">
                  <input type="hidden" name="id" value="<?= htmlspecialchars($l['id']) ?>">
                  <input type="number" name="qty" value="10" min="1" style="width:64px;padding:6px;border-radius:6px;border:1px solid #ddd">
                  <button class="btn small">Restock</button>
                </form>
              <?php else: ?><em style="font-size:13px;color:#888">Notify manager</em><?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>

      <!-- Upcoming expiry (if any) -->
      <?php if (!empty($nearExpiry)): ?>
        <div class="panel" style="margin-top:12px">
          <h3>Near Expiry Products</h3>
          <ul style="list-style:none;padding-left:0">
            <?php foreach ($nearExpiry as $ne): 
              $urgency = $ne['days'] <= 3 ? 'high' : ($ne['days'] <= 7 ? 'medium' : 'low');
              $urgencyColors = [
                'high' => '#f44336',
                'medium' => '#ff9800',
                'low' => '#4caf50'
              ];
            ?>
              <li style="padding:6px 0; border-bottom:1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                <div><?= htmlspecialchars($ne['name']) ?></div>
                <div style="color: <?= $urgencyColors[$urgency] ?>; font-weight: bold;">
                  <?= htmlspecialchars($ne['expiry']) ?> (<?= $ne['days'] ?> days)
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- tip -->
      <div class="panel" style="margin-top:12px">
        <h3>Daily Tip</h3>
        <p style="padding: 10px; background: #e3f2fd; border-radius: 6px; border-left: 4px solid #2196f3;">
          <?= htmlspecialchars($tip) ?>
        </p>
      </div>
    </aside>
  </div>
</div>

<script>
function updatePreview(){
  const sel=document.getElementById('quickProduct');
  if(!sel) return;
  const img=sel.options[sel.selectedIndex].dataset.img || 'placeholder.png';
  document.getElementById('quickPreview').src="../images/products/"+img;
}

// Update time every minute
function updateClock() {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  document.getElementById('current-time').textContent = timeStr;
}

document.addEventListener('DOMContentLoaded', ()=>{ 
  try{ 
    updatePreview(); 
    updateClock();
    setInterval(updateClock, 60000);
  } catch(e){} 
});
</script>