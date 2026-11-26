<?php
declare(strict_types=1);

require __DIR__ . '/../includes/init.php';

unset($_SESSION['buyer_id']);
session_regenerate_id(true);
header('Location: login');
exit;
