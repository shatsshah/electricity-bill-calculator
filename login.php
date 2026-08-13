<?php

require_once "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, password FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect email or password.";
        }

        $stmt->close();
    }
}

$registered = isset($_GET["registered"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-card">

    <div class="auth-heading">
        <p class="eyebrow">ELECTRICITY BILL SYSTEM</p>
        <h1>Welcome Back</h1>
        <p>Login to calculate and view your monthly electricity bills.</p>
    </div>

    <?php if ($registered): ?>
        <p class="success-message">Account created successfully. You can now login.</p>
    <?php endif; ?>

    <form method="POST">

        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?php echo htmlspecialchars($email); ?>"
            placeholder="Enter your email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <button type="submit">Login</button>

    </form>

    <p class="form-link">
        Don't have an account?
        <a href="register.php">Create one</a>
    </p>

</div>

</body>
</html>