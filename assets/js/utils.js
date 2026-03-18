/**
 * Turnen Score Systeem - JavaScript Utilities
 */

// API Base URL — determine project root from this script location so
// requests always target the correct `/api` folder regardless of page depth
const API_BASE = (() => {
    const getScriptSrc = () => {
        if (document.currentScript && document.currentScript.src) return document.currentScript.src;
        const scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1] && scripts[scripts.length - 1].src || '';
    };

    const scriptSrc = getScriptSrc();
    let basePath = '';

    if (scriptSrc) {
        basePath = scriptSrc.replace(/\/assets\/js\/.*$/i, '');
    } else {
        // fallback: remove /pages/... from pathname when possible
        basePath = window.location.pathname.replace(/\/pages\/.*$/i, '');
        basePath = window.location.origin + basePath;
    }

    basePath = basePath.replace(/\/$/, '');
    return basePath + '/api';
})();

/**
 * Make API request
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const url = `${API_BASE}/${endpoint}`;
        const response = await fetch(url, options);

        const contentType = response.headers.get('content-type') || '';

        // If HTTP status not ok, try to capture response text for debugging
        if (!response.ok) {
            const text = await response.text();
            console.error('API Error Response', { url, status: response.status, body: text });
            // Try to parse JSON from text if possible
            try {
                const json = JSON.parse(text);
                return json;
            } catch (e) {
                return { success: false, error: `HTTP ${response.status}`, status: response.status, body: text, url };
            }
        }

        // If response is JSON, parse it
        if (contentType.includes('application/json')) {
            return await response.json();
        }

        // Non-JSON response (likely HTML) — capture for debugging
        const text = await response.text();
        console.error('API returned non-JSON', { url, contentType, body: text });
        return { success: false, error: 'Invalid JSON response', status: response.status, body: text, url, contentType };
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Get all participants
 */
async function getParticipants() {
    return apiRequest('participants.php?action=list');
}

/**
 * Create participant
 */
async function createParticipant(name, number, group, gender = 'Heren') {
    return apiRequest('participants.php?action=create', 'POST', {
        name: name,
        number: number,
        group: group,
        gender: gender
    });
}

/**
 * Update participant
 */
async function updateParticipant(id, name, number, group, gender = 'Heren') {
    return apiRequest(`participants.php?action=update&id=${id}`, 'PUT', {
        name: name,
        number: number,
        group: group,
        gender: gender
    });
}

/**
 * Delete participant
 */
async function deleteParticipant(id) {
    return apiRequest(`participants.php?action=delete&id=${id}`, 'DELETE');
}

/**
 * Get all apparatus
 */
async function getApparatus() {
    return apiRequest('apparatus.php?action=list');
}

/**
 * Submit score
 */
async function submitScore(participantId, apparatusId, dScore, eScore, nScore) {
    return apiRequest('scores.php?action=submit', 'POST', {
        participant_id: participantId,
        apparatus_id: apparatusId,
        d_score: parseFloat(dScore),
        e_score: parseFloat(eScore),
        n_score: parseFloat(nScore)
    });
}

/**
 * Get pending scores
 */
async function getPendingScores() {
    return apiRequest('scores.php?action=pending');
}

/**
 * Get approved scores
 */
async function getApprovedScores() {
    return apiRequest('scores.php?action=approved');
}

/**
 * Get top 10 scores
 */
async function getTop10Scores() {
    return apiRequest('scores.php?action=top10');
}

/**
 * Get latest approved score for main display
 */
async function getCurrentScore() {
    return apiRequest('scores.php?action=current');
}

/**
 * Approve score
 */
async function approveScore(id, notes = '') {
    return apiRequest(`scores.php?action=approve&id=${id}`, 'PUT', {
        notes: notes
    });
}

/**
 * Reject score
 */
async function rejectScore(id) {
    return apiRequest(`scores.php?action=reject&id=${id}`, 'PUT');
}

/**
 * Edit score
 */
async function editScore(id, dScore, eScore, nScore, notes = '') {
    return apiRequest(`scores.php?action=edit&id=${id}`, 'PUT', {
        d_score: parseFloat(dScore),
        e_score: parseFloat(eScore),
        n_score: parseFloat(nScore),
        notes: notes
    });
}

/**
 * Calculate total score
 */
function calculateTotal(dScore, eScore, nScore) {
    const total = parseFloat(dScore) + parseFloat(eScore) - parseFloat(nScore);
    return Math.max(0, total).toFixed(2);
}

/**
 * Format currency/score
 */
function formatScore(score) {
    return parseFloat(score).toFixed(2);
}

/**
 * Escape HTML entities for safe rendering in template literals
 */
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.textContent = message;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.maxWidth = '400px';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

/**
 * Reload data with interval
 */
function autoRefresh(callback, interval = 2000) {
    callback();
    return setInterval(callback, interval);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('nl-NL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Validate score input
 */
function validateScore(dScore, eScore, nScore) {
    const d = parseFloat(dScore);
    const e = parseFloat(eScore);
    const n = parseFloat(nScore);
    
    if (isNaN(d) || isNaN(e) || isNaN(n)) {
        return { valid: false, error: 'Alle scores moeten getallen zijn' };
    }
    
    if (d < 0 || e < 0 || n < 0) {
        return { valid: false, error: 'Scores kunnen niet negatief zijn' };
    }
    
    if (d > 10 || e > 10 || n > 10) {
        return { valid: false, error: 'Scores kunnen niet hoger dan 10 zijn' };
    }
    
    return { valid: true };
}
