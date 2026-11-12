# ToolkitPro — Platform Architecture

## Product direction
A Bengali-first, accessible, responsive digital utility platform. The platform is designed to scale toward 2,500+ registered tools; this is a capacity goal, not a claim that 2,500 tools are already implemented or published.

## System shape
- **Presentation:** server-rendered PHP views, progressive-enhancement JavaScript, responsive CSS, semantic HTML and accessible controls.
- **Application:** PHP 8.2+ modular monolith with controllers, services, validation and explicit route registration.
- **Catalog:** PostgreSQL categories, tool definitions, visibility, lifecycle state, JSON input/output contracts and version metadata.
- **Execution:** allow-listed runtime handler registry. Tool records never contain executable PHP; no eval or arbitrary code execution.
- **API:** versioned read API under /api/v1 for catalog, categories and tool detail.
- **Delivery:** Composer autoloading, Docker/Render deployment and GitHub Actions CI.

## Tool lifecycle
1. Draft — definition created and validated.
2. Review — schema, metadata, permissions and handler availability checked.
3. Published — explicitly public and active; exposed in public catalog.
4. Archived — retained for audit/history but hidden from public discovery.

Every publish should validate slug uniqueness, category, localized metadata, execution type, JSON input/output schema, handler allow-list and version. Production publishing should be audited and reversible.

## Scale-ready capabilities
- Paginated catalog queries and indexed category/status filters.
- Tool versions and execution history.
- Bulk import batch records, row-level validation errors, reusable templates and tags.
- Separate client/server/API execution types.
- Future admin RBAC, visual builder, review queue, quotas, rate limits, audit log, observability and rollback.

## Security baseline
- Never execute code supplied by a database record or user.
- Validate all inputs against a strict schema and enforce payload/size/time limits.
- Keep secrets in environment variables; never commit credentials.
- Apply authentication, authorization, CSRF protection and rate limiting to future write/admin endpoints.
- Avoid returning stack traces or internal database details to public clients.
- Log execution metadata without storing sensitive user payloads by default.

## Delivery phases
- **Phase 1:** public UI, catalog API, database foundation, trusted runtime and CI.
- **Phase 2:** authentication, roles, admin dashboard, tool CRUD, review/publish/rollback.
- **Phase 3:** visual tool builder, reusable templates, import preview/validation and bulk publishing.
- **Phase 4:** implement and test real tools by category; publish only after QA.
- **Phase 5:** usage analytics, favorites, user workspaces, API keys, quotas, caching and operational monitoring.

## Operational checks
Before production launch: apply schema/migrations, configure PostgreSQL and environment variables, verify health endpoint, run CI, smoke-test catalog and detail endpoints, test mobile layout, verify security headers and inspect Render deployment logs.
