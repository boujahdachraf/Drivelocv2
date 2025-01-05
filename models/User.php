<?php

class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $role;
    private $created_at;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $email, $password, $role) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (password_verify($password, $user['password'])) {
                    $this->id = $user['id'];
                    $this->username = $user['username'];
                    $this->email = $user['email'];
                    $this->role = $user['role'];
                    $this->created_at = $user['created_at'];
                    return true;
                }
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            return false;
        }
    }

    public function countUsers() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Error getting users: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->id = $user['id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                $this->role = $user['role'];
                $this->created_at = $user['created_at'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return false;
        }
    }

    public function update($data) {
        if (!$this->id) return false;

        try {
            $updates = [];
            $values = [];

            foreach ($data as $key => $value) {
                if (in_array($key, ['username', 'email'])) {
                    $updates[] = "$key = ?";
                    $values[] = $value;
                }
            }

            if (empty($updates)) return true;

            $values[] = $this->id;
            $stmt = $this->db->prepare("
                UPDATE users 
                SET " . implode(', ', $updates) . "
                WHERE id = ?
            ");

            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword($currentPassword, $newPassword) {
        if (!$this->id) return false;

        try {
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$this->id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                return $stmt->execute([$hashedPassword, $this->id]);
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->created_at; }
    public function isAdmin() { return $this->role === 'admin'; }
}
