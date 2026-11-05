# ToolkitPro Tools Engine

## Current foundation
- Database-backed public catalog: `GET /api/v1/tools`
- Tool detail: `GET /api/v1/tools/{slug}`
- Categories and live tool counts: `GET /api/v1/categories`
- Tool definition/version service: `App\\Services\\ToolDefinitionService`
- Safe runtime registry: `App\\Services\\ToolRuntime` (only explicitly registered trusted handlers)
- Basic JSON-schema input checks: `App\\Services\\ToolInputValidator`

## Database migration
Apply `database/migrations/20261105_tools_engine_foundation.sql` to the connected PostgreSQL database after the base `database/schema.sql` has been applied.

Example:
```bash
psql "$DATABASE_URL" -v ON_ERROR_STOP=1 -f database/schema.sql
psql "$DATABASE_URL" -v ON_ERROR_STOP=1 -f database/migrations/20261105_tools_engine_foundation.sql
```

Do not expose create/publish/disable operations until authenticated admin middleware and role checks are in place. The definition service is intentionally not wired to public routes.

## Tool lifecycle
`draft → review → published → disabled / archived`

Each version is stored separately in `tool_versions`. A published tool is discoverable through the public catalog. Runtime handlers must be installed in trusted application code; database records cannot execute arbitrary PHP.

## Capacity
The catalog uses indexed PostgreSQL queries and bounded pagination (maximum 100 records per page), suitable as a foundation for a 2,500+ tool catalogue. Actual throughput still requires load testing and appropriate database/worker capacity.
