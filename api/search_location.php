<?php
require_once __DIR__ . "/../Mini_Project/model/config/db_connect.php";
require_once __DIR__ . "/../Mini_Project/model/dao/locations.php";
$search_location = new Location($conn);
if(isset($_GET['searchTerm'])|| isset($_GET['category'])){
    try{
        $searchTerm = $_GET['searchTerm']?? '';
        $category = $_GET['category'] ?? '';
        $locations = $search_location->searchLocation($searchTerm,$category);
        echo json_encode($locations,JSON_UNESCAPED_UNICODE);
        exit();

    }catch(PDOException $e){
        http_response_code(500);
    echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
    exit();
        
    }
}
?>