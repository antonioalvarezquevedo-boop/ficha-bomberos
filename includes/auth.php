<?php
if (session_status() === PHP_SESSION_NONE) {
          session_start();
}

function isLoggedIn() {
          return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

function estaAutenticado() {
          return isLoggedIn();
}

function requerirAuth() {
          if (!isLoggedIn()) {
                        header('Location: login.php');
                        exit;
          }
}

function verificarTimeout() {
          if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
                        session_destroy();
                        header('Location: login.php?timeout=1');
                        exit;
          }
          $_SESSION['last_activity'] = time();
}

function limpiarInput($data) {
          $data = trim($data);
          $data = stripslashes($data);
          $data = htmlspecialchars($data);
          return $data;
}

function login($username, $password) {
          $db = getDB();
          $stmt = $db->prepare('SELECT * FROM usuarios WHERE username = ?');
          $stmt->execute([$username]);
          $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
                  $_SESSION['user_id'] = $user['id'];
                  $_SESSION['username'] = $user['username'];
                  $_SESSION['nombre'] = $user['nombre'] ?? $user['username'];
                  $_SESSION['rol'] = $user['rol'] ?? 'admin';
                  $_SESSION['last_activity'] = time();
                  return true;
    }
          return false;
}

function logout() {
          session_start();
          session_destroy();
          header('Location: login.php');
          exit;
}

function getCurrentUser() {
          if (!isLoggedIn()) return null;
          $db = getDB();
          $stmt = $db->prepare('SELECT * FROM usuarios WHERE id = ?');
          $stmt->execute([$_SESSION['user_id']]);
          return $stmt->fetch();
}
