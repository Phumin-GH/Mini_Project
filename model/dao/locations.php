<?php
class Location{
    private $conn;
    public function __construct($conn){
        $this->conn = $conn;
    }
    public function getLocations(){
        $sql = "SELECT * FROM locations";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getLocationById($id){
        $sql = "SELECT * FROM locations WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function createLocation($name, $description, $image){
        $sql = "INSERT INTO locations (name, description, image) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name, $description, $image]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateLocation($id, $name, $description, $image){
        $sql = "UPDATE locations SET name = ?, description = ?, image = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$name, $description, $image, $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function searchLocation($searchTerm,$category){
        switch($searchTerm)
         case

    }
    public function deleteLocation($id){
        $sql = "DELETE FROM locations WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>