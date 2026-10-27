<?php
require __DIR__ . '/includes/bootstrap.php';
$errors=[];$success='';
try {
    $count=(int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        http_response_code(403);
        exit('Setup is already complete. For security, delete setup.php from the server.');
    }
} catch (Throwable $e) {
    http_response_code(500);
    exit('Database setup failed. Create the database and import schema.sql first, then configure config.php.');
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $name=trim((string)($_POST['name']??''));$email=strtolower(trim((string)($_POST['email']??'')));$password=(string)($_POST['password']??'');$confirm=(string)($_POST['confirm']??'');
    if($name==='')$errors[]='Name is required.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))$errors[]='Enter a valid email.';
    if(strlen($password)<12)$errors[]='Use a password of at least 12 characters.';
    if($password!==$confirm)$errors[]='Passwords do not match.';
    if(!$errors){
        $stmt=db()->prepare("INSERT INTO users(name,email,password_hash,role,is_active) VALUES(?,?,?,'ADMIN',1)");
        $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user']=['id'=>(int)db()->lastInsertId(),'name'=>$name,'email'=>$email,'role'=>'ADMIN','department'=>null,'is_active'=>1];
        audit('initial_admin_created','user',(int)$_SESSION['user']['id'],'Initial setup');
        flash('success','Administrator account created. Setup is complete.');
        redirect('admin/index.php');
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Initial setup · Cloudsoftera</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="<?= e(url('assets/app.css')) ?>" rel="stylesheet"></head><body><div class="login-page"><section class="login-brand-side"><a class="brand" href="<?= e(url()) ?>"><span class="brand-logo">C</span><span><strong>cloudsoftera</strong><small>INTERNAL FEEDBACK</small></span></a><div><div class="eyebrow" style="color:#aab5ff">FIRST-TIME SETUP</div><h1>Set up your feedback workspace.</h1><p>Create the first administrator account. Employee accounts can be added after you sign in.</p></div><div style="font-size:10px;color:#8995a8">Keep this application restricted to authorized staff.</div></section><section class="login-form-side"><div class="login-box"><h2>Create administrator</h2><p>Choose a strong password and use a company email address.</p><?php foreach($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?><form method="post"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><label class="form-label">Full name</label><input class="form-control" name="name" required value="<?= e($_POST['name']??'') ?>"><label class="form-label">Work email</label><input class="form-control" type="email" name="email" required value="<?= e($_POST['email']??'') ?>"><label class="form-label">Password (minimum 12 characters)</label><input class="form-control" type="password" name="password" minlength="12" required autocomplete="new-password"><label class="form-label">Confirm password</label><input class="form-control" type="password" name="confirm" minlength="12" required autocomplete="new-password"><button class="btn btn-primary w-100 mt-4 py-3">Create administrator</button></form><div class="login-footer">Delete setup.php from the server after setup.</div></div></section></div></body></html>
