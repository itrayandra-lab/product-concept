## ADDED Requirements

### Requirement: Core Schema Blueprint Finalized
Architectural documentation MUST enumerate every core table (users, guest_sessions, simulation_histories, simulation_ingredients, ingredients, ingredient_categories, scientific_references, ingredient_references, product_types, product_functions, target_demographics, packaging_types, market_cache, user_preferences, audit_logs, personal_access_tokens, plus auxiliary lookup/junction tables) with columns, types, nullability, defaults, soft-delete usage, and JSON/generator fields.

#### Scenario: Schema definitions approved
- **GIVEN** the requirements, API, UI, and workflow specifications
- **WHEN** reviewers inspect the database foundation change
- **THEN** each table’s structure is listed with authoritative details (column name, data type, constraints, soft deletes) and traceable back to `docs/database-schema.md`
- **AND** migration sequencing is documented so Laravel migrations can be implemented without ambiguity.

### Requirement: Integrity & Performance Constraints Specified
Foreign keys, cascading rules, composite and generated-column indexes, partitioning considerations, and cleanup jobs MUST be captured to guarantee data integrity and performance targets (e.g., ≤3 s form submission, ≤120 s pipeline completion).

#### Scenario: Constraint plan complete
- **GIVEN** the schema blueprint
- **WHEN** engineers prepare migrations
- **THEN** every FK indicates its on-update/on-delete behavior, all critical indexes (`idx_simulations_user_recent`, `idx_simulations_filter`, `idx_guest_sessions_cleanup`, `idx_market_cache_lookup`, generated-column indexes for JSON fields, etc.) are documented with purpose, and maintenance strategies (guest session expiry, market cache TTL, audit log rotation, soft-delete purges) are described with cadence/tooling.

### Requirement: Seed & Reference Data Strategy Ready
Lookup tables (product_types, product_functions, target_demographics, packaging_types, ingredient_categories) and other reference data MUST include seeding requirements, initial payload guidelines, and versioning/update strategy.

#### Scenario: Seeder checklist available
- **GIVEN** the list of lookup/config tables
- **WHEN** developers build Laravel seeders
- **THEN** there is a documented checklist describing which tables require seed data, the minimum dataset they must include, how to rerun or extend seeders safely, and how the data ties into validation/UI defaults so later proposals can rely on consistent values.
