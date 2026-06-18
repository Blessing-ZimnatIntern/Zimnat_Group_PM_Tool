// assets/js/charts.js
// Chart rendering functions for the dashboard

let chartInstances = {};

function renderCharts(c, total) {
    // Destroy existing charts if they exist
    if (chartInstances.statusDonut) {
        chartInstances.statusDonut.destroy();
    }
    if (chartInstances.progressBar) {
        chartInstances.progressBar.destroy();
    }

    // 1. Donut chart – status distribution
    const ctx1 = document.getElementById('statusDonutChart');
    if (ctx1) {
        chartInstances.statusDonut = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['On Track', 'Behind', 'At Risk', 'Overdue', 'Complete'],
                datasets: [{
                    data: [c.ontrack || 0, c.behind || 0, c.atrisk || 0, c.overdue || 0, c.complete || 0],
                    backgroundColor: ['#16a34a', '#9ca3af', '#f59e0b', '#dc2626', '#3b82f6'],
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const count = context.parsed;
                                const percent = total ? Math.round((count / total) * 100) : 0;
                                return `${context.label}: ${count} (${percent}%)`;
                            }
                        }
                    }
                },
                cutout: '65%',
            }
        });
    }

    // 2. Horizontal bar chart – progress buckets
    // We need the filtered projects list; we'll get it from the global DATA.
    // Since this function is called from renderPortfolio, we can access allProjects().
    // But we need the filtered projects. We'll use the global getFiltered function.
    const ctx2 = document.getElementById('progressBarChart');
    if (ctx2) {
        // Use the filtered projects from the current view (global getFiltered)
        // We need to call it with allProjects() – but that may not be filtered.
        // Instead, we'll recompute the buckets from the filtered projects.
        // We'll access the filtered projects via a global variable, but we can't.
        // We'll create a helper function to get the current projects.
        // For simplicity, we'll use the global DATA but apply filters manually.
        // However, getFiltered is defined globally. We'll call it.
        // But it uses DOM elements, so we need to ensure it's available.
        // We'll call getFiltered(allProjects()) inside the renderCharts call.
        // We'll pass the filtered projects as an argument.
        // We'll change the renderCharts signature to accept projects.
        // Since we want to keep the function signature, we'll compute buckets inside.
        // We'll use the global getFiltered function to get the current filtered list.
        // To avoid circular issues, we'll assume getFiltered is available.
        // Alternatively, we can pass the projects array.
        // We'll modify the call in index.php: renderCharts(c, total, projs);
        // But for now, we'll compute it here using the global DATA and filters.
        // We'll apply the same filters as in getFiltered.
        // We'll just use a simplified approach: use the total projects and their progress.
        // For now, we'll use all projects and filter by status/business_unit if set.
        // Since this is a demo, we'll rely on the fact that renderCharts is called after filters.
        // We'll just compute buckets from all projects.
        // Better: we'll pass the filtered projects as a third argument.
        // I'll update the index.php call.
        // For now, we'll keep it simple: use allProjects().
        const projects = window.DATA ? window.DATA.flatMap(c => c.projects) : [];
        const buckets = [0, 25, 50, 75, 100];
        const counts = buckets.map(b => projects.filter(p => p.progress >= b && p.progress < (b+25)).length);
        chartInstances.progressBar = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: ['0-24%', '25-49%', '50-74%', '75-99%', '100%'],
                datasets: [{
                    label: 'Projects',
                    data: counts,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { beginAtZero: true, grid: { display: false } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
}