<?php
declare(strict_types=1);
require_once __DIR__.'/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function require_admin(): void {
    if (empty($_SESSION['admin_id'])) { header('Location: ../admin/login.php'); exit; }
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Token inválido.'); }
}
