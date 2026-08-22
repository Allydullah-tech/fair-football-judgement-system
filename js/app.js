// ============================================
// FFJS — app.js
// Central JS: session, fetch helper, renderers
// ============================================

// ── API base paths per role ──────────────────
const API = {
    auth: 'backend/api/auth/',
    admin: 'backend/api/admin/',
    referee: 'backend/api/referee/',
    manager: 'backend/api/manager/',
    viewer: 'backend/api/viewer/',
};

// For pages inside subfolders (admin/, referee/, manager/)
const isSubPage = window.location.pathname.includes('/admin/') ||
    window.location.pathname.includes('/referee/') ||
    window.location.pathname.includes('/manager/') ||
    window.location.pathname.includes('/viewer/');

const BASE = isSubPage ? '../backend/api/' : 'backend/api/';

// ── Session helpers ──────────────────────────
function getSessionUser() {
    return {
        id: sessionStorage.getItem('user_id'),
        name: sessionStorage.getItem('user_name'),
        email: sessionStorage.getItem('user_email'),
        role: sessionStorage.getItem('user_role'),
    };
}

function setSessionUser(user) {
    sessionStorage.setItem('user_id', user.id);
    sessionStorage.setItem('user_name', user.full_name);
    sessionStorage.setItem('user_email', user.email);
    sessionStorage.setItem('user_role', user.role);
}

function clearSession() {
    sessionStorage.clear();
}

function loadUserSession() {
    const user = getSessionUser();
    const nameEl = document.getElementById('userName');
    const roleEl = document.getElementById('userRole');
    if (nameEl) nameEl.textContent = user.name || 'User';
    if (roleEl) roleEl.textContent = user.role ?
        user.role.charAt(0).toUpperCase() + user.role.slice(1) :
        '';
}

// ── Auth guard ────────────────────────────────
function requireAuth(allowedRoles = []) {
    const user = getSessionUser();
    if (!user.id) {
        window.location.href = isSubPage ? '../login.html' : 'login.html';
        return;
    }
    if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        window.location.href = isSubPage ? '../login.html' : 'login.html';
    }
}

// ── Logout ────────────────────────────────────
async function logout() {
    try {
        await fetch(BASE + 'auth/logout.php', { method: 'POST' });
    } catch (e) { /* silent */ }
    clearSession();
    window.location.href = isSubPage ? '../login.html' : 'login.html';
}

// ── Generic GET fetch ─────────────────────────
async function apiGet(path, params = {}) {
    try {
        const url = new URL(BASE + path, window.location.origin + window.location.pathname);
        Object.keys(params).forEach(k => url.searchParams.append(k, params[k]));
        const res = await fetch(url.toString());
        return await res.json();
    } catch (err) {
        console.error('apiGet error:', err);
        return { success: false, data: [] };
    }
}

// ── Generic POST fetch ────────────────────────
async function apiPost(path, body = {}) {
    try {
        const res = await fetch(BASE + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return await res.json();
    } catch (err) {
        console.error('apiPost error:', err);
        return { success: false, message: 'Could not connect to server.' };
    }
}

// ── Render helpers ────────────────────────────
function renderTable(tbodyId, rows, emptyMsg = 'No data available.') {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    if (!rows || rows.length === 0) {
        const cols = tbody.closest('table').querySelectorAll('thead th').length;
        tbody.innerHTML = `<tr><td colspan="${cols}" style="text-align:center;color:#888;padding:20px;">${emptyMsg}</td></tr>`;
        return;
    }
    tbody.innerHTML = rows.join('');
}

function renderStat(elId, value) {
    const el = document.getElementById(elId);
    if (el) el.textContent = value ? value : '--';
}

function statusBadge(status) {
    const map = {
        completed: '<span class="completed">Completed</span>',
        verified: '<span class="completed">Verified</span>',
        approved: '<span class="completed">Approved</span>',
        active: '<span class="completed">Active</span>',
        pending: '<span class="pending">Pending</span>',
        upcoming: '<span class="pending">Upcoming</span>',
        'pending review': '<span class="pending">Pending Review</span>',
    };
    return map[status ? status.toLowerCase() : ''] || `<span>${status ?? '—'}</span>`;
}

// ── Toast notification ────────────────────────
function showToast(message, type = 'success') {
    let toast = document.getElementById('ffjs-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'ffjs-toast';
        toast.style.cssText = `
            position:fixed; bottom:24px; right:24px; z-index:9999;
            padding:12px 20px; border-radius:8px; font-size:14px;
            font-family:Arial,sans-serif; box-shadow:0 4px 12px rgba(0,0,0,0.15);
            transition:opacity 0.3s ease; max-width:320px;
        `;
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.background = type === 'success' ? '#d1fae5' : '#fee2e2';
    toast.style.color = type === 'success' ? '#065f46' : '#991b1b';
    toast.style.border = type === 'success' ? '1px solid #6ee7b7' : '1px solid #fca5a5';
    toast.style.opacity = '1';
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => { toast.style.opacity = '0'; }, 3500);
}

// ── Run on every page load ────────────────────
document.addEventListener('DOMContentLoaded', loadUserSession);