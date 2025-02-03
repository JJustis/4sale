<?php
// register.php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Debug logging
    error_log("Registration attempt started");
    error_log("POST data: " . print_r($_POST, true));

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        throw new Exception('All fields are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Log the SQL query for debugging
    $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
    error_log("Check query: " . $check_query);

    $check_stmt = $conn->prepare($check_query);
    if ($check_stmt === false) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception('Username or email already exists');
    }

    $check_stmt->close();

    // Insert new user
    $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    error_log("Insert query: " . $insert_query);

    $insert_stmt = $conn->prepare($insert_query);
    if ($insert_stmt === false) {
        throw new Exception('Prepare insert statement failed: ' . $conn->error);
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Registration failed: ' . $insert_stmt->error);
    }

    $user_id = $insert_stmt->insert_id;
    $insert_stmt->close();

    // Set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;

    error_log("Registration successful for user: " . $username);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => $user_id
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
} finally {
    if (isset($check_stmt) && $check_stmt !== false) {
        $check_stmt->close();
    }
    if (isset($insert_stmt) && $insert_stmt !== false) {
        $insert_stmt->close();
    }
    if (isset($conn) && !$conn->connect_error) {
        $conn->close();
    }
}