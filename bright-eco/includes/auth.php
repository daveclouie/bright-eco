<?php
session_start();
require_once __DIR__.'/../config/db.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso($n){ return '₱'.number_format((float)$n, 2); }
function user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!user()){ header('Location: login.php'); exit; } }
function require_role(array $roles){ require_login(); if(!in_array(user()['role'],$roles,true)){ http_response_code(403); exit('Access denied.'); } }
function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.csrf().'">'; }
function csrf_check(){ if(!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')){ http_response_code(400); exit('Invalid request. Go back and try again.'); } }
function log_action($a){ db()->prepare('INSERT INTO activity_log(user_id,action) VALUES(?,?)')->execute([user()['id'] ?? null, $a]); }
function flash($m=null){ if($m!==null){ $_SESSION['flash']=$m; return; } $f=$_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }
