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
    <script>
        let lastRenderedScoreKey = '';
        const displayGenderFilter = document.getElementById('displayGenderFilter');
 
        displayGenderFilter.addEventListener('change', () => {
            lastRenderedScoreKey = '';
            refreshDisplay();
        });
 
        // Load display on page load
        refreshDisplay();
       
        // Auto-refresh every 3 seconds
        setInterval(refreshDisplay, 3000);
 
        // Refresh display
        async function refreshDisplay() {
            await loadCurrentScore();
            await loadLeaderboard();
        }
 
        // Load current score
        async function loadCurrentScore() {
            const selectedGender = displayGenderFilter.value;
            const result = await getCurrentScore(selectedGender);
            const container = document.getElementById('currentScoreDisplay');
           
            if (!result.success || !result.data) {
                container.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--text-secondary);">Geen score beschikbaar</p>';
                return;
            }
           
            const score = result.data;
            const currentScoreKey = `${score.id || ''}_${score.submitted_at || ''}`;
            const isNewScore = currentScoreKey !== '' && currentScoreKey !== lastRenderedScoreKey;
            lastRenderedScoreKey = currentScoreKey;
           
            let html = `
                <div class="${isNewScore ? 'score-updated-flash' : ''}" style="padding:2rem 1.5rem 1.5rem">
                    <div class="participant-name">${escapeHtml(score.name)}</div>
                    <div class="apparatus-name"><i class="fa-solid fa-medal" aria-hidden="true"></i> ${escapeHtml(score.apparatus_name)}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);margin:-1rem 0 1.2rem 0;">Laatst bijgewerkt: ${formatDate(score.submitted_at)}</div>
                    <div class="den-scores" style="margin-top:1.25rem;">
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
                            <span class="den-val" style="font-size:2.5rem;">${parseFloat(score.total).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
           
            container.innerHTML = html;
        }
 
        // Load leaderboard
        async function loadLeaderboard() {
            const selectedGender = displayGenderFilter.value;
            const result = await getTop10Scores(selectedGender);
            const container = document.getElementById('leaderboardDisplay');
           
            if (!result.success || !result.data || result.data.length === 0) {
                container.innerHTML = '<p style="text-align:center;padding:2rem;color:var(--text-secondary);">Nog geen scores beschikbaar</p>';
                return;
            }
           
            let html = '';
            result.data.forEach((score, index) => {
                let rankClass = '';
                let rankSymbol = (index + 1) + '.';
               
                if (index === 0) {
                    rankClass = 'gold';
                    rankSymbol = '<i class="fa-solid fa-medal" aria-hidden="true"></i>';
                } else if (index === 1) {
                    rankClass = 'silver';
                    rankSymbol = '<i class="fa-solid fa-medal" aria-hidden="true"></i>';
                } else if (index === 2) {
                    rankClass = 'bronze';
                    rankSymbol = '<i class="fa-solid fa-medal" aria-hidden="true"></i>';
                }
               
                html += `
                    <div class="leaderboard-item">
                        <div class="rank ${rankClass}">${rankSymbol}</div>
                        <div style="flex:1;margin-left:1rem;color:var(--text-primary);">
                            <strong>${escapeHtml(score.name)}</strong> <span style="color:var(--text-muted);font-size:.85rem;">#${escapeHtml(score.number)}</span>
                        </div>
                        <div style="color:var(--text-muted);margin:0 1rem;font-size:.85rem;">${escapeHtml(score.group_name || '-')}</div>
                        <div class="score">${parseFloat(score.total).toFixed(2)}</div>
                    </div>
                `;
            });
           
            container.innerHTML = html;
        }
    </script>
</body>
</html>