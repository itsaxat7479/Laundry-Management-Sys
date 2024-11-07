<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: user_login.php');
    exit();
}

// Fetch user's requests
$user_id = $_SESSION['user_id'];
$sql_requests = "
    SELECT * FROM requests
    WHERE user_id = ?
";
$stmt_requests = $conn->prepare($sql_requests);
$stmt_requests->bind_param("i", $user_id);
$stmt_requests->execute();
$result_requests = $stmt_requests->get_result();

// Add request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_request'])) {
    $request_date = $_POST['request_date'];
    $completion_date = null;
    $completion_time = null;
    $status = 'Pending';

    $sql_insert = "INSERT INTO requests (user_id, request_date, completion_date, completion_time, status) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sssss", $user_id, $request_date, $completion_date, $completion_time, $status);

    if ($stmt_insert->execute()) {
        // Redirect to the same page to avoid resubmission on refresh
        header('Location: dashboard.php');
        exit();
    } else {
        $insert_message = "Error adding request. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Laundry Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #4CAF50;
            padding: 15px;
            text-align: center;
            color: white;
            font-size: 24px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-size: 16px;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            color: red;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="navbar">
        User Dashboard - Laundry Management System
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h1>Request New Laundry</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="request_date">Request Date:</label>
                <input type="date" id="request_date" name="request_date" required>
            </div>
            <div class="form-group">
                <input type="submit" name="add_request" value="Add Request">
            </div>
        </form>

        <h1>Your Requests</h1>

        <?php if ($result_requests->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Request ID</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Completion Date</th>
                    <th>Completion Time</th>
                </tr>
                <?php while ($row = $result_requests->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['completion_date'] ? $row['completion_date'] : 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['completion_time'] ? $row['completion_time'] : 'N/A'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No requests found.</p>
        <?php endif; ?>

        <?php if (isset($insert_message)): ?>
            <div class="message"><?php echo htmlspecialchars($insert_message); ?></div>
        <?php endif; ?>
    </div>

</body>
</html>
