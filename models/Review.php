<?php

class Review {
    private $db;
    private $id;
    private $user_id;
    private $vehicle_id;
    private $rating;
    private $comment;
    private $is_deleted;
    private $created_at;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            // Check if user has rented this vehicle
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM reservations 
                WHERE user_id = ? AND vehicle_id = ? AND status = 'completed'
            ");
            $stmt->execute([$data['user_id'], $data['vehicle_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] === 0) {
                return false; // User hasn't rented this vehicle
            }

            $stmt = $this->db->prepare("
                INSERT INTO reviews (user_id, vehicle_id, rating, comment)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['vehicle_id'],
                $data['rating'],
                $data['comment']
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating review: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username as user_name
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ? AND r.is_deleted = 0
            ");
            $stmt->execute([$id]);
            
            if ($review = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach ($review as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error getting review by ID: " . $e->getMessage());
            return false;
        }
    }

    public function update($data) {
        if (!$this->id) return false;

        try {
            $updates = [];
            $values = [];

            if (isset($data['rating'])) {
                $updates[] = "rating = ?";
                $values[] = $data['rating'];
            }

            if (isset($data['comment'])) {
                $updates[] = "comment = ?";
                $values[] = $data['comment'];
            }

            if (empty($updates)) return true;

            $values[] = $this->id;
            $stmt = $this->db->prepare("
                UPDATE reviews 
                SET " . implode(', ', $updates) . "
                WHERE id = ? AND is_deleted = 0
            ");

            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("Error updating review: " . $e->getMessage());
            return false;
        }
    }

    public function delete() {
        if (!$this->id) return false;

        try {
            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE reviews 
                SET is_deleted = 1 
                WHERE id = ?
            ");
            return $stmt->execute([$this->id]);
        } catch (PDOException $e) {
            error_log("Error deleting review: " . $e->getMessage());
            return false;
        }
    }

    public static function getVehicleReviews($db, $vehicle_id) {
        try {
            $stmt = $db->prepare("
                SELECT r.*, u.username as user_name
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.vehicle_id = ? AND r.is_deleted = 0
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$vehicle_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting vehicle reviews: " . $e->getMessage());
            return [];
        }
    }

    public static function getUserReviews($db, $user_id) {
        try {
            $stmt = $db->prepare("
                SELECT r.*, 
                       CONCAT(v.brand, ' ', v.model) as vehicle_name
                FROM reviews r
                JOIN vehicles v ON r.vehicle_id = v.id
                WHERE r.user_id = ? AND r.is_deleted = 0
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user reviews: " . $e->getMessage());
            return [];
        }
    }

    public static function getAverageRating($db, $vehicle_id) {
        try {
            $stmt = $db->prepare("
                SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
                FROM reviews
                WHERE vehicle_id = ? AND is_deleted = 0
            ");
            $stmt->execute([$vehicle_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting average rating: " . $e->getMessage());
            return ['avg_rating' => 0, 'total_reviews' => 0];
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getVehicleId() { return $this->vehicle_id; }
    public function getRating() { return $this->rating; }
    public function getComment() { return $this->comment; }
    public function getIsDeleted() { return $this->is_deleted; }
    public function getCreatedAt() { return $this->created_at; }
}
