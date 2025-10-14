<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . "/../model/dao/user.php";
require_once __DIR__ . "/../model/config/db_connect.php";
$userHandler = new User($conn);
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'register') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm-password'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';
        $result = $userHandler->register_users($username, $password, $role, $email, $confirm_password);
        if ($result === true) {
            $result = $userHandler->login_users($username, $password);
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'เข้าสู่ระบบเรียบร้อย']);
            } else {
                echo json_encode(['success' => false, 'message' => $result]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
        exit();
    } elseif ($_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $result = $userHandler->login_users($username, $password);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'เข้าสู่ระบบเรียบร้อย']);
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
        exit();
    }
}