## Change ID
implement-guest-form-management

## Why
- Requirements FR-018 mandates that guest users can access the simulation form without authentication, with form data automatically saved and restored after login/registration.
- Users should be able to fill out the 18-field form as guests, see their progress saved, and seamlessly continue after authentication without losing their work.
- API spec already defines guest form endpoints (`POST /guest/save-form-data`, `GET /guest/session`, `POST /simulations/generate-from-guest`), but the frontend implementation is incomplete.
- The database schema (`guest_sessions` table) and backend repository (`GuestSessionRepository`) are already implemented, but the UI integration and auto-save behavior need to be completed.

## What Changes
- **ADDED** Frontend auto-save functionality that saves form data to localStorage every 2 seconds after changes (debounced), and to backend via API every 30 seconds.
- **ADDED** Visual status indicators showing "Saving...", "Saved", "Error" states with timestamps.
- **ADDED** Guest session restoration flow: After successful login/registration, automatically restore form data and proceed with simulation generation.
- **ADDED** Form data validation and progress tracking (completed steps, form progress percentage).
- **ADDED** Before-unload warning when there are unsaved changes.
- **MODIFIED** Guest form management API endpoints to support step tracking and progress calculation.
- **ADDED** Alpine.js component for form auto-save with debouncing, localStorage fallback, and error handling.

## Impact
- Affected specs: None (new capability for guest form management)
- Affected code:
  - `resources/js/app.js` - Add Alpine.js component for auto-save
  - `resources/views/simulator.blade.php` - Add auto-save integration
  - `app/Http/Controllers/GuestSessionController.php` - New controller for guest endpoints
  - `routes/api.php` - Add guest form endpoints
  - `tests/Feature/GuestFormTest.php` - New test suite for guest form functionality

## Out of Scope
- No changes to simulation processing logic (handled by existing n8n integration).
- No changes to ingredient database or authentication flows (already implemented).
- No admin interface for managing guest sessions (future enhancement).
- No export functionality for guest users (requires authentication).

## Acceptance Criteria
1. Guest users can fill out the 18-field form with data automatically saved every 2 seconds.
2. Form data persists in localStorage and backend (dual storage for reliability).
3. Visual indicators show save status ("Saving...", "Saved", "Error") with timestamps.
4. After successful login/registration, form data is automatically restored and simulation proceeds.
5. Expired sessions (24 hours) are handled gracefully with user notification.
6. Form validation and progress tracking work correctly across all form steps.
7. Before-unload warning appears when there are unsaved changes.
8. All API endpoints are tested with feature tests covering success, error, and edge cases.
