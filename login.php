<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect((current_user()['role'] ?? '') === 'ADMIN' ? 'admin/index.php' : 'employee/index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter your work email and password.';
    } else {
        try {
            $stmt = db()->prepare('SELECT id,name,email,password_hash,role,department,is_active FROM users WHERE email=? LIMIT 1');
            $stmt->execute([$email]); $user = $stmt->fetch();
            if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                unset($user['password_hash']);
                $_SESSION['user'] = $user;
                audit('login','user',(int)$user['id'],'Successful login');
                redirect($user['role'] === 'ADMIN' ? 'admin/index.php' : 'employee/index.php');
            }
            usleep(250000);
            $error = 'The email or password is incorrect, or the account is inactive.';
        } catch (Throwable $ex) {
            $error = 'The application could not connect to the database. Check config.php and import schema.sql.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sign in · Cloudsoftera Feedback</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="<?= e(url('assets/app.css')) ?>" rel="stylesheet"></head><body>
<div class="login-page"><section class="login-brand-side"><a class="brand" href="<?= e(url()) ?>"><span class="brand-logo">C</span><span><strong>cloudsoftera</strong><small>INTERNAL FEEDBACK</small></span></a><div><div class="eyebrow" style="color:#aab5ff">PEOPLE FIRST · ALWAYS</div><h1>Better work starts with being heard.</h1><p>A thoughtful space for ideas, honest feedback and the small improvements that make a real difference.</p></div><div style="font-size:10px;color:#8995a8">© <?= date('Y') ?> Cloudsoftera · Internal use</div></section>
<section class="login-form-side"><div class="login-box"><div class="brand" style="padding:0 0 26px;color:#182230"><span class="brand-logo">C</span><span><strong style="color:#182230">cloudsoftera</strong><small style="color:#8b95a5">FEEDBACK HUB</small></span></div><h2>Welcome back</h2><p>Sign in with your company account to continue.</p><?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" autocomplete="on"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><label class="form-label" for="email">Work email</label><input class="form-control" id="email" name="email" type="email" required autocomplete="username" placeholder="you@cloudsoftera.com" value="<?= e($_POST['email'] ?? '') ?>"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password"><button class="btn btn-primary w-100 mt-4 py-3" type="submit">Sign in securely&nbsp; ↗</button></form><div class="login-footer">Need access? Contact your Cloudsoftera administrator.</div></div></section></div></body></html>
