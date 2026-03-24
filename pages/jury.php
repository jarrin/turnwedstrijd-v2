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
                <div style="display:flex;flex-wrap:wrap;align-items:end;gap:1rem;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
                    <div class="form-group" style="margin:0;min-width:300px;flex:1;">
                        <label for="juryApparatusSelect">Jury Toestel (wordt onthouden)</label>
                        <select id="juryApparatusSelect" required>
                            <option value="">-- Kies jouw toestel --</option>
                        </select>
                    </div>
                    <button type="button" id="clearJuryApparatus" class="btn btn-ghost btn-sm">Wis toestelkeuze</button>
                    <div id="juryAssignmentInfo" style="font-size:.85rem;color:var(--text-secondary);padding:.55rem .8rem;border:1px solid var(--border);border-radius:8px;">Geen toestel geselecteerd</div>
                </div>
 
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
                            <input type="number" id="dScore" name="dScore" step="0.1" min="0" max="20" placeholder="0.0" required>
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
        const JURY_APPARATUS_STORAGE_KEY = 'jury.assignedApparatusId';
        let availableApparatus = [];
        let assignedApparatus = null;
 
        initializeJuryPage();
 
        // Real-time score calculation
        document.getElementById('dScore').addEventListener('input', updateTotal);
        document.getElementById('eScore').addEventListener('input', updateTotal);
        document.getElementById('nScore').addEventListener('input', updateTotal);
        document.getElementById('juryApparatusSelect').addEventListener('change', handleJuryApparatusChange);
        document.getElementById('clearJuryApparatus').addEventListener('click', clearAssignedApparatus);
 
        // Form submission
        document.getElementById('scoreForm').addEventListener('submit', async (e) => {
            e.preventDefault();
           
            const participantId = document.getElementById('participant').value;
            const apparatusId = assignedApparatus ? assignedApparatus.id : document.getElementById('apparatus').value;
            const dScore = document.getElementById('dScore').value;
            const eScore = document.getElementById('eScore').value;
            const nScore = document.getElementById('nScore').value;
 
            if (!assignedApparatus) {
                showCenterMessage('Kies eerst jouw jury-toestel.', 'error');
                return;
            }
           
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
                if (assignedApparatus) {
                    await setAssignedApparatus(assignedApparatus);
                }
                updateTotal();
            } else {
                showCenterMessage('Fout: ' + (result.error || 'Onbekende fout'), 'error');
            }
        });
 
        async function initializeJuryPage() {
            const apparatusResponse = await getApparatus();
            if (!apparatusResponse.success || !apparatusResponse.data) {
                showCenterMessage('Toestellen laden mislukt', 'error');
                return;
            }
 
            availableApparatus = apparatusResponse.data;
            populateJuryApparatusSelector();
            restoreAssignedApparatus();
        }
 
        function populateJuryApparatusSelector() {
            const select = document.getElementById('juryApparatusSelect');
            select.innerHTML = '<option value="">-- Kies jouw toestel --</option>';
 
            availableApparatus.forEach((apparatus) => {
                const option = document.createElement('option');
                option.value = apparatus.id;
                option.textContent = `${apparatus.name} (${apparatus.geslacht})`;
                select.appendChild(option);
            });
        }
 
        function restoreAssignedApparatus() {
            const savedId = localStorage.getItem(JURY_APPARATUS_STORAGE_KEY);
            if (!savedId) {
                updateAssignmentInfo();
                lockFormWithoutAssignment();
                return;
            }
 
            const apparatus = availableApparatus.find(a => String(a.id) === String(savedId));
            if (!apparatus) {
                clearAssignedApparatus();
                return;
            }
 
            document.getElementById('juryApparatusSelect').value = String(apparatus.id);
            setAssignedApparatus(apparatus);
        }
 
        function handleJuryApparatusChange(event) {
            const selectedId = event.target.value;
            if (!selectedId) {
                clearAssignedApparatus();
                return;
            }
 
            const apparatus = availableApparatus.find(a => String(a.id) === String(selectedId));
            if (!apparatus) {
                clearAssignedApparatus();
                return;
            }
 
            setAssignedApparatus(apparatus);
        }
 
        function clearAssignedApparatus() {
            localStorage.removeItem(JURY_APPARATUS_STORAGE_KEY);
            assignedApparatus = null;
            document.getElementById('juryApparatusSelect').value = '';
            updateAssignmentInfo();
            lockFormWithoutAssignment();
        }
 
        function lockFormWithoutAssignment() {
            const participantSelect = document.getElementById('participant');
            const apparatusSelect = document.getElementById('apparatus');
 
            participantSelect.innerHTML = '<option value="">-- Selecteer eerst een jury-toestel --</option>';
            participantSelect.disabled = true;
 
            apparatusSelect.innerHTML = '<option value="">-- Selecteer eerst een jury-toestel --</option>';
            apparatusSelect.disabled = true;
        }
 
        async function setAssignedApparatus(apparatus) {
            assignedApparatus = apparatus;
            localStorage.setItem(JURY_APPARATUS_STORAGE_KEY, String(apparatus.id));
            updateAssignmentInfo();
 
            const apparatusSelect = document.getElementById('apparatus');
            apparatusSelect.innerHTML = '';
            const option = document.createElement('option');
            option.value = apparatus.id;
            option.textContent = `${apparatus.name} (${apparatus.geslacht})`;
            option.selected = true;
            apparatusSelect.appendChild(option);
            apparatusSelect.disabled = true;
 
            await loadParticipantsForAssignedGender(apparatus.geslacht);
        }
 
        async function loadParticipantsForAssignedGender(gender) {
            const participants = await getParticipantsByGender(gender);
            const select = document.getElementById('participant');
            select.innerHTML = '<option value="">-- Selecteer deelnemer --</option>';
 
            if (!participants.success || !participants.data || participants.data.length === 0) {
                select.innerHTML = '<option value="">-- Geen deelnemers voor dit geslacht --</option>';
                select.disabled = true;
                return;
            }
 
            participants.data.forEach((participant) => {
                const option = document.createElement('option');
                option.value = participant.id;
                option.textContent = `${participant.name} (#${participant.number})`;
                select.appendChild(option);
            });
 
            select.disabled = false;
        }
 
        function updateAssignmentInfo() {
            const info = document.getElementById('juryAssignmentInfo');
            if (!assignedApparatus) {
                info.textContent = 'Geen toestel geselecteerd';
                return;
            }
 
            info.textContent = `Actief: ${assignedApparatus.name} (${assignedApparatus.geslacht})`;
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