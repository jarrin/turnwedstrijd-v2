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
    <script>
        const PARTICIPANTS_PER_PAGE = 10;
        let allParticipants = [];
        let currentParticipantsPage = 1;

        // Load participants on page load
        loadParticipants();
 
        let participantToDeleteId = null;
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        const scoreHistoryModal = document.getElementById('scoreHistoryModal');
        const scoreHistoryContent = document.getElementById('scoreHistoryContent');
        const scoreHistoryTitle = document.getElementById('scoreHistoryTitle');
        const closeScoreHistoryBtn = document.getElementById('closeScoreHistoryBtn');
 
        // Form submission
        document.getElementById('participantForm').addEventListener('submit', async (e) => {
            e.preventDefault();
           
            const name = document.getElementById('name').value;
            const number = document.getElementById('number').value;
            const gender = document.getElementById('gender').value;
            const group = document.getElementById('group').value;
           
            const result = await createParticipant(name, number, group, gender);
           
            if (result.success) {
                showToast('Deelnemer succesvol toegevoegd!', 'success');
                document.getElementById('participantForm').reset();
                currentParticipantsPage = 1;
                loadParticipants();
            } else {
                showToast('Fout: ' + (result.error || 'Onbekende fout'), 'error');
            }
        });
 
        // Load and display participants
        async function loadParticipants() {
            const result = await getParticipants();
            const container = document.getElementById('participantsList');
           
            if (!result.success || !result.data || result.data.length === 0) {
                allParticipants = [];
                currentParticipantsPage = 1;
                container.innerHTML = '<p class="empty-state">Geen deelnemers gevonden</p>';
                return;
            }

            allParticipants = result.data;

            const totalPages = Math.ceil(allParticipants.length / PARTICIPANTS_PER_PAGE);
            if (currentParticipantsPage > totalPages) {
                currentParticipantsPage = totalPages;
            }
            if (currentParticipantsPage < 1) {
                currentParticipantsPage = 1;
            }

            const startIndex = (currentParticipantsPage - 1) * PARTICIPANTS_PER_PAGE;
            const endIndex = startIndex + PARTICIPANTS_PER_PAGE;
            const visibleParticipants = allParticipants.slice(startIndex, endIndex);

            let html = '<div class="table-wrapper"><table>';
            html += '<thead><tr><th>Naam</th><th>Lidnummer</th><th>Geslacht</th><th>Groep</th><th>Acties</th></tr></thead>';
            html += '<tbody>';
           
            visibleParticipants.forEach(participant => {
                html += `<tr>
                    <td>${escapeHtml(participant.name)}</td>
                    <td>${escapeHtml(participant.number)}</td>
                    <td>${escapeHtml(participant.geslacht || '-')}</td>
                    <td>${escapeHtml(participant.group_name || '-')}</td>
                    <td>
                        <button class="btn btn-icon btn-sm btn-primary" title="Scores bekijken" onclick="viewParticipantScoresHandler(${participant.id}, '${encodeURIComponent(participant.name)}', '${encodeURIComponent(participant.number)}')"><i class="fa-solid fa-list"></i></button>
                        <button class="btn btn-icon btn-sm btn-danger" title="Verwijderen" onclick="deleteParticipantHandler(${participant.id})"><i class="fa-solid fa-trash-can"></i></button>
                    </td>
                </tr>`;
            });
           
            html += '</tbody></table></div>';

            if (allParticipants.length > PARTICIPANTS_PER_PAGE) {
                const prevDisabled = currentParticipantsPage === 1 ? 'disabled' : '';
                const nextDisabled = currentParticipantsPage === totalPages ? 'disabled' : '';
                html += `
                    <div class="pagination-bar">
                        <div class="pagination-info">
                            Toon ${startIndex + 1}-${Math.min(endIndex, allParticipants.length)} van ${allParticipants.length} deelnemers
                        </div>
                        <div class="pagination-controls">
                            <button class="btn btn-ghost btn-sm" onclick="changeParticipantsPage(-1)" ${prevDisabled}><i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Vorige</button>
                            <span class="pagination-indicator">Pagina ${currentParticipantsPage} / ${totalPages}</span>
                            <button class="btn btn-primary btn-sm" onclick="changeParticipantsPage(1)" ${nextDisabled}>Volgende <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                `;
            }

            container.innerHTML = html;
        }

        function changeParticipantsPage(direction) {
            const totalPages = Math.ceil(allParticipants.length / PARTICIPANTS_PER_PAGE);
            const nextPage = currentParticipantsPage + direction;

            if (nextPage < 1 || nextPage > totalPages) {
                return;
            }

            currentParticipantsPage = nextPage;
            loadParticipants();
        }
 
        // Delete participant handler
        function deleteParticipantHandler(id) {
            participantToDeleteId = id;
            deleteConfirmModal.style.display = 'flex';
        }
 
        async function executeDeleteParticipant() {
            if (!participantToDeleteId) {
                closeDeleteModal();
                return;
            }
 
            const id = participantToDeleteId;
            closeDeleteModal();
 
                const result = await deleteParticipant(id);
                if (result.success) {
                    showToast('Deelnemer verwijderd', 'success');
                    loadParticipants();
                } else {
                    showToast('Fout bij verwijderen: ' + (result.error || 'Onbekende fout'), 'error');
                }
        }
 
        function closeDeleteModal() {
            participantToDeleteId = null;
            deleteConfirmModal.style.display = 'none';
        }
 
        async function viewParticipantScoresHandler(id, encodedName, encodedNumber) {
            const name = decodeURIComponent(encodedName || '');
            const number = decodeURIComponent(encodedNumber || '');
 
            scoreHistoryTitle.textContent = `Scores per onderdeel - ${name} (#${number})`;
            scoreHistoryContent.innerHTML = '<div class="loading"><div class="spinner"></div>Scores laden...</div>';
            scoreHistoryModal.style.display = 'flex';
 
            const result = await getScoresByParticipant(id);
            if (!result.success || !result.data) {
                scoreHistoryContent.innerHTML = '<p class="empty-state empty-state-sm">Scores konden niet geladen worden</p>';
                return;
            }
 
            if (result.data.length === 0) {
                scoreHistoryContent.innerHTML = '<p class="empty-state empty-state-sm">Nog geen scores voor deze deelnemer</p>';
                return;
            }
 
            let html = '<table>';
            html += '<thead><tr><th>Onderdeel</th><th>D</th><th>E</th><th>N</th><th>Totaal</th><th>Status</th><th>Ingediend</th></tr></thead>';
            html += '<tbody>';
 
            result.data.forEach((score) => {
                html += `<tr>
                    <td>${escapeHtml(score.apparatus_name || '-')}</td>
                    <td>${parseFloat(score.d_score).toFixed(2)}</td>
                    <td>${parseFloat(score.e_score).toFixed(2)}</td>
                    <td>${parseFloat(score.n_score).toFixed(2)}</td>
                    <td><strong>${parseFloat(score.total).toFixed(2)}</strong></td>
                    <td>${escapeHtml(score.status_text || '-')}</td>
                    <td>${formatDate(score.submitted_at)}</td>
                </tr>`;
            });
 
            html += '</tbody></table>';
            scoreHistoryContent.innerHTML = html;
        }
 
        function closeScoreHistoryModal() {
            scoreHistoryModal.style.display = 'none';
        }
 
        confirmDeleteBtn.addEventListener('click', executeDeleteParticipant);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        closeScoreHistoryBtn.addEventListener('click', closeScoreHistoryModal);
        deleteConfirmModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmModal) {
                closeDeleteModal();
            }
        });
        scoreHistoryModal.addEventListener('click', (event) => {
            if (event.target === scoreHistoryModal) {
                closeScoreHistoryModal();
            }
        });
    </script>
</body>
</html>