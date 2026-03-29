<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretariaat - Turnen Score Systeem</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="header-brand">
                <div class="brand-icon"><i class="fa-solid fa-clipboard-check" aria-hidden="true"></i></div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Secretariaat &mdash; Score Controle</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Home</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Wachtende Scores</h2>
            <div class="secretariat-filter-row">
                <div class="secretariat-filter-group">
                    <label for="secretariatGenderFilter">Filter geslacht</label>
                    <select id="secretariatGenderFilter">
                        <option value="">Alle deelnemers</option>
                        <option value="Heren">Heren</option>
                        <option value="Dames">Dames</option>
                    </select>
                </div>
            </div>
            <div id="scoresList" class="loading">
                <div class="spinner"></div>
                Scores laden...
            </div>
        </div>

        <div id="editModal" class="modal-overlay secretariat-edit-modal">
            <div class="secretariat-modal-box secretariat-edit-box">
                <h3 class="modal-title secretariat-modal-title-lg">Score Bewerken</h3>

                <div class="secretariat-edit-meta">
                    <div>
                        <div class="secretariat-meta-label">Kandidaat</div>
                        <div id="editCandidate" class="secretariat-meta-value">-</div>
                    </div>
                    <div>
                        <div class="secretariat-meta-label">Onderdeel</div>
                        <div id="editApparatus" class="secretariat-meta-value">-</div>
                    </div>
                </div>

                <form id="editScoreForm">
                    <div class="secretariat-edit-grid">
                        <div class="den-score-item d">
                            <span class="den-label">D-Score</span>
                            <input type="number" id="editDScore" step="0.1" min="0" max="20" required class="secretariat-edit-input">
                        </div>
                        <div class="den-score-item e">
                            <span class="den-label">E-Score</span>
                            <input type="number" id="editEScore" step="0.1" min="0" max="10" required class="secretariat-edit-input">
                        </div>
                        <div class="den-score-item n">
                            <span class="den-label">N-Score</span>
                            <input type="number" id="editNScore" step="0.1" min="0" max="10" required class="secretariat-edit-input">
                        </div>
                        <div class="den-score-item total secretariat-total-cell">
                            <span class="den-label">Totaal</span>
                            <div id="editTotal" class="den-val secretariat-total-value">0.00</div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-ghost" id="cancelEditBtn">Annuleren</button>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-check" aria-hidden="true"></i> Opslaan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="rejectModal" class="modal-overlay secretariat-reject-modal">
            <div class="secretariat-modal-box secretariat-reject-box">
                <h3 class="modal-title">Score Afwijzen</h3>
                <p id="rejectModalText" class="modal-description">Weet je zeker dat je deze score wilt afwijzen?</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-ghost" id="cancelRejectBtn">Nee, annuleren</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Ja, afwijzen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script src="../assets/js/pages/secretariat.js"></script>
</body>
</html>
