// ============================================================
// GLOBAL STATE
// ============================================================
let currentView = 'dashboard';
let filters = { assignee: '', status: '', business_unit: '' };
let projects = [];
let stats = {};
let chartInstances = {};

// ============================================================
// DOM REFS
// ============================================================
const viewContainer = document.getElementById('viewContainer');
const statsGrid = document.getElementById('statsGrid');
const projectTableBody = document.getElementById('projectTableBody');
const filterAssignee = document.getElementById('filterAssignee');
const filterStatus = document.getElementById('filterStatus');
const filterBusinessUnit = document.getElementById('filterBusinessUnit');

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    loadFilters();
    loadDashboardData();
    attachFilterEvents();
    attachNavEvents();
    setupImport();
});

// ============================================================
// LOAD FILTERS
// ============================================================
async function loadFilters() {
    try {
        const res = await fetch('api/filter_data.php');
        const data = await res.json();
        if (data.status !== 'success') throw new Error(data.message);

        const assigneeSelect = filterAssignee;
        assigneeSelect.innerHTML = '<option value="">All Assignees</option>';
        data.assignees.forEach(a => {
            assigneeSelect.innerHTML += `<option value="${a.id}">${a.full_name}</option>`;
        });

        const buSelect = filterBusinessUnit;
        buSelect.innerHTML = '<option value="">All Business Units</option>';
        data.business_units.forEach(b => {
            buSelect.innerHTML += `<option value="${b.id}">${b.name}</option>`;
        });
    } catch (err) {
        console.error('Failed to load filters:', err);
    }
}

// ============================================================
// LOAD DASHBOARD DATA
// ============================================================
async function loadDashboardData() {
    const params = new URLSearchParams(filters);
    const url = `api/filter_data.php?${params}`;
    try {
        const res = await fetch(url);
        const data = await res.json();
        if (data.status !== 'success') throw new Error(data.message);
        projects = data.projects;
        stats = data.statistics;
        renderRings();
        renderTable();
        updateCharts();
    } catch (err) {
        console.error('Error loading dashboard:', err);
    }
}

// ============================================================
// RENDER RING CARDS
// ============================================================
function renderRings() {
    const total = stats.total || 0;
    const distribution = {
        completed: stats.completed || 0,
        ontrack: stats.ontrack || 0,
        atrisk: stats.atrisk || 0,
        overdue: stats.overdue || 0,
        behind: stats.behind || 0,
    };
    const colors = {
        completed: '#3b82f6',
        ontrack: '#16a34a',
        atrisk: '#f59e0b',
        overdue: '#dc2626',
        behind: '#9ca3af',
    };
    const labels = {
        completed: 'Completed',
        ontrack: 'On Track',
        atrisk: 'At Risk',
        overdue: 'Overdue',
        behind: 'Behind',
    };

    const ringSize = 120;
    const strokeWidth = 12;
    const radius = (ringSize - strokeWidth) / 2;
    const circumference = 2 * Math.PI * radius;

    let html = '';
    const statusKeys = ['completed', 'ontrack', 'atrisk', 'overdue', 'behind'];
    statusKeys.forEach(key => {
        const count = distribution[key];
        const percent = total ? Math.round((count / total) * 100) : 0;
        const dash = (percent / 100) * circumference;
        const offset = circumference - dash;

        html += `
            <div class="ring-card">
                <div class="ring-wrapper">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="${radius}" fill="none" stroke="#e5e7eb" stroke-width="${strokeWidth}"/>
                        <circle cx="60" cy="60" r="${radius}" fill="none" stroke="${colors[key]}" stroke-width="${strokeWidth}"
                            stroke-dasharray="${dash} ${circumference}"
                            stroke-dashoffset="${offset}"
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 1s ease;"
                        />
                    </svg>
                    <div class="center-label">${percent}%<small>${labels[key]}</small></div>
                </div>
                <div class="status-breakdown">
                    <span><span class="dot" style="background:${colors[key]}"></span>${labels[key]}</span>
                    <span>${count} (${percent}%)</span>
                </div>
            </div>
        `;
    });

    statsGrid.innerHTML = html;
}

// ============================================================
// RENDER TABLE
// ============================================================
function renderTable() {
    let html = '';
    projects.forEach(p => {
        const statusLabel = p.effective_status.charAt(0).toUpperCase() + p.effective_status.slice(1);
        html += `
            <tr>
                <td>${p.name}</td>
                <td>${p.business_unit_name}</td>
                <td><span style="background:${getStatusColor(p.effective_status)}; color:#fff; padding:2px 8px; border-radius:4px;">${statusLabel}</span></td>
                <td>${p.progress}%</td>
                <td>${p.owner || 'Unassigned'}</td>
            </tr>
        `;
    });
    projectTableBody.innerHTML = html || '<tr><td colspan="5" style="text-align:center;">No projects match the filters.</td></tr>';
}

