<?php
// login.php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    error_log("Login attempt started");
    error_log("POST data: " . print_r($_POST, true));

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('All fields are required');
    }

    $query = "SELECT id, username, password FROM users WHERE email = ?";
    error_log("Login query: " . $query);

    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            error_log("Login successful for user: " . $user['username']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            throw new Exception('Invalid credentials');
        }
    } else {
        throw new Exception('Invalid credentials');
    }

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
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
    if (isset($stmt) && $stmt !== false) {
        $stmt->close();
    }
    if (isset($conn) && !$conn->connect_error) {
        $conn->close();
    }
}