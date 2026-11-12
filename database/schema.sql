-- ToolkitPro PostgreSQL schema. Apply this file on a fresh database.
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS users (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role_id BIGINT REFERENCES roles(id),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    name_bn VARCHAR(160) NOT NULL,
    name_en VARCHAR(160) NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS tools (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    category_id BIGINT REFERENCES categories(id),
    slug VARCHAR(120) NOT NULL UNIQUE,
    name_bn VARCHAR(180) NOT NULL,
    name_en VARCHAR(180) NOT NULL,
    description_bn TEXT,
    description_en TEXT,
    execution_type VARCHAR(20) NOT NULL DEFAULT 'client'
        CHECK (execution_type IN ('client', 'server', 'api')),
    runtime_key VARCHAR(120),
    version VARCHAR(30) NOT NULL DEFAULT '1.0.0',
    status VARCHAR(20) NOT NULL DEFAULT 'draft'
        CHECK (status IN ('draft', 'review', 'published', 'archived')),
    visibility VARCHAR(20) NOT NULL DEFAULT 'private'
        CHECK (visibility IN ('public', 'private', 'unlisted')),
    input_schema JSONB NOT NULL DEFAULT '{}'::jsonb,
    output_schema JSONB NOT NULL DEFAULT '{}'::jsonb,
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    published_at TIMESTAMPTZ
);

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

CREATE TABLE IF NOT EXISTS favorites (
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    tool_id BIGINT NOT NULL REFERENCES tools(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (user_id, tool_id)
);

CREATE TABLE IF NOT EXISTS usage_history (
    id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    tool_id BIGINT NOT NULL REFERENCES tools(id),
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_tools_category_active ON tools(category_id, is_active);
CREATE INDEX IF NOT EXISTS idx_tools_public_catalog ON tools(status, visibility, is_active, category_id);
CREATE INDEX IF NOT EXISTS idx_tools_search_slug ON tools(slug);
CREATE INDEX IF NOT EXISTS idx_tool_versions_tool_created ON tool_versions(tool_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_executions_tool_created ON tool_executions(tool_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_executions_status_created ON tool_executions(status, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_usage_tool_created ON usage_history(tool_id, created_at);

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
