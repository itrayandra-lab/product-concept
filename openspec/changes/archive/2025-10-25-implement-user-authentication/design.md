## Overview
This design delivers the full authentication stack required by FR-015–FR-018 using Laravel Sanctum + Socialite. Goals:
- Instant registration with auto-login and optional profile fields.
- Email/password login w/ remember-me, password reset, logout (single/all).
- Google OAuth 2.0 with profile sync.
- Token lifecycle that issues 2-hour access tokens plus refresh tokens.
- Guest-to-auth restoration bridging browser/localStorage and backend guest_sessions table.
- Tier-aware rate limiting counters tied to users table.

## Architecture
```
HTTP Client ──> AuthController ─┬─> AuthTokenService (Sanctum)
                                ├─> SocialiteService (Google)
                                ├─> PasswordResetService (Mail)
                                └─> GuestSessionService ──> SimulationTrigger
```

### Key Components
| Component | Responsibility |
| --- | --- |
| `AuthController` | REST endpoints for register/login/logout/refresh/password reset/OAuth callbacks. |
| `AuthTokenService` | Encapsulates Sanctum token creation, refresh rotation, and revocation. Stores tokens in `personal_access_tokens` with ability flags (`['access']`, `['refresh']`). |
| `GuestSessionService` | Reads/writes guest session form data (bridging browser session_id with DB row) and replays simulation post-auth. |
| `RateLimitMiddleware` | Updates `daily_simulation_count`, enforces tier quotas, and denies overage requests. |
| `AuditLogger` | Writes to `audit_logs` for key auth events. |

## Token Strategy
- **Access Tokens**: Sanctum personal access tokens with ability `['access']`, TTL 2 hours (enforced via `expires_at`). Issued on register/login/OAuth/refresh.
- **Refresh Tokens**: Sanctum tokens with ability `['refresh']`, TTL 30 days (or shorter if remember-me not checked). Stored hashed (Sanctum default). Refresh endpoint validates + rotates (revoke old, issue new pair).
- **Remember Me**: Extends refresh token TTL to 60 days; access token TTL stays 2 hours.
- **Logout**:  
  - `POST /auth/logout` revokes current access token + its paired refresh token.  
  - `POST /auth/logout-all` revokes all tokens for the user (via Sanctum `tokens()->delete()`).

## Google OAuth
- Socialite handles redirect/callback.
- Callback flow: fetch Google profile → find user by email → if missing, create user with null password + provider metadata → sync avatar/name → issue tokens via `AuthTokenService`.
- Edge cases: missing email returns error instructing user to use password registration.

## Guest Session Restoration
1. Guest form stores `session_id` (UUID) in localStorage + backend `guest_sessions`.
2. When guest clicks “Generate”, UI prompts auth and appends `?guest_session=<id>` to redirect URL.
3. After auth completes (register/login/OAuth), frontend calls `/simulations/from-guest` with guest session ID + active token.
4. Backend loads guest payload, associates with authenticated user, creates `simulation_histories` entry, queues n8n workflow, and deletes guest session (or marks restored).
5. UI shows statuses per spec (“Menyimpan…”, “Tersimpan otomatis”, restoration success/failure).

## Rate Limiting
- Middleware executes on simulation-related routes.
- Each request compares `daily_simulation_count` + tier quota (e.g., free=3/day, premium=10/day, enterprise=unlimited).  
- Count resets when `last_simulation_date < today`, else increments.

## Security & Compliance
- Passwords hashed via `Hash::make`.
- Email reset tokens use Laravel default broker; templates clarify “no medical claims”.
- Sanctum tokens hashed + stored; refresh tokens rotate on use to prevent replay.
- Google OAuth credentials stored in `.env`.
- Audit log entries for register/login/logout/password reset for traceability.

## Dependencies
- Requires existing migrations (`users`, `guest_sessions`, `personal_access_tokens`, `audit_logs`).
- Needs Mail provider + queue setup (can default to log driver in dev).
- Google OAuth credentials from GCP; fallback to `.env` placeholders until configured.
