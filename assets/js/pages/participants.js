const PARTICIPANTS_PER_PAGE = 10;
const PARTICIPANTS_GENDER_FILTER_STORAGE_KEY = 'participants.genderFilter';
let allParticipants = [];
let currentParticipantsPage = 1;
let currentParticipantsGenderFilter = '';

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
const participantsGenderFilter = document.getElementById('participantsGenderFilter');

initializeParticipantsGenderFilter();
participantsGenderFilter.addEventListener('change', handleParticipantsGenderFilterChange);

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
        currentParticipantsPage = 1;
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
        allParticipants = [];
        currentParticipantsPage = 1;
        container.innerHTML = '<p class="empty-state">Geen deelnemers gevonden</p>';
        return;
    }

    allParticipants = result.data;
    const filteredParticipants = getFilteredParticipants(allParticipants, currentParticipantsGenderFilter);

    if (filteredParticipants.length === 0) {
        currentParticipantsPage = 1;
        container.innerHTML = '<p class="empty-state">Geen deelnemers voor dit filter</p>';
        return;
    }

    const totalPages = Math.ceil(filteredParticipants.length / PARTICIPANTS_PER_PAGE);
    if (currentParticipantsPage > totalPages) {
        currentParticipantsPage = totalPages;
    }
    if (currentParticipantsPage < 1) {
        currentParticipantsPage = 1;
    }

    const startIndex = (currentParticipantsPage - 1) * PARTICIPANTS_PER_PAGE;
    const endIndex = startIndex + PARTICIPANTS_PER_PAGE;
    const visibleParticipants = filteredParticipants.slice(startIndex, endIndex);

    let html = '<div class="table-wrapper"><table>';
    html += '<thead><tr><th>Naam</th><th>Lidnummer</th><th>Geslacht</th><th>Groep</th><th>Acties</th></tr></thead>';
    html += '<tbody>';

    visibleParticipants.forEach(participant => {
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

    html += '</tbody></table></div>';

    if (filteredParticipants.length > PARTICIPANTS_PER_PAGE) {
        const prevDisabled = currentParticipantsPage === 1 ? 'disabled' : '';
        const nextDisabled = currentParticipantsPage === totalPages ? 'disabled' : '';
        html += `
            <div class="pagination-bar">
                <div class="pagination-info">
                    Toon ${startIndex + 1}-${Math.min(endIndex, filteredParticipants.length)} van ${filteredParticipants.length} deelnemers
                </div>
                <div class="pagination-controls">
                    <button class="btn btn-ghost btn-sm" onclick="changeParticipantsPage(-1)" ${prevDisabled}><i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Vorige</button>
                    <span class="pagination-indicator">Pagina ${currentParticipantsPage} / ${totalPages}</span>
                    <button class="btn btn-primary btn-sm" onclick="changeParticipantsPage(1)" ${nextDisabled}>Volgende <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

function changeParticipantsPage(direction) {
    const filteredParticipants = getFilteredParticipants(allParticipants, currentParticipantsGenderFilter);
    const totalPages = Math.ceil(filteredParticipants.length / PARTICIPANTS_PER_PAGE);
    const nextPage = currentParticipantsPage + direction;

    if (nextPage < 1 || nextPage > totalPages) {
        return;
    }

    currentParticipantsPage = nextPage;
    loadParticipants();
}

function initializeParticipantsGenderFilter() {
    const savedFilter = localStorage.getItem(PARTICIPANTS_GENDER_FILTER_STORAGE_KEY) || '';
    if (savedFilter === 'Heren' || savedFilter === 'Dames') {
        currentParticipantsGenderFilter = savedFilter;
        participantsGenderFilter.value = savedFilter;
        return;
    }

    currentParticipantsGenderFilter = '';
    participantsGenderFilter.value = '';
    localStorage.removeItem(PARTICIPANTS_GENDER_FILTER_STORAGE_KEY);
}

function handleParticipantsGenderFilterChange(event) {
    const selected = event.target.value;
    if (selected !== 'Heren' && selected !== 'Dames') {
        currentParticipantsGenderFilter = '';
        localStorage.removeItem(PARTICIPANTS_GENDER_FILTER_STORAGE_KEY);
    } else {
        currentParticipantsGenderFilter = selected;
        localStorage.setItem(PARTICIPANTS_GENDER_FILTER_STORAGE_KEY, selected);
    }

    currentParticipantsPage = 1;
    loadParticipants();
}

function getFilteredParticipants(participants, genderFilter) {
    if (genderFilter !== 'Heren' && genderFilter !== 'Dames') {
        return participants;
    }

    return participants.filter((participant) => participant.geslacht === genderFilter);
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
        scoreHistoryContent.innerHTML = '<p class="empty-state empty-state-sm">Scores konden niet geladen worden</p>';
        return;
    }

    if (result.data.length === 0) {
        scoreHistoryContent.innerHTML = '<p class="empty-state empty-state-sm">Nog geen scores voor deze deelnemer</p>';
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
