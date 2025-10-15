<?php
class User
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function login_users($username, $password)
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['user_password'])) {
            $_SESSION['email'] = $user['email'];
            return true;
        } else {
            return "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
        }
    }
    public function register_users($username, $password, $role, $email, $confirm_password)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ? OR username = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email, $username]);
        if ($stmt->fetchColumn() > 0) {
            return "อีเมลนี้ถูกใช้งานแล้ว";
        }
        if ($password !== $confirm_password) {
            return "รหัสผ่านไม่ตรงกัน";
        }
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username,email,user_role,user_password) VALUES (?,?,?,?)";
        $stmt = $this->conn->prepare($sql);
        try {
            $stmt->execute([$username, $email, $role, $hashPassword]);
            $lastId = $this->conn->lastInsertId();
            $prefix = 'lct-';
            $pad_id = str_pad($lastId, 6, '0', STR_PAD_LEFT);
            $format_id = $prefix . $pad_id;
            $sql = "UPDATE  users SET location_id = ? ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$format_id]);
            return true;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
}
