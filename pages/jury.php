<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jury Invoer - Turnen Score Systeem</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <div class="header-brand">
                <div class="brand-icon">⚡</div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Jury Invoer &mdash; DEN Score Systeem</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php">← Home</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <h2>DEN-Score Invoer</h2>
            <form id="scoreForm">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="participant">Deelnemer</label>
                        <select id="participant" name="participant" required>
                            <option value="">-- Selecteer deelnemer --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="apparatus">Toestel</label>
                        <select id="apparatus" name="apparatus" required>
                            <option value="">-- Selecteer toestel --</option>
                        </select>
                    </div>
                </div>

                <div class="score-display">
                    <h2>DEN-Scores</h2>
                    <div class="score-values">
                        <div class="score-value d-score">
                            <label>D-Score (Difficulty)</label>
                            <input type="number" id="dScore" name="dScore" step="0.1" min="0" max="10" placeholder="0.0" required>
                            <div class="value" id="dValue">0.0</div>
                        </div>
                        <div class="score-value e-score">
                            <label>E-Score (Execution)</label>
                            <input type="number" id="eScore" name="eScore" step="0.1" min="0" max="10" placeholder="0.0" required>
                            <div class="value" id="eValue">0.0</div>
                        </div>
                        <div class="score-value n-score">
                            <label>N-Score (Neutral deduction)</label>
                            <input type="number" id="nScore" name="nScore" step="0.1" min="0" max="10" placeholder="0.0" required>
                            <div class="value" id="nValue">0.0</div>
                        </div>
                        <div class="score-value total-score">
                            <label>Totaal (D+E&minus;N)</label>
                            <div class="value" id="totalValue">0.00</div>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:.75rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-icon btn-success btn-lg" title="Score Verzenden"><i class="fa-solid fa-floppy-disk"></i></button>
                    <button type="reset" class="btn btn-icon btn-ghost" title="Reset"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
            </form>
        </div>

        <div id="statusMessage" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2200;align-items:center;justify-content:center;backdrop-filter:blur(5px);">
            <div id="statusMessageBox" style="min-width:320px;max-width:90vw;background:var(--bg-card);border:1px solid var(--border-light);border-radius:14px;padding:1.75rem 2rem;text-align:center;">
                <div id="statusMessageText" style="font-size:1rem;font-weight:600;color:var(--text-primary);"></div>
            </div>
        </div>
    </div>

    <script src="../assets/js/utils.js"></script>
    <script>
        // Load data on page load
        loadData();

        // Real-time score calculation
        document.getElementById('dScore').addEventListener('input', updateTotal);
        document.getElementById('eScore').addEventListener('input', updateTotal);
        document.getElementById('nScore').addEventListener('input', updateTotal);

        // Form submission
        document.getElementById('scoreForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const participantId = document.getElementById('participant').value;
            const apparatusId = document.getElementById('apparatus').value;
            const dScore = document.getElementById('dScore').value;
            const eScore = document.getElementById('eScore').value;
            const nScore = document.getElementById('nScore').value;
            
            // Validate scores
            const validation = validateScore(dScore, eScore, nScore);
            if (!validation.valid) {
                showCenterMessage(validation.error, 'error');
                return;
            }
            
            const result = await submitScore(participantId, apparatusId, dScore, eScore, nScore);
            
            if (result.success) {
                showCenterMessage(`Score verzonden! Totaal: ${result.total}`, 'success');
                document.getElementById('scoreForm').reset();
                updateTotal();
            } else {
                showCenterMessage('Fout: ' + (result.error || 'Onbekende fout'), 'error');
            }
        });

        // Load participants and apparatus
        async function loadData() {
            const participants = await getParticipants();
            const apparatus = await getApparatus();
            
            if (participants.success && participants.data) {
                const select = document.getElementById('participant');
                participants.data.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = `${p.name} (#${p.number})`;
                    select.appendChild(option);
                });
            }
            
            if (apparatus.success && apparatus.data) {
                const select = document.getElementById('apparatus');
                apparatus.data.forEach(a => {
                    const option = document.createElement('option');
                    option.value = a.id;
                    option.textContent = a.name;
                    select.appendChild(option);
                });
            }
        }

        // Update total score
        function updateTotal() {
            const d = parseFloat(document.getElementById('dScore').value) || 0;
            const e = parseFloat(document.getElementById('eScore').value) || 0;
            const n = parseFloat(document.getElementById('nScore').value) || 0;
            const total = Math.max(0, d + e - n);
            
            document.getElementById('dValue').textContent = d.toFixed(1);
            document.getElementById('eValue').textContent = e.toFixed(1);
            document.getElementById('nValue').textContent = n.toFixed(1);
            document.getElementById('totalValue').textContent = total.toFixed(2);
        }

        function showCenterMessage(message, type = 'success') {
            const overlay = document.getElementById('statusMessage');
            const box = document.getElementById('statusMessageBox');
            const text = document.getElementById('statusMessageText');

            text.textContent = message;

            if (type === 'error') {
                box.style.borderTop = '4px solid var(--danger)';
                box.style.borderBottom = '';
            } else {
                box.style.borderTop = '4px solid var(--success)';
                box.style.borderBottom = '';
            }

            overlay.style.display = 'flex';

            setTimeout(() => {
                overlay.style.display = 'none';
            }, 1800);
        }
    </script>
</body>
</html>
