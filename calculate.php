<?php

require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];
$userName = $_SESSION["user_name"];

$month = "";
$units = "";
$totalBill = null;
$breakdown = [];
$error = "";

function calculateBill($units, &$breakdown)
{
    $remaining = $units;
    $total = 0;

    if ($remaining > 0) {
        $used = min($remaining, 50);
        $amount = $used * 3.50;
        $total += $amount;

        $breakdown[] = [
            "range" => "First 50 units",
            "units" => $used,
            "rate" => 3.50,
            "amount" => $amount
        ];

        $remaining -= $used;
    }

    if ($remaining > 0) {
        $used = min($remaining, 100);
        $amount = $used * 4.00;
        $total += $amount;

        $breakdown[] = [
            "range" => "Next 100 units",
            "units" => $used,
            "rate" => 4.00,
            "amount" => $amount
        ];

        $remaining -= $used;
    }

    if ($remaining > 0) {
        $used = min($remaining, 100);
        $amount = $used * 5.20;
        $total += $amount;

        $breakdown[] = [
            "range" => "Next 100 units",
            "units" => $used,
            "rate" => 5.20,
            "amount" => $amount
        ];

        $remaining -= $used;
    }

    if ($remaining > 0) {
        $amount = $remaining * 6.50;
        $total += $amount;

        $breakdown[] = [
            "range" => "Above 250 units",
            "units" => $remaining,
            "rate" => 6.50,
            "amount" => $amount
        ];
    }

    return $total;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $month = $_POST["month"] ?? "";
    $unitsInput = trim($_POST["units"] ?? "");

    if ($month === "") {
        $error = "Please select a month.";
    } elseif ($unitsInput === "" || !ctype_digit($unitsInput)) {
        $error = "Please enter a whole number of units.";
    } else {
        $units = (int)$unitsInput;

        $billDate = $month . "-01";

        $check = $conn->prepare(
            "SELECT id FROM monthly_bills 
             WHERE user_id = ? AND bill_month = ?"
        );

        $check->bind_param("is", $userId, $billDate);
        $check->execute();

        $existing = $check->get_result();

        if ($existing->num_rows > 0) {
            $error = "A bill for this month already exists.";
        } else {
            $totalBill = calculateBill($units, $breakdown);

            $insert = $conn->prepare(
                "INSERT INTO monthly_bills 
                (user_id, bill_month, units, bill_amount)
                VALUES (?, ?, ?, ?)"
            );

            $insert->bind_param(
                "isid",
                $userId,
                $billDate,
                $units,
                $totalBill
            );

            $insert->execute();
            $insert->close();

            header("Location: history.php?added=1");
            exit;
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculate Bill</title>
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
            <p class="eyebrow">BILL CALCULATOR</p>
            <h1>Calculate Monthly Bill</h1>
            <p>Enter your monthly electricity consumption below.</p>
        </section>

        <section class="calculator-layout">

            <div class="form-card">

                <form method="POST">

                    <label for="month">Billing Month</label>
                    <input
                        type="month"
                        id="month"
                        name="month"
                        value="<?php echo htmlspecialchars($month); ?>"
                        required
                    >

                    <label for="units">Units Consumed</label>

                    <div class="input-row">
                        <input
                            type="number"
                            id="units"
                            name="units"
                            min="0"
                            step="1"
                            value="<?php echo htmlspecialchars($units); ?>"
                            placeholder="Enter whole units"
                            required
                        >
                        <span>units</span>
                    </div>

                    <?php if ($error): ?>
                        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <button type="submit">Calculate and Save</button>

                </form>

            </div>

            <div class="rate-card">

                <h2>Electricity Rates</h2>
                <p>Charges are applied progressively.</p>

                <div class="rate-list">

                    <div class="rate-item">
                        <span>First 50 units</span>
                        <strong>₹3.50</strong>
                    </div>

                    <div class="rate-item">
                        <span>Next 100 units</span>
                        <strong>₹4.00</strong>
                    </div>

                    <div class="rate-item">
                        <span>Next 100 units</span>
                        <strong>₹5.20</strong>
                    </div>

                    <div class="rate-item">
                        <span>Above 250 units</span>
                        <strong>₹6.50</strong>
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>