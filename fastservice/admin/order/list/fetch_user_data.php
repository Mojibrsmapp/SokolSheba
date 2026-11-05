<?php
include('../../includes/configuration.php'); 

// Query to fetch total user count and sum of balance
$query = "SELECT COUNT(*) AS total_users, SUM(balance) AS total_balance FROM users";
$result = mysqli_query($conn, $query);

if ($result) {
    $data = mysqli_fetch_assoc($result);
    echo json_encode([
        'totalUsers' => $data['total_users'],
        'totalBalance' => $data['total_balance']
    ]);
} else {
    echo json_encode([
        'totalUsers' => 0,
        'totalBalance' => 0
    ]);
}

mysqli_close($conn);
?>
