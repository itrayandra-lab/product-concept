## Change ID
implement-user-authentication

## Why
- Requirements FR-015–FR-018 mandate instant registration, password/OAuth login, token-based session management, and guest-to-auth restoration, none of which exist yet.
- API spec already exposes `/auth/*` endpoints, but without an approved implementation plan we risk inconsistent flows, missing rate limits, and no link to the newly provisioned `users` schema.
- Downstream capabilities (guest autosave, simulation quotas, WhatsApp conversion) depend on reliable identity, so we must stabilize auth before touching business logic.

## What Changes
- Deliver a Laravel Sanctum-based auth stack that issues 2-hour access tokens plus refresh tokens, auto-logs users in after registration, and honors optional phone/company/terms fields.
- Implement password login, Google OAuth 2.0 (Socialite) with profile sync, password reset flow, logout (single + all sessions), and “Remember Me” via refresh tokens.
- Bridge guest sessions to authenticated simulations: persist guest form data server-side, restore it post-auth, and continue processing automatically.
- Enforce tier-based rate limiting counters on the `users` table and expose standard auth API endpoints + Blade forms aligning with UI spec.

## Impact
- Unblocks simulator form by providing required auth gate and guest-to-auth flow.
- Enables subsequent proposals (guest management, simulation engine) to rely on standardized tokens and user tiers.
- Introduces Google OAuth + email workflows that require configuration (client IDs, Mail driver) but are necessary for launch UX.

## Out of Scope
- No changes to simulation processing, ingredient catalog, or n8n workflows beyond triggering the restored simulation.
- No subscription billing or tier upgrades (only tracking/limits).
- No admin portal/authz (will come later).

## Acceptance Criteria
1. Specs capture registration, login (password + Google), token lifecycle, password reset, logout, and guest restoration requirements with scenarios.
2. Tasks cover migrations/config, backend services/controllers, OAuth + email setup, guest session integration, UI wiring, and validation (tests + manual flows).
3. Design doc explains token strategy (Sanctum + refresh tokens), guest bridging, and rate-limiting counters referencing the database schema.
4. `openspec validate implement-user-authentication --strict` passes.
