<?php
require __DIR__ . '/includes/bootstrap.php';
if (current_user()) redirect((current_user()['role'] ?? '') === 'ADMIN' ? 'admin/index.php' : 'employee/index.php');
redirect('login.php');
