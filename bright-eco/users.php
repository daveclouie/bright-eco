<?php require 'includes/auth.php'; require_role(['admin']); $title='Users'; $pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();
  if(isset($_POST['toggle'])){
    $id=(int)$_POST['toggle'];
    if($id!==(int)user()['id']){ $pdo->prepare('UPDATE users SET status=1-status WHERE id=?')->execute([$id]); log_action("Toggled user #$id"); flash('User updated.'); }
  } else {
    $n=trim($_POST['name']); $un=trim($_POST['username']); $pw=$_POST['password']; $role=$_POST['role'];
    if($n===''||$un===''||strlen($pw)<8||!in_array($role,['admin','owner','staff'],true)) flash('Fill all fields. Password needs at least 8 characters.');
    else try{
      $pdo->prepare('INSERT INTO users(name,username,password_hash,role) VALUES(?,?,?,?)')->execute([$n,$un,password_hash($pw,PASSWORD_DEFAULT),$role]);
      log_action("Created user $un ($role)"); flash('User added.');
    }catch(PDOException $e){ flash('That username is already taken.'); }
  }
  header('Location: users.php'); exit;
}
$users=$pdo->query('SELECT * FROM users ORDER BY name')->fetchAll();
include 'includes/header.php'; ?>
<h1 class="h4 mb-3">Users</h1>
<div class="panel mb-4"><h2 class="h6">Add user</h2><form method="post" class="row g-2"><?= csrf_field() ?>
<div class="col-md-3"><input class="form-control" name="name" placeholder="Full name" required></div>
<div class="col-md-2"><input class="form-control" name="username" placeholder="Username" required></div>
<div class="col-md-3"><input class="form-control" type="password" name="password" placeholder="Password (8+ characters)" required></div>
<div class="col-md-2"><select class="form-select" name="role"><option value="staff">Staff</option><option value="owner">Owner</option><option value="admin">Administrator</option></select></div>
<div class="col-md-2"><button class="btn btn-sun w-100">Add user</button></div></form></div>
<div class="panel"><table class="table align-middle mb-0"><thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($users as $u): ?><tr><td><?= h($u['name']) ?></td><td><?= h($u['username']) ?></td><td><?= h($u['role']) ?></td><td><?= $u['status']?'Active':'Deactivated' ?></td>
<td class="text-end"><?php if($u['id']!=user()['id']): ?><form method="post"><?= csrf_field() ?><button name="toggle" value="<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary"><?= $u['status']?'Deactivate':'Activate' ?></button></form><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php include 'includes/footer.php'; ?>