function getStatusColor(status) {
    const map = {
        'complete': '#3b82f6',
        'ontrack': '#16a34a',
        'atrisk': '#f59e0b',
        'overdue': '#dc2626',
        'behind': '#9ca3af',
    };
    return map[status] || '#6b7280';
}

// ============================================================
// CHARTS (Chart.js)
// ============================================================
function updateCharts() {
    const ctx1 = document.getElementById('statusChart')?.getContext('2d');
    if (ctx1) {
        if (chartInstances.status) chartInstances.status.destroy();
        chartInstances.status = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'On Track', 'At Risk', 'Overdue', 'Behind'],
                datasets: [{
                    data: [stats.completed || 0, stats.ontrack || 0, stats.atrisk || 0, stats.overdue || 0, stats.behind || 0],
                    backgroundColor: ['#3b82f6', '#16a34a', '#f59e0b', '#dc2626', '#9ca3af'],
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw / (stats.total||1) * 100)}%)` } }
                }
            }
        });
    }

    const ctx2 = document.getElementById('progressChart')?.getContext('2d');
    if (ctx2) {
        if (chartInstances.progress) chartInstances.progress.destroy();
        const bins = [0, 25, 50, 75, 100];
        const counts = bins.map(b => projects.filter(p => p.progress >= b && p.progress < (b+25)).length);
        chartInstances.progress = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['0-24%', '25-49%', '50-74%', '75-99%', '100%'],
                datasets: [{
                    label: 'Projects',
                    data: counts,
                    backgroundColor: '#3b82f6',
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    }
}

// ============================================================
// FILTER EVENTS
// ============================================================
function attachFilterEvents() {
    [filterAssignee, filterStatus, filterBusinessUnit].forEach(el => {
        el.addEventListener('change', (e) => {
            filters[el.id.replace('filter', '').toLowerCase()] = el.value;
            if (currentView === 'dashboard') {
                loadDashboardData();
            } else {
                loadView(currentView);
            }
        });
    });
}

// ============================================================
// NAVIGATION
// ============================================================
function attachNavEvents() {
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const view = item.dataset.view;
            const url = item.dataset.url;
            switchView(view, url);
            document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
            item.classList.add('active');
        });
    });
}

function switchView(view, url) {
    currentView = view;
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    if (view === 'dashboard') {
        document.getElementById('dashboardView').classList.add('active');
        viewContainer.classList.remove('active');
        loadDashboardData();
    } else {
        document.getElementById('dashboardView').classList.remove('active');
        viewContainer.classList.add('active');
        loadView(view, url);
    }
}

// ============================================================
// LOAD VIEW VIA AJAX
// ============================================================
function loadView(view, url) {
    if (!url) return;
    const params = new URLSearchParams(filters);
    fetch(`${url}?${params}`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to load view');
            return res.text();
        })
        .then(html => {
            viewContainer.innerHTML = html;
            // Re-run any scripts inside the loaded content (e.g., for calendar/gantt)
            viewContainer.querySelectorAll('script').forEach(script => {
                eval(script.textContent);
            });
        })
        .catch(err => {
            viewContainer.innerHTML = `<div class="error">Error loading view: ${err.message}</div>`;
        });
}

// ============================================================
// IMPORT
// ============================================================
function setupImport() {
    document.getElementById('importBtn').addEventListener('click', () => {
        document.getElementById('importModal').classList.add('open');
    });
    document.getElementById('importCancel').addEventListener('click', () => {
        document.getElementById('importModal').classList.remove('open');
    });
    document.getElementById('importConfirm').addEventListener('click', () => {
        const fileInput = document.getElementById('importFile');
        const file = fileInput.files[0];
        if (!file) return alert('Please select a file.');
        const formData = new FormData();
        formData.append('file', file);
        fetch('api/import.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || data.error || 'Import completed');
            if (data.status === 'success') {
                document.getElementById('importModal').classList.remove('open');
                loadDashboardData();
                loadFilters();
            }
        })
        .catch(err => alert('Import error: ' + err.message));
    });
}

// ============================================================
// AUTO-REFRESH (POLLING)
// ============================================================
setInterval(() => {
    if (currentView === 'dashboard') {
        loadDashboardData();
    } else {
        // Refresh view if needed (optional)
    }
}, 10000); // every 10 seconds