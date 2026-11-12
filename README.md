# ToolkitPro

ToolkitPro is a Bengali-first, mobile-friendly digital tools platform designed to bring AI, PDF, image, text, calculator, and developer utilities into one workspace.

## Architecture

- PHP 8.3+ application with a modular-monolith structure
- PostgreSQL-backed tool catalog and execution history
- Versioned tool definitions with a trusted-handler runtime
- JSON-schema-style input validation
- REST API under `/api/v1`
- Docker deployment and GitHub Actions CI for Render delivery

## Tools Engine design

The platform is designed to manage 2,500+ tools through a database-backed catalog—not a hard-coded or fabricated tool count. Each tool has a stable slug, category, localized metadata, execution type, version, visibility, publication state, and input/output schemas. Server-side tools can execute only through explicitly registered trusted handlers; database records cannot inject PHP code.

The database supports tool versions and execution tracking. Admin authentication, role-based permissions, a visual builder, publishing/rollback workflows, and a populated production catalog are staged for subsequent implementation.

## API

- `GET /api/v1/tools?q=&category=&page=1&per_page=24`
- `GET /api/v1/categories`
- `GET /api/v1/tools/{slug}`
- `GET /health`

The catalog API requires PostgreSQL and the schema below.

## Setup

1. Install PHP 8.3+, Composer, and PostgreSQL.
2. Configure `DATABASE_URL` (recommended on Render), or `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`.
3. Run `composer install`.
4. For a fresh database, apply `database/schema.sql`.
5. For a database created with the earlier schema, apply `database/migrations/002_tools_engine.sql`.
6. Apply `database/seed.sql` to create the bilingual starter categories (safe to rerun).
7. Set the web document root to `public/`, or deploy with the included Dockerfile.

## Quality checks

- `composer check` — PHP syntax validation
- `composer test` — Tools Engine unit tests
- GitHub Actions runs syntax, Composer manifest, and test checks on pushes and pull requests to `main`.

## Current implementation status

The repository contains the application foundation, public catalog API, trusted runtime, input validator, database schema, version/execution tables, and CI workflow. A production database, admin console, visual tool builder, and real populated tool library still need to be configured and implemented. No claim of 2,500 live tools is made until those tools are actually registered, tested, and published.
