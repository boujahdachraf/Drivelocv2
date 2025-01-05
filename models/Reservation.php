<?php

class Reservation {
    private $db;
    private $id;
    private $user_id;
    private $vehicle_id;
    private $pickup_date;
    private $return_date;
    private $status;
    private $total_price;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM reservations WHERE id = ?");
        $stmt->execute([$id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reservation) {
            $this->id = $reservation['id'];
            $this->user_id = $reservation['user_id'];
            $this->vehicle_id = $reservation['vehicle_id'];
            $this->pickup_date = $reservation['pickup_date'];
            $this->return_date = $reservation['return_date'];
            $this->status = $reservation['status'];
            $this->total_price = $reservation['total_price'];
            return true;
        }
        return false;
    }

    public static function getAll($db) {
        try {
            $stmt = $db->prepare("
                SELECT r.*, 
                       u.username as user_name, 
                       CONCAT(v.brand, ' ', v.model) as vehicle_name,
                       v.price_per_day
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                JOIN vehicles v ON r.vehicle_id = v.id
                ORDER BY r.pickup_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reservations: " . $e->getMessage());
            return [];
        }
    }

    public function create($data) {
        try {
            // Check if vehicle is available for the dates
            if (!$this->isVehicleAvailable($data['vehicle_id'], $data['pickup_date'], $data['return_date'])) {
                return false;
            }

            // Calculate total price
            $stmt = $this->db->prepare("SELECT price_per_day FROM vehicles WHERE id = ?");
            $stmt->execute([$data['vehicle_id']]);
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $pickup = new DateTime($data['pickup_date']);
            $return = new DateTime($data['return_date']);
            $days = $return->diff($pickup)->days + 1;
            $total_price = $vehicle['price_per_day'] * $days;

            // Create reservation
            $stmt = $this->db->prepare("
                INSERT INTO reservations (user_id, vehicle_id, pickup_date, return_date, status, total_price, created_at)
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())
            ");

            $result = $stmt->execute([
                $data['user_id'],
                $data['vehicle_id'],
                $data['pickup_date'],
                $data['return_date'],
                $total_price
            ]);

            if ($result) {
                $this->id = $this->db->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating reservation: " . $e->getMessage());
            return false;
        }
    }

    public static function getUserReservations($db, $user_id) {
        try {
            $stmt = $db->prepare("
                SELECT r.*, 
                       v.brand, v.model, v.image_url,
                       c.name as category_name
                FROM reservations r
                JOIN vehicles v ON r.vehicle_id = v.id
                JOIN categories c ON v.category_id = c.id
                WHERE r.user_id = ?
                ORDER BY r.pickup_date DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user reservations: " . $e->getMessage());
            return [];
        }
    }

    private function isVehicleAvailable($vehicle_id, $pickup_date, $return_date) {
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
                $vehicle_id,
                $pickup_date,
                $return_date,
                $pickup_date,
                $return_date,
                $pickup_date,
                $return_date
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] == 0;
        } catch (PDOException $e) {
            error_log("Error checking vehicle availability: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($newStatus) {
        if (!$this->id) return false;

        try {
            $stmt = $this->db->prepare("UPDATE reservations SET status = ? WHERE id = ?");
            $result = $stmt->execute([$newStatus, $this->id]);

            if ($result) {
                $vehicleStatus = $newStatus === 'cancelled' ? 'available' : 'reserved';
                $stmt = $this->db->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
                $stmt->execute([$vehicleStatus, $this->vehicle_id]);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error updating reservation status: " . $e->getMessage());
            return false;
        }
    }

    public function cancel($db, $reservation_id) {
        try {
            $stmt = $db->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            return $stmt->execute([$reservation_id]);
        } catch (PDOException $e) {
            error_log("Error cancelling reservation: " . $e->getMessage());
            return false;
        }
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getVehicleId() { return $this->vehicle_id; }
    public function getPickupDate() { return $this->pickup_date; }
    public function getReturnDate() { return $this->return_date; }
    public function getStatus() { return $this->status; }
    public function getTotalPrice() { return $this->total_price; }
}
