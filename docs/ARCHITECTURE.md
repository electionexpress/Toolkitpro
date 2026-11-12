# ToolkitPro — Product & Platform Architecture

## Product north star
ToolkitPro is a Bengali-first, English-secondary, mobile-first global all-in-one digital tools platform. It should help everyday users, students, creators, office teams, businesses and developers complete common digital tasks in one trusted workspace.

The platform is designed to scale toward **2,500+ registered tools** across 20+ categories. This is a capacity and product roadmap target—not a claim that 2,500 tools are already built or published. Only tools that pass validation and functional QA may appear as published.

## Product principles
- **Useful before numerous:** ship real, reliable tools before growing the catalogue count.
- **Bengali-first, global-ready:** Bangla is the default experience; English metadata and future localization are supported.
- **Mobile-first and accessible:** responsive layouts, keyboard navigation, semantic HTML, visible focus, reduced-motion support and readable contrast.
- **Privacy and safety by design:** collect the minimum data, validate inputs, constrain file handling and never execute arbitrary user/database code.
- **One coherent experience:** shared navigation, search, category taxonomy, tool pages, help patterns and design tokens.
- **Transparent capability labels:** distinguish available, beta, planned and unavailable features.

## Audience and initial scope
- Everyday users: text, image, file, PDF, calculator and conversion tasks.
- Students and educators: learning, writing, research and productivity.
- Creators and small businesses: design, social media, SEO, finance and business utilities.
- Developers and technical users: JSON, encoding, formatting, web and network utilities.

## Category taxonomy
The bilingual catalogue is seeded with 24 categories:
AI; PDF & Documents; Image; Video; Audio; Text & Writing; Calculators; Converters; Developer; SEO & Marketing; Productivity; Security & Privacy; Finance; Business; Education & Research; Web & Network; Data & Spreadsheets; Design & Color; File Management; QR & Barcode; Social Media; GIS & Mapping; Content & Copywriting; Accessibility.

## System architecture
- **Presentation:** server-rendered PHP views, progressive-enhancement JavaScript, responsive CSS, semantic HTML and accessible controls.
- **Application:** PHP 8.3 modular monolith with explicit routes, controllers, services, validation and middleware boundaries.
- **Database:** PostgreSQL categories, users, tool definitions, visibility, lifecycle state, JSON input/output contracts, versions, execution records, import batches, tags, templates and audit logs.
- **Tool registry:** stable slug, category, localized name/description, version, execution type, schemas, status, visibility, runtime key and configuration.
- **Execution:** allow-listed trusted handlers for client/server/API execution. Tool definitions and user input never supply PHP code; no eval or arbitrary code execution.
- **API:** versioned REST endpoints under /api/v1 for catalogue, categories, tool detail and controlled execution.
- **Delivery:** Composer, Docker, Render, GitHub Actions CI and deployment health checks.

## Tool lifecycle and publishing gates
1. Draft — definition and localized metadata are created.
2. Validate — slug uniqueness, category, schema, execution type, size/time limits and handler availability are checked.
3. Review — functionality, privacy, accessibility, security and output quality are tested.
4. Publish — an authorized role approves a tested version; only public active tools enter discovery.
5. Monitor — execution status, duration, failures and user feedback are observed.
6. Disable/archive/rollback — problematic tools can be hidden or reverted without losing version history.

No tool is considered complete solely because a database row exists. A tool needs a working interface, validated inputs, meaningful output, failure states, tests and documentation.

## Admin and user capabilities
### Admin workspace (planned implementation)
- Overview dashboard: published/draft tools, categories, executions, errors and activity.
- Tool manager: create/edit, duplicate, version, preview, test, review, publish, disable and rollback.
- Visual tool builder: schema-driven form/output layout using approved components and trusted handler selection.
- Bulk manager: CSV/JSON import preview, validation report, row-level errors, templates and safe batch publishing.
- Content and SEO: titles, descriptions, canonical metadata, sitemap and localized copy.
- User and access management: Admin, Editor and Moderator roles, least-privilege permissions and audit history.
- Operations: execution analytics, quotas, rate limits and system health.

### User workspace (planned implementation)
- Registration/login, profile and preferences.
- Favorites, recent tools and usage history.
- Free/Premium entitlements, limits and subscription management.
- API access and usage quotas where appropriate.

## Security baseline
- Password hashing, secure sessions, RBAC and CSRF protection for state-changing actions.
- Rate limiting and bounded pagination on public endpoints.
- Strict schema validation, payload limits, MIME/signature checks and upload size limits.
- Allow-listed runtime handlers only; no database-supplied executable code.
- Secrets remain in environment variables and are never committed.
- Public errors reveal no stack traces or internal database details.
- Execution logs store metadata by default, not sensitive user payloads.
- Audit administrative actions and keep publishing reversible.

## Delivery roadmap
- **Phase 1 — Public experience:** responsive landing page, category discovery, live catalogue/search, SEO/accessibility, health and API reliability.
- **Phase 2 — Tool experience:** detail pages, schema-driven interfaces, first 30 genuinely functional tools and per-tool tests.
- **Phase 3 — Admin & governance:** authentication, RBAC, admin dashboard, CRUD, review/publish/disable/rollback and audit log.
- **Phase 4 — Scale operations:** visual builder, templates, bulk import, version control, monitoring and load testing.
- **Phase 5 — User & business platform:** profiles, favorites, history, free/premium plans, ads controls, API keys and usage quotas.
- **Phase 6 — Expansion:** grow toward 2,500+ tools in verified batches across the 24+ categories.

## Current verified state
- Public homepage, responsive styles, theme toggle and catalogue search/filter/pagination are present.
- Public catalogue/category/detail-read API foundation and PostgreSQL schema are present.
- Trusted server runtime and input validator foundation are present.
- GitHub Actions CI and Docker/Render deployment are configured.
- The Docker build dependency for PHP mbstring was corrected by installing libonig-dev; the Render deploy for commit e8cafa2 was reported live.
- The 24-category seed and migration have been added to the repository. They must be applied to the connected PostgreSQL database before the category grid can show all 24.
- Admin dashboard, visual builder, user workspace, production database migration execution and 30 functional tools are not yet verified as complete.

## Release checklist
- Run migrations and seed in order on the intended PostgreSQL database.
- Verify /health and API responses, including empty/error states.
- Run composer validate, composer check, composer test and JavaScript syntax checks.
- Test mobile/desktop, keyboard access, dark mode and reduced motion.
- Test every published tool's valid/invalid inputs, output, failure path and privacy behavior.
- Confirm Render deploy is live and inspect application/build logs before announcing release.
