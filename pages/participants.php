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
                <div class="brand-icon">👥</div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Deelnemersbeheer</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php">← Home</a>
            </nav>
        </div>
    </header>
 
    <div class="container">
        <div class="card">
            <h2>Voeg Deelnemer Toe</h2>
            <form id="participantForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
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
                <button type="submit" class="btn btn-primary">➕ Deelnemer Toevoegen</button>
            </form>
        </div>
 
        <div class="card">
            <h2>Deelnemerslijst</h2>
            <div id="participantsList" class="loading">
                <div class="spinner"></div>
                Deelnemers laden...
            </div>
        </div>
 
        <div id="deleteConfirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2000;align-items:center;justify-content:center;">
            <div style="background:var(--bg-card);border:1px solid var(--border-light);width:min(480px,92vw);padding:1.5rem;border-radius:16px;">
                <h3 style="margin:0 0 .75rem 0;color:var(--text-primary);font-size:1.05rem;font-weight:700;">Deelnemer verwijderen</h3>
                <p style="margin:0 0 1.25rem 0;color:var(--text-secondary);">Ben je zeker dat je deze deelnemer en alle bijbehorende scores wilt verwijderen?</p>
                <div style="display:flex;gap:.6rem;justify-content:flex-end;padding-top:1rem;border-top:1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" id="cancelDeleteBtn">Annuleren</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Ja, verwijderen</button>
                </div>
            </div>
        </div>
 
        <div id="scoreHistoryModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2000;align-items:center;justify-content:center;">
            <div style="background:var(--bg-card);border:1px solid var(--border-light);width:min(920px,95vw);padding:1.5rem;border-radius:16px;max-height:86vh;overflow:auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:.8rem;">
                    <h3 id="scoreHistoryTitle" style="margin:0;color:var(--text-primary);font-size:1.05rem;font-weight:700;">Scores per onderdeel</h3>
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
                container.innerHTML = '<p style="text-align:center;padding:2.5rem;color:var(--text-secondary);">Geen deelnemers gevonden</p>';
                return;
            }
           
            let html = '<table>';
            html += '<thead><tr><th>Naam</th><th>Lidnummer</th><th>Geslacht</th><th>Groep</th><th>Acties</th></tr></thead>';
            html += '<tbody>';
           
            result.data.forEach(participant => {
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
           
            html += '</tbody></table>';
            container.innerHTML = html;
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
                scoreHistoryContent.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--text-secondary);">Scores konden niet geladen worden</p>';
                return;
            }
 
            if (result.data.length === 0) {
                scoreHistoryContent.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--text-secondary);">Nog geen scores voor deze deelnemer</p>';
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