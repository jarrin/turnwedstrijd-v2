<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deelnemersbeheer - Turnen Score Systeem</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="header-brand">
                <div class="brand-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Deelnemersbeheer</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Home</a>
            </nav>
        </div>
    </header>
 
    <div class="container">
        <div class="card">
            <h2>Voeg Deelnemer Toe</h2>
            <form id="participantForm">
                <div class="participants-form-grid">
                    <div class="form-group">
                        <label for="name">Naam</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="number">Lidnummer</label>
                        <input type="number" id="number" name="number" min="1" step="1" inputmode="numeric" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Geslacht</label>
                        <select id="gender" name="gender" required>
                            <option value="Heren">Heren</option>
                            <option value="Dames">Dames</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="group">Groep</label>
                        <input type="text" id="group" name="group">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Deelnemer Toevoegen</button>
            </form>
        </div>
 
        <div class="card">
            <h2>Deelnemerslijst</h2>
            <div class="participants-filter-row">
                <div class="participants-filter-group">
                    <label for="participantsGenderFilter">Filter geslacht</label>
                    <select id="participantsGenderFilter">
                        <option value="">Alle deelnemers</option>
                        <option value="Heren">Heren</option>
                        <option value="Dames">Dames</option>
                    </select>
                </div>
            </div>
            <div id="participantsList" class="loading">
                <div class="spinner"></div>
                Deelnemers laden...
            </div>
        </div>
 
        <div id="deleteConfirmModal" class="modal-overlay participants-delete-modal">
            <div class="participants-modal-box participants-delete-box">
                <h3 class="modal-title">Deelnemer verwijderen</h3>
                <p class="modal-description">Ben je zeker dat je deze deelnemer en alle bijbehorende scores wilt verwijderen?</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-ghost" id="cancelDeleteBtn">Annuleren</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Ja, verwijderen</button>
                </div>
            </div>
        </div>
 
        <div id="scoreHistoryModal" class="modal-overlay participants-history-modal">
            <div class="participants-modal-box participants-history-box">
                <div class="participants-history-header">
                    <h3 id="scoreHistoryTitle" class="modal-title">Scores per onderdeel</h3>
                    <button type="button" class="btn btn-ghost btn-sm" id="closeScoreHistoryBtn">Sluiten</button>
                </div>
                <div id="scoreHistoryContent" class="loading">
                    <div class="spinner"></div>
                    Scores laden...
                </div>
            </div>
        </div>
    </div>
 
    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/pages/participants.js"></script>
</body>
</html>