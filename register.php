<?php

require_once "db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$name = "";
$email = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must contain at least 6 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $conn->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            $insert->bind_param("sss", $name, $email, $hashedPassword);
            $insert->execute();

            $insert->close();

            header("Location: login.php?registered=1");
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
    <title>Create Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

<div class="auth-card">

    <div class="auth-heading">
        <p class="eyebrow">ELECTRICITY BILL SYSTEM</p>
        <h1>Create Account</h1>
        <p>Set up your account to keep track of monthly bills.</p>
    </div>

    <form method="POST">

        <label for="name">Name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="<?php echo htmlspecialchars($name); ?>"
            placeholder="Enter your name"
            required
        >

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
            placeholder="Create a password"
            required
        >

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <button type="submit">Create Account</button>

    </form>

    <p class="form-link">
        Already have an account?
        <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>