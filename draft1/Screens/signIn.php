<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['UserName']);
    $password = $conn->real_escape_string($_POST['Password']);
    $query = "SELECT * FROM Login WHERE UserName = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['Password'])) {
            echo "Login successful!";
            header("Location: home.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this username.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literexia Sign In Page</title>
    <link rel="stylesheet" href="../CSS/LoginSignUp.css">
</head>
<body>

    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="signUp.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="form-container">
            <h1>Sign In</h1>
            <form action="signIn.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="UserName" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="Password" required>

                <button type="submit">Sign In</button>
              
                <label for="username">Create your Account:</label>

            </div>
            </form>
        </section>
        
    </main>

</body>
</html>
