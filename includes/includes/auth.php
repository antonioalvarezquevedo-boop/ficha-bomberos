<?php
// Authentication and Session Management

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
      session_start();
}

// Check if user is logged in
function isLoggedIn() {
      return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Require authentication
function requireAuth() {
      if (!isLoggedIn()) {
                header('Location: ' . SITE_URL . '/login.php');
                exit;
      }
}

// Get current user
function getCurrentUser() {
      if (!isLoggedIn()) {
                return null;
      }

    try {
              global $pdo;
              $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
              $stmt->execute([$_SESSION['user_id']]);
              return $stmt->fetch();
    } catch (Exception $e) {
              return null;
    }
}

// Check user role/permissions
function hasRole($role) {
      $user = getCurrentUser();
      return $user && $user['rol'] === $role;
}

// Logout function
function logout() {
      session_destroy();
      header('Location: ' . SITE_URL . '/login.php');
      exit;
}

// CSRF Token functions
function generateCSRFToken() {
      if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
      return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

?>
