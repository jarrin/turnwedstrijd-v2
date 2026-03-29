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
                <div class="brand-icon"><i class="fa-solid fa-bolt" aria-hidden="true"></i></div>
                <div class="brand-text">
                    <h1>Turnen Wedstrijd Beoordeling Systeem</h1>
                    <p>Jury Invoer &mdash; DEN Score Systeem</p>
                </div>
            </div>
            <nav class="header-nav">
                <a href="../index.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Home</a>
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
    <script src="../assets/js/pages/jury.js"></script>
</body>
</html>