<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Zimnat Group Project Management</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- ✅ KEEP YOUR EXISTING CSS EXACTLY -->
<?php include '../zgroup_pm_tool_1.html'; ?>

</head>
<body>

<script>
/* =============================
   ✅ REPLACED DATA LAYER
============================= */

let DATA = [];
const TODAY = new Date().toISOString().split("T")[0];

/* =============================
   ✅ LOAD FROM PHP BACKEND
============================= */

async function load() {
    const res = await fetch("../api/projects.php");
    const json = await res.json();

    DATA = groupByCompany(json.data);
    renderAll();
}

/* =============================
   ✅ GROUP DB → FRONTEND FORMAT
============================= */

function groupByCompany(projects) {
    const map = {};

    projects.forEach(p => {

        if (!map[p.business_unit]) {
            map[p.business_unit] = {
                id: p.business_unit,
                name: p.business_unit,
                color: "#7b68ee",
                projects: []
            };
        }

        map[p.business_unit].projects.push({
            id: p.id,
            company: p.business_unit,
            companyName: p.business_unit,
            name: p.name,
            stage: p.stage,
            health: p.status,
            owner: p.owner,
            prio: p.priority,
            gateDue: p.gate_due,
            compDue: p.completion_due,
            progress: parseInt(p.progress || 0),
            currentUpdate: p.current_update,
            nextSteps: p.next_steps,
            governance: {}
        });

    });

    return Object.values(map);
}

/* =============================
   ✅ SAVE TO PHP BACKEND
============================= */

async function saveProject(p) {
    await fetch("../api/saveProject.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id: p.id,
            business_unit: p.company,
            name: p.name,
            stage: p.stage,
            status: p.health,
            owner: p.owner,
            priority: p.prio,
            gate_due: p.gateDue,
            completion_due: p.compDue,
            progress: p.progress,
            current_update: p.currentUpdate,
            next_steps: p.nextSteps
        })
    });

    await load(); // refresh UI
}

/* =============================
   ✅ DELETE FROM BACKEND
============================= */

async function deleteProjectAPI(id) {
    await fetch(`../api/deleteProject.php?id=${id}`);
    await load();
}

/* =============================
   ✅ OVERRIDE EXISTING SAVE()
============================= */

async function save() {
    // Instead of bulk save, we rely on per-project saves
}

/* =============================
   ✅ PATCH DELETE BUTTON
============================= */

function attachDeleteHandler(button, projectId) {
    button.addEventListener("click", async () => {
        if (!confirm("Delete this project?")) return;

        await deleteProjectAPI(projectId);
    });
}

/* =============================
   ✅ PATCH EXISTING UI HOOKS
============================= */

function overrideProjectHandlers() {

    // Hook into drawer delete button
    document.querySelectorAll("#e-del").forEach(btn => {
        const id = btn.getAttribute("data-id");
        attachDeleteHandler(btn, id);
    });

}

/* =============================
   ✅ EXTEND UPDATE HANDLER
============================= */

function updateProjectField(p, key, value) {
    p[key] = value;
    saveProject(p);
}

/* =============================
   ✅ MODIFY EXISTING EVENTS
============================= */

// Override where original code used save()
function patchEvents() {

    document.querySelectorAll("input, select, textarea").forEach(el => {

        el.addEventListener("change", e => {

            const field = e.target.id;
            const value = e.target.value;

            if (window.currentProject) {
                updateProjectField(window.currentProject, field, value);
            }

        });

    });

}

/* =============================
   ✅ INIT APP
============================= */

(async function init() {
    await load();

    setTimeout(() => {
        patchEvents();
        overrideProjectHandlers();
    }, 500);
})();
</script>

</body>
</html>