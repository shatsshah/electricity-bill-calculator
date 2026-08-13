<?php

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];

$stmt = $conn->prepare(
    "SELECT bill_month, units, bill_amount
     FROM monthly_bills
     WHERE user_id = ?
     ORDER BY bill_month DESC"
);

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$bills = [];

while ($row = $result->fetch_assoc()) {
    $bills[] = $row;
}

$stmt->close();

$average = 0;

$averageStmt = $conn->prepare(
    "SELECT AVG(bill_amount) AS average_bill
     FROM monthly_bills
     WHERE user_id = ?"
);

$averageStmt->bind_param("i", $userId);
$averageStmt->execute();

$averageResult = $averageStmt->get_result();
$averageRow = $averageResult->fetch_assoc();

$average = $averageRow["average_bill"] ?? 0;

$averageStmt->close();

$added = isset($_GET["added"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill History</title>
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

        <section class="page-heading">
            <p class="eyebrow">MONTHLY RECORDS</p>
            <h1>Bill History</h1>
            <p>Monthly electricity bills saved for <?php echo htmlspecialchars($userName); ?>.</p>
        </section>

        <?php if ($added): ?>
            <p class="success-message">Bill saved successfully.</p>
        <?php endif; ?>

        <section class="average-card">

            <div>
                <span>Average Monthly Bill</span>
                <small>Calculated from your saved bill records</small>
            </div>

            <strong>₹<?php echo number_format($average, 2); ?></strong>

        </section>

        <section class="history-card">

            <div class="section-heading">
                <h2>Monthly Bills</h2>
                <p>Your saved electricity consumption and bill amount.</p>
            </div>

            <?php if (count($bills) > 0): ?>

                <div class="history-table">

                    <div class="history-row history-header">
                        <span>Month</span>
                        <span>Units</span>
                        <span>Bill</span>
                    </div>

                    <?php foreach ($bills as $bill): ?>

                        <div class="history-row">
                            <span>
                                <?php echo date("F Y", strtotime($bill["bill_month"])); ?>
                            </span>

                            <span>
                                <?php echo $bill["units"]; ?>
                            </span>

                            <strong>
                                ₹<?php echo number_format($bill["bill_amount"], 2); ?>
                            </strong>
                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="empty-state">
                    <h3>No bills saved yet</h3>
                    <p>Calculate your first monthly bill to see it here.</p>
                    <a href="calculate.php" class="primary-link">Calculate Bill</a>
                </div>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>