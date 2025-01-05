<?php

class Vehicle {
    private $db;
    private $id;
    private $brand;
    private $model;
    private $description;
    private $price_per_day;
    private $category_id;
    private $status;
    private $image_url;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO vehicles (brand, model, description, price_per_day, category_id, status, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['brand'],
                $data['model'],
                $data['description'],
                $data['price_per_day'],
                $data['category_id'],
                'available',
                $data['image_url'] ?? null
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function update($data) {
        if (!$this->id) return false;

        try {
            $updates = [];
            $values = [];

            foreach (['brand', 'model', 'description', 'price_per_day', 'category_id', 'status', 'image_url'] as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($updates)) return false;

            $values[] = $this->id;
            $sql = "UPDATE vehicles SET " . implode(", ", $updates) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error updating vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        if (!$this->id) return false;

        try {
            // First check if vehicle has any reservations
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM reservations 
                WHERE vehicle_id = ? AND status IN ('pending', 'confirmed')
            ");
            $stmt->execute([$this->id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                return false; // Can't delete vehicle with active reservations
            }

            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = ?");
            return $stmt->execute([$this->id]);
        } catch (PDOException $e) {
            error_log("Error deleting vehicle: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT v.*, c.name as category_name 
                FROM vehicles v
                LEFT JOIN categories c ON v.category_id = c.id
                WHERE v.id = ?
            ");
            $stmt->execute([$id]);
            
            if ($vehicle = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach ($vehicle as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error getting vehicle by ID: " . $e->getMessage());
            return false;
        }
    }

    public static function getAll($db, $filters = []) {
        try {
            $where = [];
            $values = [];
            
            if (!empty($filters['category_id'])) {
                $where[] = "v.category_id = ?";
                $values[] = $filters['category_id'];
            }
            
            if (!empty($filters['status'])) {
                $where[] = "v.status = ?";
                $values[] = $filters['status'];
            }

            if (!empty($filters['search'])) {
                $where[] = "(v.brand LIKE ? OR v.model LIKE ? OR v.description LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $values = array_merge($values, [$searchTerm, $searchTerm, $searchTerm]);
            }

            $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

            $stmt = $db->prepare("
                SELECT v.*, c.name as category_name 
                FROM vehicles v
                LEFT JOIN categories c ON v.category_id = c.id
                $whereClause
                ORDER BY v.brand, v.model
            ");

            $stmt->execute($values);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting vehicles: " . $e->getMessage());
            return [];
        }
    }

    public function isAvailable($start_date, $end_date) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM reservations
                WHERE vehicle_id = ?
                AND status IN ('pending', 'confirmed')
                AND (
                    (pickup_date BETWEEN ? AND ?) OR
                    (return_date BETWEEN ? AND ?) OR
                    (pickup_date <= ? AND return_date >= ?)
                )
            ");

            $stmt->execute([
                $this->id,
                $start_date, $end_date,
                $start_date, $end_date,
                $start_date, $end_date
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] === 0 && $this->status === 'available';
        } catch (PDOException $e) {
            error_log("Error checking vehicle availability: " . $e->getMessage());
            return false;
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getBrand() { return $this->brand; }
    public function getModel() { return $this->model; }
    public function getDescription() { return $this->description; }
    public function getPricePerDay() { return $this->price_per_day; }
    public function getCategoryId() { return $this->category_id; }
    public function getStatus() { return $this->status; }
    public function getImageUrl() { return $this->image_url; }
}
