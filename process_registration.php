<?php
$hostName = 'DESKTOP-83FBAU4';
$dbName = 'your_database_name'; 
$dbUser = 'root'; 
$dbPassword = 'ajithkavi@123'; 

try {
    $connection = new PDO("mysql:host=$hostName;dbname=$dbName", $dbUser, $dbPassword);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function sanitizeInput($data) {
        return htmlspecialchars(trim($data));
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newUsername = sanitizeInput($_POST['newUsername']);
        $newEmail = sanitizeInput($_POST['newEmail']);
        $newPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);

        $checkEmailQuery = "SELECT * FROM users WHERE email = :newEmail";
        $stmt = $connection->prepare($checkEmailQuery);
        $stmt->bindParam(':newEmail', $newEmail);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $insertUserQuery = "INSERT INTO users (username, email, password) VALUES (:newUsername, :newEmail, :newPassword)";
            $stmt = $connection->prepare($insertUserQuery);
            $stmt->bindParam(':newUsername', $newUsername);
            $stmt->bindParam(':newEmail', $newEmail);
            $stmt->bindParam(':newPassword', $newPassword);

            if ($stmt->execute()) {
                if (!empty($_FILES['profileImage']['name'])) {
                    $uploadDir = 'uploads/';
                    $uploadedFile = $uploadDir . basename($_FILES['profileImage']['name']);
                    move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadedFile);

                    $updateImageQuery = "UPDATE users SET profile_image = :profileImage WHERE email = :newEmail";
                    $stmt = $connection->prepare($updateImageQuery);
                    $stmt->bindParam(':profileImage', $uploadedFile);
                    $stmt->bindParam(':newEmail', $newEmail);
                    $stmt->execute();
                }

                echo "User registered successfully!";
            } else {
                echo "Error: " . $stmt->errorInfo()[2];
            }
        } else {
            echo "Email already exists. Choose a different email.";
        }
    }
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
} finally {
    $connection = null;
}
?>
