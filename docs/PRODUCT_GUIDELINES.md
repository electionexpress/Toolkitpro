# ToolkitPro — Product Guidelines

## North star
ToolkitPro should become a Bengali-first, globally usable all-in-one digital tools workspace. Users should be able to discover, understand and complete common digital tasks with as few steps as possible.

## Non-negotiable principles
1. Do not publish a non-functional tool or inflate live tool counts.
2. Bengali-first content, with English names and metadata for international discovery.
3. Mobile-first, fast, accessible and responsive UI.
4. Consistent design and interaction patterns across all tool pages.
5. Privacy-first handling of text, files and user data.
6. Safe, allow-listed execution only—never eval database or user-supplied code.
7. Every tool must have a clear purpose, input validation, output, reset/copy/download behavior where relevant, error states and tests.
8. Keep free and premium entitlements explicit; do not imply a subscription feature is active before it exists.

## Target audiences
Everyday users, students, educators, creators, small businesses, office teams and developers.

## Category taxonomy
AI; PDF & Documents; Image; Video; Audio; Text & Writing; Calculators; Converters; Developer; SEO & Marketing; Productivity; Security & Privacy; Finance; Business; Education & Research; Web & Network; Data & Spreadsheets; Design & Color; File Management; QR & Barcode; Social Media; GIS & Mapping; Content & Copywriting; Accessibility.

## Tool acceptance checklist
- Bilingual name and useful description
- Stable slug, category, semantic version and ownership
- Defined input/output contract
- Trusted execution handler and bounded resource use
- Responsive interface with keyboard and screen-reader support
- Valid, invalid, boundary and failure-path tests
- Privacy note and file retention policy where applicable
- Usage logging that avoids sensitive payloads by default
- Review approval before public publishing

## Scale target
The 2,500+ figure is a target for registered tool capacity. Build the catalogue, registry, bulk import, versioning and governance so growth can be managed safely. Never present planned or draft tools as published tools.

## Release gates
A release is ready only after CI passes, migrations are checked, the Render deploy is live, health/API smoke tests pass and the affected user flows are verified.
