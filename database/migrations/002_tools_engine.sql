-- ToolkitPro Tools Engine expansion (PostgreSQL)
-- Safe to run after database/schema.sql on both fresh and existing databases.
BEGIN;

ALTER TABLE tools
    ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'draft',
    ADD COLUMN IF NOT EXISTS visibility VARCHAR(20) NOT NULL DEFAULT 'private',
    ADD COLUMN IF NOT EXISTS runtime_key VARCHAR(120),
    ADD COLUMN IF NOT EXISTS input_schema JSONB NOT NULL DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS output_schema JSONB NOT NULL DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    ADD COLUMN IF NOT EXISTS published_at TIMESTAMPTZ;

ALTER TABLE tools DROP CONSTRAINT IF EXISTS tools_status_check;
ALTER TABLE tools ADD CONSTRAINT tools_status_check
    CHECK (status IN ('draft', 'review', 'published', 'archived'));

ALTER TABLE tools DROP CONSTRAINT IF EXISTS tools_visibility_check;
ALTER TABLE tools ADD CONSTRAINT tools_visibility_check
    CHECK (visibility IN ('public', 'private', 'unlisted'));

CREATE TABLE IF NOT EXISTS tool_versions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tool_id BIGINT NOT NULL REFERENCES tools(id) ON DELETE CASCADE,
    version VARCHAR(30) NOT NULL,
    definition JSONB NOT NULL,
    change_note TEXT,
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    UNIQUE (tool_id, version)
);

CREATE TABLE IF NOT EXISTS tool_executions (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    tool_id BIGINT NOT NULL REFERENCES tools(id) ON DELETE RESTRICT,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'running'
        CHECK (status IN ('running', 'succeeded', 'failed')),
    duration_ms INTEGER,
    error_code VARCHAR(80),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    finished_at TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_tools_public_catalog
    ON tools (status, visibility, is_active, category_id);
CREATE INDEX IF NOT EXISTS idx_tools_search_slug
    ON tools (slug);
CREATE INDEX IF NOT EXISTS idx_tool_versions_tool_created
    ON tool_versions (tool_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_executions_tool_created
    ON tool_executions (tool_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_executions_status_created
    ON tool_executions (status, created_at DESC);

COMMIT;
