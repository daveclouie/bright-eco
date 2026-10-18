<?php require 'includes/auth.php'; require_login(); $title='Inventory'; $pdo=db();
$can=in_array(user()['role'],['admin','owner'],true);
if($_SERVER['REQUEST_METHOD']==='POST'){
  require_role(['admin','owner']); csrf_check();
  if(isset($_POST['adjust'])){
    $id=(int)$_POST['id']; $d=(int)$_POST['delta'];
    $pdo->prepare('UPDATE products SET quantity=GREATEST(0,quantity+?) WHERE id=?')->execute([$d,$id]);
    log_action("Stock adjusted by $d (product #$id)"); flash('Stock updated.');
  } else {
    $name=trim($_POST['name']); $cat=trim($_POST['category']); $id=(int)($_POST['id'] ?? 0);
    if($name===''||$cat==='') flash('Product name and category are required.');
    else {
      $s=$pdo->prepare('SELECT id FROM categories WHERE name=?'); $s->execute([$cat]); $cid=$s->fetchColumn();
      if(!$cid){ $pdo->prepare('INSERT INTO categories(name) VALUES(?)')->execute([$cat]); $cid=$pdo->lastInsertId(); }
      $specs=trim($_POST['specs']); $price=max(0,(float)$_POST['price']); $rl=max(0,(int)$_POST['reorder_level']);
      if($id){ $pdo->prepare('UPDATE products SET name=?,category_id=?,specs=?,price=?,reorder_level=? WHERE id=?')->execute([$name,$cid,$specs,$price,$rl,$id]); log_action("Edited product #$id"); }
      else { $pdo->prepare('INSERT INTO products(name,category_id,specs,price,quantity,reorder_level) VALUES(?,?,?,?,?,?)')->execute([$name,$cid,$specs,$price,max(0,(int)$_POST['quantity']),$rl]); log_action("Added product $name"); }
      flash('Product saved.');
    }
  }
  header('Location: inventory.php'); exit;
}
$q='%'.trim($_GET['q'] ?? '').'%';
$s=$pdo->prepare('SELECT p.*,c.name AS cat FROM products p JOIN categories c ON c.id=p.category_id WHERE p.status=1 AND (p.name LIKE ? OR p.specs LIKE ? OR c.name LIKE ?) ORDER BY p.name');
$s->execute([$q,$q,$q]); $rows=$s->fetchAll();
$cats=$pdo->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
$e=null; if($can && isset($_GET['edit'])){ $x=$pdo->prepare('SELECT p.*,c.name AS cat FROM products p JOIN categories c ON c.id=p.category_id WHERE p.id=?'); $x->execute([(int)$_GET['edit']]); $e=$x->fetch(); }
include 'includes/header.php'; ?>
<h1 class="h4 mb-3">Inventory</h1>
<?php if($can): ?><div class="panel mb-4"><h2 class="h6"><?= $e?'Edit product':'Add product' ?></h2>
<form method="post" class="row g-2"><?= csrf_field() ?><?php if($e): ?><input type="hidden" name="id" value="<?= $e['id'] ?>"><?php endif; ?>
<div class="col-md-4"><input class="form-control" name="name" placeholder="Product name" value="<?= h($e['name'] ?? '') ?>" required></div>
<div class="col-md-3"><input class="form-control" name="category" list="cats" placeholder="Category" value="<?= h($e['cat'] ?? '') ?>" required>
<datalist id="cats"><?php foreach($cats as $c) echo '<option value="'.h($c).'">'; ?></datalist></div>
<div class="col-md-5"><input class="form-control" name="specs" placeholder="Specs (e.g. 550W monocrystalline)" value="<?= h($e['specs'] ?? '') ?>"></div>
<div class="col-md-3"><input class="form-control" type="number" step="0.01" min="0" name="price" placeholder="Price (PHP)" value="<?= h($e['price'] ?? '') ?>" required></div>
<?php if(!$e): ?><div class="col-md-3"><input class="form-control" type="number" min="0" name="quantity" placeholder="Starting stock" value="0" required></div><?php endif; ?>
<div class="col-md-3"><input class="form-control" type="number" min="0" name="reorder_level" placeholder="Reorder level" value="<?= h($e['reorder_level'] ?? 5) ?>" required></div>
<div class="col-md-3"><button class="btn btn-sun w-100"><?= $e?'Save changes':'Add product' ?></button></div></form>
<?php if($e): ?><p class="small text-secondary mt-2 mb-0">To change stock, use the +/- box in the product list.</p><?php endif; ?></div><?php endif; ?>
<form class="mb-3 d-flex gap-2"><input class="form-control" name="q" placeholder="Search products" value="<?= h($_GET['q'] ?? '') ?>"><button class="btn btn-outline-secondary">Search</button></form>
<div class="panel table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Product</th><th>Category</th><th>Specs</th><th class="text-end">Price</th><th class="text-end">Stock</th><?php if($can) echo '<th></th>'; ?></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="6" class="text-secondary">No products found.</td></tr><?php endif; ?>
<?php foreach($rows as $r): ?><tr class="<?= $r['quantity']<=$r['reorder_level']?'low':'' ?>"><td><?= h($r['name']) ?></td><td><?= h($r['cat']) ?></td><td><?= h($r['specs']) ?></td><td class="text-end"><?= peso($r['price']) ?></td><td class="text-end"><?= (int)$r['quantity'] ?></td>
<?php if($can): ?><td class="text-end text-nowrap"><form method="post" class="d-inline-flex gap-1"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><input type="number" name="delta" class="form-control form-control-sm" style="width:80px" placeholder="+/-" required><button name="adjust" value="1" class="btn btn-sm btn-outline-secondary">Adjust</button><a class="btn btn-sm btn-outline-secondary" href="?edit=<?= $r['id'] ?>">Edit</a></form></td><?php endif; ?></tr><?php endforeach; ?>
</tbody></table></div>
<?php include 'includes/footer.php'; ?>
