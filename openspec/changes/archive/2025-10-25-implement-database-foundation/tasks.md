## 1. Schema Documentation
- [x] 1.1 Cross-reference `docs/database-schema.md` with requirements/api/ui specs to confirm table list and relationships.
- [x] 1.2 Define column types, nullability, defaults, soft-delete usage, JSON fields, and generated columns per table.

## 2. Migration & Index Plan
- [x] 2.1 Outline Laravel migration order (core entities + supporting tables + junction tables).
- [x] 2.2 Enumerate all PK/FK constraints plus composite/virtual indexes required for performance (`idx_simulations_user_recent`, `idx_market_cache_lookup`, etc.).
- [x] 2.3 Capture cascade behaviors and table options (utf8mb4 charset/collation, partition-ready structure) for future migrations.

## 3. Data Integrity & Maintenance
- [x] 3.1 Document cleanup jobs (guest session expiry, market cache TTL, audit log rotation, soft-delete purges) with cadence/tooling expectations.
- [x] 3.2 Describe how error logging/status tracking fields (e.g., n8n workflow IDs, error_details) integrate with monitoring.

## 4. Seeders & Reference Data
- [x] 4.1 Identify lookup tables needing seeders (product_types, product_functions, target_demographics, packaging_types, ingredient_categories).
- [x] 4.2 Define initial payload expectations, versioning strategy, and how seed data supports validations/UI defaults.

## 5. Validation & Handoff
- [x] 5.1 Write spec deltas in `specs/database/schema/spec.md` with scenarios covering schema, constraints, and seeding strategy.
- [x] 5.2 Run `openspec validate implement-database-foundation --strict` and resolve findings.
- [x] 5.3 Summarize dependencies + outputs for Proposal B (User Authentication) before requesting approval.
