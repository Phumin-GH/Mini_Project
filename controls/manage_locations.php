<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . "/../model/dao/locations.php";
require_once __DIR__ . "/../model/config/db_connect.php";
$locationHandler = new Location($conn);
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = $_POST['locationName'] ?? '';
        $lat = $_POST['latitude'] ?? '';
        $lng = $_POST['longitude'] ?? '';
        $dst = $_POST['description'] ?? '';
        $email = $_SESSION['email'];
        $category = $_POST['category'] ?? '';
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ไม่พบข้อมูลผู้ใช้ในระบบ (กรุณาล็อกอิน)']);
            exit();
        }
        $result = $locationHandler->createLocation($name, $lat, $lng, $dst, $email, $category);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'เพิ่มหมุดสำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
        exit();
    }
}
