<?php
class Location
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getLocations()
    {
        $sql = "SELECT l.lat,l.lng,l.location_name,l.category,l.location_description,l.location_id,l.id,u.email,u.username,u.user_role,i.img_name FROM locations l
        INNER JOIN users u ON l.user_id = u.user_id
        LEFT JOIN images_lct i ON l.id = i.lct_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getLocationById($id)
    {
        $sql = "SELECT lat,lng,location_name,category,location_description,location_id,id FROM locations WHERE location_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result =  $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    public function createLocation($name, $lat, $lng, $dst, $email, $category)
    {


        $user_id = $this->get_userId($email);
        $sql = "INSERT INTO locations (location_name, location_description,user_id,lat,lng,category) VALUES (?,?,?,?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$name, $dst, $user_id, $lat, $lng, $category]);
            $lastId = $this->conn->lastInsertId();
            $result = $this->prefix_location_id($lastId);
            $upload = $this->upload_images($lastId);
            if ($result === true && $upload === true) {
                return true;
            } else {
                return $result;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    private function upload_images($lastId)
    {
        $check_img = "SELECT COUNT(*) FROM images_lct WHERE lct_id = ?";
        $stmt = $this->conn->prepare($check_img);
        $stmt->execute([$lastId]);
        $result = $stmt->fetchColumn();
        if (isset($_FILES['EditlocationImages'])) {
            if ($result > 0) {
                $del_img = "DELETE FROM images_lct WHERE lct_id = ?";
                $stmt = $this->conn->prepare($del_img);
                $stmt->execute([$lastId]);
            }
            $uploadDir = __DIR__ . "/../../images/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            foreach ($_FILES['EditlocationImages']['tmp_name'] as $key => $tmpname) {
                $fileName = $_FILES['EditlocationImages']['name'][$key];
                $uniqueName = uniqid('img_') . "_" . basename($fileName);
                $targetPath = $uploadDir . $uniqueName;
                if (move_uploaded_file($tmpname, $targetPath)) {
                    $stmt = $this->conn->prepare("INSERT INTO images_lct (lct_id,img_name) VALUES (?,?)");
                    $stmt->execute([$lastId, "images/" . $uniqueName]);
                }
            }
            return true;
        }
        if (isset($_FILES['locationImages'])) {
            $uploadDir = __DIR__ . "/../../images/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            foreach ($_FILES['locationImages']['tmp_name'] as $key => $tmpname) {
                $fileName = $_FILES['locationImages']['name'][$key];
                $uniqueName = uniqid('img_') . "_" . basename($fileName);
                $targetPath = $uploadDir . $uniqueName;
                if (move_uploaded_file($tmpname, $targetPath)) {
                    $stmt = $this->conn->prepare("INSERT INTO images_lct (lct_id,img_name) VALUES (?,?)");
                    $stmt->execute([$lastId, "images/" . $uniqueName]);
                }
            }
            return true;
        }
    }
    private function prefix_location_id($lastId)
    {
        $prefix = 'lct_';
        $pad_id = str_pad($lastId, 6, '0', STR_PAD_LEFT);
        $format = $prefix . $pad_id;
        $updatesql = "UPDATE locations SET location_id = ? WHERE id = ? ";
        $stmt1 = $this->conn->prepare($updatesql);
        try {
            $stmt1->execute([$format, $lastId]);

            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    private function get_userId($email)
    {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $user_id = $stmt->fetchColumn();
        return $user_id;
    }
    public function updateLocation($location_id, $name, $lat, $lng, $dst,  $category)
    {
        $get_lct_id = "SELECT id FROM locations WHERE location_id = ?";
        $stmt = $this->conn->prepare($get_lct_id);
        $stmt->execute([$location_id]);
        $lct_id = $stmt->fetchColumn();
        $this->upload_images($lct_id);
        $sql = "UPDATE locations SET location_name = ?, location_description = ?, lat = ?,lng = ?,category=? WHERE location_id = ?";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$name, $dst, $lat, $lng, $category, $location_id]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    public function searchLocation($searchTerm, $category)
    {
        $search = "SELECT l.lat,l.lng,l.location_name,l.category,l.location_description,l.location_id,l.id,u.email,u.username,u.user_role,i.img_name FROM locations l
        INNER JOIN users u ON l.user_id = u.user_id
        LEFT JOIN images_lct i ON l.id = i.lct_id";
        $params = [];
        $search .= " WHERE category LIKE ? AND location_name LIKE ?";
        $likeCate = "%" . $category . "%";
        $likeLct = "%" . $searchTerm . "%";
        $params[] = $likeCate;
        $params[] = $likeLct;
        $stmt = $this->conn->prepare($search);
        try {
            $stmt->execute($params);
            $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $locations;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    public function deleteLocation($id)
    {
        $sql = "DELETE FROM locations WHERE location_id = ?";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return true;
    }
}