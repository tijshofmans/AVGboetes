<?php
session_start();

// Controleer of gebruiker is ingelogd
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Database configuratie
$host = 'localhost';
$dbname = 'avgboetes_database';
$username = 'avgboetes_database';
$password = 'fCbSq8oxyRYxThwS$YNI784v3%S4Yn5n'; // Vervang dit door je echte database wachtwoord

try {
    // Database verbinding
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Haal alle boetes op, gesorteerd op datum (nieuwste eerst)
    $stmt = $pdo->prepare("SELECT * FROM avg_boetes ORDER BY datum DESC");
    $stmt->execute();
    $boetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tel aantal boetes
    $aantal_boetes = count($boetes);
    $totaal_bedrag = 0;
    foreach ($boetes as $boete) {
        $totaal_bedrag += $boete['boetebedrag'];
    }
    
} catch (PDOException $e) {
    die('Database fout: ' . $e->getMessage());
}

// Verwerk delete actie
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $delete_stmt = $pdo->prepare("DELETE FROM avg_boetes WHERE id = ?");
        $delete_stmt->execute([$_GET['delete']]);
        header('Location: admin.php?deleted=1');
        exit();
    } catch (PDOException $e) {
        $error_message = 'Fout bij verwijderen: ' . $e->getMessage();
    }
}

$success_message = '';
if (isset($_GET['deleted'])) {
    $success_message = 'Boete succesvol verwijderd.';
} elseif (isset($_GET['added'])) {
    $success_message = 'Boete succesvol toegevoegd.';
} elseif (isset($_GET['updated'])) {
    $success_message = 'Boete succesvol bijgewerkt.';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin Dashboard - AVG Boetes CMS</title>
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
            --success-color: #28a745;
            --error-color: #dc3545;
            --font-main: 'Source Code Pro', monospace;
        }

        body {
            background-color: var(--background-color);
            font-family: var(--font-main);
            font-size: 20px;
            line-height: 1.6;
            color: var(--text-color);
            
            max-width: 1200px;
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

        h3 {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 15px;
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
           3. Header & Navigation
           ========================================================================== */

        .site-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-nav {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .welcome-text {
            font-size: 18px;
            margin: 0;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .button {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.2s ease;
        }

        .button:hover {
            background-color: var(--primary-color-hover);
            text-decoration: none;
            color: white;
        }

        .button-secondary {
            background-color: var(--text-color-light);
        }

        .button-secondary:hover {
            background-color: var(--text-color);
        }

        /* ==========================================================================
           4. Statistieken
           ========================================================================== */

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 16px;
            color: var(--text-color-light);
        }

        /* ==========================================================================
           5. Messages
           ========================================================================== */

        .success-message {
            background-color: #d4edda;
            color: var(--success-color);
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 20px;
        }

        /* ==========================================================================
           6. Boetes Tabel
           ========================================================================== */

        .table-container {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
        }

        .boetes-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
        }

        .boetes-table th,
        .boetes-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .boetes-table th {
            background-color: #f8f9fa;
            font-weight: 700;
            color: var(--text-color);
        }

        .boetes-table td {
            color: var(--text-color);
        }

        .bedrag {
            font-weight: 700;
            color: var(--primary-color);
        }

        .datum {
            white-space: nowrap;
        }

        .acties {
            white-space: nowrap;
        }

        .acties a {
            margin-right: 10px;
            font-size: 14px;
        }

        .delete-link {
            color: var(--error-color);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-color-light);
        }

        /* ==========================================================================
           7. Responsiviteit
           ========================================================================== */

        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                text-align: center;
            }
            
            .nav-links {
                justify-content: center;
            }
            
            h1 {
                font-size: 36px;
            }
            
            h2 {
                font-size: 28px;
            }
            
            .boetes-table {
                font-size: 14px;
            }
            
            .boetes-table th,
            .boetes-table td {
                padding: 8px;
            }
        }
    </style>
</head>

<body>
    <header class="site-header">
        <h1>AVG Boetes CMS</h1>
        <h2>Admin Dashboard</h2>
    </header>

    <nav class="admin-nav">
        <p class="welcome-text">Welkom, <?= htmlspecialchars($_SESSION['admin_naam']) ?>!</p>
        <div class="nav-links">
            <a href="index.html" class="button button-secondary" target="_blank">Website bekijken</a>
            <a href="add_boete.php" class="button">Nieuwe boete toevoegen</a>
            <a href="logout.php" class="button button-secondary">Uitloggen</a>
        </div>
    </nav>

    <main>
        <?php if ($success_message): ?>
            <div class="success-message">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Statistieken -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= $aantal_boetes ?></div>
                <div class="stat-label">Totaal aantal boetes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">€ <?= number_format($totaal_bedrag, 0, ',', '.') ?></div>
                <div class="stat-label">Totaal boetebedrag</div>
            </div>
        </div>

        <!-- Boetes overzicht -->
        <div class="table-container">
            <h3>Alle boetes</h3>
            
            <?php if (empty($boetes)): ?>
                <div class="empty-state">
                    <p>Nog geen boetes toegevoegd.</p>
                    <a href="add_boete.php" class="button">Voeg eerste boete toe</a>
                </div>
            <?php else: ?>
                <table class="boetes-table">
                    <thead>
                        <tr>
                            <th>Overtreder</th>
                            <th>Bedrag</th>
                            <th>Datum</th>
                            <th>AVG Artikel</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($boetes as $boete): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($boete['overtreder']) ?></strong></td>
                            <td class="bedrag">€ <?= number_format($boete['boetebedrag'], 0, ',', '.') ?></td>
                            <td class="datum"><?= date('d-m-Y', strtotime($boete['datum'])) ?></td>
                            <td><?= htmlspecialchars($boete['avg_artikel']) ?></td>
                            <td><?= htmlspecialchars($boete['status']) ?></td>
                            <td class="acties">
                                <a href="edit_boete.php?id=<?= $boete['id'] ?>">Bewerken</a>
                                <a href="admin.php?delete=<?= $boete['id'] ?>" 
                                   class="delete-link" 
                                   onclick="return confirm('Weet je zeker dat je deze boete wilt verwijderen?')">Verwijderen</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>