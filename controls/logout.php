<?php
header('Content-Type: application/json');
session_start();
session_destroy();
unset($_SESSION['email']);
echo json_encode(['success' => true, 'message' => 'กำลังออกจากระบบ']);
