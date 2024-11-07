<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch user details
$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

// Fetch laundry requests with user names
$sql_requests = "
    SELECT requests.id AS request_id, users.username, requests.status, requests.completion_date, requests.completion_time
    FROM requests
    JOIN users ON requests.user_id = users.id
";
$result_requests = $conn->query($sql_requests);

// Check if the query was successful
if (!$result_users || !$result_requests) {
    die("Database query failed: " . $conn->error);
}

// Update request status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    
    $completion_date = null;
    $completion_time = null;

    if ($status == 'Completed') {
        $completion_date = date('Y-m-d');
        $completion_time = date('H:i:s');
    }
    
    $sql_update = "UPDATE requests SET status = ?, completion_date = ?, completion_time = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $status, $completion_date, $completion_time, $request_id);
    
    if ($stmt_update->execute()) {
        // Redirect to the same page to avoid resubmission on refresh
        header('Location: admin_dashboard.php');
        exit();
    } else {
        $update_message = "Error updating status. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Laundry Management System</title>
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
        .form-group select, .form-group input {
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
        Admin Dashboard - Laundry Management System
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <h1>User Details</h1>

        <?php if ($result_users->num_rows > 0): ?>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                </tr>
                <?php while ($row = $result_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>

        <h1>Laundry Requests</h1>

        <?php if ($result_requests->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Request ID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Completion Date</th>
                    <th>Completion Time</th>
                    <th>Update Status</th>
                </tr>
                <?php while ($row = $result_requests->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['completion_date'] ? $row['completion_date'] : 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['completion_time'] ? $row['completion_time'] : 'N/A'); ?></td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($row['request_id']); ?>">
                                <div class="form-group">
                                    <select name="status">
                                        <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <input type="submit" name="update_status" value="Update Status">
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No laundry requests found.</p>
        <?php endif; ?>

        <?php if (isset($update_message)): ?>
            <div class="message"><?php echo htmlspecialchars($update_message); ?></div>
        <?php endif; ?>
    </div>

</body>
</html>
