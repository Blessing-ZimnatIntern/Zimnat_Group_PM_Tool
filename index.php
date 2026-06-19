<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Zimnat - Project Management Portal</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<!-- Frappe Gantt -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.5.0/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.5.0/dist/frappe-gantt.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
  /* ================================================================
     ORIGINAL CSS (complete) + NEW DASHBOARD STYLES
     ================================================================ */
  :root {
    --zimnat-primary: #12A052;
    --zimnat-primary-light: #0e8a45;
    --zimnat-secondary: #4F8BBF;
    --zimnat-secondary-light: #3d7aae;
    --zimnat-dark: #0a6b36;
    --zimnat-gray: #f0f2f5;
    --zimnat-card-bg: #ffffff;
    --zimnat-text: #1a2332;
    --zimnat-muted: #6b7a8f;
    --zimnat-border: #e2e8f0;
    --zimnat-success: #2d9b6e;
    --zimnat-warning: #e8a838;
    --zimnat-danger: #dc3545;

    --font: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --bg: #f0f2f5;
    --rail: #12A052;
    --side: #1a4a7a;
    --side-text: #ffffff;
    --side-muted: rgba(255,255,255,0.7);
    --side-hover: rgba(255,255,255,0.12);
    --side-active: rgba(255,255,255,0.18);
    --line: #e2e8f0;
    --line-2: #d1d9e6;
    --text: #1a2332;
    --muted: #6b7a8f;
    --muted-2: #9aa8b9;
    --hover: rgba(255,255,255,0.15);
    --cu: #4F8BBF;
    --cu-d: #3d7aae;

    --urgent: #D32F2F;
    --high: #F9A825;
    --normal: #4F8BBF;
    --low: #9aa8b9;
    --ontrack: #2E7D32;
    --atrisk: #FB8C00;
    --behind: #F9A825;
    --overdue: #D32F2F;
    --complete: #1976D2;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; font-family: var(--font); background: var(--bg); color: var(--text); }

  /* ================================================================
     LOGIN / REGISTER SCREEN
     ================================================================ */
  .auth-screen {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #1a4a7a 0%, #12A052 60%, #0e8a45 100%);
    padding: 20px;
    position: relative;
    overflow: hidden;
  }
  .auth-screen::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
  }
  .auth-screen::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: rgba(79, 139, 191, 0.12);
  }
  .auth-container {
    background: #fff;
    border-radius: 16px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 25px 80px rgba(0,0,0,0.2);
    position: relative;
    z-index: 1;
  }
  .auth-container .logo {
    text-align: center;
    margin-bottom: 32px;
  }
  .auth-logo-container {
    background: #ffffff;
    padding: 24px 32px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 8px;
    margin-bottom: 12px;
  }
  .auth-logo-img {
    height: 56px;
    width: auto;
    object-fit: contain;
    display: block;
  }
  .auth-container .logo .brand-icon {
    width: 56px;
    height: 56px;
    background: #12A052;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    font-weight: 900;
    margin-bottom: 12px;
    box-shadow: 0 8px 24px rgba(18, 160, 82, 0.3);
  }
  .auth-container .logo h1 {
    font-size: 24px;
    font-weight: 800;
    color: #12A052;
    letter-spacing: -0.5px;
  }
  .auth-container .logo h1 span {
    color: #4F8BBF;
  }
  .auth-container .logo p {
    color: var(--zimnat-muted);
    font-size: 14px;
    margin-top: 2px;
  }

  .auth-container .form-group {
    margin-bottom: 16px;
  }
  .auth-container .form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--zimnat-text);
    margin-bottom: 5px;
  }
  .auth-container .form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--zimnat-border);
    border-radius: 10px;
    font-size: 14px;
    font-family: var(--font);
    transition: border-color 0.2s, box-shadow 0.2s;
    background: #fafbfc;
  }
  .auth-container .form-group input:focus {
    outline: none;
    border-color: #12A052;
    box-shadow: 0 0 0 4px rgba(18, 160, 82, 0.1);
    background: #fff;
  }
  .auth-container .auth-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: #12A052;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    font-family: var(--font);
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 16px rgba(18, 160, 82, 0.25);
  }
  .auth-container .auth-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(18, 160, 82, 0.35);
    background: #0e8a45;
  }
  .auth-container .auth-btn:active {
    transform: translateY(0);
  }
  .auth-container .auth-btn.secondary {
    background: transparent;
    color: var(--zimnat-primary);
    border: 2px solid var(--zimnat-border);
    box-shadow: none;
  }
  .auth-container .auth-btn.secondary:hover {
    background: var(--zimnat-gray);
    box-shadow: none;
    transform: none;
  }
  .auth-container .auth-switch {
    text-align: center;
    margin-top: 16px;
    font-size: 14px;
    color: var(--zimnat-muted);
  }
  .auth-container .auth-switch a {
    color: var(--zimnat-primary);
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
  }
  .auth-container .auth-switch a:hover {
    text-decoration: underline;
  }
  .auth-container .auth-links {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 12px;
    font-size: 13px;
  }
  .auth-container .auth-links a {
    color: var(--zimnat-muted);
    text-decoration: none;
    font-weight: 500;
  }
  .auth-container .auth-links a:hover {
    color: var(--zimnat-primary);
    text-decoration: underline;
  }
  .auth-container .error-msg {
    background: #fde8e8;
    color: #b42318;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
    display: none;
    border-left: 3px solid var(--zimnat-danger);
  }
  .auth-container .error-msg.show {
    display: block;
  }
  .auth-container .success-msg {
    background: #e6f7e6;
    color: #0b6e0b;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
    display: none;
    border-left: 3px solid var(--zimnat-success);
  }
  .auth-container .success-msg.show {
    display: block;
  }

  /* ================================================================
     MAIN APP
     ================================================================ */
  .app {
    font-family: var(--font);
    color: var(--text);
    background: var(--bg);
    height: 100vh;
    display: none;
    overflow: hidden;
    font-size: 14px;
  }
  .app.active {
    display: flex;
  }

  .rail {
    width: 0;
    background: #0e8a45;
    border-right: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0;
    gap: 4px;
    flex-shrink: 0;
    overflow: hidden;
  }
  .ws-avatar,
  .rail-btn,
  .rail-spacer,
  .rail-user {
    display: none;
  }

  .side {
    width: 260px;
    background: var(--side);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
  }
  .sidebar-logo-wrapper {
    width: 100%;
    background: transparent;
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .sidebar-logo-img {
    width: 100%;
    height: auto;
    object-fit: contain;
    display: block;
    background: transparent;
    padding: 0;
    margin: 0;
  }
  .side-head {
    padding: 12px 0 8px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }
  .side-head .ws-sub {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 2px;
    padding: 0 18px;
  }
  .side-footer {
    padding: 12px 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
  }
  .side-logout-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    font-family: var(--font);
    cursor: pointer;
    transition: all 0.15s;
  }
  .side-logout-btn:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    border-color: rgba(255, 255, 255, 0.3);
  }
  .side-logout-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    stroke: rgba(255, 255, 255, 0.7);
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 6px 10px 0;
    padding: 9px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13.5px;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.75);
    transition: all 0.15s;
  }
  .nav-item:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
  }
  .nav-item.active {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
    font-weight: 600;
  }
  .nav-item svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    stroke: rgba(255, 255, 255, 0.7);
  }
  .side-section .lbl {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.4);
    padding: 16px 18px 6px;
  }
  .spaces {
    overflow-y: auto;
    flex: 1;
    padding: 4px 8px 8px;
  }
  .space {
    margin: 1px 0;
  }
  .space-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 10px;
    border-radius: 7px;
    cursor: pointer;
    transition: background 0.1s;
    color: rgba(255, 255, 255, 0.75);
  }
  .space-row:hover {
    background: rgba(255, 255, 255, 0.10);
    color: #fff;
  }
  .space-row.active {
    background: rgba(255, 255, 255, 0.16);
    color: #fff;
  }
  .space-row.active .space-name {
    color: #fff;
    font-weight: 600;
  }
  .caret {
    width: 14px;
    color: rgba(255, 255, 255, 0.4);
    font-size: 10px;
    transition: .15s;
    text-align: center;
  }
  .space.open .caret {
    transform: rotate(90deg);
  }
  .space-icon {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    color: #fff;
    font-weight: 700;
    font-size: 11px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
  }
  .space-name {
    font-size: 13.5px;
    font-weight: 500;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: inherit;
  }
  .space-count {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.5);
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 0 8px;
    line-height: 18px;
  }
  .projlist {
    display: none;
    padding: 2px 0 4px;
  }
  .space.open .projlist {
    display: block;
  }
  .proj-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 5px 8px 5px 32px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
    transition: all 0.1s;
  }
  .proj-row:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
  }
  .proj-row .pdot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
  }
  .proj-row span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
    background: var(--bg);
  }
  .topbar {
    padding: 0 28px 0;
    background: #12A052;
    border-bottom: 1px solid #0e8a45;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding-top: 0;
    padding-bottom: 0;
  }
  .title-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 8px 0 6px;
  }
  .title-row h1 {
    font-family: 'Aptos', 'Segoe UI', 'Inter', sans-serif;
    font-weight: 600;
    letter-spacing: 0.4px;
    font-size: 2.2rem;
    margin: 0;
    color: #fff;
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .title-actions {
    display: flex;
    gap: 8px;
  }
  .pill-btn {
    border: 1px solid var(--line-2);
    background: #fff;
    border-radius: 8px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    color: var(--text);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: var(--font);
    transition: all 0.15s;
  }
  .pill-btn:hover {
    background: var(--hover);
  }
  .pill-btn.primary {
    background: #12A052;
    border-color: #12A052;
    color: #fff;
    box-shadow: 0 2px 8px rgba(18, 160, 82, 0.2);
  }
  .pill-btn.primary:hover {
    background: #0e8a45;
    box-shadow: 0 4px 16px rgba(18, 160, 82, 0.3);
    transform: translateY(-1px);
  }

  /* Tabs – always visible */
  .tabs-wrapper {
    background: #12A052;
    padding: 0 28px;
    border-bottom: 1px solid #0e8a45;
  }
  .tabs {
    display: flex;
    gap: 2px;
  }
  .tab {
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 500;
    color: rgba(255,255,255,0.78);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.15s;
  }
  .tab:hover { color: #fff; }
  .tab.active { color: #fff; border-bottom-color: #fff; font-weight: 600; }

  .toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 28px;
    background: #fff;
    border-bottom: 1px solid var(--line);
    flex-wrap: wrap;
  }
  .search {
    position: relative;
    display: flex;
    align-items: center;
  }
  .search svg {
    position: absolute;
    left: 12px;
    width: 16px;
    height: 16px;
    color: var(--muted-2);
  }
  .search input {
    border: 1px solid var(--line-2);
    border-radius: 8px;
    padding: 8px 12px 8px 36px;
    font-size: 13px;
    font-family: var(--font);
    width: 220px;
    background: var(--bg);
    transition: all 0.2s;
  }
  .search input:focus {
    outline: none;
    border-color: #12A052;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(18, 160, 82, 0.08);
  }
  .fsel {
    font-family: var(--font);
    font-size: 13px;
    border: 1px solid var(--line-2);
    border-radius: 8px;
    padding: 8px 12px;
    background: var(--bg);
    cursor: pointer;
    transition: all 0.2s;
  }
  .fsel:focus {
    outline: none;
    border-color: #12A052;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(18, 160, 82, 0.08);
  }
  .tspacer {
    flex: 1;
  }
  .content {
    flex: 1;
    overflow: auto;
    padding: 0 0 40px;
  }

  /* Cards and summary – original styles kept */
  .sample-note {
    margin: 16px 28px 0;
    background: #fef9e7;
    border: 1px solid #f5e6b8;
    color: #7a6210;
    font-size: 13px;
    padding: 10px 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .sample-note .icon {
    font-size: 18px;
  }
  .sumwrap {
    padding: 20px 28px;
  }
  .stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 14px;
    margin-bottom: 10px;
  }
  .scard {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 16px 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    cursor: pointer;
    transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
  }
  .scard:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.10);
    border-color: rgba(18, 160, 82, 0.35);
  }
  .scard .n {
    font-size: 28px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -0.5px;
  }
  .scard .l {
    font-size: 12.5px;
    color: var(--muted);
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .scard .l .d {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }
  .sec-title {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--muted-2);
    margin: 28px 0 14px;
  }
  .co-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
  }
  .co-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 18px 20px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  }
  .co-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
    border-color: var(--zimnat-primary-light);
  }
  .co-card .top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
  }
  .co-card .ci {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    color: #fff;
    font-weight: 700;
    display: grid;
    place-items: center;
    font-size: 14px;
  }
  .co-card .cn {
    font-weight: 700;
    font-size: 15px;
    color: var(--zimnat-primary);
  }
  .co-card .cs {
    font-size: 12px;
    color: var(--muted);
  }
  .segbar {
    height: 8px;
    border-radius: 5px;
    display: flex;
    overflow: hidden;
    background: var(--line-2);
    margin: 10px 0 8px;
  }
  .segbar i {
    height: 100%;
  }
  .co-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12.5px;
    color: var(--muted);
  }
  .co-meta b {
    color: var(--text);
  }
  .attn {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  }
  .attn-row {
    display: grid;
    grid-template-columns: 1fr 80px 120px 130px;
    gap: 12px;
    align-items: center;
    padding: 10px 18px;
    border-bottom: 1px solid var(--line);
    font-size: 13px;
    cursor: pointer;
    transition: background 0.1s;
  }
  .attn-row:last-child {
    border-bottom: none;
  }
  .attn-row:hover {
    background: var(--hover);
  }
  .attn-row .co {
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    padding: 2px 10px;
    border-radius: 5px;
    justify-self: start;
  }

  @media print {
    .side, .rail, .topbar, .toolbar, .tabs-wrapper, .title-actions, .side-footer,
    .scrim, .drawer, .drill-overlay, .drill-panel, .import-overlay {
        display: none !important;
    }
    .main { margin: 0 !important; padding: 0 !important; }
    .content { padding: 20px !important; overflow: visible !important; }
    body { background: #fff !important; }
    .ltable, .board, .sumwrap, .dashboard-grid, .charts-grid, .attn {
        page-break-inside: avoid;
    }
    .stat-card, .ring-card-custom, .chart-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
  }

  /* List / Board (original) */
  .group { margin-bottom: 2px; }
  .group-head { display: flex; align-items: center; gap: 10px; padding: 10px 28px; cursor: pointer; user-select: none; background: #fff; border-radius: 8px; margin: 4px 0; transition: background 0.1s; }
  .group-head:hover { background: var(--hover); }
  .group-bar { font-size: 11px; font-weight: 700; letter-spacing: .04em; color: #fff; padding: 3px 14px; border-radius: 5px; }
  .group-count { font-size: 12px; color: var(--muted-2); }
  .gcaret { color: var(--muted-2); font-size: 10px; transition: .15s; }
  .group.collapsed .gcaret { transform: rotate(-90deg); }
  .group.collapsed .rows { display: none; }
  .ltable { min-width: 880px; }
  .lh, .lrow { display: grid; grid-template-columns: minmax(220px, 1fr) 130px 110px 70px 110px 110px; gap: 12px; align-items: center; padding: 9px 28px; }
  .lh { font-size: 11px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: var(--muted-2); border-bottom: 1px solid var(--line); background: #fff; padding: 10px 28px; }
  .lrow { border-bottom: 1px solid var(--line); cursor: pointer; background: #fff; transition: background 0.1s; }
  .lrow:hover { background: var(--hover); }
  .lname { display: flex; align-items: center; gap: 10px; font-weight: 500; min-width: 0; }
  .lname .ptxt { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .flag { width: 14px; height: 14px; flex-shrink: 0; }
  .avatar { width: 26px; height: 26px; border-radius: 50%; color: #fff; font-size: 11px; font-weight: 600; display: grid; place-items: center; }
  .pill { font-size: 11px; font-weight: 600; color: #fff; padding: 3px 12px; border-radius: 5px; display: inline-block; }
  .due { font-size: 12.5px; color: var(--muted); }
  .due.over { color: var(--overdue); font-weight: 600; }
  .gov-mini { font-size: 11px; color: var(--muted); }

  .board { display: flex; gap: 16px; padding: 20px 28px; overflow-x: auto; align-items: flex-start; }
  .col { background: var(--side); border: 1px solid var(--line); border-radius: 12px; width: 260px; flex-shrink: 0; display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
  .col-head { padding: 12px 16px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--line); }
  .col-dot { width: 9px; height: 9px; border-radius: 3px; }
  .col-head .ct { font-size: 12px; font-weight: 700; }
  .col-head .cc { font-size: 11px; color: var(--muted-2); background: #fff; border: 1px solid var(--line-2); border-radius: 9px; padding: 0 8px; }
  .col-body { padding: 10px; display: flex; flex-direction: column; gap: 8px; min-height: 40px; }
  .card { background: #fff; border: 1px solid var(--line-2); border-radius: 9px; padding: 12px 14px; cursor: pointer; transition: all 0.15s; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
  .card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }
  .card .cn { font-weight: 500; font-size: 13.5px; margin-bottom: 9px; display: flex; gap: 7px; }
  .card .cm { display: flex; align-items: center; justify-content: space-between; }

  /* Drawer */
  .scrim { position: fixed; inset: 0; background: rgba(13,31,51,0.5); opacity: 0; pointer-events: none; transition: opacity 0.25s; z-index: 40; backdrop-filter: blur(2px); }
  .scrim.open { opacity: 1; pointer-events: auto; }
  .drawer { position: fixed; top: 0; right: 0; height: 100vh; width: 50%; max-width: 680px; min-width: 480px; background: #fff; box-shadow: -8px 0 40px rgba(0,0,0,0.12); transform: translateX(100%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); z-index: 50; display: flex; flex-direction: column; }
  .drawer.open { transform: none; }
  .dr-head { padding: 22px 28px 0; border-bottom: 1px solid var(--line); background: #fff; }
  .dr-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
  .dr-co { font-size: 11px; font-weight: 700; color: #fff; padding: 2px 12px; border-radius: 5px; display: inline-block; margin-bottom: 8px; }
  .dr-head h2 { font-size: 20px; margin: 0; font-weight: 700; color: var(--zimnat-primary); letter-spacing: -0.3px; }
  .x { background: none; border: none; font-size: 28px; color: var(--muted); cursor: pointer; line-height: 1; padding: 4px 10px; border-radius: 6px; transition: background 0.15s; }
  .x:hover { background: var(--hover); }
  .dr-tabs { display: flex; gap: 4px; margin-top: 16px; }
  .dr-tab { padding: 9px 16px; font-size: 13px; font-weight: 500; color: var(--muted); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.15s; }
  .dr-tab:hover { color: var(--text); }
  .dr-tab.active { color: var(--cu); border-bottom-color: var(--cu); font-weight: 600; }
  .dr-body { padding: 24px 28px; overflow-y: auto; flex: 1; background: var(--bg); }
  .fld { margin-bottom: 18px; }
  .fld label { display: block; font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 5px; }
  .fld input, .fld select, .fld textarea { width: 100%; font-family: var(--font); font-size: 14px; border: 1px solid var(--line-2); border-radius: 8px; padding: 10px 14px; background: #fff; color: var(--text); transition: all 0.2s; }
  .fld textarea { min-height: 96px; resize: vertical; }
  .fld input:focus, .fld select:focus, .fld textarea:focus { outline: none; border-color: #12A052; box-shadow: 0 0 0 3px rgba(18,160,82,0.08); }
  .two { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .rng { display: flex; align-items: center; gap: 12px; }
  .rng input { flex: 1; }
  .save-row { display: flex; align-items: center; gap: 10px; margin-top: 6px; }
  .saved-tag { font-size: 12px; color: var(--ontrack); opacity: 0; transition: .2s; }
  .saved-tag.show { opacity: 1; }

  /* Governance (unchanged) */
  .gov-overall { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; font-size: 13px; background: #fff; padding: 14px 18px; border-radius: 10px; border: 1px solid var(--line); }
  .gov-overall .bar { flex: 1; height: 8px; background: var(--line-2); border-radius: 5px; overflow: hidden; }
  .gov-overall .bar i { display: block; height: 100%; background: var(--ontrack); }
  .gstage { margin-bottom: 16px; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; background: #fff; }
  .gstage-h { padding: 10px 16px; background: var(--bg); font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; }
  .gstage-h .num { width: 22px; height: 22px; border-radius: 50%; background: #12A052; color: #fff; font-size: 11px; display: grid; place-items: center; font-weight: 700; }
  .gstage-h .cur-tag { margin-left: auto; font-size: 10px; font-weight: 700; color: #fff; background: #4F8BBF; padding: 2px 10px; border-radius: 5px; }
  .gitem { display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-top: 1px solid var(--line); }
  .gitem .gname { flex: 1; font-size: 13.5px; }
  .gitem select { font-family: var(--font); font-size: 12.5px; border: 1px solid var(--line-2); border-radius: 6px; padding: 5px 10px; font-weight: 600; }
  .gitem .none { font-size: 12px; color: var(--muted-2); padding: 10px 16px; }

  /* NEW DASHBOARD STYLES */
  .dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    padding: 24px;
  }
  .stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
  }
  .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
  .stat-number { font-size: 28px; font-weight: 800; line-height: 1.2; }
  .stat-label { font-size: 13px; color: #6b7280; margin-top: 4px; }
  .stat-color-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; }

  .ring-grid-custom {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 0 24px 24px;
  }
  .ring-card-custom {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    text-align: center;
    transition: transform 0.2s;
    cursor: pointer;
  }
  .ring-card-custom:hover { transform: translateY(-2px); }
  .ring-wrapper-custom {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 12px;
  }
  .ring-wrapper-custom svg { transform: rotate(-90deg); }
  .ring-center-custom {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
  }
  .ring-center-custom small { font-size: 10px; font-weight: 400; color: #6b7280; }
  .ring-breakdown-custom {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 12px;
    text-align: left;
    font-size: 12px;
    margin-top: 8px;
  }
  .ring-breakdown-custom .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; }

  .charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    padding: 0 24px 24px;
  }
  .chart-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
  }
  .chart-card canvas { max-height: 200px; width: 100% !important; }

  /* Responsive */
  @media (max-width: 1024px) {
    .dashboard-grid { grid-template-columns: repeat(2,1fr); }
    .charts-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 768px) {
    .dashboard-grid { grid-template-columns: 1fr; }
    .ring-grid-custom { grid-template-columns: 1fr 1fr; }
    .tabs-wrapper { padding: 0 16px; }
    .tab { padding: 6px 12px; font-size: 12px; }
  }

  @media print {
    .side, .rail, .topbar, .toolbar, .tabs-wrapper, .title-actions, .footer, .scrim, .drawer, .drill-overlay, .drill-panel { display: none !important; }
    .main { margin: 0 !important; padding: 0 !important; }
    .content { padding: 20px !important; }
  }

  /* Import modal (unchanged) */
  .import-overlay { position: fixed; inset: 0; background: rgba(10,30,50,0.55); backdrop-filter: blur(3px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
  .import-overlay.open { opacity: 1; pointer-events: auto; }
  .import-modal { background: #fff; border-radius: 16px; padding: 32px; width: 100%; max-width: 460px; box-shadow: 0 24px 80px rgba(0,0,0,0.2); position: relative; }
  .import-modal h3 { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
  .import-modal p.sub { font-size: 13px; color: var(--muted); margin-bottom: 20px; }
  .import-close { position: absolute; top: 16px; right: 18px; background: none; border: none; font-size: 24px; color: var(--muted); cursor: pointer; line-height: 1; border-radius: 6px; padding: 2px 8px; transition: background 0.15s; }
  .import-close:hover { background: var(--bg); }
  .drop-zone { border: 2px dashed var(--line-2); border-radius: 12px; padding: 36px 24px; text-align: center; background: var(--bg); transition: all 0.2s; cursor: pointer; position: relative; }
  .drop-zone:hover, .drop-zone.dragover { border-color: #12A052; background: rgba(18,160,82,0.05); }
  .drop-zone.file-selected { border-color: #12A052; background: rgba(18,160,82,0.06); }
  .drop-zone .dz-icon { font-size: 36px; margin-bottom: 10px; }
  .drop-zone .dz-title { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
  .drop-zone .dz-sub { font-size: 12px; color: var(--muted); margin-bottom: 14px; }
  .drop-zone .dz-types { font-size: 11px; color: var(--muted-2); font-weight: 500; }
  .drop-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
  .dz-browse { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border: 1px solid var(--line-2); border-radius: 8px; background: #fff; font-size: 13px; font-weight: 500; color: var(--text); cursor: pointer; margin-bottom: 12px; transition: all 0.15s; font-family: var(--font); }
  .dz-browse:hover { background: var(--bg); border-color: #12A052; color: #12A052; }
  .dz-file-name { font-size: 13px; color: #12A052; font-weight: 600; margin-top: 8px; display: none; }
  .import-actions { display: flex; gap: 8px; margin-top: 20px; justify-content: flex-end; }

  /* Drill panel, ring icons, etc. */
  .drill-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.28); opacity: 0; pointer-events: none; transition: opacity 0.18s ease; z-index: 55; }
  .drill-overlay.open { opacity: 1; pointer-events: auto; }
  .drill-panel { position: fixed; top: 0; right: 0; width: min(480px, 94vw); height: 100vh; background: #fff; z-index: 56; box-shadow: -22px 0 50px rgba(15,23,42,0.18); transform: translateX(105%); transition: transform 0.22s ease; display: flex; flex-direction: column; }
  .drill-panel.open { transform: translateX(0); }
  .drill-head { padding: 20px 22px 14px; border-bottom: 1px solid var(--line); display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
  .drill-head h3 { font-size: 18px; margin: 0 0 4px; }
  .drill-head p { margin: 0; color: var(--muted); font-size: 13px; }
  .drill-close { border: 1px solid var(--line); background: #fff; border-radius: 8px; width: 34px; height: 34px; cursor: pointer; font-size: 20px; color: var(--muted); }
  .drill-body { overflow: auto; padding: 12px; }
  .drill-item { display: grid; grid-template-columns: 1fr auto; gap: 8px 12px; align-items: center; padding: 12px; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 10px; cursor: pointer; transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease; }
  .drill-item:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(15,23,42,0.08); border-color: rgba(18,160,82,0.35); }
  .drill-title { font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .drill-meta { display: flex; gap: 8px; align-items: center; color: var(--muted); font-size: 12px; }
  .drill-progress { font-size: 12px; color: var(--muted); justify-self: end; }
  .empty-state { color: var(--muted); text-align: center; padding: 40px 16px; border: 1px dashed var(--line-2); border-radius: 8px; background: var(--bg); }

  .bu-icon { width: 36px; height: 36px; border-radius: 9px; display: grid; place-items: center; flex-shrink: 0; }
  .bu-icon svg { width: 20px; height: 20px; }
  .space-icon.bu { width: 28px; height: 28px; border-radius: 7px; display: grid; place-items: center; background: transparent; }
  .space-icon.bu svg { width: 16px; height: 16px; }

  .dropdown { position: relative; display: inline-block; }
  .dropdown-menu { display: none; position: absolute; right: 0; background: #fff; min-width: 140px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border-radius: 8px; z-index: 10; border: 1px solid var(--line); overflow: hidden; }
  .dropdown-menu.show { display: block; }
  .dropdown-menu a { display: block; padding: 8px 16px; color: var(--text); text-decoration: none; font-size: 13px; }
  .dropdown-menu a:hover { background: var(--hover); }

  #calendar { background: #fff; padding: 20px; border-radius: 12px; margin-top: 16px; }
  #ganttChart { height: 400px; }

  .gantt-ontrack { fill: #16a34a; }
  .gantt-behind { fill: #9ca3af; }
  .gantt-atrisk { fill: #f59e0b; }
  .gantt-overdue { fill: #dc2626; }
  .gantt-complete { fill: #3b82f6; }
</style>
</head>
<body>

<!-- ============================================================ -->
<!-- LOGIN SCREEN -->
<!-- ============================================================ -->
<div id="loginScreen" class="auth-screen">
  <div class="auth-container">
    <div class="logo">
      <div class="auth-logo-container"><img src="logo.png" alt="Zimnat" class="auth-logo-img"></div>
      <p>Project Management Portal</p>
    </div>
    <div id="loginError" class="error-msg"></div>
    <div id="loginSuccess" class="success-msg"></div>
    <form id="loginForm">
      <div class="form-group"><label>Email or Employee Number</label><input type="text" id="loginEmail" placeholder="Enter your email or employee number" required></div>
      <div class="form-group"><label>Password</label><input type="password" id="loginPassword" placeholder="Enter your password" required></div>
      <button type="submit" class="auth-btn">Login</button>
      <div class="auth-links"><a href="#" id="forgotPassword">Forgot your password?</a></div>
    </form>
    <div class="auth-switch">Don't have an account? <a id="showRegister">Create one</a></div>
  </div>
</div>

<!-- ============================================================ -->
<!-- REGISTER SCREEN -->
<!-- ============================================================ -->
<div id="registerScreen" class="auth-screen" style="display:none;">
  <div class="auth-container">
    <div class="logo">
      <div class="auth-logo-container"><img src="logo.png" alt="Zimnat" class="auth-logo-img"></div>
      <p>Create your account</p>
    </div>
    <div id="registerError" class="error-msg"></div>
    <div id="registerSuccess" class="success-msg"></div>
    <form id="registerForm">
      <div class="form-group"><label>Full Name</label><input type="text" id="regFullName" placeholder="John Doe" required></div>
      <div class="form-group"><label>Employee Number</label><input type="text" id="regUsername" placeholder="EMP-XXXX" required></div>
      <div class="form-group"><label>Email Address</label><input type="email" id="regEmail" placeholder="you@zimnat.com" required></div>
      <div class="form-group"><label>Password</label><input type="password" id="regPassword" placeholder="Min 8 characters" required minlength="8"></div>
      <button type="submit" class="auth-btn">Create Account</button>
    </form>
    <div class="auth-switch">Already have an account? <a id="showLogin">Sign in</a></div>
  </div>
</div>

<!-- ============================================================ -->
<!-- MAIN APP -->
<!-- ============================================================ -->
<div class="app" id="mainApp">
  <nav class="rail"></nav>
  <aside class="side">
    <div class="side-head">
      <div class="sidebar-logo-wrapper"><img src="logo.png" alt="Zimnat" class="sidebar-logo-img"></div>
      <div class="ws-sub" id="wsSub">Portfolio &middot; Business units</div>
    </div>
    <div class="nav-item active" id="navPortfolio">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 13a8 8 0 0116 0"/><path d="M12 13l5-5"/></svg>
      Portfolio
    </div>
    <div class="side-section"><div class="lbl">Business Units</div></div>
    <div class="spaces" id="spaces"></div>
    <div class="side-footer">
      <button class="side-logout-btn" id="logoutBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        Logout
      </button>
    </div>
  </aside>

  <section class="main">
    <div class="topbar">
      <div class="title-row">
        <h1 id="mainTitle">Project Management</h1>
        <div class="title-actions">
          <div class="dropdown">
            <button class="pill-btn" id="exportDropdownBtn">Export ▾</button>
            <div class="dropdown-menu" id="exportMenu">
              <a href="#" data-export="csv">CSV</a>
              <a href="#" data-export="excel">Excel</a>
              <a href="#" data-export="print">Print</a>
            </div>
          </div>
          <button class="pill-btn" id="shareBtn">Share</button>
          <button class="pill-btn" id="importBtn">Import</button>
          <button class="pill-btn primary" id="addProjectBtn">+ Add Project</button>
        </div>
      </div>
    </div>
    <!-- TABS WRAPPER – ALWAYS VISIBLE -->
    <div class="tabs-wrapper">
      <div class="tabs" id="tabs"></div>
    </div>
    <div class="toolbar" id="toolbar">
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
        <input id="search" placeholder="Search projects...">
      </div>
      <select class="fsel" id="fCompany"><option value="">All business units</option></select>
      <select class="fsel" id="fStatus">
        <option value="">All statuses</option>
        <option value="ontrack">On Track</option>
        <option value="atrisk">At Risk</option>
        <option value="behind">Behind</option>
        <option value="overdue">Overdue</option>
        <option value="complete">Complete</option>
      </select>
      <select class="fsel" id="fAssignee"><option value="">All assignees</option></select>
      <div class="tspacer"></div>
    </div>
    <div class="content" id="content"></div>
  </section>
</div>

<!-- ============================================================ -->
<!-- IMPORT MODAL -->
<!-- ============================================================ -->
<div class="import-overlay" id="importOverlay">
  <div class="import-modal">
    <button class="import-close" id="importClose">&times;</button>
    <h3>Import Projects</h3>
    <p class="sub">Upload a spreadsheet to import project data into the portal.</p>
    <div class="drop-zone" id="dropZone">
      <input type="file" id="importFile" accept=".xlsx,.csv" />
      <div class="dz-icon">📁</div>
      <div class="dz-title">Drag &amp; drop your file here</div>
      <div class="dz-sub">or</div>
      <button class="dz-browse" type="button" onclick="document.getElementById('importFile').click()">Browse Files</button>
      <div class="dz-types">Supported formats: .xlsx · .csv</div>
      <div class="dz-file-name" id="dzFileName"></div>
    </div>
    <div class="import-actions">
      <button class="pill-btn" id="importCancelBtn">Cancel</button>
      <button class="pill-btn primary" id="importConfirmBtn" disabled style="opacity:0.5;cursor:not-allowed">Import Data</button>
    </div>
  </div>
</div>

<!-- Drill-Down Panel -->
<div class="drill-overlay" id="drillOverlay"></div>
<aside class="drill-panel" id="drillPanel">
  <div class="drill-head">
    <div><h3 id="drillTitle">Projects</h3><p id="drillSub">0 projects</p></div>
    <button class="drill-close" id="drillClose" type="button">&times;</button>
  </div>
  <div class="drill-body" id="drillBody"></div>
</aside>

<!-- ============================================================ -->
<!-- DRAWER -->
<!-- ============================================================ -->
<div class="scrim" id="scrim"></div>
<div class="drawer" id="drawer">
  <div class="dr-head">
    <div class="dr-top">
      <div><span class="dr-co" id="drCo">ZFS</span><h2 id="drName">Project Name</h2></div>
      <button class="x" id="drClose">&times;</button>
    </div>
    <div class="dr-tabs" id="drTabs">
      <div class="dr-tab active" data-dt="overview">Overview</div>
      <div class="dr-tab" data-dt="updates">Update &amp; Next Steps</div>
      <div class="dr-tab" data-dt="gov">Governance</div>
    </div>
  </div>
  <div class="dr-body" id="drBody"></div>
</div>

<script>
// ================================================================
// ZIMNAT PROJECT MANAGEMENT – PHASE 1 (CORRECTED)
// ================================================================

// ----- AUTHENTICATION -----
const loginScreen = document.getElementById('loginScreen');
const registerScreen = document.getElementById('registerScreen');
const mainApp = document.getElementById('mainApp');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const loginError = document.getElementById('loginError');
const loginSuccess = document.getElementById('loginSuccess');
const registerError = document.getElementById('registerError');
const registerSuccess = document.getElementById('registerSuccess');

document.getElementById('showRegister').addEventListener('click', (e) => {
    e.preventDefault();
    loginScreen.style.display = 'none';
    registerScreen.style.display = 'flex';
    loginError.classList.remove('show');
    registerError.classList.remove('show');
});
document.getElementById('showLogin').addEventListener('click', (e) => {
    e.preventDefault();
    registerScreen.style.display = 'none';
    loginScreen.style.display = 'flex';
    registerError.classList.remove('show');
    loginError.classList.remove('show');
});
document.getElementById('forgotPassword').addEventListener('click', (e) => {
    e.preventDefault();
    loginError.textContent = 'Please contact your IT administrator to reset your password.';
    loginError.classList.add('show');
});

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    loginError.classList.remove('show');
    loginSuccess.classList.remove('show');
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    if (!email || !password) {
        loginError.textContent = 'Please enter both email and password.';
        loginError.classList.add('show');
        return;
    }
    try {
        const response = await fetch('api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const data = await response.json();
        if (data.status === 'success') {
            loginScreen.style.display = 'none';
            mainApp.classList.add('active');
            initApp();
        } else {
            loginError.textContent = data.error || 'Login failed';
            loginError.classList.add('show');
        }
    } catch (err) {
        loginError.textContent = 'Network error. Please try again.';
        loginError.classList.add('show');
    }
});

registerForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    registerError.classList.remove('show');
    registerSuccess.classList.remove('show');
    const fullName = document.getElementById('regFullName').value;
    const username = document.getElementById('regUsername').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;
    if (!fullName || !username || !email || !password) {
        registerError.textContent = 'All fields are required.';
        registerError.classList.add('show');
        return;
    }
    if (password.length < 8) {
        registerError.textContent = 'Password must be at least 8 characters.';
        registerError.classList.add('show');
        return;
    }
    try {
        const response = await fetch('api/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ full_name: fullName, username, email, password })
        });
        const data = await response.json();
        if (data.status === 'success') {
            registerSuccess.textContent = 'Account created! Redirecting to login...';
            registerSuccess.classList.add('show');
            setTimeout(() => {
                registerSuccess.classList.remove('show');
                registerScreen.style.display = 'none';
                loginScreen.style.display = 'flex';
                document.getElementById('loginEmail').value = email;
            }, 1000);
        } else {
            registerError.textContent = data.error || 'Registration failed';
            registerError.classList.add('show');
        }
    } catch (err) {
        registerError.textContent = 'Network error. Please try again.';
        registerError.classList.add('show');
    }
});

document.getElementById('logoutBtn').addEventListener('click', () => {
    if (confirm('Are you sure you want to logout?')) {
        fetch('api/auth/logout.php', { method: 'POST' }).then(() => {
            mainApp.classList.remove('active');
            loginScreen.style.display = 'flex';
            registerScreen.style.display = 'none';
        });
    }
});

// ----- APP DATA & CONFIG -----
const STAGES = [
    { id: 'initiation', label: 'Initiation' },
    { id: 'planning', label: 'Planning' },
    { id: 'execution', label: 'Execution / Development' },
    { id: 'qa', label: 'Quality Assurance' },
    { id: 'uat', label: 'User Acceptance Testing' },
    { id: 'closure', label: 'Project Closure' }
];
const STAGE_IDX = Object.fromEntries(STAGES.map((s, i) => [s.id, i]));
const GOVERNANCE = [
    { id: 'boscard', label: 'BOSCARD', stage: 'initiation' },
    { id: 'brd', label: 'Business Requirements Document (BRD)', stage: 'planning' },
    { id: 'plan', label: 'Project Plan / Gantt / Development Plan', stage: 'planning' },
    { id: 'qa', label: 'QA Testing Report', stage: 'qa' },
    { id: 'uat', label: 'UAT Sign-offs', stage: 'uat' },
    { id: 'golive', label: 'Go-live Change Request Sign-off', stage: 'closure' },
    { id: 'closure', label: 'Closure Report Sign-off', stage: 'closure' }
];
const HEALTH = {
    ontrack: { label: 'On Track', color: '#16a34a' },
    behind: { label: 'Behind', color: '#9ca3af' },
    atrisk: { label: 'At Risk', color: '#f59e0b' },
    overdue: { label: 'Overdue', color: '#dc2626' },
    complete: { label: 'Complete', color: '#3b82f6' }
};
const GOV_ST = {
    pending: { l: 'Pending', c: '#9aa8b9' },
    progress: { l: 'In progress', c: '#2a5a8c' },
    signed: { l: 'Signed off', c: '#2d9b6e' },
    na: { l: 'N/A', c: '#d1d9e6' }
};
const PRIO = {
    urgent: '#dc2626',
    high: '#f59e0b',
    normal: '#3b82f6',
    low: '#9ca3af'
};
const AV = ['#1a3a5c', '#2d9b6e', '#e8a838', '#2a5a8c', '#dc3545', '#6c5ce7', '#00b894'];
const TODAY = new Date().toISOString().split('T')[0];

// ----- DATA LAYER -----
let DATA = [];
let USERS = [];
let loadError = null;

async function loadData() {
    try {
        const response = await fetch('api/projects/list.php', { credentials: 'same-origin' });
        const payload = await response.json();
        if (!response.ok || payload.status !== 'success') {
            throw new Error(payload.error || 'Unable to load projects');
        }

        try {
            const userRes = await fetch('api/users/list.php', { credentials: 'same-origin' });
            const userData = await userRes.json();
            USERS = userData.data || [];
        } catch (e) {
            console.warn('Could not fetch users, using empty list', e);
            USERS = [];
        }

        DATA = mapApiData(payload.data.business_units, payload.data.projects);
        loadError = null;
    } catch (error) {
        console.error('Failed to load data:', error);
        loadError = error.message;
        DATA = [];
        USERS = [];
    }
    renderAll();
}

function mapApiData(units, projects) {
    return units.map(unit => {
        const slug = (unit.slug || unit.name.toLowerCase().replace(/[^a-z0-9]+/g, '')).trim();
        return {
            dbId: Number(unit.id),
            id: slug || 'unit-' + unit.id,
            name: unit.name,
            color: unit.color || '#12A052',
            projects: projects
                .filter(p => Number(p.business_unit_id) === Number(unit.id))
                .map(p => mapApiProject(p))
        };
    });
}

// 🔥 NEW: Map a single project from API
function mapApiProject(p) {
    return {
        id: p.id,
        company: p.business_unit_slug || 'unit-' + p.business_unit_id,
        companyDbId: p.business_unit_id,
        companyName: p.business_unit_name,
        companyColor: p.business_unit_color,
        name: p.name || '',
        stage: p.stage || 'initiation',
        health: p.status || 'ontrack',
        owner: p.owner || '',
        assignee_id: p.assignee_id || null,
        prio: p.priority || 'normal',
        gateDue: p.gate_due || '',
        compDue: p.completion_due || '',
        progress: Number(p.progress || 0),
        currentUpdate: p.current_update || '',
        nextSteps: p.next_steps || '',
        is_gate_overdue: p.is_gate_overdue || 0,
        governance: normalizeGovernance(p.governance || [])
    };
}

function normalizeGovernance(items) {
    const g = {};
    GOVERNANCE.forEach(item => g[item.id] = 'pending');
    items.forEach(item => {
        const key = normalizeGovKey(item.item_key || item.governance_item || '');
        if (key && g[key] !== undefined) g[key] = normalizeGovStatus(item.status);
    });
    return g;
}
function normalizeGovKey(key) {
    return ({ qa_report: 'qa', uat_signoff: 'uat', golive_cr: 'golive', closure_report: 'closure' })[key] || key;
}
function apiGovKey(key) {
    return ({ qa: 'qa_report', uat: 'uat_signoff', golive: 'golive_cr', closure: 'closure_report' })[key] || key;
}
function normalizeGovStatus(status) {
    return ({ in_progress: 'progress', progress: 'progress', signed_off: 'signed', signed: 'signed', na: 'na', 'n/a': 'na' })[(status || '').toLowerCase()] || 'pending';
}
function apiGovStatus(status) {
    return ({ progress: 'progress', signed: 'signed', na: 'na' })[status] || 'pending';
}

// ----- API HELPERS -----
async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
        ...options
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok || payload.status === 'error' || payload.error) {
        throw new Error(payload.error || payload.message || 'Request failed');
    }
    return payload;
}

function projectPayload(p) {
    return {
        id: p.id,
        name: p.name,
        business_unit_id: p.companyDbId || co(p.company)?.dbId,
        stage: p.stage,
        status: p.health,
        owner: p.owner,
        assignee_id: p.assignee_id || null,
        priority: p.prio,
        gate_due: p.gateDue || null,
        completion_due: p.compDue || null,
        progress: p.progress,
        current_update: p.currentUpdate,
        next_steps: p.nextSteps
    };
}

// 🔥 FIXED: persistProject now updates the local project object
async function persistProject(p, patch = {}) {
    if (!p.id || String(p.id).includes('-n')) return;
    const payload = { ...projectPayload(p), ...patch };
    const response = await apiRequest('api/projects/update.php', {
        method: 'PUT',
        body: JSON.stringify({ id: p.id, ...payload })
    });
    if (response.data) {
        // Merge updated fields back into local project
        const updated = mapApiProject(response.data);
        Object.assign(p, updated);
        renderSidebar();
        renderContent();
        renderDrawer();
    }
    return response;
}

// ----- HELPERS -----
const $ = id => document.getElementById(id);
const co = id => DATA.find(c => c.id === id);
const allProjects = () => DATA.flatMap(c => c.projects);

function findP(id) {
    for (const c of DATA) {
        for (const p of c.projects) {
            if (p.id === id) return p;
        }
    }
    return null;
}
function avC(i) { let s = 0; for (const ch of i) s += ch.charCodeAt(0); return AV[s % AV.length]; }
function esc(s) { return (s || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]); }
function fmt(d) { if (!d) return '—'; return new Date(d + 'T00:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: '2-digit' }); }
function flag(p) { return `<svg class="flag" viewBox="0 0 24 24" fill="${PRIO[p]}"><path d="M5 3v18M5 4h12l-2 4 2 4H5"/></svg>`; }
function av(i) { return `<span class="avatar" style="background:${avC(i)}">${esc(i)}</span>`; }
function effStatus(p) { if (p.health === 'complete') return 'complete'; if (p.compDue && p.compDue < TODAY) return 'overdue'; return p.health; }
function gateOverdue(p) { return p.is_gate_overdue == 1; }
function govPct(p) { const items = GOVERNANCE.filter(g => p.governance[g.id] !== 'na'); if (!items.length) return 100; const signed = items.filter(g => p.governance[g.id] === 'signed').length; return Math.round(signed / items.length * 100); }
function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 5); }

// ----- STATE -----
let screen = 'portfolio';
let selected = null;
let view = 'list';
let currentTab = 'dashboard';
let collapsed = {};
let drawerTab = 'overview';
let openId = null;

// ----- RENDER FUNCTIONS -----
function renderSidebar() {
    if (loadError) {
        $('spaces').innerHTML = `<div style="color:var(--zimnat-danger);padding:12px;">⚠️ ${esc(loadError)}</div>`;
        return;
    }
    $('navPortfolio').classList.toggle('active', screen === 'portfolio');
    $('spaces').innerHTML = DATA.map(c => {
        const icon = buIcon(c.id);
        return `
        <div class="space ${c.id === selected ? 'open' : ''}" data-id="${c.id}">
            <div class="space-row ${c.id === selected && screen === 'company' ? 'active' : ''}" data-space="${c.id}">
                <span class="caret">▶</span>
                <span class="space-icon bu" style="background:${icon.bg}">${icon.svg}</span>
                <span class="space-name">${c.name}</span>
                <span class="space-count">${c.projects.length}</span>
            </div>
            <div class="projlist">${c.projects.map(p => `
                <div class="proj-row" data-pid="${p.id}" title="${esc(p.name)}">
                    <span class="pdot" style="background:${HEALTH[effStatus(p)].color}"></span>
                    <span>${esc(p.name)}</span>
                </div>
            `).join('')}</div>
        </div>`;
    }).join('');
    $('spaces').querySelectorAll('.space-row').forEach(r => r.addEventListener('click', () => {
        const id = r.getAttribute('data-space');
        if (id === selected && screen === 'company') {
            r.closest('.space').classList.toggle('open');
        } else {
            selected = id;
            screen = 'company';
            renderAll();
        }
    }));
    $('spaces').querySelectorAll('.proj-row').forEach(r => r.addEventListener('click', e => {
        e.stopPropagation();
        openDrawer(r.getAttribute('data-pid'));
    }));
}

function renderHeader() {
    const tabs = ['dashboard', 'list', 'board', 'calendar', 'gantt', 'report', 'mail'];
    const labels = {
        dashboard: 'Dashboard',
        list: 'List',
        board: 'Board',
        calendar: 'Calendar',
        gantt: 'Gantt',
        report: 'Reports',
        mail: 'Mail'
    };
    $('tabs').innerHTML = tabs.map(tab =>
        `<div class="tab ${currentTab === tab ? 'active' : ''}" data-view="${tab}">${labels[tab]}</div>`
    ).join('');

    $('tabs').querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const viewName = this.dataset.view;
            currentTab = viewName;
            $('tabs').querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (viewName === 'dashboard') {
                renderContent();
            } else {
                loadView(viewName);
            }
        });
    });

    if (screen === 'portfolio') {
        $('mainTitle').textContent = 'Project Management';
    } else {
        const c = co(selected);
        $('mainTitle').textContent = c ? c.name : 'Business Unit';
    }
}

// 🔥 FIXED: renderToolbar shows ALL users, not just assigned
function renderToolbar() {
    if (loadError) return;
    const sel = document.getElementById('fAssignee');
    if (sel) {
        const currentVal = sel.value;
        sel.innerHTML = '<option value="">All assignees</option>';
        USERS.forEach(u => {
            sel.innerHTML += `<option value="${u.id}">${esc(u.full_name)}</option>`;
        });
        sel.value = currentVal;
    }
    const buSel = document.getElementById('fCompany');
    if (buSel) {
        const current = buSel.value;
        buSel.innerHTML = '<option value="">All business units</option>' +
            DATA.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        buSel.value = current;
    }
}

function getFiltered(scope) {
    const q = ($('search')?.value || '').trim().toLowerCase();
    const fst = $('fStatus')?.value || '';
    const fco = $('fCompany')?.value || '';
    const fAssignee = $('fAssignee')?.value || '';

    return scope.filter(p => {
        if (q && !p.name.toLowerCase().includes(q)) return false;
        if (fst && effStatus(p) !== fst) return false;
        if (fco && p.company !== fco) return false;
        if (fAssignee && String(p.assignee_id) !== fAssignee) return false;
        return true;
    });
}

function statBlock(projs) {
    const c = { ontrack: 0, atrisk: 0, behind: 0, overdue: 0, complete: 0 };
    projs.forEach(p => c[effStatus(p)]++);
    const avg = projs.length ? Math.round(projs.reduce((a, p) => a + p.progress, 0) / projs.length) : 0;
    return { c, avg, total: projs.length };
}

function buIcon(id) {
    const key = (id || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
    const icons = {
        zfs: { bg: '#1a3a5c', svg: `<svg viewBox="0 0 20 20" fill="none" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="13" width="3" height="5" rx="0.5"/><rect x="7.5" y="9" width="3" height="9" rx="0.5"/><rect x="13" y="5" width="3" height="13" rx="0.5"/><polyline points="3.5,12 8.5,8 14,4" stroke="#7eb8e8" stroke-width="1.3"/><polyline points="14,4 17,3 16,6" fill="#7eb8e8" stroke="#7eb8e8" stroke-width="0.8"/></svg>` },
        zam: { bg: '#0a5c6e', svg: `<svg viewBox="0 0 20 20" fill="none" stroke="#fff" stroke-width="1.6"><circle cx="10" cy="10" r="7.5" stroke="rgba(255,255,255,0.3)"/><circle cx="10" cy="10" r="5" stroke="rgba(255,255,255,0.15)"/><path d="M4,14 Q7,5 16,6" stroke="#7ee8f0" stroke-width="2"/><circle cx="15.5" cy="6.2" r="1.5" fill="#7ee8f0"/></svg>` },
        zgi: { bg: '#1a5c2e', svg: `<svg viewBox="0 0 20 20" fill="none" stroke="#fff" stroke-width="1.6"><path d="M10 2L3 5v5c0 4 3.2 6.8 7 8 3.8-1.2 7-4 7-8V5L10 2z"/><polyline points="7,10 9,12.5 13,8" stroke="#a8e6c0" stroke-width="1.8"/><path d="M6,5.5h8" stroke="rgba(255,255,255,0.35)" stroke-width="1"/></svg>` },
        zla: { bg: '#5c1a3a', svg: `<svg viewBox="0 0 20 20" fill="none" stroke="#fff" stroke-width="1.6"><path d="M10 17 C10 17 3 13 3 8a4 4 0 018-1 4 4 0 018 1c0 5-7 9-7 9z" fill="rgba(255,255,255,0.15)"/><circle cx="10" cy="6" r="2" fill="rgba(255,180,160,0.8)" stroke="rgba(255,200,180,0.9)" stroke-width="0.8"/></svg>` },
        groupprojects: { bg: '#5b8def', svg: `<svg viewBox="0 0 20 20" fill="none" stroke="#fff" stroke-width="1.6"><rect x="2" y="2" width="6" height="6" rx="1"/><rect x="12" y="2" width="6" height="6" rx="1"/><rect x="2" y="12" width="6" height="6" rx="1"/><rect x="12" y="12" width="6" height="6" rx="1"/></svg>` },
        ictinitiatives: { bg: '#2bb673', svg: `<svg viewBox="0 0 20 20" fill="none" stroke="#fff" stroke-width="1.6"><rect x="2" y="4" width="16" height="10" rx="1.5"/><path d="M8 8l4 2-4 2V8z" fill="#fff" stroke="none"/><line x1="6" y1="16" x2="14" y2="16" stroke-width="1.2"/><line x1="10" y1="14" x2="10" y2="16" stroke-width="1.2"/></svg>` }
    };
    return icons[key] || icons.zfs;
}

// ----- DASHBOARD (portfolio) -----
function renderDashboard() {
    if (loadError) {
        $('content').innerHTML = `<div style="padding:40px;text-align:center;color:var(--zimnat-danger);">⚠️ ${esc(loadError)}</div>`;
        return;
    }
    const projs = getFiltered(allProjects());
    const { c, avg, total } = statBlock(projs);

    const stats = [
        { label: 'Total Projects', value: total, color: '#1a4a7a' },
        { label: 'On Track', value: c.ontrack, color: HEALTH.ontrack.color },
        { label: 'Overdue', value: c.overdue, color: HEALTH.overdue.color },
        { label: 'Behind', value: c.behind, color: HEALTH.behind.color },
        { label: 'At Risk', value: c.atrisk, color: HEALTH.atrisk.color },
        { label: 'Complete', value: c.complete, color: HEALTH.complete.color },
    ];

    let html = `<div class="dashboard-grid">`;
    stats.forEach(stat => {
        html += `
            <div class="stat-card" data-drill="${stat.label.toLowerCase().replace(' ', '')}" data-title="${esc(stat.label)}">
                <div class="stat-number" style="color:${stat.color}">${stat.value}</div>
                <div class="stat-label"><span class="stat-color-dot" style="background:${stat.color}"></span>${stat.label}</div>
            </div>
        `;
    });
    html += `</div>`;

    const statusKeys = ['ontrack', 'behind', 'atrisk', 'overdue', 'complete'];
    const labels = {
        ontrack: 'On Track',
        behind: 'Behind',
        atrisk: 'At Risk',
        overdue: 'Overdue',
        complete: 'Complete'
    };
    const colors = {
        ontrack: '#16a34a',
        behind: '#9ca3af',
        atrisk: '#f59e0b',
        overdue: '#dc2626',
        complete: '#3b82f6'
    };

    html += `<div class="ring-grid-custom">`;
    statusKeys.forEach(key => {
        const count = c[key] || 0;
        const percent = total ? Math.round((count / total) * 100) : 0;
        const circumference = 2 * Math.PI * 40;
        const dash = (percent / 100) * circumference;
        const offset = circumference - dash;

        html += `
            <div class="ring-card-custom" data-drill="${key}" data-title="${labels[key]}">
                <div class="ring-wrapper-custom">
                    <svg width="120" height="120" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="40" fill="none" stroke="#e5e7eb" stroke-width="12"/>
                        <circle cx="60" cy="60" r="40" fill="none" stroke="${colors[key]}" stroke-width="12"
                            stroke-dasharray="${dash} ${circumference}"
                            stroke-dashoffset="${offset}"
                            stroke-linecap="round"
                            style="transition: stroke-dashoffset 1s ease;"
                        />
                    </svg>
                    <div class="ring-center-custom">${percent}%<small>${labels[key]}</small></div>
                </div>
                <div class="ring-breakdown-custom">
                    <span><span class="dot" style="background:${colors[key]}"></span>${labels[key]}</span>
                    <span>${count} (${percent}%)</span>
                </div>
            </div>
        `;
    });
    html += `</div>`;

    html += `<div class="charts-grid">
        <div class="chart-card">
            <h4 style="margin-bottom:12px;font-size:14px;font-weight:600;color:#1f2937;">Status Distribution</h4>
            <canvas id="statusDonutChart"></canvas>
        </div>
        <div class="chart-card">
            <h4 style="margin-bottom:12px;font-size:14px;font-weight:600;color:#1f2937;">Progress Distribution</h4>
            <canvas id="progressBarChart"></canvas>
        </div>
    </div>`;

    const attn = projs.filter(p => ['overdue', 'behind', 'atrisk'].includes(effStatus(p)) || gateOverdue(p))
        .sort((a, b) => (a.compDue || '').localeCompare(b.compDue || ''));
    html += `
        <div class="sec-title" style="margin:24px 0 12px;padding:0 24px;">Needs Attention (${attn.length})</div>
        <div class="attn" style="margin:0 24px 24px;">${attn.length ? attn.map(p => `
            <div class="attn-row" data-pid="${p.id}">
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${flag(p.prio)} ${esc(p.name)}</span>
                <span class="co" style="background:${p.companyColor}">${p.companyName}</span>
                <span class="pill" style="background:${HEALTH[effStatus(p)].color}">${HEALTH[effStatus(p)].label}</span>
                <span class="due ${p.compDue < TODAY ? 'over' : ''}">Due ${fmt(p.compDue)}</span>
            </div>
        `).join('') : `<div style="padding:16px;color:var(--muted)">Nothing flagged. 🎉</div>`}</div>
    `;

    $('content').innerHTML = html;
    renderCharts(c, total, projs);

    $('content').querySelectorAll('.stat-card, .ring-card-custom').forEach(el => {
        el.addEventListener('click', function() {
            const key = this.dataset.drill;
            const title = this.dataset.title;
            openDrill(title, key, projs);
        });
    });
    $('content').querySelectorAll('.attn-row').forEach(r => r.addEventListener('click', () => openDrawer(r.getAttribute('data-pid'))));
}

// ----- CHARTS -----
function renderCharts(c, total, filteredProjects) {
    const ctx1 = document.getElementById('statusDonutChart');
    if (ctx1) {
        if (window._statusDonut) window._statusDonut.destroy();
        window._statusDonut = new Chart(ctx1, {
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
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
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

    const ctx2 = document.getElementById('progressBarChart');
    if (ctx2) {
        if (window._progressBar) window._progressBar.destroy();
        const buckets = [0, 25, 50, 75, 100];
        const counts = buckets.map(b => filteredProjects.filter(p => p.progress >= b && p.progress < (b+25)).length);
        window._progressBar = new Chart(ctx2, {
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
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { display: false } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
}

// ----- COMPANY VIEW -----
function renderCompany() {
    if (loadError) {
        $('content').innerHTML = `<div style="padding:40px;text-align:center;color:var(--zimnat-danger);">⚠️ ${esc(loadError)}</div>`;
        return;
    }
    const comp = co(selected);
    if (!comp) {
        $('content').innerHTML = `<div style="padding:40px;text-align:center;color:var(--muted);">Select a business unit</div>`;
        return;
    }
    const projs = getFiltered(comp.projects);
    const { c, avg, total } = statBlock(projs);
    const cards = [
        ['Projects', total, comp.color, 'all'],
        ['On Track', c.ontrack, HEALTH.ontrack.color, 'ontrack'],
        ['Overdue', c.overdue, HEALTH.overdue.color, 'overdue'],
        ['Behind Schedule', c.behind, HEALTH.behind.color, 'behind'],
        ['At Risk', c.atrisk, HEALTH.atrisk.color, 'atrisk'],
        ['Complete', c.complete, HEALTH.complete.color, 'complete']
    ];
    const stg = {};
    STAGES.forEach(s => stg[s.id] = 0);
    comp.projects.forEach(p => stg[p.stage]++);
    let html = `<div class="sumwrap" style="padding-bottom:6px"><div class="stat-row">${cards.map(([l, n, col, key]) => `
            <div class="scard" data-drill="${key}" data-title="${esc(l)}"><div class="n" style="color:${col}">${n}</div><div class="l">${l}</div></div>
        `).join('')}</div>
        <div class="sec-title" style="margin:18px 0 10px">Stage Gate Distribution</div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px">${STAGES.map(s => `<span style="background:var(--bg);padding:4px 12px;border-radius:6px;border:1px solid var(--line)"><b>${stg[s.id]}</b> ${s.label}</span>`).join('')}</div></div>`;
    $('content').innerHTML = html + (view === 'list' ? listHtml(projs) : boardHtml(projs));
    $('content').querySelectorAll('.scard[data-drill]').forEach(card => card.addEventListener('click', () => openDrill(`${card.dataset.title} Projects`, card.dataset.drill, projs)));
    wireList();
    wireBoard();
}

// ----- LIST HTML (with gate overdue) -----
function listHtml(projs) {
    let html = `<div class="ltable">`;
    STAGES.forEach(st => {
        const items = projs.filter(p => p.stage === st.id);
        if (!items.length) return;
        const colKey = selected + st.id;
        const isCol = collapsed[colKey];
        html += `<div class="group ${isCol ? 'collapsed' : ''}" data-st="${st.id}"><div class="group-head"><span class="gcaret">▼</span><span class="group-bar" style="background:var(--cu)">${st.label.toUpperCase()}</span><span class="group-count">${items.length}</span></div>
            <div class="rows"><div class="lh"><span>Project</span><span>Status</span><span>Owner</span><span>Gov</span><span>Gate due</span><span>Completion</span></div>
            ${items.map(p => { const es = effStatus(p); const gateFlag = gateOverdue(p); return `<div class="lrow" data-pid="${p.id}"><div class="lname">${flag(p.prio)}<span class="ptxt">${esc(p.name)}</span></div><div><span class="pill" style="background:${HEALTH[es].color}">${HEALTH[es].label}</span></div><div>${av(p.owner)}</div><div class="gov-mini">${govPct(p)}%</div><div class="due ${gateFlag ? 'over' : ''}">${fmt(p.gateDue)}${gateFlag ? ' ⚠' : ''}</div><div class="due ${p.compDue < TODAY && es !== 'complete' ? 'over' : ''}">${fmt(p.compDue)}</div></div>`; }).join('')}</div></div>`;
    });
    html += `</div>`;
    return html;
}

function wireList() {
    $('content').querySelectorAll('.group-head').forEach(h => h.addEventListener('click', () => { const st = h.closest('.group').getAttribute('data-st'); collapsed[selected + st] = !collapsed[selected + st]; renderCompany(); }));
    $('content').querySelectorAll('.lrow').forEach(r => r.addEventListener('click', () => openDrawer(r.getAttribute('data-pid'))));
}

function boardHtml(projs) {
    return `<div class="board">` + STAGES.map(st => {
        const items = projs.filter(p => p.stage === st.id);
        return `<div class="col"><div class="col-head"><span class="col-dot" style="background:var(--cu)"></span><span class="ct">${st.label}</span><span class="cc">${items.length}</span></div>
            <div class="col-body">${items.map(p => { const es = effStatus(p); return `<div class="card" data-pid="${p.id}"><div class="cn">${flag(p.prio)}<span>${esc(p.name)}</span></div><div class="cm">${av(p.owner)}<span class="pill" style="background:${HEALTH[es].color}">${HEALTH[es].label}</span></div></div>`; }).join('') || '<div style="color:var(--muted-2);font-size:12px;padding:4px;text-align:center">—</div>'}</div></div>`;
    }).join('') + `</div>`;
}
function wireBoard() {
    $('content').querySelectorAll('.card').forEach(c => c.addEventListener('click', () => openDrawer(c.getAttribute('data-pid'))));
}

// 🔥 FIXED: loadView passes numeric business_unit ID
function loadView(viewName) {
    const urlMap = {
        'list': 'views/list.php',
        'board': 'views/board.php',
        'calendar': 'views/calendar.php',
        'gantt': 'views/gantt.php',
        'report': 'views/reports.php',
        'mail': 'views/mail.php'
    };
    const url = urlMap[viewName];
    if (!url) {
        currentTab = 'dashboard';
        renderContent();
        return;
    }

    const params = new URLSearchParams();
    const company = document.getElementById('fCompany').value;
    const status = document.getElementById('fStatus').value;
    const assignee = document.getElementById('fAssignee').value;
    if (company) params.append('business_unit', company);
    if (status) params.append('status', status);
    if (assignee) params.append('assignee', assignee);
    if (screen === 'company' && selected) {
        const unit = co(selected);
        if (unit) params.append('business_unit', unit.dbId); // 🔥 FIX: send numeric ID
    }

    fetch(`${url}?${params}`)
        .then(res => {
            if (!res.ok) throw new Error('Failed to load view');
            return res.text();
        })
        .then(html => {
            $('content').innerHTML = html;
            $('content').querySelectorAll('script').forEach(script => eval(script.textContent));
            $('content').querySelectorAll('.project-row, .lrow, .card, .attn-row').forEach(el => {
                el.addEventListener('click', function(e) {
                    const pid = this.dataset.pid || this.getAttribute('data-pid');
                    if (pid) openDrawer(pid);
                });
            });
            currentTab = viewName;
        })
        .catch(err => {
            $('content').innerHTML = `<div style="padding:20px;color:red;">Error loading view: ${err.message}</div>`;
        });
}

function renderContent() {
    if (screen === 'portfolio') {
        if (currentTab === 'dashboard') {
            renderDashboard();
        } else {
            loadView(currentTab);
        }
    } else {
        if (currentTab === 'dashboard') {
            renderCompany();
        } else {
            loadView(currentTab);
        }
    }
}

// 🔥 FIXED: renderAll updates the business unit filter dropdown
function renderAll() {
    renderSidebar();
    renderHeader();
    renderToolbar();
    // Set the business unit filter to match the current view
    const buFilter = document.getElementById('fCompany');
    if (screen === 'company' && selected) {
        const unit = co(selected);
        if (unit) buFilter.value = unit.id;
    } else {
        buFilter.value = '';
    }
    renderContent();
}

// ----- DRILL-DOWN -----
function openDrill(title, statusKey, scope) {
    const projects = (statusKey === 'all' ? scope : scope.filter(p => effStatus(p) === statusKey || (statusKey === 'overdue' && gateOverdue(p)))).slice().sort((a, b) => (a.companyName || '').localeCompare(b.companyName || '') || (a.compDue || '').localeCompare(b.compDue || ''));
    $('drillTitle').textContent = title === 'Projects' ? 'Projects' : `${title} Projects`;
    $('drillSub').textContent = `${projects.length} project${projects.length !== 1 ? 's' : ''}`;
    $('drillBody').innerHTML = projects.length ? projects.map(p => { const es = effStatus(p); return `<div class="drill-item" data-pid="${p.id}"><div><div class="drill-title">${esc(p.name)}</div><div class="drill-meta"><span>${esc(p.companyName)}</span><span class="pill" style="background:${HEALTH[es].color}">${HEALTH[es].label}</span></div></div><div class="drill-progress">${p.progress}%</div></div>`; }).join('') : `<div class="empty-state">No ${title.toLowerCase()} projects</div>`;
    $('drillBody').querySelectorAll('.drill-item').forEach(item => item.addEventListener('click', () => { closeDrill(); openDrawer(item.dataset.pid); }));
    $('drillOverlay').classList.add('open');
    $('drillPanel').classList.add('open');
}
function closeDrill() {
    $('drillOverlay').classList.remove('open');
    $('drillPanel').classList.remove('open');
}
$('drillOverlay').addEventListener('click', closeDrill);
$('drillClose').addEventListener('click', closeDrill);

// ----- DRAWER -----
function openDrawer(id) {
    openId = id;
    drawerTab = 'overview';
    renderDrawer();
    $('scrim').classList.add('open');
    $('drawer').classList.add('open');
}
function closeDrawer() {
    $('scrim').classList.remove('open');
    $('drawer').classList.remove('open');
    openId = null;
}
$('scrim').addEventListener('click', closeDrawer);
$('drClose').addEventListener('click', closeDrawer);
$('drTabs').querySelectorAll('.dr-tab').forEach(t => t.addEventListener('click', () => { drawerTab = t.getAttribute('data-dt'); renderDrawer(); }));

function renderDrawer() {
    const p = findP(openId);
    if (!p) return;
    $('drCo').textContent = p.companyName;
    $('drCo').style.background = p.companyColor;
    $('drName').textContent = p.name;
    $('drTabs').querySelectorAll('.dr-tab').forEach(t => t.classList.toggle('active', t.getAttribute('data-dt') === drawerTab));
    if (drawerTab === 'overview') drawerOverview(p);
    else if (drawerTab === 'updates') drawerUpdates(p);
    else drawerGov(p);
}

// 🔥 FIXED: drawerOverview with Create/Delete button and async save
function drawerOverview(p) {
    const es = effStatus(p);
    const gateFlag = gateOverdue(p);
    const isNew = p.id.includes('-n');

    let html = `
        <div class="fld"><label>Assigned To (Owner)</label><input id="e-owner" value="${esc(p.owner)}"></div>
        ${gateFlag ? `<div class="sample-note" style="margin:0 0 16px;background:#fde8e8;border-color:#f5c6c6;color:#b42318">⚠️ Stage gate due date has passed!</div>` : ''}
        ${es === 'overdue' ? `<div class="sample-note" style="margin:0 0 16px;background:#fde8e8;border-color:#f5c6c6;color:#b42318">⚠️ Completion date has passed — this project is overdue.</div>` : ''}
        <div class="fld"><label>Project name <span style="color:red;">*</span></label><input id="e-name" value="${esc(p.name)}" ${isNew ? 'required' : ''}></div>
        <div class="two">
            <div class="fld"><label>Priority</label><select id="e-prio">${Object.keys(PRIO).map(k => `<option value="${k}" ${p.prio === k ? 'selected' : ''}>${k[0].toUpperCase() + k.slice(1)}</option>`).join('')}</select></div>
            <div class="fld"><label>Stage gate</label><select id="e-stage">${STAGES.map(s => `<option value="${s.id}" ${p.stage === s.id ? 'selected' : ''}>${s.label}</option>`).join('')}</select></div>
        </div>
        <div class="two">
            <div class="fld"><label>Status</label><select id="e-health">${['ontrack', 'atrisk', 'behind', 'complete'].map(k => `<option value="${k}" ${p.health === k ? 'selected' : ''}>${HEALTH[k].label}</option>`).join('')}</select></div>
            <div class="fld"><label>Assignee (User)</label><select id="e-assignee"><option value="">None</option>${USERS.map(u => `<option value="${u.id}" ${p.assignee_id == u.id ? 'selected' : ''}>${esc(u.full_name)}</option>`).join('')}</select></div>
        </div>
        <div class="two">
            <div class="fld"><label>Stage gate due date</label><input type="date" id="e-gate" value="${p.gateDue || ''}"></div>
            <div class="fld"><label>Project completion date</label><input type="date" id="e-comp" value="${p.compDue || ''}"></div>
        </div>
        <div class="fld">
            <label>Progress — <span id="pv">${p.progress}</span>%</label>
            <div class="rng"><input type="range" id="e-prog" min="0" max="100" value="${p.progress}"></div>
        </div>
        <p style="font-size:12px;color:var(--muted)">Status shows <b style="color:${HEALTH[es].color}">${HEALTH[es].label}</b> (Overdue & Gate overdue are detected automatically).</p>
    `;

    if (isNew) {
        html += `<p style="margin-top:18px"><button class="pill-btn primary" id="e-create">Create Project</button></p>`;
    } else {
        html += `<p style="margin-top:18px"><button class="pill-btn" id="e-del" style="color:var(--overdue);border-color:#f5c6c6">Delete Project</button></p>`;
    }

    $('drBody').innerHTML = html;

    // 🔥 Async upd function
    const upd = async (k, v, patch = null) => {
        p[k] = v;
        try {
            await persistProject(p, patch || {});
        } catch (err) {
            alert('Update failed: ' + err.message);
        }
    };

    // Bind events
    document.getElementById('e-owner').addEventListener('change', e => upd('owner', e.target.value, { owner: e.target.value }));
    document.getElementById('e-name').addEventListener('change', e => upd('name', e.target.value, { name: e.target.value }));
    document.getElementById('e-prio').addEventListener('change', e => upd('prio', e.target.value, { priority: e.target.value }));
    document.getElementById('e-stage').addEventListener('change', e => upd('stage', e.target.value, { stage: e.target.value }));
    document.getElementById('e-health').addEventListener('change', e => { upd('health', e.target.value, { status: e.target.value }); });
    document.getElementById('e-assignee').addEventListener('change', e => upd('assignee_id', e.target.value ? parseInt(e.target.value) : null, { assignee_id: e.target.value || null }));
    document.getElementById('e-gate').addEventListener('change', e => upd('gateDue', e.target.value, { gate_due: e.target.value || null }));
    document.getElementById('e-comp').addEventListener('change', e => { upd('compDue', e.target.value, { completion_due: e.target.value || null }); });
    document.getElementById('e-prog').addEventListener('input', e => { p.progress = +e.target.value; document.getElementById('pv').textContent = e.target.value; });
    document.getElementById('e-prog').addEventListener('change', e => { upd('progress', +e.target.value, { progress: +e.target.value }); });

    if (isNew) {
        document.getElementById('e-create').addEventListener('click', async () => {
            const name = document.getElementById('e-name').value.trim();
            if (!name) { alert('Project name is required.'); return; }
            const data = {
                name: name,
                business_unit_id: p.companyDbId,
                stage: document.getElementById('e-stage').value,
                status: document.getElementById('e-health').value,
                owner: document.getElementById('e-owner').value,
                priority: document.getElementById('e-prio').value,
                assignee_id: document.getElementById('e-assignee').value || null,
                gate_due: document.getElementById('e-gate').value || null,
                completion_due: document.getElementById('e-comp').value || null,
                progress: parseInt(document.getElementById('e-prog').value) || 0,
                current_update: document.getElementById('e-upd')?.value || '',
                next_steps: document.getElementById('e-next')?.value || ''
            };
            try {
                const response = await apiRequest('api/projects/create.php', { method: 'POST', body: JSON.stringify(data) });
                const realId = response.id || response.data?.id;
                if (realId) {
                    const c = co(p.company);
                    c.projects = c.projects.filter(x => x.id !== p.id);
                    const newProject = mapApiProject({ ...data, id: realId, ...response.data });
                    c.projects.push(newProject);
                    closeDrawer();
                    openDrawer(realId);
                    renderAll();
                }
            } catch (err) { alert('Creation failed: ' + err.message); }
        });
    } else {
        document.getElementById('e-del').addEventListener('click', async () => {
            if (!confirm('Delete this project? This cannot be undone.')) return;
            try {
                await apiRequest(`api/projects/delete.php?id=${encodeURIComponent(p.id)}`, { method: 'DELETE', headers: {} });
                const c = co(p.company);
                c.projects = c.projects.filter(x => x.id !== p.id);
                closeDrawer();
                renderAll();
            } catch (err) { alert(err.message); }
        });
    }
}

function drawerUpdates(p) {
    fetch(`api/projects/history.php?id=${p.id}`)
        .then(res => res.json())
        .then(data => {
            const historyHtml = data.data.map(h => `
                <div style="border-bottom:1px solid var(--line);padding:8px 0;font-size:13px;">
                    <div style="font-weight:500;">${esc(h.update_text)}</div>
                    <div style="display:flex;gap:12px;color:var(--muted);font-size:12px;">
                        <span>${esc(h.full_name)}</span>
                        <span>${new Date(h.created_at).toLocaleString()}</span>
                    </div>
                    ${h.next_steps ? `<div style="color:var(--muted-2);font-style:italic;margin-top:4px;">Next: ${esc(h.next_steps)}</div>` : ''}
                </div>
            `).join('') || '<div style="color:var(--muted);padding:12px 0;">No previous updates.</div>';

            const container = document.getElementById('drBody');
            container.innerHTML = `
                <div class="fld"><label>Current update / comment</label><textarea id="e-upd" placeholder="Where things stand right now…">${esc(p.currentUpdate)}</textarea></div>
                <div class="fld"><label>Next steps</label><textarea id="e-next" placeholder="What happens next, and who owns it…">${esc(p.nextSteps)}</textarea></div>
                <div class="save-row"><button class="pill-btn primary" id="e-save">Save Update</button><span class="saved-tag" id="savedTag">Saved ✓</span></div>
                <div style="margin-top:24px;border-top:2px solid var(--line);padding-top:16px;">
                    <h4 style="font-size:14px;font-weight:600;margin-bottom:12px;">📜 History</h4>
                    ${historyHtml}
                </div>
            `;
            document.getElementById('e-save').addEventListener('click', async () => {
                p.currentUpdate = document.getElementById('e-upd').value;
                p.nextSteps = document.getElementById('e-next').value;
                try { await persistProject(p, { current_update: p.currentUpdate, next_steps: p.nextSteps }); } catch (err) { alert(err.message); return; }
                const t = document.getElementById('savedTag');
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 1600);
                drawerUpdates(p);
            });
        })
        .catch(err => {
            console.error('Failed to load history', err);
            document.getElementById('drBody').innerHTML = `<div style="color:red;">Error loading history</div>`;
        });
}

function drawerGov(p) {
    let html = `<div class="gov-overall"><span>Governance <b>${govPct(p)}%</b> signed off</span><div class="bar"><i style="width:${govPct(p)}%"></i></div></div>`;
    STAGES.forEach((s, i) => {
        const items = GOVERNANCE.filter(g => g.stage === s.id);
        html += `<div class="gstage"><div class="gstage-h"><span class="num">${i + 1}</span>${s.label}${p.stage === s.id ? '<span class="cur-tag">CURRENT</span>' : ''}</div>`;
        html += items.length ? items.map(it => `<div class="gitem"><span class="gname">${it.label}</span><select data-gid="${it.id}">${Object.entries(GOV_ST).map(([k, v]) => `<option value="${k}" ${p.governance[it.id] === k ? 'selected' : ''}>${v.l}</option>`).join('')}</select></div>`).join('') : `<div class="none">No formal deliverable at this gate.</div>`;
        html += `</div>`;
    });
    $('drBody').innerHTML = html;
    $('drBody').querySelectorAll('select[data-gid]').forEach(sel => {
        const setColor = () => { sel.style.color = GOV_ST[sel.value].c; };
        setColor();
        sel.addEventListener('change', () => {
            const gid = sel.getAttribute('data-gid');
            p.governance[gid] = sel.value;
            setColor();
            renderSidebar();
            renderContent();
            apiRequest('api/governance/update.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, item_key: apiGovKey(gid), status: apiGovStatus(sel.value) }) }).catch(err => alert(err.message));
            drawerGov(p);
        });
    });
}

// ----- NAVIGATION -----
$('navPortfolio').addEventListener('click', () => {
    screen = 'portfolio';
    selected = null;
    currentTab = 'dashboard';
    renderAll();
});

// ----- ADD PROJECT -----
$('addProjectBtn').addEventListener('click', async () => {
    if (loadError || DATA.length === 0) {
        alert('Cannot add project – data not loaded.');
        return;
    }
    const cid = (screen === 'company' && selected) ? selected : DATA[0].id;
    const c = co(cid);
    if (!c) return;
    const np = {
        id: cid + '-n' + uid(),
        company: cid,
        companyDbId: c.dbId,
        companyName: c.name,
        companyColor: c.color,
        name: '',
        stage: 'initiation',
        health: 'ontrack',
        owner: '',
        assignee_id: null,
        prio: 'normal',
        gateDue: '',
        compDue: '',
        progress: 0,
        currentUpdate: '',
        nextSteps: '',
        is_gate_overdue: 0,
        governance: Object.fromEntries(GOVERNANCE.map(g => [g.id, 'pending']))
    };
    c.projects.push(np);
    selected = cid;
    screen = 'company';
    renderAll();
    openDrawer(np.id);
    setTimeout(() => { const el = document.getElementById('e-name'); if (el) { el.focus(); el.select(); } }, 150);
});

// ----- IMPORT, EXPORT, SHARE -----
function initImport() {
    const overlay = document.getElementById('importOverlay');
    document.getElementById('importBtn').addEventListener('click', () => overlay.classList.add('open'));
    document.getElementById('importClose').addEventListener('click', () => { overlay.classList.remove('open'); resetImport(); });
    document.getElementById('importCancelBtn').addEventListener('click', () => { overlay.classList.remove('open'); resetImport(); });
    overlay.addEventListener('click', (e) => { if (e.target === overlay) { overlay.classList.remove('open'); resetImport(); } });
    function resetImport() {
        document.getElementById('importFile').value = '';
        document.getElementById('dzFileName').style.display = 'none';
        document.getElementById('dzFileName').textContent = '';
        document.getElementById('dropZone').classList.remove('file-selected');
        document.getElementById('importConfirmBtn').disabled = true;
        document.getElementById('importConfirmBtn').style.opacity = '0.5';
        document.getElementById('importConfirmBtn').style.cursor = 'not-allowed';
    }
    document.getElementById('importFile').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('dzFileName').textContent = '✓ ' + file.name;
            document.getElementById('dzFileName').style.display = 'block';
            document.getElementById('dropZone').classList.add('file-selected');
            document.getElementById('importConfirmBtn').disabled = false;
            document.getElementById('importConfirmBtn').style.opacity = '1';
            document.getElementById('importConfirmBtn').style.cursor = 'pointer';
        }
    });
    const dropZone = document.getElementById('dropZone');
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && (file.name.endsWith('.xlsx') || file.name.endsWith('.csv'))) {
            document.getElementById('dzFileName').textContent = '✓ ' + file.name;
            document.getElementById('dzFileName').style.display = 'block';
            document.getElementById('dropZone').classList.add('file-selected');
            document.getElementById('importConfirmBtn').disabled = false;
            document.getElementById('importConfirmBtn').style.opacity = '1';
            document.getElementById('importConfirmBtn').style.cursor = 'pointer';
        } else alert('Please upload a .xlsx or .csv file.');
    });
    document.getElementById('importConfirmBtn').addEventListener('click', () => {
        const fileInput = document.getElementById('importFile');
        const file = fileInput.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('file', file);
        fetch('api/import/excel.php', { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                alert(data.message || data.error || 'Import completed');
                if (data.status === 'success') {
                    overlay.classList.remove('open');
                    resetImport();
                    loadData();
                }
            }).catch(err => alert('Import error: ' + err.message));
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('exportMenu');
        if (e.target.closest('#exportDropdownBtn')) {
            menu.classList.toggle('show');
        } else if (!e.target.closest('.dropdown')) {
            menu.classList.remove('show');
        }
    });
    document.querySelectorAll('#exportMenu a[data-export]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.dataset.export;
            if (type === 'print') {
                window.print();
            } else {
                const params = new URLSearchParams();
                const search = document.getElementById('search').value;
                const company = document.getElementById('fCompany').value;
                const status = document.getElementById('fStatus').value;
                const assignee = document.getElementById('fAssignee').value;
                if (search) params.append('search', search);
                if (company) params.append('unit', company);
                if (status) params.append('status', status);
                if (assignee) params.append('assignee', assignee);
                window.location.href = `api/export/${type}.php?${params.toString()}`;
            }
            document.getElementById('exportMenu').classList.remove('show');
        });
    });
    document.getElementById('shareBtn').addEventListener('click', function() {
        fetch('api/share/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ report_type: 'portfolio', expires_in: 7 }),
            credentials: 'same-origin'
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') alert('Shareable link:\n' + data.url);
            else alert('Error creating share link: ' + (data.error || 'Unknown error'));
        }).catch(err => alert('Share error: ' + err.message));
    });
    initImport();

    document.getElementById('fCompany').addEventListener('change', function() {
        if (currentTab === 'dashboard') renderContent();
        else loadView(currentTab);
    });
    document.getElementById('fStatus').addEventListener('change', function() {
        if (currentTab === 'dashboard') renderContent();
        else loadView(currentTab);
    });
    document.getElementById('fAssignee').addEventListener('change', function() {
        if (currentTab === 'dashboard') renderContent();
        else loadView(currentTab);
    });
});

// ----- INIT -----
async function initApp() {
    await loadData();
}
</script>
</body>
</html>