## 1. Foundations & Config
- [ ] 1.1 Enable Laravel Sanctum (API + SPA middleware) and confirm `personal_access_tokens` indexes align with access/refresh usage.
- [ ] 1.2 Install/configure Laravel Socialite + Google OAuth credentials placeholders, update `.env.example`, and document required secrets.
- [ ] 1.3 Configure mail driver + password reset notification templates per requirements.

## 2. Registration & Local Auth
- [ ] 2.1 Implement `AuthController@register` to create users (name/email/password optional phone/company), auto-login, mark optional terms flag, and seed default permissions/subscription tier counters.
- [ ] 2.2 Implement `AuthController@login` with password verification, rate-limit guard, remember-me flag (drives refresh token lifetime), and `logout`/`logout-all` endpoints.
- [ ] 2.3 Add password reset endpoints (`forgot-password`, `reset-password`) wired to notifications + form validation.

## 3. Token Lifecycle
- [ ] 3.1 Create `AuthTokenService` that mints 2-hour access tokens + long-lived refresh tokens (stored via Sanctum abilities), rotates refresh tokens on use, and revokes tokens on logout.
- [ ] 3.2 Expose `/auth/refresh` endpoint that validates refresh tokens, issues new access tokens, and updates user simulation counters.

## 4. Google OAuth Flow
- [ ] 4.1 Build redirect/callback routes using Socialite, mapping Google profile data to users (auto-create or attach) and issuing tokens.
- [ ] 4.2 Sync avatar/name/email fields on each OAuth login and handle missing email edge cases gracefully.

## 5. Guest Session Restoration
- [ ] 5.1 Extend GuestSessionRepository to fetch/persist 24h form drafts keyed by `session_id`, including server-side copy.
- [ ] 5.2 After login/register, restore guest data via new endpoint (`/simulations/from-guest`) and queue simulation processing automatically.
- [ ] 5.3 Provide frontend hooks (Blade/Alpine) to detect guest session IDs, call restoration endpoint, and show status messaging per UI spec.

## 6. Rate Limiting & Monitoring
- [ ] 6.1 Implement middleware updating `daily_simulation_count` / `last_simulation_date` and enforce tier-specific quotas.
- [ ] 6.2 Emit audit log entries for auth-critical events (register, login, logout, password reset) referencing new audit table.

## 7. Validation
- [ ] 7.1 Write feature tests covering registration, login, refresh, logout, password reset, Google OAuth (mock), and guest restoration flows.
- [ ] 7.2 Document manual verification steps (Postman collection + UI walkthrough) before marking change complete.
