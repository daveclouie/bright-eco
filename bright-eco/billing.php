<?php require 'includes/auth.php'; require_login(); $title='Billing'; $pdo=db();
$can=in_array(user()['role'],['admin','owner'],true); $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $cust=trim($_POST['customer_name'] ?? ''); $pids=$_POST['pid'] ?? []; $qts=$_POST['qty'] ?? [];
  $disc=$can ? max(0,(float)($_POST['discount'] ?? 0)) : 0;
  try{
    if($cust==='') throw new Exception('Enter the customer name.');
    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO bills(user_id,customer_name) VALUES(?,?)')->execute([user()['id'],$cust]);
    $bid=$pdo->lastInsertId(); $sub=0; $n=0;
    foreach($pids as $i=>$pid){
      $q=(int)($qts[$i] ?? 0); if($q<1) continue;
      $s=$pdo->prepare('SELECT id,name,price,quantity FROM products WHERE id=? AND status=1 FOR UPDATE'); $s->execute([(int)$pid]); $p=$s->fetch();
      if(!$p) throw new Exception('A selected product was not found.');
      if($p['quantity']<$q) throw new Exception("Not enough stock for {$p['name']}. Only {$p['quantity']} left.");
      $pdo->prepare('INSERT INTO bill_items(bill_id,product_id,quantity,unit_price) VALUES(?,?,?,?)')->execute([$bid,$p['id'],$q,$p['price']]);
      $pdo->prepare('UPDATE products SET quantity=quantity-? WHERE id=?')->execute([$q,$p['id']]);
      $sub+=$q*$p['price']; $n++;
    }
    if(!$n) throw new Exception('Add at least one item.');
    if($disc>$sub) throw new Exception('Discount cannot be more than the subtotal.');
    $pdo->prepare('UPDATE bills SET subtotal=?,discount=?,total=? WHERE id=?')->execute([$sub,$disc,$sub-$disc,$bid]);
    $pdo->commit(); log_action("Created bill #$bid"); flash('Bill created.'); header("Location: bill_view.php?id=$bid"); exit;
  }catch(Exception $e){ if($pdo->inTransaction()) $pdo->rollBack(); $err=$e->getMessage(); }
}
$products=$pdo->query('SELECT id,name,price,quantity FROM products WHERE status=1 AND quantity>0 ORDER BY name')->fetchAll();
$bills=$pdo->query('SELECT b.*,u.name AS cashier FROM bills b JOIN users u ON u.id=b.user_id ORDER BY b.id DESC LIMIT 15')->fetchAll();
include 'includes/header.php'; ?>
<h1 class="h4 mb-3">Billing</h1>
<?php if($err): ?><div class="alert alert-danger py-2"><?= h($err) ?></div><?php endif; ?>
<div class="panel mb-4"><h2 class="h6">New bill</h2>
<form method="post" id="f"><?= csrf_field() ?>
<input class="form-control mb-3" name="customer_name" placeholder="Customer name" style="max-width:380px" required>
<table class="table align-middle"><thead><tr><th>Product</th><th style="width:110px">Qty</th><th class="text-end">Amount</th><th></th></tr></thead><tbody id="rows"></tbody></table>
<button type="button" class="btn btn-outline-secondary btn-sm mb-3" onclick="addRow()">Add item</button>
<div class="d-flex flex-wrap gap-4 align-items-center">
<?php if($can): ?><div><label class="form-label small mb-0">Discount (PHP)</label><input class="form-control" type="number" step="0.01" min="0" name="discount" id="disc" value="0" style="width:150px"></div><?php else: ?><input type="hidden" id="disc" value="0"><?php endif; ?>
<div class="fs-5">Total: <strong id="total">₱0.00</strong></div><button class="btn btn-sun ms-auto">Save bill</button></div></form></div>
<div class="panel table-responsive"><h2 class="h6">Recent bills</h2><table class="table mb-0"><thead><tr><th>Bill</th><th>Customer</th><th>Cashier</th><th>Date</th><th class="text-end">Total</th></tr></thead><tbody>
<?php if(!$bills): ?><tr><td colspan="5" class="text-secondary">No bills yet.</td></tr><?php endif; ?>
<?php foreach($bills as $b): ?><tr><td><a href="bill_view.php?id=<?= $b['id'] ?>">BE-<?= str_pad($b['id'],6,'0',STR_PAD_LEFT) ?></a></td><td><?= h($b['customer_name']) ?></td><td><?= h($b['cashier']) ?></td><td><?= h($b['created_at']) ?></td><td class="text-end"><?= peso($b['total']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<script>
const P=<?= json_encode($products, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
const esc=s=>String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmt=n=>'₱'+n.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
function addRow(){
  const tr=document.createElement('tr');
  tr.innerHTML='<td><select name="pid[]" class="form-select" required><option value="">Choose a product</option>'+P.map(p=>'<option value="'+p.id+'">'+esc(p.name)+' ('+p.quantity+' in stock)</option>').join('')+'</select></td><td><input name="qty[]" type="number" min="1" value="1" class="form-control" required></td><td class="text-end amt">₱0.00</td><td><button type="button" class="btn btn-sm btn-outline-danger">Remove</button></td>';
  tr.querySelector('button').onclick=()=>{tr.remove();calc()};
  tr.querySelectorAll('select,input').forEach(el=>el.oninput=calc);
  document.getElementById('rows').appendChild(tr); calc();
}
function calc(){
  let sub=0;
  document.querySelectorAll('#rows tr').forEach(tr=>{
    const p=P.find(x=>x.id==tr.querySelector('select').value), q=+tr.querySelector('input').value||0;
    const a=p?p.price*q:0; sub+=a; tr.querySelector('.amt').textContent=fmt(a);
  });
  document.getElementById('total').textContent=fmt(Math.max(0,sub-(+document.getElementById('disc').value||0)));
}
document.getElementById('disc').oninput=calc; addRow();
</script>
<?php include 'includes/footer.php'; ?>
