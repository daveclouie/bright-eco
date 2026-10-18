<?php require 'includes/auth.php';
if(user()){ header('Location: index.php'); exit; }
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  $s=db()->prepare('SELECT * FROM users WHERE username=? AND status=1'); $s->execute([trim($_POST['username'] ?? '')]);
  $u=$s->fetch();
  if($u && password_verify($_POST['password'] ?? '', $u['password_hash'])){
    session_regenerate_id(true);
    $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role']];
    log_action('Logged in'); header('Location: index.php'); exit;
  }
  $err='Wrong username or password.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Log in - Bright Eco Solar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head>
<body class="d-flex align-items-center" style="min-height:100vh"><div class="container" style="max-width:380px">
<div class="panel"><h1 class="h4 mb-1">Bright Eco Solar</h1><p class="text-secondary">Inventory and billing system</p>
<?php if($err): ?><div class="alert alert-danger py-2"><?= h($err) ?></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label class="form-label">Username</label><input class="form-control mb-3" name="username" required autofocus>
<label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required>
<button class="btn btn-sun w-100">Log in</button></form></div></div></body></html>
