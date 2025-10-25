# auth Specification

## Purpose
TBD - created by archiving change implement-user-authentication. Update Purpose after archive.
## Requirements
### Requirement: Instant Registration & Password Auth
The system MUST provide email/password registration and login that align with FR-015–FR-017: registration collects name, email, password, optional phone/company, optional terms checkbox, creates the user immediately (no email verification), and auto-issues tokens. Password login SHALL support rate-limited attempts, “remember me” flag, password reset via email, and profile sync (name/phone/company updates allowed from settings).

#### Scenario: Register, auto-login, and reset password
- **GIVEN** a guest user submits the registration form with required fields (name, email, password) plus optional phone/company and unchecked terms
- **WHEN** the registration endpoint validates the payload
- **THEN** a user record is created with the provided optional fields, `terms_accepted` stays false, and the system issues access + refresh tokens and returns them in the response while also authenticating the browser session
- **AND** the password reset flow accepts the same email, sends a reset link, and allows the user to set a new password without re-confirming the account.

### Requirement: Multi-Provider Authentication & Token Lifecycle
Authentication MUST support both local credentials and Google OAuth 2.0. Successful login SHALL sync Google profile data (name/email/avatar), issue 2-hour access tokens and long-lived refresh tokens (rotated on use) via Sanctum, honor “remember me” by extending refresh TTL, and provide endpoints to refresh tokens, logout current session, and logout all sessions. Failed refresh tokens MUST be rejected and revoked.

#### Scenario: Google OAuth login with token refresh
- **GIVEN** a user selects “Masuk dengan Google” and authorizes the app
- **WHEN** Google redirects to the callback
- **THEN** the system either attaches to the existing email or creates a new user with provider metadata, syncs avatar/name, and issues both access and refresh tokens
- **AND** within two hours, the client may call `/auth/refresh` with the refresh token to receive a new access token (old refresh token revoked, new pair returned), while logout endpoints revoke the relevant tokens immediately.

### Requirement: Tier-Based Session Tracking & Rate Limiting
Every authenticated request MUST update `daily_simulation_count` and `last_simulation_date`, enforce per-tier quotas (e.g., free 3/day, premium 10/day, enterprise unlimited), and log auth-critical events (register/login/logout/password reset) into `audit_logs`. Exceeding quotas MUST block simulation-triggering endpoints with an actionable error.

#### Scenario: Enforce quota and audit login
- **GIVEN** a free-tier user has already run 3 simulations today
- **WHEN** they attempt another simulation request
- **THEN** middleware detects the quota breach using `daily_simulation_count` + `last_simulation_date`, responds with HTTP 429 + guidance to upgrade, and records the denial
- **AND** every successful login (local or Google) writes an audit_log row containing the user_id, event_type `login`, and contextual metadata.

### Requirement: Guest Session Restoration
Guest users MUST be able to fill the form, have data autosaved (localStorage + `guest_sessions` table with 24-hour TTL), and upon authenticating, automatically restore the draft, associate it to their account, and resume the simulation. Restoration failures MUST surface clear UI states (`Tersimpan otomatis`, `Tersimpan offline`, error states) per the UI spec.

#### Scenario: Guest generates after login
- **GIVEN** a guest user has `guest_session_id=abc123` stored with saved form data and clicks “Generate”
- **WHEN** they are prompted to login/register and succeed
- **THEN** the frontend sends `/simulations/from-guest` with `guest_session_id=abc123`, the backend rehydrates the form into a new `simulation_histories` record tied to the authenticated user, deletes (or expires) the guest session, and triggers the simulation pipeline
- **AND** the UI shows a success toast indicating data was restored and the simulation is running; if the session expired, the UI instructs the user to refill the form.

