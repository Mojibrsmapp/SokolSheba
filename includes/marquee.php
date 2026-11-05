<?php
class Marquee {
    private $conn;
    private $id;

    // Constructor to initialize the database connection and id
    public function __construct($conn, $id) {
        $this->conn = $conn;
        $this->id = $id;
    }

    // Method to display the marquee
    public function display() {
        if (isset($_GET['message'])) {
            $this->displayFromUrl($_GET['message']);
        } else {
            $this->displayFromDatabase();
        }
    }

    // Private method to display the marquee from the URL parameter
    private function displayFromUrl($message) {
        echo '<marquee >' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</marquee>';
    }

    // Private method to display the marquee from the database
    private function displayFromDatabase() {
        $sqlData = "SELECT marquee_text FROM marquee WHERE id = ?";
        if ($stmt = $this->conn->prepare($sqlData)) {
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->bind_result($marquee_text);
            if ($stmt->fetch()) {
                echo '<marquee style="border:1px solid #05C3FB; padding:5px;margin:10px;border-radius:3px;">' . htmlspecialchars(base64_decode($marquee_text), ENT_QUOTES, 'UTF-8') . '</marquee>';
            } else {
                echo "No marquee found for ID: " . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8');
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $this->conn->error;
        }
    }
}
?>
