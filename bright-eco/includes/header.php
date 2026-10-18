<?php
$u = user(); $page = basename($_SERVER['SCRIPT_NAME']);
function nav($f,$l,$p){ return '<a class="nav-link'.($p===$f?' active':'').'" href="'.$f.'">'.$l.'</a>'; }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($title ?? 'Bright Eco') ?> - Bright Eco Solar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet"></head><body>
<nav class="topbar"><div class="container-xl d-flex flex-wrap align-items-center gap-3 py-2">
  <strong class="brand">Bright Eco Solar</strong>
  <div class="nav flex-grow-1">
    <?= nav('index.php','Dashboard',$page) ?><?= nav('inventory.php','Inventory',$page) ?><?= nav('billing.php','Billing',$page) ?>
    <?php if($u['role']==='admin') echo nav('users.php','Users',$page); ?>
  </div>
  <span class="small"><?= h($u['name']) ?> (<?= h($u['role']) ?>) &nbsp; <a href="logout.php">Log out</a></span>
</div></nav>
<main class="container-xl py-4">
<?php if($m = flash()): ?><div class="alert alert-success py-2"><?= h($m) ?></div><?php endif; ?>
