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
    <script>
        const SCORES_PER_PAGE = 6;
        let allPendingScores = [];
        let currentScoresPage = 1;

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
                allPendingScores = [];
                currentScoresPage = 1;
                container.innerHTML = '<p class="empty-state">Geen wachtende scores</p>';
                return;
            }

            allPendingScores = result.data;
            const totalPages = Math.ceil(allPendingScores.length / SCORES_PER_PAGE);

            if (currentScoresPage > totalPages) {
                currentScoresPage = totalPages;
            }
            if (currentScoresPage < 1) {
                currentScoresPage = 1;
            }

            const startIndex = (currentScoresPage - 1) * SCORES_PER_PAGE;
            const endIndex = startIndex + SCORES_PER_PAGE;
            const visibleScores = allPendingScores.slice(startIndex, endIndex);
            
            let html = '';
            visibleScores.forEach(score => {
                html += `
                    <div class="card pending-score-card">
                        <div class="pending-score-layout">
                            <div>
                                <div class="pending-score-name">${escapeHtml(score.name)} <span class="pending-score-number">#${escapeHtml(score.number)}</span></div>
                                <div class="pending-score-apparatus"><i class="fa-solid fa-medal" aria-hidden="true"></i> <strong>${escapeHtml(score.apparatus_name)}</strong></div>
                                <div class="pending-score-submitted">Ingediend: ${formatDate(score.submitted_at)}</div>
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
                        <div class="pending-score-actions">
                            <button class="btn btn-success btn-sm" onclick="approveScoreHandler(${score.id})"><i class="fa-solid fa-check" aria-hidden="true"></i> Goedkeuren</button>
                            <button class="btn btn-danger btn-sm" onclick="rejectScoreHandler(${score.id})"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Afwijzen</button>
                            <button class="btn btn-warning btn-sm" onclick="editScoreHandler(${score.id}, ${score.d_score}, ${score.e_score}, ${score.n_score}, '${encodeURIComponent(score.name)}', '${encodeURIComponent(score.number)}', '${encodeURIComponent(score.apparatus_name)}')"><i class="fa-solid fa-pen" aria-hidden="true"></i> Bewerken</button>
                        </div>
                    </div>
                `;
            });

            if (allPendingScores.length > SCORES_PER_PAGE) {
                const prevDisabled = currentScoresPage === 1 ? 'disabled' : '';
                const nextDisabled = currentScoresPage === totalPages ? 'disabled' : '';

                html += `
                    <div class="pagination-bar pagination-bar-compact">
                        <div class="pagination-info">
                            Toon ${startIndex + 1}-${Math.min(endIndex, allPendingScores.length)} van ${allPendingScores.length} wachtende scores
                        </div>
                        <div class="pagination-controls">
                            <button class="btn btn-ghost btn-sm" onclick="changeScoresPage(-1)" ${prevDisabled}><i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Vorige</button>
                            <span class="pagination-indicator">Pagina ${currentScoresPage} / ${totalPages}</span>
                            <button class="btn btn-primary btn-sm" onclick="changeScoresPage(1)" ${nextDisabled}>Volgende <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                `;
            }
            
            container.innerHTML = html;
        }

        function changeScoresPage(direction) {
            const totalPages = Math.ceil(allPendingScores.length / SCORES_PER_PAGE);
            const nextPage = currentScoresPage + direction;

            if (nextPage < 1 || nextPage > totalPages) {
                return;
            }

            currentScoresPage = nextPage;
            refreshScores();
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
