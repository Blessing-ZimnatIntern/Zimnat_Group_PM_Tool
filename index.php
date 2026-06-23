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
    min-width: 0;
    background: transparent;
    padding: 0 18px;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .sidebar-logo-img {
    width: 100%;
    max-width: 100%;
    min-width: 0;
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
    gap: 10px;
  }
  .mobile-menu-btn {
    display: none;
    flex-shrink: 0;
    background: rgba(255,255,255,0.15);
    border: none;
    border-radius: 8px;
    width: 36px;
    height: 36px;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #fff;
  }
  .mobile-menu-btn svg { width: 20px; height: 20px; }
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
  .pill-btn.danger {
    background: var(--overdue);
    border-color: var(--overdue);
    color: #fff;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
  }
  .pill-btn.danger:hover {
    background: #b42318;
    box-shadow: 0 4px 16px rgba(220, 53, 69, 0.35);
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
  .tag-chip { font-size: 10px; font-weight: 600; padding: 1px 7px; border-radius: 9px; margin-left: 5px; display: inline-block; white-space: nowrap; }
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
  .drawer { position: fixed; top: 0; right: 0; height: 100vh; width: 68%; max-width: 1000px; min-width: 600px; background: #fff; box-shadow: -8px 0 40px rgba(0,0,0,0.12); transform: translateX(100%); transition: transform 0.3s cubic-bezier(0.4,0,0.2,1); z-index: 50; display: flex; flex-direction: column; }
  .drawer.open { transform: none; }
  .dr-head { padding: 22px 28px 0; border-bottom: 1px solid var(--line); background: #fff; }
  .dr-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
  .dr-co { font-size: 11px; font-weight: 700; color: #fff; padding: 2px 12px; border-radius: 5px; display: inline-block; margin-bottom: 8px; }
  .dr-head h2 { font-size: 20px; margin: 0; font-weight: 700; color: var(--zimnat-primary); letter-spacing: -0.3px; }
  .x { background: none; border: none; font-size: 28px; color: var(--muted); cursor: pointer; line-height: 1; padding: 4px 10px; border-radius: 6px; transition: background 0.15s; }
  .x:hover { background: var(--hover); }
  .dr-tabs { display: flex; gap: 4px; margin-top: 16px; overflow-x: auto; scrollbar-width: thin; }
  .dr-tab { padding: 9px 14px; font-size: 13px; font-weight: 500; color: var(--muted); cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.15s; white-space: nowrap; flex-shrink: 0; }
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
    .tabs-wrapper { padding: 0 16px; overflow-x: auto; }
    .tab { padding: 6px 12px; font-size: 12px; white-space: nowrap; }

    /* Sidebar becomes an off-canvas overlay instead of a permanent column */
    .mobile-menu-btn { display: flex; }
    .rail { display: none; }
    .side {
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 60;
      transform: translateX(-100%);
      transition: transform 0.25s ease;
      box-shadow: 8px 0 30px rgba(0,0,0,0.2);
    }
    .side.mobile-open { transform: translateX(0); }
    #sideScrim { z-index: 59; }
    #sideScrim.open { opacity: 1; pointer-events: auto; }
    .main { width: 100%; }

    .title-row h1 { font-size: 1.4rem; }
    .title-actions { flex-wrap: wrap; gap: 6px; }
    .title-actions .pill-btn { padding: 6px 10px; font-size: 12px; }
    .toolbar { padding: 10px 16px; }
    .search input { width: 100%; }
    .content { padding: 0 0 24px; }
    .sumwrap, .dr-body { padding: 16px; }

    /* Drawer becomes full-width on small screens instead of a fixed-minimum side panel */
    .drawer { width: 100%; min-width: 0; max-width: 100%; }
    .two { grid-template-columns: 1fr; }
    .attn-row { grid-template-columns: 1fr; gap: 4px; }
    .lh, .lrow { grid-template-columns: 1fr; }
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

  /* Loading skeletons */
  @keyframes skeleton-sweep { 0% { background-position: -200px 0; } 100% { background-position: 200px 0; } }
  .skeleton-row { display: flex; align-items: center; gap: 10px; padding: 10px 18px; }
  .skeleton-bar {
    height: 12px;
    border-radius: 4px;
    background: linear-gradient(90deg, #eef0f3 0%, #f7f8fa 50%, #eef0f3 100%);
    background-size: 200px 100%;
    animation: skeleton-sweep 1.2s ease-in-out infinite;
  }

  .bu-icon { width: 36px; height: 36px; border-radius: 9px; display: grid; place-items: center; flex-shrink: 0; }
  .bu-icon svg { width: 20px; height: 20px; }
  .space-icon.bu { width: 28px; height: 28px; border-radius: 7px; display: grid; place-items: center; background: transparent; }
  .space-icon.bu svg { width: 16px; height: 16px; }

  .dropdown { position: relative; display: inline-block; }
  .dropdown-menu { display: none; position: absolute; right: 0; background: #fff; min-width: 140px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); border-radius: 8px; z-index: 10; border: 1px solid var(--line); overflow: hidden; }
  .dropdown-menu.show { display: block; }
  .dropdown-menu a { display: block; padding: 8px 16px; color: var(--text); text-decoration: none; font-size: 13px; }
  .dropdown-menu a:hover { background: var(--hover); }
  .filter-popover { width: 280px; min-width: 280px; padding: 16px; overflow: visible; }
  .filter-popover .fld { margin-bottom: 12px; }
  .filter-popover .fld label { display: block; font-size: 11px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
  .filter-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 16px; height: 16px; padding: 0 4px; margin-left: 5px; background: #12A052; color: #fff; border-radius: 8px; font-size: 10px; font-weight: 700; }

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
      <div class="form-group" style="display:flex;align-items:flex-start;gap:8px;">
        <input type="checkbox" id="regConsent" required style="margin-top:3px;width:auto;">
        <label for="regConsent" style="font-size:12px;color:var(--zimnat-muted);font-weight:400;margin-bottom:0;">
          I consent to Zimnat processing my name, employee number and email to operate this portal, retained for as long as my account is active, in line with company data-protection policy.
        </label>
      </div>
      <button type="submit" class="auth-btn">Create Account</button>
    </form>
    <div class="auth-switch">Already have an account? <a id="showLogin">Sign in</a></div>
  </div>
</div>

<!-- ============================================================ -->
<!-- MAIN APP -->
<!-- ============================================================ -->
<div class="app" id="mainApp">
  <div class="scrim" id="sideScrim"></div>
  <nav class="rail"></nav>
  <aside class="side" id="sideNav">
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
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18"/><path d="M3 6h18"/><path d="M3 18h18"/></svg>
        </button>
        <h1 id="mainTitle">Project Management</h1>
        <div class="title-actions">
          <div class="dropdown" id="notifDropdownWrap">
            <button class="pill-btn" id="notifBtn" title="Notifications" style="position:relative;">
              🔔<span id="notifBadge" style="display:none;position:absolute;top:-4px;right:-4px;background:var(--overdue);color:#fff;font-size:10px;font-weight:700;border-radius:10px;padding:1px 5px;min-width:16px;text-align:center;line-height:1.4;"></span>
            </button>
            <div class="dropdown-menu" id="notifPanel" style="width:380px;max-height:480px;overflow-y:auto;padding:0;">
              <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:1px solid var(--line);">
                <b style="font-size:13px;">Notifications</b>
                <button class="pill-btn" id="notifMarkAllBtn" style="font-size:11px;padding:4px 10px;">Mark all read</button>
              </div>
              <div id="notifList"></div>
            </div>
          </div>
          <div class="dropdown">
            <button class="pill-btn" id="exportDropdownBtn">Export ▾</button>
            <div class="dropdown-menu" id="exportMenu">
              <a href="#" data-export="csv">CSV</a>
              <a href="#" data-export="excel">Excel</a>
              <a href="#" data-export="text">Text</a>
              <a href="#" data-export="copy">Copy to clipboard</a>
              <a href="#" data-export="print">Print / PDF</a>
            </div>
          </div>
          <button class="pill-btn" id="cmdkBtn" title="Search everything (Ctrl/Cmd+K)">🔎 Search <span style="opacity:.6;font-size:11px;">Ctrl K</span></button>
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
      <div class="dropdown" id="filterDropdownWrap">
        <button class="pill-btn" id="filterBtn" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
          Filters<span id="filterBadge" class="filter-badge" style="display:none;"></span>
        </button>
        <div class="dropdown-menu filter-popover" id="filterPopover">
          <div class="fld"><label>Business Unit</label><select class="fsel" id="fCompany"><option value="">All business units</option></select></div>
          <div class="fld">
            <label>Status</label>
            <select class="fsel" id="fStatus">
              <option value="">All statuses</option>
              <option value="ontrack">On Track</option>
              <option value="atrisk">At Risk</option>
              <option value="behind">Behind</option>
              <option value="overdue">Overdue</option>
              <option value="complete">Complete</option>
            </select>
          </div>
          <div class="fld"><label>Assigned To (Owner)</label><select class="fsel" id="fOwner"><option value="">All owners</option></select></div>
          <div class="fld"><label>Assignee (Allocator)</label><select class="fsel" id="fAssignee"><option value="">All assignees</option></select></div>
          <div class="fld"><label>Tag</label><select class="fsel" id="fTag"><option value="">All tags</option></select></div>
          <div class="fld" style="margin-bottom:10px;"><label>Stakeholder</label><select class="fsel" id="fStakeholder"><option value="">All stakeholders</option></select></div>
          <button class="pill-btn" id="clearFiltersBtn" type="button" style="width:100%;justify-content:center;">Clear all filters</button>
        </div>
      </div>
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

<!-- ============================================================ -->
<!-- CONFIRM MODAL -->
<!-- ============================================================ -->
<div class="import-overlay" id="confirmOverlay">
  <div class="import-modal" style="max-width:400px;text-align:center;">
    <div id="confirmIcon" style="font-size:32px;margin-bottom:8px;">⚠️</div>
    <h3 id="confirmTitle">Are you sure?</h3>
    <p class="sub" id="confirmMessage" style="margin-bottom:24px;"></p>
    <div class="import-actions" style="justify-content:center;">
      <button class="pill-btn" id="confirmCancelBtn">Cancel</button>
      <button class="pill-btn primary" id="confirmOkBtn">Confirm</button>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- REJECT ALLOCATION MODAL -->
<!-- ============================================================ -->
<div class="import-overlay" id="rejectAllocOverlay">
  <div class="import-modal" style="max-width:440px;">
    <button class="import-close" id="rejectAllocClose" type="button">&times;</button>
    <h3>Reject this allocation</h3>
    <p class="sub">Give the assignee a reason so they can reassign it — they'll see this in their notifications.</p>
    <div class="fld"><textarea id="rejectAllocReason" placeholder="e.g. I'm at full capacity this sprint, please reassign." style="min-height:90px;"></textarea></div>
    <div class="import-actions">
      <button class="pill-btn" id="rejectAllocCancel">Cancel</button>
      <button class="pill-btn danger" id="rejectAllocConfirm">Reject</button>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- SHARE LINK MODAL -->
<!-- ============================================================ -->
<div class="import-overlay" id="shareLinkOverlay">
  <div class="import-modal" style="max-width:480px;">
    <button class="import-close" id="shareLinkClose" type="button">&times;</button>
    <h3>Shareable link created</h3>
    <p class="sub">Anyone with this link can view a read-only snapshot of this report. It expires in 7 days.</p>
    <div style="display:flex;gap:8px;">
      <input type="text" id="shareLinkInput" readonly style="flex:1;padding:10px 14px;border:1px solid var(--line-2);border-radius:8px;font-size:13px;background:var(--bg);">
      <button class="pill-btn primary" id="shareLinkCopyBtn">Copy</button>
    </div>
  </div>
</div>

<!-- ============================================================ -->
<!-- GLOBAL SEARCH (Cmd/Ctrl+K) -->
<!-- ============================================================ -->
<div class="import-overlay" id="cmdkOverlay">
  <div class="import-modal" style="max-width:560px;padding:0;overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--line);">
      <input id="cmdkInput" placeholder="Search projects, owners, business units…" style="width:100%;border:none;outline:none;font-size:16px;font-family:var(--font);">
    </div>
    <div id="cmdkResults" style="max-height:420px;overflow-y:auto;padding:8px;"></div>
    <div style="padding:8px 18px;border-top:1px solid var(--line);font-size:11px;color:var(--muted-2);">↑↓ to navigate &middot; Enter to open &middot; Esc to close</div>
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
      <div class="dr-tab" data-dt="stakeholders">Stakeholders</div>
      <div class="dr-tab active" data-dt="overview">Overview</div>
      <div class="dr-tab" data-dt="updates">Update &amp; Next Steps</div>
      <div class="dr-tab" data-dt="gov">Governance</div>
      <div class="dr-tab" data-dt="gantt">Gantt</div>
      <div class="dr-tab" data-dt="time">Time</div>
      <div class="dr-tab" data-dt="assignees">Assignees</div>
      <div class="dr-tab" data-dt="deps">Dependencies</div>
      <div class="dr-tab" data-dt="comments">Comments</div>
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
            CURRENT_USER = data.user || null;
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
    const consent = document.getElementById('regConsent').checked;
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
    if (!consent) {
        registerError.textContent = 'You must consent to data processing to create an account.';
        registerError.classList.add('show');
        return;
    }
    try {
        const response = await fetch('api/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ full_name: fullName, username, email, password, consent })
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

document.getElementById('logoutBtn').addEventListener('click', async () => {
    if (await confirmDialog('Are you sure you want to logout?', { danger: false, confirmLabel: 'Logout' })) {
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
const CURRENCIES = ['USD', 'ZWL', 'ZAR', 'EUR', 'GBP'];
const TODAY = new Date().toISOString().split('T')[0];

// ----- DATA LAYER -----
let DATA = [];
let USERS = [];
let ALL_TAGS = [];
let ALL_STAKEHOLDER_NAMES = [];
let CURRENT_USER = null;
let loadError = null;
// Assignees are the allocator/manager who handed the project out — restricted to editor/admin.
// Owners ("Assigned To") are the developer doing the work and can be any role.
function allocatorUsers() { return USERS.filter(u => u.role === 'editor' || u.role === 'admin'); }

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

        try {
            const tagRes = await fetch('api/tags/list.php', { credentials: 'same-origin' });
            const tagData = await tagRes.json();
            ALL_TAGS = tagData.data || [];
        } catch (e) {
            console.warn('Could not fetch tags, using empty list', e);
            ALL_TAGS = [];
        }

        try {
            const stakeholderRes = await fetch('api/stakeholders/names.php', { credentials: 'same-origin' });
            const stakeholderData = await stakeholderRes.json();
            ALL_STAKEHOLDER_NAMES = stakeholderData.data || [];
        } catch (e) {
            console.warn('Could not fetch stakeholder names, using empty list', e);
            ALL_STAKEHOLDER_NAMES = [];
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
        const id = slug || 'unit-' + unit.id;
        return {
            dbId: Number(unit.id),
            id: id,
            name: unit.name,
            color: unit.color || '#12A052',
            projects: projects
                .filter(p => Number(p.business_unit_id) === Number(unit.id))
                .map(p => ({ ...mapApiProject(p), company: id }))
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
        owner: p.owner_name || p.owner || '',
        ownerId: p.owner_id || null,
        ownerAcceptanceStatus: p.owner_acceptance_status || 'pending',
        ownerRejectionReason: p.owner_rejection_reason || null,
        assignee_id: p.assignee_id || null,
        prio: p.priority || 'normal',
        gateDue: p.gate_due || '',
        compDue: p.completion_due || '',
        progress: Number(p.progress || 0),
        currentUpdate: p.current_update || '',
        nextSteps: p.next_steps || '',
        is_gate_overdue: p.is_gate_overdue || 0,
        budget: p.budget !== null && p.budget !== undefined ? Number(p.budget) : null,
        actualCost: p.actual_cost !== null && p.actual_cost !== undefined ? Number(p.actual_cost) : null,
        currency: p.currency || 'USD',
        tags: p.tags || [],
        stakeholders: p.stakeholders || [],
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
        // Merge updated fields back into local project. mapApiProject can't resolve the
        // correct business-unit slug on its own (the API doesn't return one per-project),
        // so keep the slug already assigned by mapApiData rather than letting it be overwritten.
        const updated = mapApiProject(response.data);
        delete updated.company;
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
function fmtMoney(n) { return '$' + Math.round(n || 0).toLocaleString(); }
function flag(p) { return `<svg class="flag" viewBox="0 0 24 24" fill="${PRIO[p]}"><path d="M5 3v18M5 4h12l-2 4 2 4H5"/></svg>`; }
function av(i) { return `<span class="avatar" style="background:${avC(i)}">${esc(i)}</span>`; }
function tagChips(p) {
    return (p.tags || []).map(t =>
        `<span class="tag-chip" style="background:${t.color}1a;color:${t.color};border:1px solid ${t.color}55;">${esc(t.name)}</span>`
    ).join('');
}
function skeletonRows(n = 3) {
    return Array.from({ length: n }, (_, i) => `
        <div class="skeleton-row">
            <div class="skeleton-bar" style="width:${i % 2 ? '55%' : '70%'};"></div>
            <div class="skeleton-bar" style="width:60px;margin-left:auto;"></div>
        </div>
    `).join('');
}
function effStatus(p) { if (p.health === 'complete') return 'complete'; if (p.compDue && p.compDue < TODAY) return 'overdue'; return p.health; }
function gateOverdue(p) { return p.is_gate_overdue == 1; }
function govPct(p) { const items = GOVERNANCE.filter(g => p.governance[g.id] !== 'na'); if (!items.length) return 100; const signed = items.filter(g => p.governance[g.id] === 'signed').length; return Math.round(signed / items.length * 100); }
function uid() { return Date.now().toString(36) + Math.random().toString(36).slice(2, 5); }

// ----- STATE -----
let screen = 'portfolio';
let selected = null;
let currentTab = 'dashboard';
let ganttProjectFilter = null;
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
    const tabs = ['dashboard', 'list', 'board', 'calendar', 'gantt', 'resources', 'report', 'mail'];
    const labels = {
        dashboard: 'Dashboard',
        list: 'List',
        board: 'Board',
        calendar: 'Calendar',
        gantt: 'Gantt',
        resources: 'Resources',
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
            ganttProjectFilter = null;
            $('tabs').querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            renderContent();
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
        allocatorUsers().forEach(u => {
            sel.innerHTML += `<option value="${u.id}">${esc(u.full_name)}</option>`;
        });
        sel.value = currentVal;
    }
    const ownerSel = document.getElementById('fOwner');
    if (ownerSel) {
        const currentVal = ownerSel.value;
        ownerSel.innerHTML = '<option value="">All owners</option>';
        USERS.forEach(u => {
            ownerSel.innerHTML += `<option value="${u.id}">${esc(u.full_name)}</option>`;
        });
        ownerSel.value = currentVal;
    }
    const buSel = document.getElementById('fCompany');
    if (buSel) {
        const current = buSel.value;
        buSel.innerHTML = '<option value="">All business units</option>' +
            DATA.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        buSel.value = current;
    }
    const tagSel = document.getElementById('fTag');
    if (tagSel) {
        const current = tagSel.value;
        tagSel.innerHTML = '<option value="">All tags</option>' +
            ALL_TAGS.map(t => `<option value="${esc(t.name)}">${esc(t.name)}</option>`).join('');
        tagSel.value = current;
    }
    const stakeholderSel = document.getElementById('fStakeholder');
    if (stakeholderSel) {
        const current = stakeholderSel.value;
        stakeholderSel.innerHTML = '<option value="">All stakeholders</option>' +
            ALL_STAKEHOLDER_NAMES.map(n => `<option value="${esc(n)}">${esc(n)}</option>`).join('');
        stakeholderSel.value = current;
    }
    updateFilterBadge();
}

function updateFilterBadge() {
    const badge = $('filterBadge');
    if (!badge) return;
    const count = ['fCompany', 'fStatus', 'fOwner', 'fAssignee', 'fTag', 'fStakeholder'].filter(id => $(id)?.value).length;
    badge.textContent = count;
    badge.style.display = count ? 'inline-flex' : 'none';
}

function getFiltered(scope) {
    const q = ($('search')?.value || '').trim().toLowerCase();
    const fst = $('fStatus')?.value || '';
    const fco = $('fCompany')?.value || '';
    const fAssignee = $('fAssignee')?.value || '';
    const fOwner = $('fOwner')?.value || '';
    const fTag = $('fTag')?.value || '';
    const fStakeholder = $('fStakeholder')?.value || '';

    return scope.filter(p => {
        if (q && !p.name.toLowerCase().includes(q)) return false;
        if (fst && effStatus(p) !== fst) return false;
        if (fco && p.company !== fco) return false;
        if (fAssignee && String(p.assignee_id) !== fAssignee) return false;
        if (fOwner && String(p.ownerId) !== fOwner) return false;
        if (fTag && !(p.tags || []).some(t => t.name === fTag)) return false;
        if (fStakeholder && !(p.stakeholders || []).some(s => s.name === fStakeholder)) return false;
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
    const totalBudget = projs.reduce((a, p) => a + (p.budget || 0), 0);
    const totalActual = projs.reduce((a, p) => a + (p.actualCost || 0), 0);
    const totalOwners = new Set(projs.map(p => p.ownerId).filter(Boolean)).size;

    const stats = [
        { label: 'Total Projects', key: 'all', value: total, color: '#1a4a7a' },
        { label: 'Total Budget', value: fmtMoney(totalBudget), color: '#2a5a8c', noDrill: true },
        { label: 'Total Actual Cost', value: fmtMoney(totalActual), color: totalActual > totalBudget && totalBudget > 0 ? HEALTH.overdue.color : '#2d9b6e', noDrill: true },
        { label: 'Total Owners / Assigned To', value: totalOwners, color: '#6b46c1', noDrill: true },
    ];

    let html = `
        <div style="margin:24px 24px 0;background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-weight:700;font-size:14px;color:#1a2332;">📋 Executive Summary</span>
                <span style="display:flex;gap:8px;">
                    <button class="pill-btn" id="execSummaryCopy" style="display:none;font-size:12px;padding:5px 10px;">Copy</button>
                    <button class="pill-btn" id="execSummaryRefresh" style="font-size:12px;padding:5px 10px;">Generate</button>
                </span>
            </div>
            <div id="execSummaryBody" style="font-size:13.5px;line-height:1.6;color:var(--text);">Click "Generate" for an auto-written summary of portfolio health, risk, budget, and resourcing.</div>
        </div>
    `;
    html += `<div class="dashboard-grid">`;
    stats.forEach(stat => {
        html += `
            <div class="stat-card" ${stat.noDrill ? '' : `data-drill="${stat.key}" data-title="${esc(stat.label)}"`}>
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
        <div class="chart-card">
            <h4 style="margin-bottom:12px;font-size:14px;font-weight:600;color:#1f2937;">Budget vs Actual by Business Unit</h4>
            <canvas id="budgetVsActualChart"></canvas>
        </div>
    </div>`;

    html += `<div class="sec-title" style="margin:0 0 12px;padding:0 24px;">Recent Activity</div>
        <div class="attn" id="activityFeed" style="margin:0 24px 24px;max-height:340px;overflow-y:auto;">${skeletonRows(4)}</div>`;

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

    html += `<div class="sec-title" style="margin:0 0 12px;padding:0 24px;">Overallocated Team Members</div>
        <div class="attn" id="overallocList" style="margin:0 24px 24px;">${skeletonRows(2)}</div>`;

    $('content').innerHTML = html;
    renderCharts(c, total, projs);
    renderOverallocation();
    renderActivityFeed();
    initExecSummary();

    $('content').querySelectorAll('.stat-card, .ring-card-custom').forEach(el => {
        if (!el.dataset.drill) { el.style.cursor = 'default'; return; }
        el.addEventListener('click', function() {
            const key = this.dataset.drill;
            const title = this.dataset.title;
            openDrill(title, key, projs);
        });
    });
    $('content').querySelectorAll('.attn-row').forEach(r => r.addEventListener('click', () => openDrawer(r.getAttribute('data-pid'))));
}

function renderOverallocation() {
    const el = document.getElementById('overallocList');
    if (!el) return;
    fetch('api/resources/heatmap.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const list = data.overallocated || [];
            el.innerHTML = list.length ? list.map(u => `
                <div class="attn-row" style="grid-template-columns:1fr 100px;cursor:default;">
                    <span>${esc(u.full_name)}</span>
                    <span class="pill" style="background:#dc2626;">${u.total_pct}%</span>
                </div>
            `).join('') : `<div style="padding:16px;color:var(--muted)">No one is over-allocated. 🎉</div>`;
        })
        .catch(() => { el.innerHTML = '<div style="padding:16px;color:var(--muted)">Could not load resource allocation.</div>'; });
}

function timeAgo(iso) {
    const seconds = Math.max(0, Math.floor((Date.now() - new Date(iso.replace(' ', 'T'))) / 1000));
    if (seconds < 60) return 'just now';
    const mins = Math.floor(seconds / 60);
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days}d ago`;
    return new Date(iso.replace(' ', 'T')).toLocaleDateString();
}

function renderActivityFeed(businessUnitId) {
    const el = document.getElementById('activityFeed');
    if (!el) return;
    const params = new URLSearchParams({ limit: 25 });
    if (businessUnitId) params.append('business_unit', businessUnitId);
    fetch(`api/activity/list.php?${params.toString()}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const items = data.data || [];
            el.innerHTML = items.length ? items.map(a => `
                <div class="attn-row" style="grid-template-columns:1fr 90px;${a.project_id ? 'cursor:pointer;' : 'cursor:default;'}" ${a.project_id ? `data-pid="${a.project_id}"` : ''}>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><b>${esc(a.user_name)}</b> ${esc(a.description)}</span>
                    <span style="font-size:11px;color:var(--muted-2);text-align:right;">${timeAgo(a.created_at)}</span>
                </div>
            `).join('') : `<div style="padding:16px;color:var(--muted)">No activity yet.</div>`;
            el.querySelectorAll('[data-pid]').forEach(row => row.addEventListener('click', () => openDrawer(row.dataset.pid)));
        })
        .catch(() => { el.innerHTML = '<div style="padding:16px;color:var(--muted)">Could not load recent activity.</div>'; });
}

function initExecSummary(businessUnitId) {
    const refreshBtn = document.getElementById('execSummaryRefresh');
    const copyBtn = document.getElementById('execSummaryCopy');
    const body = document.getElementById('execSummaryBody');
    if (!refreshBtn) return;
    let lastSummary = '';
    refreshBtn.addEventListener('click', () => {
        body.textContent = 'Generating…';
        const params = new URLSearchParams();
        if (businessUnitId) params.append('business_unit', businessUnitId);
        fetch(`api/insights/executive_summary.php?${params.toString()}`, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                lastSummary = data.summary || 'No summary available.';
                body.textContent = lastSummary;
                copyBtn.style.display = 'inline-flex';
            })
            .catch(() => { body.textContent = 'Could not generate summary.'; });
    });
    copyBtn.addEventListener('click', () => {
        navigator.clipboard.writeText(lastSummary).then(() => alert('Summary copied to clipboard.'));
    });
}

// ----- MOBILE SIDEBAR (off-canvas nav below 768px) -----
function initMobileNav() {
    const sideNav = $('sideNav');
    const scrim = $('sideScrim');
    const openNav = () => { sideNav.classList.add('mobile-open'); scrim.classList.add('open'); };
    const closeNav = () => { sideNav.classList.remove('mobile-open'); scrim.classList.remove('open'); };
    $('mobileMenuBtn').addEventListener('click', openNav);
    scrim.addEventListener('click', closeNav);
    // Close the overlay whenever the user picks something from the sidebar.
    // Capture phase: .proj-row's own handler calls stopPropagation(), which would
    // otherwise prevent a bubble-phase listener here from ever seeing the click.
    sideNav.addEventListener('click', e => {
        if (e.target.closest('.nav-item, .space-row, .proj-row')) closeNav();
    }, { capture: true });
}

// ----- NOTIFICATIONS (owner allocation accept/reject workflow) -----
async function refreshNotifBadge() {
    if (!CURRENT_USER) return;
    try {
        const res = await fetch('api/notifications/unread_count.php', { credentials: 'same-origin' });
        const data = await res.json();
        const count = data.data?.count || 0;
        const badge = document.getElementById('notifBadge');
        if (!badge) return;
        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = count ? 'inline-block' : 'none';
    } catch (e) { /* non-fatal */ }
}

async function loadNotifications() {
    const list = document.getElementById('notifList');
    list.innerHTML = skeletonRows(3);
    try {
        const res = await fetch('api/notifications/list.php', { credentials: 'same-origin' });
        const data = await res.json();
        const items = data.data || [];
        list.innerHTML = items.map(n => {
            const canRespond = n.type === 'project_assigned' && n.owner_acceptance_status === 'pending';
            return `
                <div style="padding:12px 14px;border-bottom:1px solid var(--line);cursor:pointer;${n.is_read ? '' : 'background:#f0f7ff;'}" data-nid="${n.id}" data-pid="${n.project_id}">
                    <div style="font-size:13px;line-height:1.5;margin-bottom:4px;">${esc(n.message)}</div>
                    <div style="font-size:11px;color:var(--muted-2);margin-bottom:${canRespond ? '8' : '0'}px;">${timeAgo(n.created_at)}</div>
                    ${canRespond ? `
                        <div style="display:flex;gap:6px;">
                            <button class="pill-btn primary notif-accept" data-pid="${n.project_id}" data-nid="${n.id}" style="font-size:11px;padding:4px 10px;">Start Working</button>
                            <button class="pill-btn notif-reject" data-pid="${n.project_id}" data-nid="${n.id}" style="font-size:11px;padding:4px 10px;">Reject</button>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('') || '<div style="padding:24px 14px;text-align:center;color:var(--muted);font-size:13px;">No notifications yet.</div>';

        list.querySelectorAll('.notif-accept').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                try {
                    await apiRequest('api/projects/respond.php', { method: 'POST', body: JSON.stringify({ project_id: btn.dataset.pid, action: 'accept' }) });
                    await apiRequest('api/notifications/mark_read.php', { method: 'POST', body: JSON.stringify({ id: +btn.dataset.nid }) });
                    await loadData();
                    loadNotifications();
                    refreshNotifBadge();
                } catch (err) { alert(err.message); }
            });
        });
        list.querySelectorAll('.notif-reject').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                openRejectAllocModal(btn.dataset.pid, btn.dataset.nid);
            });
        });
        list.querySelectorAll('[data-nid]').forEach(row => {
            row.addEventListener('click', async () => {
                const nid = +row.dataset.nid;
                try { await apiRequest('api/notifications/mark_read.php', { method: 'POST', body: JSON.stringify({ id: nid }) }); refreshNotifBadge(); } catch (e) {}
                document.getElementById('notifPanel').classList.remove('show');
                openDrawer(row.dataset.pid);
            }, { once: true });
        });
    } catch (e) {
        list.innerHTML = '<div style="padding:16px;color:var(--zimnat-danger);font-size:13px;">Could not load notifications.</div>';
    }
}

function openRejectAllocModal(projectId, notifId) {
    const overlay = document.getElementById('rejectAllocOverlay');
    document.getElementById('rejectAllocReason').value = '';
    overlay.classList.add('open');
    const confirmBtn = document.getElementById('rejectAllocConfirm');
    const cancelBtn = document.getElementById('rejectAllocCancel');
    const closeBtn = document.getElementById('rejectAllocClose');
    const cleanup = () => {
        overlay.classList.remove('open');
        confirmBtn.replaceWith(confirmBtn.cloneNode(true));
        cancelBtn.replaceWith(cancelBtn.cloneNode(true));
        closeBtn.replaceWith(closeBtn.cloneNode(true));
        bindRejectModalCloseHandlers();
    };
    const onConfirm = async () => {
        const reason = document.getElementById('rejectAllocReason').value.trim();
        if (!reason) { alert('Please give a reason for rejecting this allocation.'); return; }
        try {
            await apiRequest('api/projects/respond.php', { method: 'POST', body: JSON.stringify({ project_id: projectId, action: 'reject', reason }) });
            await apiRequest('api/notifications/mark_read.php', { method: 'POST', body: JSON.stringify({ id: +notifId }) });
            cleanup();
            await loadData();
            loadNotifications();
            refreshNotifBadge();
        } catch (err) { alert(err.message); }
    };
    document.getElementById('rejectAllocConfirm').addEventListener('click', onConfirm);
    document.getElementById('rejectAllocCancel').addEventListener('click', cleanup);
    document.getElementById('rejectAllocClose').addEventListener('click', cleanup);
}
function bindRejectModalCloseHandlers() {
    document.getElementById('rejectAllocCancel').addEventListener('click', () => document.getElementById('rejectAllocOverlay').classList.remove('open'));
    document.getElementById('rejectAllocClose').addEventListener('click', () => document.getElementById('rejectAllocOverlay').classList.remove('open'));
}

// ----- CUSTOM CONFIRM MODAL (Promise-based replacement for window.confirm) -----
function showShareLinkModal(url) {
    document.getElementById('shareLinkInput').value = url;
    document.getElementById('shareLinkOverlay').classList.add('open');
}

function confirmDialog(message, opts = {}) {
    const overlay = $('confirmOverlay');
    $('confirmTitle').textContent = opts.title || 'Are you sure?';
    $('confirmMessage').textContent = message;
    $('confirmIcon').textContent = opts.danger === false ? '❓' : '⚠️';
    const okBtn = $('confirmOkBtn');
    okBtn.textContent = opts.confirmLabel || (opts.danger === false ? 'Confirm' : 'Delete');
    okBtn.className = 'pill-btn ' + (opts.danger === false ? 'primary' : 'danger');
    overlay.classList.add('open');

    return new Promise(resolve => {
        const cleanup = (result) => {
            overlay.classList.remove('open');
            okBtn.removeEventListener('click', onOk);
            $('confirmCancelBtn').removeEventListener('click', onCancel);
            overlay.removeEventListener('click', onOverlay);
            resolve(result);
        };
        const onOk = () => cleanup(true);
        const onCancel = () => cleanup(false);
        const onOverlay = (e) => { if (e.target === overlay) cleanup(false); };
        okBtn.addEventListener('click', onOk);
        $('confirmCancelBtn').addEventListener('click', onCancel);
        overlay.addEventListener('click', onOverlay);
    });
}

// ----- GLOBAL SEARCH (Cmd/Ctrl+K) -----
function initCommandPalette() {
    const overlay = $('cmdkOverlay');
    const input = $('cmdkInput');
    const results = $('cmdkResults');
    let activeIdx = -1;
    let matches = [];

    function openPalette() {
        overlay.classList.add('open');
        input.value = '';
        renderResults('');
        setTimeout(() => input.focus(), 30);
    }
    function closePalette() {
        overlay.classList.remove('open');
    }
    function renderResults(q) {
        q = q.trim().toLowerCase();
        const all = DATA.flatMap(c => c.projects);
        matches = !q ? all.slice(0, 8) : all.filter(p =>
            p.name.toLowerCase().includes(q) ||
            (p.owner || '').toLowerCase().includes(q) ||
            (p.companyName || '').toLowerCase().includes(q) ||
            (p.currentUpdate || '').toLowerCase().includes(q) ||
            (p.tags || []).some(t => t.name.toLowerCase().includes(q))
        ).slice(0, 15);
        activeIdx = matches.length ? 0 : -1;
        renderList();
    }
    function renderList() {
        results.innerHTML = matches.length ? matches.map((p, i) => `
            <div class="cmdk-row" data-idx="${i}" style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;cursor:pointer;${i === activeIdx ? 'background:var(--hover);' : ''}">
                <span style="width:8px;height:8px;border-radius:50%;background:${p.companyColor};flex-shrink:0;"></span>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13.5px;">${esc(p.name)}</span>
                <span style="font-size:11px;color:var(--muted-2);">${esc(p.companyName)}</span>
                <span class="pill" style="background:${HEALTH[effStatus(p)].color};">${HEALTH[effStatus(p)].label}</span>
            </div>
        `).join('') : `<div style="padding:20px;text-align:center;color:var(--muted)">No matching projects.</div>`;
        results.querySelectorAll('.cmdk-row').forEach(row => {
            row.addEventListener('mouseenter', () => { activeIdx = +row.dataset.idx; renderList(); });
            row.addEventListener('click', () => selectMatch(+row.dataset.idx));
        });
    }
    function selectMatch(i) {
        const p = matches[i];
        if (!p) return;
        closePalette();
        screen = 'company';
        selected = p.company;
        currentTab = 'dashboard';
        renderAll();
        openDrawer(p.id);
    }

    document.getElementById('cmdkBtn').addEventListener('click', openPalette);
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            overlay.classList.contains('open') ? closePalette() : openPalette();
        } else if (e.key === 'Escape' && overlay.classList.contains('open')) {
            closePalette();
        }
    });
    overlay.addEventListener('click', e => { if (e.target === overlay) closePalette(); });
    input.addEventListener('input', () => renderResults(input.value));
    input.addEventListener('keydown', e => {
        if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, matches.length - 1); renderList(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); renderList(); }
        else if (e.key === 'Enter') { e.preventDefault(); selectMatch(activeIdx); }
    });
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

    const ctx3 = document.getElementById('budgetVsActualChart');
    if (ctx3) {
        if (window._budgetVsActual) window._budgetVsActual.destroy();
        const byUnit = {};
        filteredProjects.forEach(p => {
            if (!byUnit[p.companyName]) byUnit[p.companyName] = { budget: 0, actual: 0 };
            byUnit[p.companyName].budget += p.budget || 0;
            byUnit[p.companyName].actual += p.actualCost || 0;
        });
        const unitNames = Object.keys(byUnit);
        window._budgetVsActual = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: unitNames,
                datasets: [
                    { label: 'Budget', data: unitNames.map(u => byUnit[u].budget), backgroundColor: '#2a5a8c' },
                    { label: 'Actual', data: unitNames.map(u => byUnit[u].actual), backgroundColor: '#dc2626' }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
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
    const unitBudget = projs.reduce((a, p) => a + (p.budget || 0), 0);
    const unitActual = projs.reduce((a, p) => a + (p.actualCost || 0), 0);
    const unitOwners = new Set(projs.map(p => p.ownerId).filter(Boolean)).size;
    let html = `<div class="sumwrap" style="padding-bottom:6px">
        <div style="background:#fff;border:1px solid var(--line);border-radius:12px;padding:18px 22px;margin-bottom:18px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-weight:700;font-size:14px;color:#1a2332;">📋 Executive Summary</span>
                <span style="display:flex;gap:8px;">
                    <button class="pill-btn" id="execSummaryCopy" style="display:none;font-size:12px;padding:5px 10px;">Copy</button>
                    <button class="pill-btn" id="execSummaryRefresh" style="font-size:12px;padding:5px 10px;">Generate</button>
                </span>
            </div>
            <div id="execSummaryBody" style="font-size:13.5px;line-height:1.6;color:var(--text);">Click "Generate" for an auto-written summary of this business unit's health, risk, and budget.</div>
        </div>
        <div class="stat-row">${cards.map(([l, n, col, key]) => `
            <div class="scard" data-drill="${key}" data-title="${esc(l)}"><div class="n" style="color:${col}">${n}</div><div class="l">${l}</div></div>
        `).join('')}</div>
        <div class="stat-row" style="margin-top:10px">
            <div class="scard" style="cursor:default"><div class="n" style="color:#2a5a8c">${fmtMoney(unitBudget)}</div><div class="l">Total Budget</div></div>
            <div class="scard" style="cursor:default"><div class="n" style="color:${unitActual > unitBudget && unitBudget > 0 ? HEALTH.overdue.color : '#2d9b6e'}">${fmtMoney(unitActual)}</div><div class="l">Total Actual Cost</div></div>
            <div class="scard" style="cursor:default"><div class="n" style="color:#6b46c1">${unitOwners}</div><div class="l">Total Owners / Assigned To</div></div>
        </div>
        <div class="sec-title" style="margin:18px 0 10px">Stage Gate Distribution</div>
        <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px">${STAGES.map(s => `<span style="background:var(--bg);padding:4px 12px;border-radius:6px;border:1px solid var(--line)"><b>${stg[s.id]}</b> ${s.label}</span>`).join('')}</div>
        <div class="sec-title" style="margin:18px 0 10px">Recent Activity</div>
        <div class="attn" id="activityFeed" style="max-height:280px;overflow-y:auto;">${skeletonRows(4)}</div>
        </div>`;
    $('content').innerHTML = html;
    $('content').querySelectorAll('.scard[data-drill]').forEach(card => card.addEventListener('click', () => openDrill(`${card.dataset.title} Projects`, card.dataset.drill, projs)));
    renderActivityFeed(comp.dbId);
    initExecSummary(comp.dbId);
}

// ----- LIST / BOARD TABS (single rendering path for both portfolio-wide and BU-scoped views) -----
function renderProjectsView(viewName) {
    if (loadError) {
        $('content').innerHTML = `<div style="padding:40px;text-align:center;color:var(--zimnat-danger);">⚠️ ${esc(loadError)}</div>`;
        return;
    }
    const scope = (screen === 'company' && selected) ? (co(selected)?.projects || []) : allProjects();
    const projs = getFiltered(scope);
    $('content').innerHTML = viewName === 'board' ? boardHtml(projs) : listHtml(projs);
    if (viewName === 'board') wireBoard(); else wireList();
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
            ${items.map(p => { const es = effStatus(p); const gateFlag = gateOverdue(p); return `<div class="lrow" data-pid="${p.id}"><div class="lname">${flag(p.prio)}<span class="ptxt">${esc(p.name)}</span>${tagChips(p)}</div><div><span class="pill" style="background:${HEALTH[es].color}">${HEALTH[es].label}</span></div><div>${av(p.owner)}</div><div class="gov-mini">${govPct(p)}%</div><div class="due ${gateFlag ? 'over' : ''}">${fmt(p.gateDue)}${gateFlag ? ' ⚠' : ''}</div><div class="due ${p.compDue < TODAY && es !== 'complete' ? 'over' : ''}">${fmt(p.compDue)}</div></div>`; }).join('')}</div></div>`;
    });
    html += `</div>`;
    return html;
}

function wireList() {
    $('content').querySelectorAll('.group-head').forEach(h => h.addEventListener('click', () => { const st = h.closest('.group').getAttribute('data-st'); collapsed[selected + st] = !collapsed[selected + st]; renderProjectsView('list'); }));
    $('content').querySelectorAll('.lrow').forEach(r => r.addEventListener('click', () => openDrawer(r.getAttribute('data-pid'))));
}

function boardHtml(projs) {
    return `<div class="board">` + STAGES.map(st => {
        const items = projs.filter(p => p.stage === st.id);
        return `<div class="col"><div class="col-head"><span class="col-dot" style="background:var(--cu)"></span><span class="ct">${st.label}</span><span class="cc">${items.length}</span></div>
            <div class="col-body">${items.map(p => { const es = effStatus(p); return `<div class="card" data-pid="${p.id}"><div class="cn">${flag(p.prio)}<span>${esc(p.name)}</span>${tagChips(p)}</div><div class="cm">${av(p.owner)}<span class="pill" style="background:${HEALTH[es].color}">${HEALTH[es].label}</span></div></div>`; }).join('') || '<div style="color:var(--muted-2);font-size:12px;padding:4px;text-align:center">—</div>'}</div></div>`;
    }).join('') + `</div>`;
}
function wireBoard() {
    $('content').querySelectorAll('.card').forEach(c => c.addEventListener('click', () => openDrawer(c.getAttribute('data-pid'))));
}

// 🔥 FIXED: loadView passes numeric business_unit ID
function loadView(viewName) {
    const urlMap = {
        'calendar': 'views/calender.php',
        'gantt': 'views/gantt.php',
        'resources': 'views/resources.php',
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
    const tag = document.getElementById('fTag').value;
    if (company) params.append('business_unit', company);
    if (status) params.append('status', status);
    if (assignee) params.append('assignee', assignee);
    if (tag) params.append('tag', tag);
    if (screen === 'company' && selected) {
        const unit = co(selected);
        if (unit) params.append('business_unit', unit.dbId); // 🔥 FIX: send numeric ID
    }
    if (viewName === 'gantt' && ganttProjectFilter) {
        params.append('project', ganttProjectFilter);
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
    if (currentTab === 'dashboard') {
        screen === 'portfolio' ? renderDashboard() : renderCompany();
    } else if (currentTab === 'list' || currentTab === 'board') {
        renderProjectsView(currentTab);
    } else {
        loadView(currentTab);
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
    updateFilterBadge();
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
    if (drawerTab === 'stakeholders') drawerStakeholders(p);
    else if (drawerTab === 'overview') drawerOverview(p);
    else if (drawerTab === 'updates') drawerUpdates(p);
    else if (drawerTab === 'gov') drawerGov(p);
    else if (drawerTab === 'gantt') drawerGantt(p);
    else if (drawerTab === 'time') drawerTime(p);
    else if (drawerTab === 'assignees') drawerAssignees(p);
    else if (drawerTab === 'deps') drawerDependencies(p);
    else drawerComments(p);
}

function drawerGantt(p) {
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before viewing its Gantt chart.</p>';
        return;
    }
    if (!p.gateDue && !p.compDue) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Set a stage gate due date and/or completion date to see this project on a Gantt chart.</p>';
        return;
    }
    $('drBody').innerHTML = `
        <div style="margin-bottom:14px;display:flex;justify-content:flex-end;">
            <button class="pill-btn" id="dr-gantt-print">Download / Print</button>
        </div>
        <div id="drGanttChart"></div>
    `;
    const start = p.gateDue || p.compDue;
    const end = p.compDue || p.gateDue;
    const task = {
        id: p.id,
        name: p.name,
        start: start,
        end: start === end ? end : end,
        progress: p.progress / 100,
        custom_class: 'gantt-' + effStatus(p)
    };
    // Frappe Gantt needs distinct start/end dates
    if (task.start === task.end) {
        const d = new Date(task.end);
        d.setDate(d.getDate() + 1);
        task.end = d.toISOString().split('T')[0];
    }
    new Gantt('#drGanttChart', [task], {});
    scrollGanttToToday('drGanttChart');
    document.getElementById('dr-gantt-print').addEventListener('click', () => window.print());
}

// Frappe Gantt defaults to the start of its date range, not today — scroll the
// inner .gantt-container so today's highlighted column is centered in view.
function scrollGanttToToday(mountId) {
    const mount = document.getElementById(mountId);
    if (!mount) return;
    const wrapper = mount.querySelector('.gantt-container') || mount;
    const todayRect = mount.querySelector('.today-highlight');
    if (!todayRect) return;
    const rectBox = todayRect.getBoundingClientRect();
    const wrapperBox = wrapper.getBoundingClientRect();
    const offset = (rectBox.left - wrapperBox.left) + wrapper.scrollLeft - wrapper.clientWidth / 2;
    wrapper.scrollLeft = Math.max(0, offset);
}

function drawerTime(p) {
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before logging time against it.</p>';
        return;
    }
    $('drBody').innerHTML = skeletonRows(4);
    fetch(`api/time/list.php?project_id=${encodeURIComponent(p.id)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const entries = data.data || [];
            const totalHours = data.total_hours || 0;
            const rowsHtml = entries.map(e => `
                <tr>
                    <td style="padding:6px 8px;">${esc(e.date)}</td>
                    <td style="padding:6px 8px;text-align:right;">${Number(e.hours)}</td>
                    <td style="padding:6px 8px;">${esc(e.description || '')}</td>
                    <td style="padding:6px 8px;color:var(--muted);">${esc(e.logged_by)}</td>
                    <td style="padding:6px 8px;text-align:right;"><button class="x" style="font-size:16px;" data-tid="${e.id}" title="Delete entry">&times;</button></td>
                </tr>
            `).join('') || `<tr><td colspan="5" style="padding:12px 8px;color:var(--muted);text-align:center;">No time logged yet.</td></tr>`;

            $('drBody').innerHTML = `
                <div class="fld"><label>Log time</label></div>
                <div class="two">
                    <div class="fld"><label>Date</label><input type="date" id="t-date" value="${TODAY}"></div>
                    <div class="fld"><label>Hours</label><input type="number" id="t-hours" min="0.25" step="0.25" placeholder="e.g. 2.5"></div>
                </div>
                <div class="fld"><label>Description</label><input id="t-desc" placeholder="What did you work on?"></div>
                <button class="pill-btn primary" id="t-log">Log Time</button>
                <div style="margin-top:24px;border-top:2px solid var(--line);padding-top:16px;">
                    <h4 style="font-size:14px;font-weight:600;margin-bottom:12px;">⏱ Logged Time — Total: ${totalHours} hrs</h4>
                    <table style="width:100%;font-size:13px;border-collapse:collapse;">
                        <thead><tr style="text-align:left;border-bottom:1px solid var(--line);color:var(--muted-2);font-size:11px;text-transform:uppercase;">
                            <th style="padding:6px 8px;">Date</th><th style="padding:6px 8px;text-align:right;">Hours</th><th style="padding:6px 8px;">Description</th><th style="padding:6px 8px;">Logged by</th><th></th>
                        </tr></thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </div>
            `;
            document.getElementById('t-log').addEventListener('click', async () => {
                const date = document.getElementById('t-date').value;
                const hours = parseFloat(document.getElementById('t-hours').value);
                const description = document.getElementById('t-desc').value;
                if (!date || !hours || hours <= 0) { alert('Date and a positive number of hours are required.'); return; }
                try {
                    await apiRequest('api/time/create.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, date, hours, description }) });
                    drawerTime(p);
                } catch (err) { alert(err.message); }
            });
            $('drBody').querySelectorAll('[data-tid]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!(await confirmDialog('This will permanently remove the logged time entry.', { title: 'Delete time entry?' }))) return;
                    try {
                        await apiRequest(`api/time/delete.php?id=${btn.dataset.tid}`, { method: 'DELETE', headers: {} });
                        drawerTime(p);
                    } catch (err) { alert(err.message); }
                });
            });
        });
}

