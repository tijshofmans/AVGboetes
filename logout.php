<?php
session_start();

// Vernietig alle session data
$_SESSION = array();

// Vernietig session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Vernietig de session
session_destroy();

// Redirect naar login pagina
header('Location: login.php?logged_out=1');
exit();
?>