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

UPDATE tools SET status = 'published', visibility = 'public' WHERE is_active = TRUE AND status = 'draft';

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


CREATE TABLE IF NOT EXISTS tool_import_batches (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    source VARCHAR(30) NOT NULL DEFAULT 'manual'
        CHECK (source IN ('manual', 'csv', 'json', 'api')),
    status VARCHAR(20) NOT NULL DEFAULT 'queued'
        CHECK (status IN ('queued', 'validating', 'processing', 'completed', 'failed', 'cancelled')),
    total_rows INTEGER NOT NULL DEFAULT 0 CHECK (total_rows >= 0),
    accepted_rows INTEGER NOT NULL DEFAULT 0 CHECK (accepted_rows >= 0),
    rejected_rows INTEGER NOT NULL DEFAULT 0 CHECK (rejected_rows >= 0),
    created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    completed_at TIMESTAMPTZ
);

CREATE TABLE IF NOT EXISTS tool_import_errors (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    batch_id BIGINT NOT NULL REFERENCES tool_import_batches(id) ON DELETE CASCADE,
    row_number INTEGER NOT NULL CHECK (row_number > 0),
    tool_slug VARCHAR(120),
    error_code VARCHAR(80) NOT NULL,
    error_message TEXT NOT NULL,
    row_payload JSONB,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS tool_tags (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    label_bn VARCHAR(100) NOT NULL,
    label_en VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS tool_tag_map (
    tool_id BIGINT NOT NULL REFERENCES tools(id) ON DELETE CASCADE,
    tag_id BIGINT NOT NULL REFERENCES tool_tags(id) ON DELETE CASCADE,
    PRIMARY KEY (tool_id, tag_id)
);

CREATE TABLE IF NOT EXISTS tool_templates (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    template_key VARCHAR(100) NOT NULL UNIQUE,
    name_bn VARCHAR(160) NOT NULL,
    name_en VARCHAR(160) NOT NULL,
    definition JSONB NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_tool_import_batches_status_created
    ON tool_import_batches (status, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_import_errors_batch
    ON tool_import_errors (batch_id, row_number);
CREATE INDEX IF NOT EXISTS idx_tool_tag_map_tag
    ON tool_tag_map (tag_id, tool_id);

COMMIT;