function drawerAssignees(p) {
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before assigning team members to it.</p>';
        return;
    }
    $('drBody').innerHTML = skeletonRows(4);
    fetch(`api/assignees/list.php?project_id=${encodeURIComponent(p.id)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const current = {};
            (data.data || []).forEach(a => current[a.user_id] = a.allocation_pct);

            const rowsHtml = allocatorUsers().map(u => {
                const checked = current[u.id] !== undefined;
                const pct = current[u.id] ?? 100;
                return `
                    <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--line);">
                        <input type="checkbox" class="a-check" data-uid="${u.id}" ${checked ? 'checked' : ''} style="width:auto;">
                        <span style="flex:1;font-size:13.5px;">${esc(u.full_name)}</span>
                        <input type="number" class="a-pct" data-uid="${u.id}" min="1" max="100" value="${pct}" ${checked ? '' : 'disabled'} style="width:70px;padding:5px 8px;">
                        <span style="font-size:12px;color:var(--muted);">%</span>
                    </div>
                `;
            }).join('') || '<p style="color:var(--muted)">No editor/admin users available.</p>';

            $('drBody').innerHTML = `
                <p style="font-size:12px;color:var(--muted);margin-bottom:14px;">Assign the editor(s)/admin(s) who are allocating and managing this project, and set each person's allocation — the percentage of their capacity this project consumes.</p>
                ${rowsHtml}
                <button class="pill-btn primary" id="a-save" style="margin-top:16px;">Save Assignees</button>
                <span class="saved-tag" id="aSavedTag">Saved ✓</span>
            `;

            $('drBody').querySelectorAll('.a-check').forEach(cb => {
                cb.addEventListener('change', () => {
                    const pctInput = $('drBody').querySelector(`.a-pct[data-uid="${cb.dataset.uid}"]`);
                    pctInput.disabled = !cb.checked;
                });
            });

            document.getElementById('a-save').addEventListener('click', async () => {
                const assignees = [];
                $('drBody').querySelectorAll('.a-check:checked').forEach(cb => {
                    const pctInput = $('drBody').querySelector(`.a-pct[data-uid="${cb.dataset.uid}"]`);
                    assignees.push({ user_id: parseInt(cb.dataset.uid), allocation_pct: parseInt(pctInput.value) || 100 });
                });
                try {
                    const res = await apiRequest('api/assignees/set.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, assignees }) });
                    const t = document.getElementById('aSavedTag');
                    t.classList.add('show');
                    setTimeout(() => t.classList.remove('show'), 1600);
                    if (res.warnings && res.warnings.length) {
                        alert('Heads up — these team members are now allocated over 100% of their capacity across all projects:\n\n' +
                            res.warnings.map(w => `${w.full_name}: ${w.total_pct}%`).join('\n'));
                    }
                } catch (err) { alert(err.message); }
            });
        });
}

function drawerDependencies(p) {
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before setting dependencies.</p>';
        return;
    }
    $('drBody').innerHTML = skeletonRows(4);
    fetch(`api/dependencies/list.php?project_id=${encodeURIComponent(p.id)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const blockedBy = data.blocked_by || [];
            const blocking = data.blocking || [];
            const blockedIds = new Set(blockedBy.map(b => b.project_id));
            const allOthers = allProjects().filter(other => other.id !== p.id);

            const rowsHtml = allOthers.map(other => {
                const checked = blockedIds.has(other.id);
                const es = effStatus(other);
                return `
                    <div style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid var(--line);">
                        <input type="checkbox" class="dep-check" data-pid="${other.id}" ${checked ? 'checked' : ''} style="width:auto;">
                        <span style="flex:1;font-size:13px;">${esc(other.name)}</span>
                        <span style="font-size:11px;color:var(--muted-2);">${esc(other.companyName)}</span>
                        <span class="pill" style="background:${HEALTH[es].color};font-size:10px;">${HEALTH[es].label}</span>
                    </div>
                `;
            }).join('') || '<p style="color:var(--muted)">No other projects exist yet.</p>';

            const blockingHtml = blocking.length ? blocking.map(b => `
                <div class="attn-row" style="grid-template-columns:1fr 90px;cursor:pointer;" data-pid="${b.project_id}">
                    <span>${esc(b.name)}</span>
                    <span class="pill" style="background:${HEALTH[b.effective_status]?.color || '#6b7280'};">${HEALTH[b.effective_status]?.label || b.effective_status}</span>
                </div>
            `).join('') : '<div style="padding:12px;color:var(--muted);">No other project depends on this one.</div>';

            $('drBody').innerHTML = `
                <p style="font-size:12px;color:var(--muted);margin-bottom:10px;">Select the projects this one is blocked by. A dependency that isn't yet Complete is a risk to this project's timeline.</p>
                <div style="max-height:260px;overflow-y:auto;border:1px solid var(--line);border-radius:8px;padding:0 10px;">${rowsHtml}</div>
                <button class="pill-btn primary" id="dep-save" style="margin-top:14px;">Save Dependencies</button>
                <span class="saved-tag" id="depSavedTag">Saved ✓</span>
                <div class="sec-title" style="margin:20px 0 8px;">Blocked by this project</div>
                <div class="attn">${blockingHtml}</div>
            `;

            $('drBody').querySelectorAll('.attn-row[data-pid]').forEach(row => row.addEventListener('click', () => openDrawer(row.dataset.pid)));

            document.getElementById('dep-save').addEventListener('click', async () => {
                const dependsOn = Array.from($('drBody').querySelectorAll('.dep-check:checked')).map(c => c.dataset.pid);
                try {
                    const res = await apiRequest('api/dependencies/set.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, depends_on: dependsOn }) });
                    const t = document.getElementById('depSavedTag');
                    t.classList.add('show');
                    setTimeout(() => t.classList.remove('show'), 1600);
                    if (res.rejected_cycles && res.rejected_cycles.length) {
                        alert('Some dependencies were rejected because they would create a circular dependency: ' + res.rejected_cycles.map(id => findP(id)?.name || id).join(', '));
                    }
                    drawerDependencies(p);
                } catch (err) { alert(err.message); }
            });
        });
}

