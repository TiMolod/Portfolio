<?php
session_start();

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

// Если запрос через AJAX
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    echo json_encode(['authenticated' => isAuthenticated()]);
    exit;
}

// Если обычный запрос и пользователь не авторизован
if (!isAuthenticated()) {
    header('Location: auth.html');
    exit;
} 