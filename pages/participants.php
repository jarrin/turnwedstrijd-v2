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
    </div>

    <script src="../assets/js/utils.js"></script>
    <script>
        // Load participants on page load
        loadParticipants();

        let participantToDeleteId = null;
        const deleteConfirmModal = document.getElementById('deleteConfirmModal');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

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

        confirmDeleteBtn.addEventListener('click', executeDeleteParticipant);
        cancelDeleteBtn.addEventListener('click', closeDeleteModal);
        deleteConfirmModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmModal) {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
