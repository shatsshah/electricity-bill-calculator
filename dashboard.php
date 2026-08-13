<?php

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total_months, 
            COALESCE(SUM(bill_amount), 0) AS total_paid,
            COALESCE(AVG(bill_amount), 0) AS average_bill
     FROM monthly_bills
     WHERE user_id = ?"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$summary = $stmt->get_result()->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="site">

    <nav class="navbar">
        <a href="dashboard.php" class="brand">Electricity Bill</a>

        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="calculate.php">Calculate</a>
            <a href="history.php">History</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <main class="main-container">

        <div class="welcome-section">
            <p class="eyebrow">DASHBOARD</p>
            <h1>Welcome, <?php echo htmlspecialchars($userName); ?></h1>
            <p>Manage your monthly electricity bill records from one place.</p>
        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <span>Months Saved</span>
                <strong><?php echo $summary["total_months"]; ?></strong>
            </div>

            <div class="stat-card">
                <span>Total Bill Amount</span>
                <strong>₹<?php echo number_format($summary["total_paid"], 2); ?></strong>
            </div>

            <div class="stat-card">
                <span>Average Bill</span>
                <strong>₹<?php echo number_format($summary["average_bill"], 2); ?></strong>
            </div>

        </div>

        <section class="dashboard-card">

            <div>
                <h2>Calculate a Monthly Bill</h2>
                <p>
                    Enter the month and number of units consumed to calculate
                    and save a new bill.
                </p>
            </div>

            <a href="calculate.php" class="primary-link">Calculate Bill</a>

        </section>

    </main>

</div>

</body>
</html>