function drawerComments(p) {
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before commenting on it.</p>';
        return;
    }
    $('drBody').innerHTML = skeletonRows(3);
    fetch(`api/comments/list.php?project_id=${encodeURIComponent(p.id)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const comments = data.data || [];
            const renderBody = (text) => esc(text).replace(/@([A-Za-z][A-Za-z .'-]*)/g, (m, name) => {
                return USERS.some(u => u.full_name.toLowerCase() === name.trim().toLowerCase()) ? `<span style="color:var(--cu);font-weight:600;">@${esc(name)}</span>` : m;
            });
            const commentsHtml = comments.map(c => `
                <div style="padding:10px 0;border-bottom:1px solid var(--line);">
                    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:3px;">
                        <span style="font-weight:600;font-size:13px;">${esc(c.full_name)}</span>
                        <span style="font-size:11px;color:var(--muted-2);">${new Date(c.created_at.replace(' ', 'T')).toLocaleString()}</span>
                    </div>
                    <div style="font-size:13px;line-height:1.5;">${renderBody(c.body)}</div>
                    <button class="comment-del" data-cid="${c.id}" style="border:none;background:none;color:var(--muted-2);font-size:11px;cursor:pointer;padding:2px 0;margin-top:2px;">Delete</button>
                </div>
            `).join('') || '<div style="padding:16px 0;color:var(--muted);text-align:center;">No comments yet. Start the discussion below.</div>';

            $('drBody').innerHTML = `
                <div style="max-height:340px;overflow-y:auto;margin-bottom:14px;">${commentsHtml}</div>
                <div class="fld"><label>Add a comment — type @ to mention someone</label>
                    <textarea id="c-body" placeholder="Share an update, ask a question, mention a teammate with @Name…" style="min-height:70px;"></textarea>
                </div>
                <div id="c-mention-box" style="display:none;border:1px solid var(--line);border-radius:8px;background:#fff;box-shadow:0 4px 16px rgba(0,0,0,0.1);max-height:160px;overflow-y:auto;margin-top:-6px;margin-bottom:8px;"></div>
                <button class="pill-btn primary" id="c-post">Post Comment</button>
            `;

            const textarea = document.getElementById('c-body');
            const mentionBox = document.getElementById('c-mention-box');
            textarea.addEventListener('input', () => {
                const cursor = textarea.selectionStart;
                const upToCursor = textarea.value.slice(0, cursor);
                const match = upToCursor.match(/@([A-Za-z]*)$/);
                if (!match) { mentionBox.style.display = 'none'; return; }
                const q = match[1].toLowerCase();
                const matches = USERS.filter(u => u.full_name.toLowerCase().includes(q)).slice(0, 6);
                if (!matches.length) { mentionBox.style.display = 'none'; return; }
                mentionBox.innerHTML = matches.map(u => `<div class="mention-opt" data-name="${esc(u.full_name)}" style="padding:7px 12px;cursor:pointer;font-size:13px;">${esc(u.full_name)}</div>`).join('');
                mentionBox.style.display = 'block';
                mentionBox.querySelectorAll('.mention-opt').forEach(opt => {
                    opt.addEventListener('mouseenter', () => opt.style.background = 'var(--hover)');
                    opt.addEventListener('mouseleave', () => opt.style.background = '');
                    opt.addEventListener('click', () => {
                        const name = opt.dataset.name;
                        textarea.value = upToCursor.replace(/@([A-Za-z]*)$/, '@' + name + ' ') + textarea.value.slice(cursor);
                        mentionBox.style.display = 'none';
                        textarea.focus();
                    });
                });
            });

            document.getElementById('c-post').addEventListener('click', async () => {
                const body = textarea.value.trim();
                if (!body) return;
                try {
                    await apiRequest('api/comments/create.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, body }) });
                    drawerComments(p);
                } catch (err) { alert(err.message); }
            });
            $('drBody').querySelectorAll('.comment-del').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!(await confirmDialog('This will permanently remove the comment.', { title: 'Delete comment?' }))) return;
                    try {
                        await apiRequest(`api/comments/delete.php?id=${btn.dataset.cid}`, { method: 'DELETE', headers: {} });
                        drawerComments(p);
                    } catch (err) { alert(err.message); }
                });
            });
        });
}

function drawerStakeholders(p) {
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before adding stakeholders.</p>';
        return;
    }
    $('drBody').innerHTML = skeletonRows(3);
    fetch(`api/stakeholders/list.php?project_id=${encodeURIComponent(p.id)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const stakeholders = data.data || [];

            const stakeholderHtml = stakeholders.map(s => `
                <div class="stakeholder-card" data-sid="${s.id}" style="border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:8px;">
                        <div>
                            <div style="font-weight:600;font-size:14px;">${esc(s.name)}</div>
                            ${s.role_title ? `<div style="font-size:12px;color:var(--muted);">${esc(s.role_title)}</div>` : ''}
                        </div>
                        <button class="stakeholder-del" data-sid="${s.id}" style="border:none;background:none;color:var(--muted-2);font-size:11px;cursor:pointer;">Remove</button>
                    </div>
                    <textarea class="stakeholder-notes" data-sid="${s.id}" placeholder="How have they contributed, or what are their expectations of this project?" style="min-height:60px;margin-bottom:10px;">${esc(s.notes || '')}</textarea>
                    <div class="stakeholder-docs" style="margin-bottom:8px;">
                        ${(s.documents || []).map(d => `
                            <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;padding:4px 0;">
                                <a href="api/stakeholders/download.php?id=${d.id}" style="color:var(--cu);text-decoration:none;flex:1;">📄 ${esc(d.original_name)}</a>
                                <span style="color:var(--muted-2);">${(d.size_bytes / 1024).toFixed(0)} KB</span>
                                <button class="stakeholder-doc-del" data-did="${d.id}" data-sid="${s.id}" style="border:none;background:none;color:var(--muted-2);font-size:11px;cursor:pointer;">&times;</button>
                            </div>
                        `).join('') || '<div style="font-size:12px;color:var(--muted-2);">No requirements document uploaded yet.</div>'}
                    </div>
                    <label class="pill-btn" style="font-size:12px;display:inline-flex;cursor:pointer;">
                        Upload requirements (pdf, doc, docx, txt)
                        <input type="file" class="stakeholder-upload-input" data-sid="${s.id}" accept=".pdf,.doc,.docx,.txt" style="display:none;">
                    </label>
                </div>
            `).join('') || '<p style="color:var(--muted);text-align:center;padding:12px 0;">No stakeholders added yet.</p>';

            $('drBody').innerHTML = `
                <p style="font-size:12px;color:var(--muted);margin-bottom:14px;">Stakeholders have expectations of or contribute to this project, separate from the owner (developer) and assignee (allocator). Capture their requirements as notes, or upload a requirements document.</p>
                ${stakeholderHtml}
                <div class="fld"><label>Name</label><input id="sh-name" placeholder="Stakeholder name"></div>
                <div class="fld"><label>Role / Title (optional)</label><input id="sh-role" placeholder="e.g. Head of Compliance"></div>
                <button class="pill-btn primary" id="sh-add">Add Stakeholder</button>
            `;

            document.getElementById('sh-add').addEventListener('click', async () => {
                const name = document.getElementById('sh-name').value.trim();
                if (!name) { alert('Stakeholder name is required.'); return; }
                const role_title = document.getElementById('sh-role').value.trim();
                try {
                    await apiRequest('api/stakeholders/create.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, name, role_title }) });
                    drawerStakeholders(p);
                } catch (err) { alert(err.message); }
            });

            $('drBody').querySelectorAll('.stakeholder-notes').forEach(ta => {
                ta.addEventListener('change', async () => {
                    try {
                        await apiRequest('api/stakeholders/update.php', { method: 'PUT', body: JSON.stringify({ id: +ta.dataset.sid, notes: ta.value }) });
                    } catch (err) { alert(err.message); }
                });
            });
            $('drBody').querySelectorAll('.stakeholder-del').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!(await confirmDialog('This will remove the stakeholder and any uploaded documents.', { title: 'Remove stakeholder?' }))) return;
                    try {
                        await apiRequest('api/stakeholders/delete.php', { method: 'DELETE', body: JSON.stringify({ id: +btn.dataset.sid }) });
                        drawerStakeholders(p);
                    } catch (err) { alert(err.message); }
                });
            });
            $('drBody').querySelectorAll('.stakeholder-upload-input').forEach(input => {
                input.addEventListener('change', async () => {
                    const file = input.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('stakeholder_id', input.dataset.sid);
                    formData.append('file', file);
                    try {
                        const res = await fetch('api/stakeholders/upload.php', { method: 'POST', body: formData, credentials: 'same-origin' });
                        const json = await res.json();
                        if (!res.ok || json.status !== 'success') throw new Error(json.error || 'Upload failed');
                        drawerStakeholders(p);
                    } catch (err) { alert('Upload failed: ' + err.message); }
                });
            });
            $('drBody').querySelectorAll('.stakeholder-doc-del').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!(await confirmDialog('This will permanently remove the document.', { title: 'Delete document?' }))) return;
                    try {
                        await apiRequest('api/stakeholders/delete_document.php', { method: 'DELETE', body: JSON.stringify({ id: +btn.dataset.did }) });
                        drawerStakeholders(p);
                    } catch (err) { alert(err.message); }
                });
            });
        });
}

