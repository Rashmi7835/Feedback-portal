<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) audit('logout','user',(int)current_user()['id'],'Signed out');
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', (bool)$p['secure'], (bool)$p['httponly']);
}
session_destroy();
redirect('login.php');
