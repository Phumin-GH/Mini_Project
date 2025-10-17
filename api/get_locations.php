<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../model/config/db_connect.php";
require_once __DIR__ . "/../model/dao/locations.php";
$locationHandler = new Location($conn);
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'edit') {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $result = $locationHandler->getLocationById($id);
            $Getlocation = [];
            if ($result) {
                $Getlocation = $result;
            } else {
                $Getlocation = [];
            }
            echo json_encode($Getlocation);
            exit();
        }
    }
    if ($_GET['action'] === 'search') {
        if (isset($_GET['search']) && isset($_GET['category'])) {
            $category = $_GET['category'] ?? '';
            $search = $_GET['search'] ?? '';
            $result = $locationHandler->searchLocation($search, $category);
            echo json_encode($result);
        }
    }
} else {
    $locations = $locationHandler->getLocations();
    echo json_encode($locations);
    exit();
}