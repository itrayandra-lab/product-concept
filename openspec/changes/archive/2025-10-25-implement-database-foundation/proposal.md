## Change ID
implement-database-foundation

## Why
- Database schema is the first dependency in the sequential delivery plan (`docs/openspec-implementation-guide.md`) and unblocks all downstream capabilities.
- Requirements, API, UI, and n8n docs already lock in 16+ tables plus relationships, so we need a vetted proposal before migrations.
- Defining integrity, indexing, and maintenance guarantees the platform can hit performance targets (≤3 s form submit, ≤120 s processing) and avoid future rewrites.

## What Changes
- Deliver canonical schema blueprint covering users, guest sessions, simulations, ingredients, references, market cache, configuration lookups, audit logs, and token tables.
- Specify all constraints: UTF8MB4 defaults, soft deletes, JSON column usage, FK cascades, composite & generated indexes, and cleanup/partition strategies.
- Document seeding/maintenance plans (lookup data, cache pruning, guest expiry, audit rotation) so later proposals can rely on consistent data.

## Impact
- Unlocks Proposal B (User Authentication) by finalizing the `users` table and token storage.
- Provides stable FK targets for ingredient catalog, simulation engine, market intelligence, and n8n pipeline.
- Establishes storage/performance posture (~1 GB/year, indexed queries) before implementation.

## Out of Scope
- No application code (controllers, services, jobs) beyond what is necessary to describe migrations/seeders.
- No UI, API endpoints, or business logic changes.
- No production deployment steps; focus remains on schema design and documentation.

## Acceptance Criteria
1. ERD + migration plan covers every table and relationship outlined in `docs/database-schema.md`.
2. Each table’s columns, constraints, and indexes are documented with Laravel migration guidance.
3. Seed data + maintenance/cleanup requirements are specified for all lookup/config tables.
4. Spec deltas validate with `openspec validate implement-database-foundation --strict`.
