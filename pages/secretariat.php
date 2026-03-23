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
                <div class="brand-icon">✅</div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Secretariaat &mdash; Score Controle</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php">← Home</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>Wachtende Scores</h2>
            <div id="scoresList" class="loading">
                <div class="spinner"></div>
                Scores laden...
            </div>
        </div>

        <div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2200;align-items:center;justify-content:center;backdrop-filter:blur(5px);">
            <div style="background:var(--bg-card);border:1px solid var(--border-light);width:min(760px,94vw);padding:1.5rem;border-radius:16px;box-shadow:0 24px 72px rgba(0,0,0,.6);">
                <h3 style="margin:0 0 1.25rem 0;color:var(--text-primary);font-size:1.05rem;font-weight:700;">Score Bewerken</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);">
                    <div>
                        <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem;">Kandidaat</div>
                        <div id="editCandidate" style="font-size:1rem;font-weight:700;color:var(--text-primary);">-</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.3rem;">Onderdeel</div>
                        <div id="editApparatus" style="font-size:1rem;font-weight:700;color:var(--text-primary);">-</div>
                    </div>
                </div>

                <form id="editScoreForm">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem;">
                        <div class="den-score-item d">
                            <span class="den-label">D-Score</span>
                            <input type="number" id="editDScore" step="0.1" min="0" max="20" required style="margin-top:.5rem;">
                        </div>
                        <div class="den-score-item e">
                            <span class="den-label">E-Score</span>
                            <input type="number" id="editEScore" step="0.1" min="0" max="10" required style="margin-top:.5rem;">
                        </div>
                        <div class="den-score-item n">
                            <span class="den-label">N-Score</span>
                            <input type="number" id="editNScore" step="0.1" min="0" max="10" required style="margin-top:.5rem;">
                        </div>
                        <div class="den-score-item total" style="text-align:center;">
                            <span class="den-label">Totaal</span>
                            <div id="editTotal" class="den-val" style="margin-top:.5rem;">0.00</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:.6rem;justify-content:flex-end;padding-top:1rem;border-top:1px solid var(--border);">
                        <button type="button" class="btn btn-ghost" id="cancelEditBtn">Annuleren</button>
                        <button type="submit" class="btn btn-success">&#10003; Opslaan</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:2200;align-items:center;justify-content:center;">
            <div style="background:var(--bg-card);border:1px solid var(--border-light);width:min(520px,92vw);padding:1.5rem;border-radius:16px;">
                <h3 style="margin:0 0 .75rem 0;color:var(--text-primary);font-size:1.05rem;font-weight:700;">Score Afwijzen</h3>
                <p id="rejectModalText" style="margin:0 0 1.25rem 0;color:var(--text-secondary);">Weet je zeker dat je deze score wilt afwijzen?</p>
                <div style="display:flex;gap:.6rem;justify-content:flex-end;padding-top:1rem;border-top:1px solid var(--border);">
                    <button type="button" class="btn btn-ghost" id="cancelRejectBtn">Nee, annuleren</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectBtn">Ja, afwijzen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script>
        // Load scores on page load
        refreshScores();

        let currentEditScoreId = null;
        let isEditModalOpen = false;
        let currentRejectScoreId = null;

        const editModal = document.getElementById('editModal');
        const editScoreForm = document.getElementById('editScoreForm');
        const editDScore = document.getElementById('editDScore');
        const editEScore = document.getElementById('editEScore');
        const editNScore = document.getElementById('editNScore');
        const editTotal = document.getElementById('editTotal');
        const editCandidate = document.getElementById('editCandidate');
        const editApparatus = document.getElementById('editApparatus');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const rejectModal = document.getElementById('rejectModal');
        const rejectModalText = document.getElementById('rejectModalText');
        const confirmRejectBtn = document.getElementById('confirmRejectBtn');
        const cancelRejectBtn = document.getElementById('cancelRejectBtn');
        
        // Auto-refresh every 2 seconds
        setInterval(refreshScores, 2000);

        // Refresh scores
        async function refreshScores() {
            if (isEditModalOpen) {
                return;
            }

            const result = await getPendingScores();
            const container = document.getElementById('scoresList');
            
            if (!result.success || !result.data || result.data.length === 0) {
                container.innerHTML = '<p style="text-align:center;padding:2.5rem;color:var(--text-secondary);">Geen wachtende scores</p>';
                return;
            }
            
            let html = '';
            result.data.forEach(score => {
                html += `
                    <div class="card" style="border-left:4px solid var(--warning);">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:1.25rem;align-items:start;">
                            <div>
                                <div style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:.4rem;">${escapeHtml(score.name)} <span style="font-weight:400;color:var(--text-muted);font-size:.9rem;">#${escapeHtml(score.number)}</span></div>
                                <div style="font-size:.85rem;color:var(--text-secondary);margin-bottom:.25rem;">&#127941; <strong>${escapeHtml(score.apparatus_name)}</strong></div>
                                <div style="font-size:.78rem;color:var(--text-muted);">Ingediend: ${formatDate(score.submitted_at)}</div>
                            </div>
                            <div class="den-scores">
                                <div class="den-score-item d">
                                    <span class="den-label">D-Score</span>
                                    <span class="den-val">${parseFloat(score.d_score).toFixed(2)}</span>
                                </div>
                                <div class="den-score-item e">
                                    <span class="den-label">E-Score</span>
                                    <span class="den-val">${parseFloat(score.e_score).toFixed(2)}</span>
                                </div>
                                <div class="den-score-item n">
                                    <span class="den-label">N-Score</span>
                                    <span class="den-val">${parseFloat(score.n_score).toFixed(2)}</span>
                                </div>
                                <div class="den-score-item total">
                                    <span class="den-label">Totaal</span>
                                    <span class="den-val">${parseFloat(score.total).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:.75rem;padding-top:1rem;border-top:1px solid var(--border);">
                            <button class="btn btn-success btn-sm" onclick="approveScoreHandler(${score.id})">&#10003; Goedkeuren</button>
                            <button class="btn btn-danger btn-sm" onclick="rejectScoreHandler(${score.id})">&#10007; Afwijzen</button>
                            <button class="btn btn-warning btn-sm" onclick="editScoreHandler(${score.id}, ${score.d_score}, ${score.e_score}, ${score.n_score}, '${encodeURIComponent(score.name)}', '${encodeURIComponent(score.number)}', '${encodeURIComponent(score.apparatus_name)}')">&#9998; Bewerken</button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        // Approve score (handler) — calls the API helper in `assets/js/utils.js`
        async function approveScoreHandler(id) {
            const result = await approveScore(id);
            if (result.success) {
                showToast('Score goedgekeurd!', 'success');
                refreshScores();
            } else {
                showToast('Fout bij goedkeuren', 'error');
            }
        }

        // Reject score
        function rejectScoreHandler(id) {
            currentRejectScoreId = id;
            rejectModalText.textContent = 'Weet je zeker dat je deze score wilt afwijzen?';
            rejectModal.style.display = 'flex';
        }

        async function confirmRejectScore() {
            if (!currentRejectScoreId) {
                closeRejectModal();
                return;
            }

            const id = currentRejectScoreId;
            closeRejectModal();

            const result = await rejectScore(id);
            if (result.success) {
                showToast('Score afgewezen', 'success');
                refreshScores();
            } else {
                showToast('Fout bij afwijzen', 'error');
            }
        }

        function closeRejectModal() {
            currentRejectScoreId = null;
            rejectModal.style.display = 'none';
        }

        confirmRejectBtn.addEventListener('click', confirmRejectScore);
        cancelRejectBtn.addEventListener('click', closeRejectModal);
        rejectModal.addEventListener('click', (event) => {
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        });

        // Edit score
        function editScoreHandler(id, d, e, n, nameEncoded, numberEncoded, apparatusEncoded) {
            currentEditScoreId = id;
            isEditModalOpen = true;

            editDScore.value = parseFloat(d).toFixed(2);
            editEScore.value = parseFloat(e).toFixed(2);
            editNScore.value = parseFloat(n).toFixed(2);

            const name = decodeURIComponent(nameEncoded || '');
            const number = decodeURIComponent(numberEncoded || '');
            const apparatus = decodeURIComponent(apparatusEncoded || '');

            editCandidate.textContent = `${name} (#${number})`;
            editApparatus.textContent = apparatus;

            updateEditTotal();
            editModal.style.display = 'flex';
        }

        async function submitEditScore(event) {
            event.preventDefault();

            const newD = editDScore.value;
            const newE = editEScore.value;
            const newN = editNScore.value;

            const validation = validateScore(newD, newE, newN);
            if (!validation.valid) {
                showToast(validation.error, 'error');
                return;
            }

            const result = await editScore(currentEditScoreId, newD, newE, newN, '');
            if (result.success) {
                showToast(`Score bewerkt! Nieuw totaal: ${result.total}`, 'success');
                closeEditModal();
                refreshScores();
            } else {
                showToast('Fout bij bewerken', 'error');
            }
        }

        function updateEditTotal() {
            const d = parseFloat(editDScore.value) || 0;
            const e = parseFloat(editEScore.value) || 0;
            const n = parseFloat(editNScore.value) || 0;
            editTotal.textContent = Math.max(0, d + e - n).toFixed(2);
        }

        function closeEditModal() {
            isEditModalOpen = false;
            currentEditScoreId = null;
            editModal.style.display = 'none';
        }

        editScoreForm.addEventListener('submit', submitEditScore);
        cancelEditBtn.addEventListener('click', closeEditModal);
        editDScore.addEventListener('input', updateEditTotal);
        editEScore.addEventListener('input', updateEditTotal);
        editNScore.addEventListener('input', updateEditTotal);
        editModal.addEventListener('click', (event) => {
            if (event.target === editModal) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
