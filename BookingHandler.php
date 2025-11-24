<?php
/**
 * BookingHandler.php
 * Handles bookings with auto-calculated return dates
 */

class BookingHandler {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    /**
     * Create booking with auto-calculated return date
     * @param array $booking_data
     * @return array
     */
    public function createBooking($booking_data) {
        try {
            // Get destination and suggested duration
            $query = "SELECT suggested_duration_days, name FROM destinations WHERE destination_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $booking_data['destination_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return ['success' => false, 'message' => 'Invalid destination'];
            }

            $destination = $result->fetch_assoc();
            $duration_days = $destination['suggested_duration_days'];

            // Calculate return date
            $travel_date_obj = new DateTime($booking_data['travel_date']);
            $travel_date_obj->add(new DateInterval("P{$duration_days}D"));
            $calculated_return_date = $travel_date_obj->format('Y-m-d');

            // Use provided return_date if exists, otherwise use calculated
            $return_date = (!empty($booking_data['return_date'])) 
                          ? $booking_data['return_date'] 
                          : $calculated_return_date;

            // Insert booking
            $query = "INSERT INTO bookings 
                     (user_id, destination_id, booking_date, travel_date, return_date, 
                      guests, total_amount, status, payment_status, payment_method, special_requests) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);
            $booking_date = date('Y-m-d');
            $status = 'pending';
            $payment_status = 'pending';

            $stmt->bind_param(
                "iisssidssss",
                $booking_data['user_id'],
                $booking_data['destination_id'],
                $booking_date,
                $booking_data['travel_date'],
                $return_date,
                $booking_data['guests'],
                $booking_data['total_amount'],
                $status,
                $payment_status,
                $booking_data['payment_method'] ?? 'upi',
                $booking_data['special_requests'] ?? ''
            );

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'booking_id' => $this->conn->insert_id,
                    'message' => 'Booking created successfully',
                    'auto_calculated' => [
                        'destination' => $destination['name'],
                        'duration_days' => $duration_days,
                        'return_date' => $return_date
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Failed: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>
