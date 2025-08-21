<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>AVG Boetes Nederland - Overzicht van privacyboetes</title>
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
            font-weight: 300; /* Lichte variant voor subtitels */
        }

        h3 {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 10px;
        }

        p, li {
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

        hr {
            border: 0;
            height: 1px;
            background-color: var(--border-color);
            margin: 40px 0;
        }

        /* ==========================================================================
           3. Header & Layout
           ========================================================================== */

        .site-header {
            text-align: center;
            margin-bottom: 40px;
        }

        /* ==========================================================================
           4. Boete Cards
           ========================================================================== */

        .boetes-container {
            display: grid;
            gap: 20px;
            grid-template-columns: 1fr;
            margin-top: 40px;
        }

        .boete-card {
            background-color: var(--card-background);
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 25px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .boete-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .boete-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .bedrijf-naam {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            flex: 1;
            min-width: 200px;
        }

        .boete-bedrag {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        .boete-datum {
            font-size: 16px;
            color: var(--text-color-light);
            margin-bottom: 15px;
        }

        .boete-intro {
            font-size: 18px;
            line-height: 1.5;
            color: var(--text-color);
            margin: 0;
        }

        /* ==========================================================================
           5. Responsiviteit
           ========================================================================== */

        @media (max-width: 600px) {
            .boete-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .bedrijf-naam, .boete-bedrag {
                font-size: 20px;
            }
            
            h1 {
                font-size: 36px;
            }
            
            h2 {
                font-size: 28px;
            }
        }

        /* Hover effect voor links in cards */
        .boete-card a {
            color: inherit;
            text-decoration: none;
        }

        .boete-card a:hover {
            text-decoration: none;
        }
    </style>
</head>

<body>
    <header class="site-header">
        <h1>AVG Boetes Nederland</h1>
        <h2>Overzicht van privacyboetes en AVG-overtredingen</h2>
    </header>

    <main>
        <p>Welkom bij AVG Boetes Nederland. Hier vind je een overzicht van alle bekendgemaakte boetes die zijn uitgedeeld onder de Algemene Verordening Gegevensbescherming (AVG). Deze database helpt bij het inzichtelijk maken van de handhaving van privacywetgeving in Nederland.</p>
        
        <hr />

        <div class="boetes-container">
            <div class="boete-card" onclick="window.location.href='boete-detail.html?id=1'">
                <div class="boete-header">
                    <h3 class="bedrijf-naam">TechGigant B.V.</h3>
                    <div class="boete-bedrag">€ 725.000</div>
                </div>
                <div class="boete-datum">Uitgedeeld op: 15 maart 2024</div>
                <p class="boete-intro">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore.</p>
            </div>
        </div>
    </main>

    <script>
        // Functionaliteit voor het klikken op boete cards kan hier toegevoegd worden
        // Momenteel navigeert de onclick in de HTML naar een detail pagina
    </script>
</body>
</html>