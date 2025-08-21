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

$error_message = '';
$success_message = '';
$boete = null;

// Controleer of er een geldige ID is opgegeven
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$boete_id = (int)$_GET['id'];

try {
    // Database verbinding
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Haal boete op
    $stmt = $pdo->prepare("SELECT * FROM avg_boetes WHERE id = ?");
    $stmt->execute([$boete_id]);
    $boete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$boete) {
        header('Location: admin.php');
        exit();
    }
    
} catch (PDOException $e) {
    die('Database fout: ' . $e->getMessage());
}

// Verwerk formulier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $overtreder = trim($_POST['overtreder'] ?? '');
    $boetebedrag = trim($_POST['boetebedrag'] ?? '');
    $datum = trim($_POST['datum'] ?? '');
    $avg_artikel = trim($_POST['avg_artikel'] ?? '');
    $introtekst = trim($_POST['introtekst'] ?? '');
    $volledige_tekst = trim($_POST['volledige_tekst'] ?? '');
    $externe_link = trim($_POST['externe_link'] ?? '');
    $status = $_POST['status'] ?? 'gepubliceerd';
    
    // Validatie
    if (empty($overtreder)) {
        $error_message = 'Overtreder is verplicht.';
    } elseif (empty($boetebedrag) || !is_numeric($boetebedrag)) {
        $error_message = 'Boetebedrag moet een geldig getal zijn.';
    } elseif (empty($datum)) {
        $error_message = 'Datum is verplicht.';
    } elseif (empty($avg_artikel)) {
        $error_message = 'AVG artikel is verplicht.';
    } elseif (empty($introtekst)) {
        $error_message = 'Introtekst is verplicht.';
    } elseif (empty($volledige_tekst)) {
        $error_message = 'Volledige tekst is verplicht.';
    } else {
        try {
            // Genereer nieuwe slug als overtreder of bedrag is veranderd
            if ($overtreder !== $boete['overtreder'] || $boetebedrag !== $boete['boetebedrag'] || $datum !== $boete['datum']) {
                $slug = strtolower($overtreder . '-' . $boetebedrag . '-' . date('Y', strtotime($datum)));
                $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
                $slug = trim($slug, '-');
                
                // Controleer of slug al bestaat (behalve bij huidige boete)
                $slug_check = $pdo->prepare("SELECT COUNT(*) FROM avg_boetes WHERE slug = ? AND id != ?");
                $slug_check->execute([$slug, $boete_id]);
                if ($slug_check->fetchColumn() > 0) {
                    $slug .= '-' . time(); // Voeg timestamp toe als slug al bestaat
                }
            } else {
                $slug = $boete['slug']; // Behoud bestaande slug
            }
            
            // Update boete
            $stmt = $pdo->prepare("
                UPDATE avg_boetes 
                SET overtreder = ?, boetebedrag = ?, datum = ?, avg_artikel = ?, 
                    introtekst = ?, volledige_tekst = ?, externe_link = ?, status = ?, slug = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $overtreder,
                $boetebedrag,
                $datum,
                $avg_artikel,
                $introtekst,
                $volledige_tekst,
                $externe_link ?: null,
                $status,
                $slug,
                $boete_id
            ]);
            
            // Redirect naar admin met success message
            header('Location: admin.php?updated=1');
            exit();
            
        } catch (PDOException $e) {
            $error_message = 'Database fout: ' . $e->getMessage();
        }
    }
} else {
    // Vul formulier met bestaande data (alleen bij GET request)
    $_POST = [
        'overtreder' => $boete['overtreder'],
        'boetebedrag' => $boete['boetebedrag'],
        'datum' => $boete['datum'],
        'avg_artikel' => $boete['avg_artikel'],
        'introtekst' => $boete['introtekst'],
        'volledige_tekst' => $boete['volledige_tekst'],
        'externe_link' => $boete['externe_link'],
        'status' => $boete['status']
    ];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Boete Bewerken - AVG Boetes CMS</title>
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

        .breadcrumb {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .boete-info {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 30px;
            font-size: 16px;
        }

        /* ==========================================================================
           4. Formulier
           ========================================================================== */

        .form-container {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 18px;
        }

        .required {
            color: var(--error-color);
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group input[type="url"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 4px;
            font-family: var(--font-main);
            font-size: 16px;
            transition: border-color 0.2s ease;
            box-sizing: border-box;
            resize: vertical;
        }

        .form-group textarea {
            min-height: 100px;
        }

        .form-group textarea.large {
            min-height: 200px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-help {
            font-size: 14px;
            color: var(--text-color-light);
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .button {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-family: var(--font-main);
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
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

        .button-danger {
            background-color: var(--error-color);
        }

        .button-danger:hover {
            background-color: #c82333;
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
           6. Responsiviteit
           ========================================================================== */

        @media (max-width: 600px) {
            .form-actions {
                flex-direction: column;
            }
            
            h1 {
                font-size: 36px;
            }
            
            h2 {
                font-size: 28px;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <header class="site-header">
        <h1>AVG Boetes CMS</h1>
        <h2>Boete bewerken</h2>
    </header>

    <div class="breadcrumb">
        <a href="admin.php">← Terug naar dashboard</a>
    </div>

    <div class="boete-info">
        <strong>Bewerken:</strong> <?= htmlspecialchars($boete['overtreder']) ?> - 
        €<?= number_format($boete['boetebedrag'], 0, ',', '.') ?> - 
        <?= date('d-m-Y', strtotime($boete['datum'])) ?>
        <br><small>Aangemaakt: <?= date('d-m-Y H:i', strtotime($boete['created_at'])) ?></small>
        <?php if ($boete['updated_at'] !== $boete['created_at']): ?>
            <br><small>Laatst gewijzigd: <?= date('d-m-Y H:i', strtotime($boete['updated_at'])) ?></small>
        <?php endif; ?>
    </div>

    <main>
        <?php if ($success_message): ?>
            <div class="success-message">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="overtreder">Overtreder <span class="required">*</span></label>
                    <input type="text" id="overtreder" name="overtreder" required 
                           value="<?= htmlspecialchars($_POST['overtreder'] ?? '') ?>" 
                           placeholder="Bijv. TechGigant B.V.">
                </div>
                
                <div class="form-group">
                    <label for="boetebedrag">Boetebedrag (in euro's) <span class="required">*</span></label>
                    <input type="number" id="boetebedrag" name="boetebedrag" required 
                           value="<?= htmlspecialchars($_POST['boetebedrag'] ?? '') ?>" 
                           placeholder="725000" min="0" step="0.01">
                    <div class="form-help">Voer het bedrag in zonder euro-teken</div>
                </div>
                
                <div class="form-group">
                    <label for="datum">Datum van boete <span class="required">*</span></label>
                    <input type="date" id="datum" name="datum" required 
                           value="<?= htmlspecialchars($_POST['datum'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="avg_artikel">AVG Artikel <span class="required">*</span></label>
                    <input type="text" id="avg_artikel" name="avg_artikel" required 
                           value="<?= htmlspecialchars($_POST['avg_artikel'] ?? '') ?>" 
                           placeholder="Bijv. Artikel 32 (Beveiliging van de verwerking)">
                </div>
                
                <div class="form-group">
                    <label for="introtekst">Introtekst voor homepage <span class="required">*</span></label>
                    <textarea id="introtekst" name="introtekst" required 
                              placeholder="Korte samenvatting voor op de homepage..."><?= htmlspecialchars($_POST['introtekst'] ?? '') ?></textarea>
                    <div class="form-help">Deze tekst wordt getoond op de homepage in de boete-card</div>
                </div>
                
                <div class="form-group">
                    <label for="volledige_tekst">Volledige tekst voor detail pagina <span class="required">*</span></label>
                    <textarea id="volledige_tekst" name="volledige_tekst" required class="large" 
                              placeholder="Uitgebreide informatie over deze boete..."><?= htmlspecialchars($_POST['volledige_tekst'] ?? '') ?></textarea>
                    <div class="form-help">Deze tekst wordt getoond op de detail pagina van de boete</div>
                </div>
                
                <div class="form-group">
                    <label for="externe_link">Externe link (optioneel)</label>
                    <input type="url" id="externe_link" name="externe_link" 
                           value="<?= htmlspecialchars($_POST['externe_link'] ?? '') ?>" 
                           placeholder="https://autoriteitpersoonsgegevens.nl/...">
                    <div class="form-help">Link naar de originele bron (bijv. AP.nl)</div>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="gepubliceerd" <?= ($_POST['status'] ?? 'gepubliceerd') === 'gepubliceerd' ? 'selected' : '' ?>>Gepubliceerd</option>
                        <option value="concept" <?= ($_POST['status'] ?? '') === 'concept' ? 'selected' : '' ?>>Concept</option>
                    </select>
                    <div class="form-help">Concepten worden niet getoond op de website</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button">Wijzigingen opslaan</button>
                    <a href="admin.php" class="button button-secondary">Annuleren</a>
                    <a href="admin.php?delete=<?= $boete['id'] ?>" 
                       class="button button-danger" 
                       onclick="return confirm('Weet je zeker dat je deze boete wilt verwijderen?')">Verwijderen</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>