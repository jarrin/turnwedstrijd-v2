<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnen Score Systeem</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="header-brand">
                <div class="brand-icon"><i class="fa-solid fa-medal" aria-hidden="true"></i></div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <div style="text-align:center; margin-bottom:2.5rem; padding-top:.5rem;">
            <p class="home-subtitle">Turnwedstrijd Portaal</p>
            <h2 style="font-size:2rem; font-weight:800; color:var(--text-primary); letter-spacing:-.03em;">Selecteer een module</h2>
        </div>

        <div class="grid">
            <!-- Deelnemersbeheer -->
            <a href="pages/participants.php" class="module-card blue">
                <div class="module-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                <h3>Deelnemersbeheer</h3>
                <p>Registreer en beheer alle deelnemers per groep en geslacht</p>
            </a>

            <!-- Jury Invoer -->
            <a href="pages/jury.php" class="module-card green">
                <div class="module-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></div>
                <h3>Jury Invoer</h3>
                <p>Voer D-, E- en N-scores in per deelnemer en toestel</p>
            </a>

            <!-- Secretariaat -->
            <a href="pages/secretariat.php" class="module-card orange">
                <div class="module-icon"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></div>
                <h3>Secretariaat</h3>
                <p>Controleer, keur goed, bewerk of wijs ingediende scores af</p>
            </a>

            <!-- Hoofdscherm -->
            <a href="pages/display.php" class="module-card purple">
                <div class="module-icon"><i class="fa-solid fa-tv" aria-hidden="true"></i></div>
                <h3>Hoofdscherm</h3>
                <p>Live score-weergave en Top&nbsp;10 klassement voor het publiek</p>
            </a>
        </div>

    </div>

    <script src="assets/js/utils.js"></script>
</body>
</html>
