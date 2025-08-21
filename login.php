<?php
session_start();

// Database configuratie
$host = 'localhost';
$dbname = 'avgboetes_database';
$username = 'avgboetes_database';
$password = 'fCbSq8oxyRYxThwS$YNI784v3%S4Yn5n'; // Vervang dit door je echte database wachtwoord

// Controleer of gebruiker al is ingelogd
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit();
}

$error_message = '';

// Verwerk login formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_username = $_POST['username'] ?? '';
    $login_password = $_POST['password'] ?? '';
    
    try {
        // Database verbinding
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Zoek gebruiker in database
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND status = 'actief'");
        $stmt->execute([$login_username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Controleer gebruiker en wachtwoord
        if ($user && $user['password'] === $login_password) {
            // Login succesvol
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_naam'] = $user['naam'];
            
            // Update last_login
            $update_stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $update_stmt->execute([$user['id']]);
            
            header('Location: admin.php');
            exit();
        } else {
            $error_message = 'Onjuiste inloggegevens';
        }
        
    } catch (PDOException $e) {
        $error_message = 'Database fout: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Inloggen - AVG Boetes CMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* ==========================================================================
           1. Variabelen & Basisstijlen
           ========================================================================== */

        :root {
            --primary-color: #fb4d4d;
            --primary-color-hover: #e03c3c;
            --text-color: #333;
            --text-color-light: #666;
            --background-color: #f2f2f2;
            --border-color: #ddd;
            --card-background: #fafafa;
            --error-color: #dc3545;
            --font-main: 'Source Code Pro', monospace;
        }

        body {
            background-color: var(--background-color);
            font-family: var(--font-main);
            font-size: 20px;
            line-height: 1.6;
            color: var(--text-color);
            
            /* Gecentreerde en responsieve content-wrapper */
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* ==========================================================================
           2. Typografie
           ========================================================================== */

        h1 {
            font-size: 50px;
            margin-top: 0;
            margin-bottom: 0;
        }

        h2 {
            font-size: 36px;
            margin-top: 10px;
            margin-bottom: 20px;
            font-weight: 300;
        }

        p {
            font-size: 20px;
            margin-bottom: 1em;
        }

        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: text-decoration 0.2s ease-in-out;
        }

        a:hover {
            text-decoration: underline;
        }

        /* ==========================================================================
           3. Header & Layout
           ========================================================================== */

        .site-header {
            text-align: center;
            margin-bottom: 40px;
        }

        /* ==========================================================================
           4. Login Formulier
           ========================================================================== */

        .login-container {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 40px;
            margin: 40px auto;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 18px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            font-family: var(--font-main);
            font-size: 16px;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .login-button {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-family: var(--font-main);
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s ease;
            width: 100%;
        }

        .login-button:hover {
            background-color: var(--primary-color-hover);
        }

        .error-message {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        /* ==========================================================================
           5. Responsiviteit
           ========================================================================== */

        @media (max-width: 600px) {
            .login-container {
                margin: 20px auto;
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 36px;
            }
            
            h2 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    <header class="site-header">
        <h1>AVG Boetes CMS</h1>
        <h2>Admin login</h2>
    </header>

    <main>
        <div class="login-container">
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Gebruikersnaam:</label>
                    <input type="text" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Wachtwoord:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="login-button">Inloggen</button>
            </form>
            
            <div class="back-link">
                <a href="index.html">← Terug naar website</a>
            </div>
        </div>
    </main>
</body>
</html>