// 🔥 FIXED: drawerOverview with Create/Delete button and async save
function drawerOverview(p) {
    const es = effStatus(p);
    const gateFlag = gateOverdue(p);
    const isNew = p.id.includes('-n');

    let html = `
        <div class="fld"><label>Assigned To (Owner — the developer doing the work)</label><select id="e-owner"><option value="">None</option>${USERS.map(u => `<option value="${u.id}" ${p.ownerId == u.id ? 'selected' : ''}>${esc(u.full_name)}</option>`).join('')}</select></div>
        ${p.ownerId ? (() => {
            const statusMap = {
                pending: { label: 'Pending response', bg: '#fff8e1', border: '#ffe0a3', color: '#8a6d1f' },
                accepted: { label: 'Accepted — in progress', bg: '#e8f8ee', border: '#bfe8cf', color: '#1c7a43' },
                rejected: { label: 'Rejected', bg: '#fde8e8', border: '#f5c6c6', color: '#b42318' },
            };
            const meta = statusMap[p.ownerAcceptanceStatus] || statusMap.pending;
            return `<div class="sample-note" style="margin:0 0 16px;background:${meta.bg};border-color:${meta.border};color:${meta.color}">
                Allocation status: <b>${meta.label}</b>${p.ownerAcceptanceStatus === 'rejected' && p.ownerRejectionReason ? `<br>Reason: ${esc(p.ownerRejectionReason)}` : ''}
            </div>`;
        })() : ''}
        ${gateFlag ? `<div class="sample-note" style="margin:0 0 16px;background:#fde8e8;border-color:#f5c6c6;color:#b42318">⚠️ Stage gate due date has passed!</div>` : ''}
        ${es === 'overdue' ? `<div class="sample-note" style="margin:0 0 16px;background:#fde8e8;border-color:#f5c6c6;color:#b42318">⚠️ Completion date has passed — this project is overdue.</div>` : ''}
        <div class="fld"><label>Project name <span style="color:red;">*</span></label><input id="e-name" value="${esc(p.name)}" ${isNew ? 'required' : ''}></div>
        <div class="two">
            <div class="fld"><label>Priority</label><select id="e-prio">${Object.keys(PRIO).map(k => `<option value="${k}" ${p.prio === k ? 'selected' : ''}>${k[0].toUpperCase() + k.slice(1)}</option>`).join('')}</select></div>
            <div class="fld"><label>Stage gate</label><select id="e-stage">${STAGES.map(s => `<option value="${s.id}" ${p.stage === s.id ? 'selected' : ''}>${s.label}</option>`).join('')}</select></div>
        </div>
        <div class="two">
            <div class="fld"><label>Status</label><select id="e-health">${['ontrack', 'atrisk', 'behind', 'complete'].map(k => `<option value="${k}" ${p.health === k ? 'selected' : ''}>${HEALTH[k].label}</option>`).join('')}</select></div>
            <div class="fld"><label>Assignee (allocator — editor/admin who assigned this out)</label><select id="e-assignee"><option value="">None</option>${allocatorUsers().map(u => `<option value="${u.id}" ${p.assignee_id == u.id ? 'selected' : ''}>${esc(u.full_name)}</option>`).join('')}</select></div>
        </div>
        <div class="two">
            <div class="fld"><label>Stage gate due date</label><input type="date" id="e-gate" value="${p.gateDue || ''}"></div>
            <div class="fld"><label>Project completion date</label><input type="date" id="e-comp" value="${p.compDue || ''}"></div>
        </div>
        <div class="fld">
            <label>Progress — <span id="pv">${p.progress}</span>%</label>
            <div class="rng"><input type="range" id="e-prog" min="0" max="100" value="${p.progress}"></div>
        </div>
        <p style="font-size:12px;color:var(--muted);margin-bottom:18px">Status shows <b style="color:${HEALTH[es].color}">${HEALTH[es].label}</b> (Overdue & Gate overdue are detected automatically).</p>
        <div class="two">
            <div class="fld"><label>Budget</label><input type="number" step="0.01" min="0" id="e-budget" value="${p.budget ?? ''}" placeholder="0.00"></div>
            <div class="fld"><label>Actual Cost</label><input type="number" step="0.01" min="0" id="e-actual" value="${p.actualCost ?? ''}" placeholder="0.00"></div>
        </div>
        <div id="budget-warning"></div>
        <div class="fld" style="max-width:160px">
            <label>Currency</label>
            <select id="e-currency">${CURRENCIES.map(c => `<option value="${c}" ${p.currency === c ? 'selected' : ''}>${c}</option>`).join('')}</select>
        </div>
        <div class="fld">
            <label>Tags</label>
            <div id="e-tag-chips" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">${(p.tags || []).map(t => `<span class="tag-chip" data-tag="${esc(t.name)}" style="background:${t.color}1a;color:${t.color};border:1px solid ${t.color}55;cursor:pointer;">${esc(t.name)} &times;</span>`).join('') || '<span style="font-size:12px;color:var(--muted-2)">No tags yet</span>'}</div>
            <input id="e-tag-input" list="e-tag-suggestions" placeholder="Type a tag and press Enter…">
            <datalist id="e-tag-suggestions">${ALL_TAGS.map(t => `<option value="${esc(t.name)}">`).join('')}</datalist>
        </div>
    `;

    if (isNew) {
        html += `<p style="margin-top:18px"><button class="pill-btn primary" id="e-create">Create Project</button></p>`;
    } else {
        html += `<p style="margin-top:18px;display:flex;gap:8px"><button class="pill-btn" id="e-gantt">Full-page Gantt</button><button class="pill-btn" id="e-del" style="color:var(--overdue);border-color:#f5c6c6">Delete Project</button></p>`;
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
    document.getElementById('e-owner').addEventListener('change', async e => {
        const uid = e.target.value ? parseInt(e.target.value) : null;
        const selectedUser = USERS.find(u => u.id == uid);
        p.ownerId = uid;
        p.owner = selectedUser ? selectedUser.full_name : '';
        try {
            await persistProject(p, { owner_id: uid });
        } catch (err) {
            alert('Update failed: ' + err.message);
        }
    });
    document.getElementById('e-name').addEventListener('change', e => upd('name', e.target.value, { name: e.target.value }));
    document.getElementById('e-prio').addEventListener('change', e => upd('prio', e.target.value, { priority: e.target.value }));
    document.getElementById('e-stage').addEventListener('change', e => upd('stage', e.target.value, { stage: e.target.value }));
    document.getElementById('e-health').addEventListener('change', e => { upd('health', e.target.value, { status: e.target.value }); });
    document.getElementById('e-assignee').addEventListener('change', e => upd('assignee_id', e.target.value ? parseInt(e.target.value) : null, { assignee_id: e.target.value || null }));
    document.getElementById('e-gate').addEventListener('change', e => upd('gateDue', e.target.value, { gate_due: e.target.value || null }));
    document.getElementById('e-comp').addEventListener('change', e => { upd('compDue', e.target.value, { completion_due: e.target.value || null }); });
    document.getElementById('e-prog').addEventListener('input', e => { p.progress = +e.target.value; document.getElementById('pv').textContent = e.target.value; });
    document.getElementById('e-prog').addEventListener('change', e => { upd('progress', +e.target.value, { progress: +e.target.value }); });
    const refreshBudgetWarning = () => {
        const budgetEl = document.getElementById('e-budget');
        const actualEl = document.getElementById('e-actual');
        const budget = budgetEl.value !== '' ? +budgetEl.value : null;
        const actual = actualEl.value !== '' ? +actualEl.value : null;
        const overBudget = budget !== null && actual !== null && actual > budget;
        actualEl.style.borderColor = overBudget ? 'var(--overdue)' : '';
        actualEl.style.color = overBudget ? 'var(--overdue)' : '';
        const warning = document.getElementById('budget-warning');
        warning.innerHTML = overBudget
            ? `<p style="font-size:12px;color:var(--overdue);font-weight:600;margin:-10px 0 14px;">⚠️ Over budget by ${esc(p.currency || 'USD')} ${(actual - budget).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>`
            : '';
    };
    refreshBudgetWarning();
    document.getElementById('e-budget').addEventListener('input', refreshBudgetWarning);
    document.getElementById('e-actual').addEventListener('input', refreshBudgetWarning);
    document.getElementById('e-budget').addEventListener('change', e => upd('budget', e.target.value ? +e.target.value : null, { budget: e.target.value || null }));
    document.getElementById('e-actual').addEventListener('change', e => upd('actualCost', e.target.value ? +e.target.value : null, { actual_cost: e.target.value || null }));
    document.getElementById('e-currency').addEventListener('change', e => upd('currency', e.target.value, { currency: e.target.value }));

    const saveTags = async () => {
        try {
            const res = await apiRequest('api/tags/set.php', { method: 'POST', body: JSON.stringify({ project_id: p.id, tags: (p.tags || []).map(t => t.name) }) });
            p.tags = res.tags || [];
            (res.tags || []).forEach(t => { if (!ALL_TAGS.some(at => at.id === t.id)) ALL_TAGS.push(t); });
            drawerOverview(p);
            renderSidebar();
            renderContent();
        } catch (err) { alert('Could not save tags: ' + err.message); }
    };
    if (!isNew) {
        $('drBody').querySelectorAll('#e-tag-chips .tag-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                p.tags = (p.tags || []).filter(t => t.name !== chip.dataset.tag);
                saveTags();
            });
        });
        document.getElementById('e-tag-input').addEventListener('keydown', e => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const name = e.target.value.trim();
            if (!name) return;
            if (!(p.tags || []).some(t => t.name.toLowerCase() === name.toLowerCase())) {
                p.tags = [...(p.tags || []), { id: null, name, color: '#6b7280' }];
                saveTags();
            }
            e.target.value = '';
        });
    } else {
        document.getElementById('e-tag-input').disabled = true;
        document.getElementById('e-tag-input').placeholder = 'Save the project first to add tags';
    }

    if (isNew) {
        document.getElementById('e-create').addEventListener('click', async () => {
            const name = document.getElementById('e-name').value.trim();
            if (!name) { alert('Project name is required.'); return; }
            const data = {
                name: name,
                business_unit_id: p.companyDbId,
                stage: document.getElementById('e-stage').value,
                status: document.getElementById('e-health').value,
                owner_id: document.getElementById('e-owner').value || null,
                priority: document.getElementById('e-prio').value,
                assignee_id: document.getElementById('e-assignee').value || null,
                gate_due: document.getElementById('e-gate').value || null,
                completion_due: document.getElementById('e-comp').value || null,
                progress: parseInt(document.getElementById('e-prog').value) || 0,
                current_update: document.getElementById('e-upd')?.value || '',
                next_steps: document.getElementById('e-next')?.value || '',
                budget: document.getElementById('e-budget').value || null,
                actual_cost: document.getElementById('e-actual').value || null,
                currency: document.getElementById('e-currency').value
            };
            try {
                const response = await apiRequest('api/projects/create.php', { method: 'POST', body: JSON.stringify(data) });
                const realId = response.id || response.data?.id;
                if (realId) {
                    const c = co(p.company);
                    c.projects = c.projects.filter(x => x.id !== p.id);
                    const newProject = { ...mapApiProject({ ...data, id: realId, ...response.data }), company: c.id };
                    c.projects.push(newProject);
                    closeDrawer();
                    openDrawer(realId);
                    renderAll();
                }
            } catch (err) { alert('Creation failed: ' + err.message); }
        });
    } else {
        document.getElementById('e-gantt').addEventListener('click', () => {
            ganttProjectFilter = p.id;
            currentTab = 'gantt';
            closeDrawer();
            $('tabs').querySelectorAll('.tab').forEach(t => t.classList.toggle('active', t.dataset.view === 'gantt'));
            loadView('gantt');
        });
        document.getElementById('e-del').addEventListener('click', async () => {
            if (!(await confirmDialog('This will permanently delete "' + p.name + '" and all of its governance, time, comment, and attachment data. This cannot be undone.', { title: 'Delete project?' }))) return;
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
    if (p.id.includes('-n')) {
        $('drBody').innerHTML = '<p style="color:var(--muted)">Save the project before managing governance.</p>';
        return;
    }
    $('drBody').innerHTML = skeletonRows(6);
    fetch(`api/attachments/list.php?project_id=${encodeURIComponent(p.id)}`, { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            const attachments = data.data || [];
            let html = `<div class="gov-overall"><span>Governance <b>${govPct(p)}%</b> signed off</span><div class="bar"><i style="width:${govPct(p)}%"></i></div></div>`;
            STAGES.forEach((s, i) => {
                const items = GOVERNANCE.filter(g => g.stage === s.id);
                html += `<div class="gstage"><div class="gstage-h"><span class="num">${i + 1}</span>${s.label}${p.stage === s.id ? '<span class="cur-tag">CURRENT</span>' : ''}</div>`;
                html += items.length ? items.map(it => {
                    const serverKey = apiGovKey(it.id);
                    const files = attachments.filter(a => a.item_key === serverKey);
                    const filesHtml = files.map(f => `
                        <div style="display:flex;align-items:center;gap:6px;font-size:11.5px;color:var(--muted);padding:3px 0;">
                            <a href="api/attachments/download.php?id=${f.id}" target="_blank" style="color:var(--cu);text-decoration:none;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">📎 ${esc(f.original_name)}</a>
                            <span style="flex-shrink:0;">${(f.size_bytes / 1024).toFixed(0)}KB</span>
                            <button class="gov-file-del" data-aid="${f.id}" style="border:none;background:none;color:var(--overdue);cursor:pointer;font-size:14px;flex-shrink:0;">&times;</button>
                        </div>
                    `).join('');
                    return `<div style="padding:10px 16px;border-top:1px solid var(--line);">
                        <div class="gitem" style="padding:0;border-top:none;"><span class="gname">${it.label}</span><select data-gid="${it.id}">${Object.entries(GOV_ST).map(([k, v]) => `<option value="${k}" ${p.governance[it.id] === k ? 'selected' : ''}>${v.l}</option>`).join('')}</select></div>
                        <div style="margin-top:6px;">${filesHtml}
                            <label style="font-size:11px;color:var(--cu);cursor:pointer;display:inline-block;margin-top:2px;">+ Attach file<input type="file" class="gov-upload-input" data-item-key="${serverKey}" style="display:none;"></label>
                        </div>
                    </div>`;
                }).join('') : `<div class="none">No formal deliverable at this gate.</div>`;
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
            $('drBody').querySelectorAll('.gov-upload-input').forEach(input => {
                input.addEventListener('change', async () => {
                    const file = input.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('project_id', p.id);
                    formData.append('item_key', input.dataset.itemKey);
                    formData.append('file', file);
                    try {
                        const res = await fetch('api/attachments/upload.php', { method: 'POST', body: formData, credentials: 'same-origin' });
                        const json = await res.json();
                        if (!res.ok || json.status !== 'success') throw new Error(json.error || 'Upload failed');
                        drawerGov(p);
                    } catch (err) { alert('Upload failed: ' + err.message); }
                });
            });
            $('drBody').querySelectorAll('.gov-file-del').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!(await confirmDialog('This will permanently remove the attached file.', { title: 'Delete attachment?' }))) return;
                    try {
                        await apiRequest(`api/attachments/delete.php?id=${btn.dataset.aid}`, { method: 'DELETE', headers: {} });
                        drawerGov(p);
                    } catch (err) { alert(err.message); }
                });
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
        ownerId: null,
        assignee_id: null,
        prio: 'normal',
        gateDue: '',
        compDue: '',
        progress: 0,
        currentUpdate: '',
        nextSteps: '',
        is_gate_overdue: 0,
        budget: null,
        actualCost: null,
        currency: 'USD',
        tags: [],
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
        const filterPopover = document.getElementById('filterPopover');
        if (e.target.closest('#filterBtn')) {
            filterPopover.classList.toggle('show');
        } else if (!e.target.closest('#filterDropdownWrap')) {
            filterPopover.classList.remove('show');
        }
        const notifPanel = document.getElementById('notifPanel');
        if (e.target.closest('#notifBtn')) {
            notifPanel.classList.toggle('show');
            if (notifPanel.classList.contains('show')) loadNotifications();
        } else if (!e.target.closest('#notifDropdownWrap')) {
            notifPanel.classList.remove('show');
        }
    });
    document.getElementById('clearFiltersBtn').addEventListener('click', () => {
        ['fCompany', 'fStatus', 'fOwner', 'fAssignee', 'fTag', 'fStakeholder'].forEach(id => { document.getElementById(id).value = ''; });
        updateFilterBadge();
        renderContent();
    });
    ['fCompany', 'fStatus', 'fOwner', 'fAssignee', 'fTag', 'fStakeholder'].forEach(id => {
        document.getElementById(id).addEventListener('change', updateFilterBadge);
    });
    document.querySelectorAll('#exportMenu a[data-export]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.dataset.export;
            if (type === 'print') {
                window.print();
            } else if (type === 'copy') {
                const allProjects = DATA.flatMap(c => c.projects);
                const rows = getFiltered(allProjects).map(p =>
                    `${p.name} | ${p.companyName} | ${HEALTH[effStatus(p)].label} | Owner: ${p.owner || '-'} | Progress: ${p.progress}% | Completion: ${p.compDue || '-'}`
                );
                const text = 'Project Report (' + new Date().toISOString().slice(0, 16).replace('T', ' ') + ')\n\n' + rows.join('\n');
                navigator.clipboard.writeText(text)
                    .then(() => alert('Report copied to clipboard.'))
                    .catch(() => alert('Could not copy to clipboard.'));
            } else {
                const params = new URLSearchParams();
                const search = document.getElementById('search').value;
                const company = document.getElementById('fCompany').value;
                const status = document.getElementById('fStatus').value;
                const owner = document.getElementById('fOwner').value;
                const assignee = document.getElementById('fAssignee').value;
                if (search) params.append('search', search);
                if (company) params.append('unit', company);
                if (status) params.append('status', status);
                if (owner) params.append('owner_id', owner);
                if (assignee) params.append('assignee', assignee);
                window.location.href = `api/export/${type}.php?${params.toString()}`;
            }
            document.getElementById('exportMenu').classList.remove('show');
        });
    });
    document.getElementById('shareBtn').addEventListener('click', function() {
        const company = document.getElementById('fCompany').value;
        const status = document.getElementById('fStatus').value;
        const owner = document.getElementById('fOwner').value;
        const assignee = document.getElementById('fAssignee').value;
        const search = document.getElementById('search').value;
        const filters = {};
        if (company) { const unit = co(company); if (unit) filters.business_unit = unit.dbId; }
        if (status) filters.status = status;
        if (owner) filters.owner_id = owner;
        if (assignee) filters.assignee = assignee;
        if (search) filters.search = search;
        fetch('api/share/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ report_type: 'portfolio', expires_in: 7, filters }),
            credentials: 'same-origin'
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') showShareLinkModal(data.url);
            else alert('Error creating share link: ' + (data.error || 'Unknown error'));
        }).catch(err => alert('Share error: ' + err.message));
    });
    document.getElementById('shareLinkClose').addEventListener('click', () => {
        document.getElementById('shareLinkOverlay').classList.remove('open');
    });
    document.getElementById('shareLinkCopyBtn').addEventListener('click', () => {
        const input = document.getElementById('shareLinkInput');
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = document.getElementById('shareLinkCopyBtn');
            const original = btn.textContent;
            btn.textContent = 'Copied ✓';
            setTimeout(() => { btn.textContent = original; }, 1500);
        }).catch(() => alert('Could not copy — select the link text and copy manually.'));
    });
    initImport();
    initCommandPalette();
    initMobileNav();
    document.getElementById('notifMarkAllBtn').addEventListener('click', async () => {
        try {
            await apiRequest('api/notifications/mark_read.php', { method: 'POST', body: JSON.stringify({}) });
            loadNotifications();
            refreshNotifBadge();
        } catch (err) { alert(err.message); }
    });

    document.getElementById('fCompany').addEventListener('change', renderContent);
    document.getElementById('fStatus').addEventListener('change', renderContent);
    document.getElementById('fOwner').addEventListener('change', renderContent);
    document.getElementById('fAssignee').addEventListener('change', renderContent);
    document.getElementById('fTag').addEventListener('change', renderContent);
    document.getElementById('fStakeholder').addEventListener('change', renderContent);
});

// ----- INIT -----
async function initApp() {
    await loadData();
    refreshNotifBadge();
    setInterval(refreshNotifBadge, 60000);
}

// On page load (e.g. after a refresh), resume an already-authenticated session instead
// of forcing a re-login — the server-side session cookie may still be valid.
(async function resumeSessionIfAuthenticated() {
    try {
        const res = await fetch('api/auth/check.php', { credentials: 'same-origin' });
        const data = await res.json();
        if (data.status === 'success') {
            CURRENT_USER = data.user || null;
            loginScreen.style.display = 'none';
            registerScreen.style.display = 'none';
            mainApp.classList.add('active');
            initApp();
        }
    } catch (e) { /* not authenticated — leave the login screen showing */ }
})();
</script>
</body>
</html>