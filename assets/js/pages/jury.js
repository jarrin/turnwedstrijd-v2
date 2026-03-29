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
