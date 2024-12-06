<?php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $firstName = $conn->real_escape_string($_POST['FirstName']);
    $lastName = $conn->real_escape_string($_POST['LastName']);
    $email = $conn->real_escape_string($_POST['Email']);
    $address = $conn->real_escape_string($_POST['Address']);
    $contactNumber = $conn->real_escape_string($_POST['ContactNumber']);
    $section = $conn->real_escape_string($_POST['Section']);
    $username = $conn->real_escape_string($_POST['UserName']);
    $password = $conn->real_escape_string($_POST['Password']);
    $gender = $conn->real_escape_string($_POST['Gender']);

    $errors = [];
    
    if (strlen($firstName) < 2) {
        $errors[] = "First name must be at least 2 characters long";
    }
    if (strlen($lastName) < 2) {
        $errors[] = "Last name must be at least 2 characters long";
    }

    if (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters long";
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo $error . "<br>";
        }
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $checkQuery = "SELECT * FROM Registration WHERE Email = ? OR LoginID IN (SELECT LoginID FROM Login WHERE UserName = ?)";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "An account with this email or username already exists.";
        exit;
    }

    $conn->begin_transaction();

    try {
        $insertLoginQuery = "INSERT INTO Login (UserName, Password) VALUES (?, ?)";
        $stmt = $conn->prepare($insertLoginQuery);
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();

        $loginID = $conn->insert_id;

        $insertRegistrationQuery = "INSERT INTO Registration (FirstName, LastName, Email, Address, ContactNumber, Section, LoginID, Gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertRegistrationQuery);
        $stmt->bind_param("ssssssss", $firstName, $lastName, $email, $address, $contactNumber, $section, $loginID, $gender);
        $stmt->execute();

        $registrationID = $conn->insert_id;

        $insertUserQuery = "INSERT INTO User (RegistrationID) VALUES (?)";
        $stmt = $conn->prepare($insertUserQuery);
        $stmt->bind_param("i", $registrationID);
        $stmt->execute();

        $conn->commit();

        echo "Registration successful!";
        header("Location: signIn.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        echo "Detailed Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literexia Sign Up</title>
    <link rel="stylesheet" href="../CSS/LoginSignUp.css">
    <style>
        .password-requirements {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        .forgot-password {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="signIn.php">Sign In</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="form-container">
            <h1>Create Your Account</h1>

            <form action="signUp.php" method="POST">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="FirstName" required minlength="2">

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="LastName" required minlength="2">

                <label for="username">Username:</label>
                <input type="text" id="username" name="UserName" required minlength="4" 
                       pattern="[a-zA-Z0-9_]+" title="Username can only contain letters, numbers, and underscores">

                <label for="email">Email:</label>
                <input type="email" id="email" name="Email" required>

                <label for="gender">Gender:</label>
                <select name="Gender" id="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                    <option value="Prefer Not to Say">Prefer Not to Say</option>
                </select>

                <label for="address">Address:</label>
                <input type="text" id="address" name="Address" required>

                <label for="contact_number">Contact Number:</label>
                <input type="tel" id="contact_number" name="ContactNumber" pattern="^[0-9]{11}$" title="Enter a valid 11-digit number" required>

                <label for="section">Choose your Section:</label>
                <select name="Section" id="section" required>
                    <option value="Peace123">Peace123</option>
                    <option value="Unity345">Unity345</option>
                    <option value="Kindness234">Kindness234</option>
                    <option value="Prosperity234">Prosperity234</option>
                </select>

                <label for="password">Password:</label>
                <input type="password" id="password" name="Password" required minlength="8">
                <div class="password-requirements"></div>

                <button type="submit">Sign Up</button>
            </form>

          
        </section>
    </main>
</body>
</html>