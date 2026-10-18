<?php require 'includes/auth.php'; require_login(); $title='Dashboard';
$pdo=db(); $mgr=in_array(user()['role'],['admin','owner'],true);
$count=$pdo->query('SELECT COUNT(*) FROM products WHERE status=1')->fetchColumn();
$low=$pdo->query('SELECT name,quantity,reorder_level FROM products WHERE status=1 AND quantity<=reorder_level ORDER BY quantity')->fetchAll();
if($mgr) $today=$pdo->query('SELECT COALESCE(SUM(total),0) FROM bills WHERE DATE(created_at)=CURDATE()')->fetchColumn();
include 'includes/header.php'; ?>
<h1 class="h4 mb-3">Dashboard</h1>
<div class="row g-3 mb-4">
  <div class="col-sm-4"><div class="panel"><div class="big"><?= (int)$count ?></div>Active products</div></div>
  <div class="col-sm-4"><div class="panel"><div class="big"><?= count($low) ?></div>Items at or below reorder level</div></div>
  <?php if($mgr): ?><div class="col-sm-4"><div class="panel"><div class="big"><?= peso($today) ?></div>Sales today</div></div><?php endif; ?>
</div>
<div class="panel"><h2 class="h6">Needs restocking</h2>
<?php if(!$low): ?><p class="mb-0 text-secondary">All products are above their reorder level.</p><?php else: ?>
<table class="table table-sm mb-0"><thead><tr><th>Product</th><th>In stock</th><th>Reorder at</th></tr></thead><tbody>
<?php foreach($low as $r): ?><tr><td><?= h($r['name']) ?></td><td><?= (int)$r['quantity'] ?></td><td><?= (int)$r['reorder_level'] ?></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></div>
<?php include 'includes/footer.php'; ?>
