<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Wania';
$_SESSION['user_role'] = 'admin';
header('Location: user/dashboard/index.php');
exit();
?>
