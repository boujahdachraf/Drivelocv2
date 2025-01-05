<?php

class Category {
    private $db;
    private $id;
    private $name;
    private $description;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($name, $description) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, description)
                VALUES (?, ?)
            ");
            
            $stmt->execute([$name, $description]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }

    public function update($name, $description) {
        if (!$this->id) return false;

        try {
            $stmt = $this->db->prepare("
                UPDATE categories 
                SET name = ?, description = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([$name, $description, $this->id]);
        } catch (PDOException $e) {
            error_log("Error updating category: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        if (!$this->id) return false;

        try {
            // Check if category has vehicles
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM vehicles WHERE category_id = ?");
            $stmt->execute([$this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return false; // Can't delete category with vehicles
            }

            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            return $stmt->execute([$this->id]);
        } catch (PDOException $e) {
            error_log("Error deleting category: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($category = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->id = $category['id'];
                $this->name = $category['name'];
                $this->description = $category['description'];
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error getting category by ID: " . $e->getMessage());
            return false;
        }
    }

    public static function getAll($db) {
        try {
            $stmt = $db->prepare("SELECT * FROM categories ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }

    public function getVehicles() {
        if (!$this->id) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vehicles 
                WHERE category_id = ? 
                ORDER BY brand, model
            ");
            $stmt->execute([$this->id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting category vehicles: " . $e->getMessage());
            return [];
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
}
