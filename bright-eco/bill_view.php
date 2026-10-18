<?php require 'includes/auth.php'; require_login(); $title='Bill'; $pdo=db();
$s=$pdo->prepare('SELECT b.*,u.name AS cashier FROM bills b JOIN users u ON u.id=b.user_id WHERE b.id=?'); $s->execute([(int)($_GET['id'] ?? 0)]); $b=$s->fetch();
if(!$b){ http_response_code(404); exit('Bill not found.'); }
$i=$pdo->prepare('SELECT bi.*,p.name FROM bill_items bi JOIN products p ON p.id=bi.product_id WHERE bi.bill_id=?'); $i->execute([$b['id']]); $items=$i->fetchAll();
include 'includes/header.php'; ?>
<div class="panel" style="max-width:720px">
<h1 class="h5 mb-0">Bright Eco Solar Solution</h1><p class="text-secondary small">Estacion, Paniqui, Tarlac</p>
<p class="mb-3"><strong>Bill BE-<?= str_pad($b['id'],6,'0',STR_PAD_LEFT) ?></strong><br>Customer: <?= h($b['customer_name']) ?><br>Date: <?= h($b['created_at']) ?><br>Cashier: <?= h($b['cashier']) ?></p>
<table class="table"><thead><tr><th>Item</th><th class="text-end">Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th></tr></thead><tbody>
<?php foreach($items as $r): ?><tr><td><?= h($r['name']) ?></td><td class="text-end"><?= (int)$r['quantity'] ?></td><td class="text-end"><?= peso($r['unit_price']) ?></td><td class="text-end"><?= peso($r['quantity']*$r['unit_price']) ?></td></tr><?php endforeach; ?>
</tbody><tfoot><tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end"><?= peso($b['subtotal']) ?></td></tr>
<tr><td colspan="3" class="text-end">Discount</td><td class="text-end"><?= peso($b['discount']) ?></td></tr>
<tr><td colspan="3" class="text-end"><strong>Total</strong></td><td class="text-end"><strong><?= peso($b['total']) ?></strong></td></tr></tfoot></table>
<button class="btn btn-sun no-print" onclick="print()">Print bill</button> <a class="btn btn-outline-secondary no-print" href="billing.php">Back to billing</a></div>
<?php include 'includes/footer.php'; ?>
