# Zimnat Group — Project Portfolio Management Tool

A web-based project portfolio management platform for tracking projects across Zimnat Group's business units through a stage-gate governance process. Built on PHP 8 / MySQL (MariaDB), styled after ClickUp, running on XAMPP.

## Business Units

ZFS · ZAM · ZGI · ZLA · Group Projects · ICT Initiatives — each project belongs to exactly one unit.

## Stage Gates

Initiation → Planning → Execution/Development → Quality Assurance → User Acceptance Testing → Project Closure

## Requirements

- PHP 8.1+
- MySQL / MariaDB (developed against MariaDB 10.4)
- Composer
- Apache with `mod_rewrite` (XAMPP ships with both)

## Setup

1. **Clone into your XAMPP `htdocs`** (e.g. `c:\xampp\htdocs\zg_pm_tool`).

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Create the database and import the schema:**
   ```bash
   mysql -u root -e "CREATE DATABASE zpm_tool"
   mysql -u root zpm_tool < zpm_tool.sql
   ```
   Then apply every migration in `database/migrations/` in date order (each is idempotent — safe to re-run):
   ```bash
   for f in database/migrations/*.sql; do mysql -u root zpm_tool < "$f"; done
   ```

4. **Configure environment:**
   ```bash
   cp .env.example .env
   ```
   Edit `.env` — at minimum set `DB_*` to match your MySQL setup. Set `SMTP_*`/`ADMIN_EMAIL`/`DIGEST_RECIPIENTS` if you want email notifications or the weekly digest to actually send.

5. **Start Apache + MySQL** via the XAMPP Control Panel, then visit `http://localhost/zg_pm_tool/`.

6. **Register the first account** from the login screen — the first registered user is automatically made `admin`. Every account after that defaults to `viewer`; promote users by updating their `role` column directly (`admin` / `editor` / `viewer`) until an in-app admin panel exists.

## Architecture

- **`index.php`** — the entire SPA shell: auth screens, main layout, and all client-side JS (dashboard, drawer, list/board rendering, command palette, etc.) in one file.
- **`api/`** — JSON endpoints, grouped by resource (`projects/`, `governance/`, `assignees/`, `dependencies/`, `comments/`, `attachments/`, `stakeholders/`, `notifications/`, `time/`, `tags/`, `activity/`, `insights/`, `export/`, `import/`, `analytics/`, `auth/`, `share/`, `users/`). `api/bootstrap.php` wires up the DB connection, env, and session for every request; `api/middleware/auth.php` enforces role-based access (`Auth::requireAuth()` / `Auth::requireRole('editor')`); `api/helpers/Notifications.php` centralizes the owner-assignment/accept/reject notification logic shared by `projects/create.php`, `projects/update.php`, and `projects/respond.php`.
- **`views/`** — server-rendered partials fetched via AJAX for tabs that need PHP-side aggregation (Calendar, Gantt, Resources heatmap, Reports, Mail). List and Board are rendered entirely client-side from data already loaded into `index.php`.
- **`config/`** — `Database.php` (mysqli singleton), `environment.php`, `autoload.php`.
- **`database/migrations/`** — incremental schema changes layered on top of `zpm_tool.sql`. Run them all when setting up fresh.
- **`cron/`** — standalone PHP scripts meant to run on a schedule (Windows Task Scheduler or cron), not through the web server: `daily-notifications.php` (overdue project alerts) and `weekly-digest.php` (portfolio health digest — supports `--dry-run` to preview without sending).
- **`uploads/`** — governance document attachments, stored outside web-accessible paths logically (protected by `.htaccess`) and served only through `api/attachments/download.php`.

## Roles

| Role | Can do |
|---|---|
| `viewer` | Read-only — view dashboards, reports, projects. Can still be the **owner** (developer) on a project. |
| `editor` | Everything a viewer can, plus create/edit/delete projects, governance, attachments, dependencies, time entries, tags, stakeholders. Can be an **assignee** (allocator). |
| `admin` | Everything an editor can, plus visibility into every assignee's allocation performance (editors only see their own). |

## Owner vs. Assignee

- **Owner ("Assigned To")** — the developer actually doing the work. Any role. Tracked for productivity (project count, completion rate, average days to close, current active workload).
- **Assignee** — the editor/admin who allocated the project out. Tracked for "how are the projects I've handed out doing." Editors only see their own allocations in Reports; admins can see everyone's.
- **Stakeholder** — someone with expectations of or contributions to a project, separate from the above two. Tracked per-project with notes and an uploadable requirements document.

## Features

- Portfolio and per-business-unit dashboards: stat cards (Total Projects, Budget, Actual Cost, Total Owners/Assigned To — over-budget figures turn red), status-ring breakdowns, Recent Activity feed, and an auto-generated Executive Summary (rule-based narrative — no external API or cost).
- List and Kanban Board views, grouped/filterable by stage gate, business unit, status, owner, assignee, tag, and stakeholder.
- Calendar and Gantt views (Gantt includes native dependency arrows and auto-scrolls to today).
- Governance checklist per project (7 deliverables mapped to stage gates) with file attachments per checklist item.
- Multi-assignee support with per-person capacity allocation %, plus a Resources heatmap and overallocation warnings.
- **Stakeholders** tab (first in the project drawer) — contribution/expectation notes plus requirements document upload (pdf/doc/docx/txt).
- **In-app notifications** with an unread-count bell: a new/reassigned owner is notified and can accept ("Start Working") or reject with a reason; rejection notifies the assignee(s) so they can reassign. Full history kept in `project_allocation_log`.
- **Reports** — Business Unit Ranking, Owner (Developer) Productivity (completion rate, avg days to close, active workload vs. team-average "sweet spot"), Assignee (Allocator) Performance (role-restricted), Timeline Analysis, Resource Allocation.
- Cross-project dependencies with cycle detection.
- Threaded comments with @mentions.
- Time tracking, budget/actual cost tracking (over-budget figures highlighted in red, per-project and in totals).
- Global Cmd/Ctrl+K command palette (instant client-side project search) and a Filters popover (owner, assignee, status, business unit, tag, stakeholder).
- Excel/CSV import and CSV/Excel/Text/PDF export, plus shareable read-only report links (with a copy-to-clipboard button) — all filters carry through to exports.
- Activity log surfaced as a human-readable feed (not just a raw audit table).
- Weekly email digest script (dry-run supported) reusing the same Executive Summary generator.

## Known limitations

- Authentication is custom session-based PHP rather than a managed auth provider (e.g. Supabase/Firebase) as the original spec suggested — functional and reasonably secure (bcrypt, RBAC, prepared statements throughout) but worth revisiting before a public-facing deployment.
- No in-app admin UI for managing user roles yet — done via direct DB update.
- The Reports tab (business unit ranking, assignee ranking, timeline analysis, resource allocation) is intentionally kept separate from the main dashboard to avoid overloading it — see commit history for the reasoning.

## Development notes

- Run `php -l <file>` after editing any PHP file — there's no CI yet, so this is the cheapest available sanity check.
- `composer.lock` and `vendor/` are gitignored; run `composer install` after pulling.
- `uploads/.htaccess` is intentionally tracked despite `uploads/*` being gitignored — don't delete it, it's what prevents direct access to uploaded governance documents.
