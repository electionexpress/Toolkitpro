# ToolkitPro

ToolkitPro is a Bengali-first, English-secondary, mobile-first all-in-one digital tools platform for everyday users, students, creators, businesses and developers.

## Product vision
Bring useful AI, PDF, image, video, audio, text, calculator, converter, developer, SEO, productivity, security, finance, education, business, web and other utilities into one coherent workspace.

The long-term capacity target is **2,500+ registered tools across 20+ categories**. This is a roadmap target, not a claim that all tools are currently implemented. Only validated and tested tools should be published.

## Architecture
- PHP 8.3+ modular monolith with server-rendered views
- PostgreSQL-backed categories, tools, versions, executions, imports, tags and templates
- Trusted allow-listed handler registry (no eval or arbitrary code execution)
- JSON-schema-style input validation
- REST API under /api/v1
- Responsive Bengali-first frontend with progressive JavaScript enhancement
- Docker deployment on Render and GitHub Actions CI

## Public API
- GET /health
- GET /api/v1/tools?q=&category=&page=1&per_page=24
- GET /api/v1/categories
- GET /api/v1/tools/{slug}

## Database setup
1. Configure DATABASE_URL (recommended on Render), or DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD.
2. Run composer install.
3. On a fresh database, apply database/schema.sql.
4. On an existing database, apply the applicable migrations in database/migrations in chronological order. Do not reapply an incompatible historical migration; inspect the database's current schema first.
5. Apply database/seed.sql to upsert the bilingual category directory (24 categories).
6. Verify /health and the catalogue/category endpoints.

## Quality checks
- composer validate --no-check-publish
- composer check
- composer test
- node --check public/assets/js/app.js

GitHub Actions runs the project checks for pushes and pull requests to main.

## Current implementation status
The repository contains the public homepage/catalogue foundation, category discovery, API read endpoints, PostgreSQL schema, tool definition/runtime services, input validation, CI and Docker/Render delivery configuration. The 24-category seed and migration are committed but require application to the connected database. Admin dashboard, visual builder, user accounts, a production-populated tool library and the first 30 functional tool interfaces remain implementation work. No claim of 2,500 live tools is made until they are registered, tested and published.

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) and [docs/TOOLS_ENGINE.md](docs/TOOLS_ENGINE.md).
