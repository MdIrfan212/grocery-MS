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
$filter = $_GET['filter'] ?? 'all';
$list = $orders;
if ($filter === 'today') $list = array_filter($orders, fn($o)=> str_starts_with($o['date'], date("Y-m-d")));
if ($filter === 'unpaid') $list = array_filter($orders, fn($o)=> !$o['paid']);
?>
<div class="panel">
  <h3>Orders</h3>
  <div style="margin-bottom:16px; display: flex; gap: 8px; flex-wrap: wrap;">
    <a class="btn <?= $filter === 'today' ? 'primary' : 'ghost' ?>" href="dashboard.php?page=orders&filter=today">Today</a>
    <a class="btn <?= $filter === 'unpaid' ? 'primary' : 'ghost' ?>" href="dashboard.php?page=orders&filter=unpaid">Unpaid</a>
    <a class="btn <?= $filter === 'all' ? 'primary' : 'ghost' ?>" href="dashboard.php?page=orders&filter=all">All Orders</a>
  </div>

  <?php if (empty($list)): ?>
    <div class="muted" style="padding: 20px; text-align: center;">
      No orders found<?= $filter !== 'all' ? " for this filter" : "" ?>.
    </div>
  <?php else: ?>
    <div style="margin-bottom: 12px; padding: 8px; background: #f8f8f8; border-radius: 6px;">
      <strong>Summary:</strong> 
      <?php 
      $totalOrders = count($list);
      $paidOrders = count(array_filter($list, fn($o) => $o['paid']));
      $unpaidOrders = $totalOrders - $paidOrders;
      $totalRevenue = array_sum(array_map(fn($o) => $o['total'], $list));
      ?>
      <span style="margin-left: 15px;">Orders: <?= $totalOrders ?></span>
      <span style="margin-left: 15px; color: #4caf50;">Paid: <?= $paidOrders ?></span>
      <span style="margin-left: 15px; color: #f44336;">Unpaid: <?= $unpaidOrders ?></span>
      <span style="margin-left: 15px; color: #2196f3;">Revenue: ৳<?= $totalRevenue ?></span>
    </div>

    <table class="table">
      <tr>
        <th>ID</th>
        <th>Item</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Total</th>
        <th>By</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
      <?php foreach (array_reverse($list) as $o): ?>
        <tr>
          <td><code><?= htmlspecialchars($o['id']) ?></code></td>
          <td><?= htmlspecialchars($o['product_name']) ?></td>
          <td><?= (int)$o['qty'] ?></td>
          <td>৳<?= (int)$o['price'] ?></td>
          <td><strong>৳<?= (int)$o['total'] ?></strong></td>
          <td><?= htmlspecialchars($o['by']) ?></td>
          <td><?= htmlspecialchars(date("M j, H:i", strtotime($o['date']))) ?></td>
          <td>
            <span class="badge <?= $o['paid'] ? 'ok' : 'danger' ?>">
              <?= htmlspecialchars($o['status']) ?> 
              <?= $o['paid'] ? "• Paid" : "• Unpaid" ?>
            </span>
          </td>
          <td>
            <?php if (!$o['paid'] && (can_manage() || $o['by'] === $name)): ?>
              <form method="post" style="display:inline-block">
                <input type="hidden" name="action" value="mark_paid">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($o['id']) ?>">
                <select name="method" style="padding: 4px; border-radius: 4px; border: 1px solid #ddd;">
                  <option>Cash</option>
                  <option>Card</option>
                  <option>Mobile</option>
                </select>
                <button class="btn small" style="margin-left: 4px;">Mark Paid</button>
              </form>
            <?php else: ?>
              <?php if ($o['paid']): ?>
                <span class="muted">Paid via <?= htmlspecialchars($o['method'] ?? 'Cash') ?></span>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>