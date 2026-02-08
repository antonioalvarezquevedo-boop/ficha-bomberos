<?php
if (session_status() === PHP_SESSION_NONE) {
      session_start();
}

function isLoggedIn() {
      return isset($_SESSION['user_id']) && isset($_SESSION['username']);
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

function getCurrentUser() {
      if (!isLoggedIn()) return null;
      global $pdo;
      $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
      $stmt->execute([$_SESSION['user_id']]);
      return $stmt->fetch();
}
