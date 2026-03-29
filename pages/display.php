<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoofdscherm - Turnen Score Systeem</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="display-mode">
    <header>
        <div class="header-inner">
            <div class="header-brand">
                <div class="brand-icon"><i class="fa-solid fa-tv" aria-hidden="true"></i></div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Hoofdscherm &mdash; Live Klassement</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Home</a>
            </nav>
        </div>
    </header>
 
    <div class="container">
        <div style="margin-bottom:1.5rem;">
            <p style="font-size:.7rem;text-transform:uppercase;letter-spacing:.1em;color:var(--text-muted);margin-bottom:.2rem;">Live weergave</p>
            <h2 style="font-size:1.4rem;font-weight:800;color:var(--text-primary);letter-spacing:-.02em;">Hoofdscherm</h2>
            <div style="margin-top:.9rem;max-width:280px;">
                <label for="displayGenderFilter" style="margin-bottom:.35rem;">Filter geslacht</label>
                <select id="displayGenderFilter">
                    <option value="">Alle deelnemers</option>
                    <option value="Heren">Heren</option>
                    <option value="Dames">Dames</option>
                </select>
            </div>
        </div>
 
        <div class="display-container">
            <!-- Current Score -->
            <div class="current-score">
                <h2>Huidige Deelnemer</h2>
                <div id="currentScoreDisplay">
                    <div class="loading">
                        <div class="spinner"></div>
                        Laden...
                    </div>
                </div>
            </div>
 
            <!-- Leaderboard -->
            <div class="leaderboard-container">
                <h3><i class="fa-solid fa-trophy" aria-hidden="true"></i> Top 10 Klassement</h3>
                <div id="leaderboardDisplay">
                    <div class="loading">
                        <div class="spinner"></div>
                        Laden...
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/pages/display.js"></script>
</body>
</html>