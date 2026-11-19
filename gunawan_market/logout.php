<?php
require_once __DIR__ . '/includes/csrf.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}
verify_csrf();
unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['role'], $_SESSION['last_order_code']);
session_regenerate_id(true);
header('Location: index.php');
exit;
