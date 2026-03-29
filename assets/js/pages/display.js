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
