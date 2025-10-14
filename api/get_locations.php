<?php
require_once __DIR__ . "/../model/config/db_connect.php";
require_once __DIR__ . "/../model/dao/locations.php";
$location = new Location($conn);
$locations = $location->getLocations();
echo json_encode($locations);
?>