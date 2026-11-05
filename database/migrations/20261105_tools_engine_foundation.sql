-- ToolkitPro Tools Engine foundation (PostgreSQL)
BEGIN;

ALTER TABLE tools
  ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'draft',
  ADD COLUMN IF NOT EXISTS visibility VARCHAR(20) NOT NULL DEFAULT 'public',
  ADD COLUMN IF NOT EXISTS input_schema JSONB NOT NULL DEFAULT '{}'::jsonb,
  ADD COLUMN IF NOT EXISTS output_schema JSONB NOT NULL DEFAULT '{}'::jsonb,
  ADD COLUMN IF NOT EXISTS runtime_key VARCHAR(100),
  ADD COLUMN IF NOT EXISTS config JSONB NOT NULL DEFAULT '{}'::jsonb,
  ADD COLUMN IF NOT EXISTS updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW();

DO $$ BEGIN
  ALTER TABLE tools ADD CONSTRAINT tools_status_check CHECK (status IN ('draft','review','published','disabled','archived'));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS tool_versions (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tool_id BIGINT NOT NULL REFERENCES tools(id) ON DELETE CASCADE,
  version VARCHAR(30) NOT NULL,
  definition JSONB NOT NULL,
  changelog TEXT,
  created_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE(tool_id, version)
);

CREATE TABLE IF NOT EXISTS tool_executions (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tool_id BIGINT NOT NULL REFERENCES tools(id) ON DELETE CASCADE,
  user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  request_id UUID NOT NULL DEFAULT gen_random_uuid(),
  status VARCHAR(20) NOT NULL DEFAULT 'queued' CHECK (status IN ('queued','running','succeeded','failed','rejected')),
  duration_ms INTEGER,
  error_code VARCHAR(80),
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  finished_at TIMESTAMPTZ
);

CREATE TABLE IF NOT EXISTS tool_plugins (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  name VARCHAR(180) NOT NULL,
  version VARCHAR(30) NOT NULL,
  manifest JSONB NOT NULL DEFAULT '{}'::jsonb,
  is_enabled BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS admin_audit_logs (
  id BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  actor_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  action VARCHAR(100) NOT NULL,
  resource_type VARCHAR(60) NOT NULL,
  resource_id VARCHAR(120),
  metadata JSONB NOT NULL DEFAULT '{}'::jsonb,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_tools_status_slug ON tools(status, slug);
CREATE INDEX IF NOT EXISTS idx_tools_category_status ON tools(category_id, status);
CREATE INDEX IF NOT EXISTS idx_tool_versions_tool_created ON tool_versions(tool_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_executions_tool_created ON tool_executions(tool_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_tool_executions_status_created ON tool_executions(status, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_admin_audit_created ON admin_audit_logs(created_at DESC);

INSERT INTO categories (slug, name_bn, name_en, sort_order, is_active) VALUES
('ai','এআই টুলস','AI Tools',1,TRUE),
('pdf-documents','পিডিএফ ও ডকুমেন্ট','PDF & Documents',2,TRUE),
('image-design','ইমেজ ও ডিজাইন','Image & Design',3,TRUE),
('video-audio','ভিডিও ও অডিও','Video & Audio',4,TRUE),
('text-writing','টেক্সট ও রাইটিং','Text & Writing',5,TRUE),
('developer','ডেভেলপার টুলস','Developer Tools',6,TRUE),
('seo-marketing','এসইও ও মার্কেটিং','SEO & Marketing',7,TRUE),
('calculators','ক্যালকুলেটর ও কনভার্টার','Calculators & Converters',8,TRUE),
('data-spreadsheets','ডেটা ও স্প্রেডশিট','Data & Spreadsheets',9,TRUE),
('business-finance','ব্যবসা ও ফাইন্যান্স','Business & Finance',10,TRUE),
('education-research','শিক্ষা ও গবেষণা','Education & Research',11,TRUE),
('security-privacy','নিরাপত্তা ও প্রাইভেসি','Security & Privacy',12,TRUE),
('files-productivity','ফাইল ও প্রোডাক্টিভিটি','Files & Productivity',13,TRUE),
('gis-mapping','জিআইএস ও ম্যাপিং','GIS & Mapping',14,TRUE),
('social-media','সোশ্যাল মিডিয়া','Social Media',15,TRUE),
('web-network','ওয়েব ও নেটওয়ার্ক','Web & Network',16,TRUE)
ON CONFLICT (slug) DO UPDATE SET name_bn=EXCLUDED.name_bn, name_en=EXCLUDED.name_en, sort_order=EXCLUDED.sort_order;

COMMIT;
