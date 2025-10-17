<?php
// var_dump($_FILES);
// die();
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
            echo json_encode(['success' => false, 'message' => $result . JSON_UNESCAPED_UNICODE]);
        }
        exit();
    }
    if ($_POST['action'] === 'edit') {
        $name = $_POST['EditlocationName'] ?? '';
        $lat = $_POST['Editlatitude'] ?? '';
        $lng = $_POST['Editlongitude'] ?? '';
        $dst = $_POST['Editdescription'] ?? '';
        $location_id = $_POST['EditlocationId'] ?? '';
        $category = $_POST['EditCategory'] ?? '';
        if (empty($location_id)) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ไม่พบข้อมูลสถานที่ในระบบ']);
            exit();
        }
        $result = $locationHandler->updateLocation($location_id, $name, $lat, $lng, $dst, $category);
        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'เพิ่มหมุดสำเร็จ']);
        } else {
            echo json_encode(['success' => false, 'message' => $result . JSON_UNESCAPED_UNICODE]);
        }
        exit();
    }
    if ($_POST['action'] === 'delete') {
        $id = $_POST['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID was not provided.']);
            exit();
        }
        $result = $locationHandler->deleteLocation($id);
        if ($result === true) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $result . JSON_UNESCAPED_UNICODE]);
        }
        exit();
    }
}